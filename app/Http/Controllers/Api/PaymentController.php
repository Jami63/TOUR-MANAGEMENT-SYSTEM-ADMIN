<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseShare;
use App\Models\Payment;
use App\Models\PaymentVerification;
use App\Models\TourTimeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Submit partial or full payment for an expense share (Tour Member)
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'share_id' => ['required', 'exists:expense_shares,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'in:bKash,Nagad,Cash'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'proof_file' => ['nullable', 'image', 'max:5120'], // 5MB max
        ]);

        $share = ExpenseShare::with('tourMember')->find($validated['share_id']);

        if ($share->tourMember->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized for this expense share'], 403);
        }

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('payment_proofs', 'public');
        }

        $payment = Payment::create([
            'share_id' => $share->id,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'proof_file' => $proofPath,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment submitted successfully and waiting for Tour Manager verification',
            'payment' => $payment,
        ], 201);
    }

    /**
     * List all pending payments for Tour Manager to verify
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Only Tour Managers can view pending payments'], 403);
        }

        $payments = Payment::with(['share.expense.tour', 'share.tourMember.user'])
            ->where('status', 'pending')
            ->latest('submitted_at')
            ->get();

        return response()->json([
            'success' => true,
            'payments' => $payments,
        ]);
    }

    /**
     * Verify or Reject a payment (Tour Manager)
     */
    public function verify(Request $request, int $paymentId): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Only Tour Managers can verify payments'], 403);
        }

        $payment = Payment::with('share.expense.tour')->find($paymentId);
        if (! $payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:verified,rejected'],
            'reject_reason' => ['nullable', 'string'],
        ]);

        $payment->update([
            'status' => $validated['status'],
        ]);

        PaymentVerification::updateOrCreate(
            ['payment_id' => $payment->id],
            [
                'verified_by' => $user->id,
                'reject_reason' => $validated['reject_reason'] ?? null,
                'decided_at' => now(),
            ]
        );

        // Timeline event
        TourTimeline::create([
            'tour_id' => $payment->share->expense->tour_id,
            'event_type' => 'payment_' . $validated['status'],
            'description' => "Payment of ৳{$payment->amount} was {$validated['status']} by {$user->full_name}",
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Payment has been marked as {$validated['status']}",
            'payment' => $payment->load('verification'),
        ]);
    }

    /**
     * Get member's payment history (all or filtered by tour)
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $tourId = $request->query('tour_id');

        $query = Payment::with([
            'share.expense.tour',
            'share.expense.category',
            'verification.verifier',
        ])
        ->whereHas('share.tourMember', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        if ($tourId) {
            $query->whereHas('share.expense', function ($q) use ($tourId) {
                $q->where('tour_id', $tourId);
            });
        }

        $payments = $query->latest('submitted_at')->get();

        $paymentsData = $payments->map(function ($p) {
            return [
                'id' => $p->id,
                'share_id' => $p->share_id,
                'tour_id' => $p->share?->expense?->tour_id,
                'tour_name' => $p->share?->expense?->tour?->tour_name ?? 'Tour',
                'expense_name' => $p->share?->expense?->expense_name ?? 'General Expense',
                'category_name' => $p->share?->expense?->category?->category_name ?? 'General',
                'amount' => (float) $p->amount,
                'method' => $p->method,
                'transaction_id' => $p->transaction_id,
                'proof_file' => $p->proof_file,
                'status' => $p->status,
                'submitted_at' => $p->submitted_at?->format('d M, Y h:i A') ?? $p->created_at?->format('d M, Y h:i A'),
                'verified_at' => $p->verification?->decided_at?->format('d M, Y h:i A'),
                'verifier_name' => $p->verification?->verifier?->full_name,
                'reject_reason' => $p->verification?->reject_reason,
            ];
        });

        $totalSubmitted = $paymentsData->sum('amount');
        $totalVerified = $paymentsData->where('status', 'verified')->sum('amount');
        $totalPending = $paymentsData->where('status', 'pending')->sum('amount');
        $totalRejected = $paymentsData->where('status', 'rejected')->sum('amount');

        return response()->json([
            'success' => true,
            'summary' => [
                'total_submitted' => (float) $totalSubmitted,
                'total_verified' => (float) $totalVerified,
                'total_pending' => (float) $totalPending,
                'total_rejected' => (float) $totalRejected,
                'total_count' => $paymentsData->count(),
            ],
            'payments' => $paymentsData,
        ]);
    }

    /**
     * Get specific payment details with verification proof
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $payment = Payment::with([
            'share.expense.tour',
            'share.expense.category',
            'verification.verifier',
        ])->find($id);

        if (! $payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        $isOwner = $payment->share?->tourMember?->user_id === $user->id;
        $isManager = $payment->share?->expense?->tour?->created_by === $user->id || $user->role === 'manager';

        if (! $isOwner && ! $isManager) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'payment' => [
                'id' => $payment->id,
                'share_id' => $payment->share_id,
                'tour_id' => $payment->share?->expense?->tour_id,
                'tour_name' => $payment->share?->expense?->tour?->tour_name ?? 'Tour',
                'expense_name' => $payment->share?->expense?->expense_name ?? 'General Expense',
                'category_name' => $payment->share?->expense?->category?->category_name ?? 'General',
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'transaction_id' => $payment->transaction_id,
                'proof_file' => $payment->proof_file,
                'status' => $payment->status,
                'submitted_at' => $payment->submitted_at?->format('d M, Y h:i A') ?? $payment->created_at?->format('d M, Y h:i A'),
                'verified_at' => $payment->verification?->decided_at?->format('d M, Y h:i A'),
                'verifier_name' => $payment->verification?->verifier?->full_name,
                'reject_reason' => $payment->verification?->reject_reason,
            ],
        ]);
    }
}
