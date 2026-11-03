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
}
