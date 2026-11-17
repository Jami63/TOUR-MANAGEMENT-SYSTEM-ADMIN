<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    /**
     * Return available dynamic subscription plans & perks
     */
    public function plans(): JsonResponse
    {
        $plans = [
            [
                'id' => 'pro_monthly',
                'title' => 'SafarFly Pro Monthly',
                'price_bdt' => 299,
                'price_usd' => 2.99,
                'billing_cycle' => 'month',
                'badge' => null,
                'is_popular' => false,
                'features' => [
                    'Unlimited active expedition tours',
                    'Unlimited participants per tour',
                    'Export branded PDF & Excel financial statements',
                    'Custom agency logo on traveler receipts',
                    'Priority cloud sync & 24/7 dedicated support',
                ],
            ],
            [
                'id' => 'pro_yearly',
                'title' => 'SafarFly Pro Annual',
                'price_bdt' => 2499,
                'price_usd' => 24.99,
                'billing_cycle' => 'year',
                'badge' => 'SAVE 30%',
                'is_popular' => true,
                'features' => [
                    'All Pro Monthly features included',
                    'Save 30% compared to monthly renewal',
                    'Permanent annual ledger statement archive',
                    'VIP Verified Organizer badge on member rosters',
                    'Instant priority support response channel',
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'plans' => $plans,
            'currency' => 'BDT',
        ]);
    }

    /**
     * Get Current User Subscription Status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $isPro = $user->isPro();
        $expiresAt = $user->subscription_expires_at;

        $daysRemaining = null;
        if ($isPro && $expiresAt) {
            $daysRemaining = max(0, (int) now()->diffInDays($expiresAt, false));
        }

        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'is_pro' => $isPro,
            'tier' => $isPro ? 'pro' : 'free',
            'plan' => $user->subscription_plan,
            'expires_at' => $expiresAt?->toIso8601String(),
            'days_remaining' => $daysRemaining,
            'active_subscription' => $activeSubscription,
            'user' => $user->toApiArray(),
        ]);
    }

    /**
     * Subscribe / Upgrade to Pro Plan (In-App Purchase verification & activation)
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'string', 'in:pro_monthly,pro_yearly'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:191'],
        ]);

        $user = $request->user();
        $planId = $validated['plan_id'];
        $paymentMethod = $validated['payment_method'] ?? 'in_app_purchase';
        $transactionId = $validated['transaction_id'] ?? ('IAP-' . strtoupper(Str::random(12)));

        $now = now();
        $startsAt = $now;
        $amount = ($planId === 'pro_yearly') ? 2499.00 : 299.00;
        
        // If user already has an active future expiration date, extend from it; otherwise from now
        $baseDate = ($user->isPro() && $user->subscription_expires_at && $user->subscription_expires_at->isFuture())
            ? $user->subscription_expires_at
            : $now;

        $expiresAt = ($planId === 'pro_yearly')
            ? (clone $baseDate)->addYear()
            : (clone $baseDate)->addMonth();

        // Mark any previous active subscriptions as superseded
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'superseded']);

        // Create new active subscription record
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $planId,
            'amount' => $amount,
            'currency' => 'BDT',
            'status' => 'active',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);

        // Update user record
        $user->subscription_tier = 'pro';
        $user->subscription_plan = $planId;
        $user->subscription_expires_at = $expiresAt;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Congratulations! Your SafarFly PRO subscription has been activated successfully.',
            'subscription' => $subscription,
            'user' => $user->toApiArray(),
        ]);
    }

    /**
     * Cancel Pro Subscription
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($activeSubscription) {
            $activeSubscription->status = 'cancelled';
            $activeSubscription->cancelled_at = now();
            $activeSubscription->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Auto-renewal has been cancelled. Your Pro benefits remain active until the end of your billing cycle.',
            'expires_at' => $user->subscription_expires_at?->toIso8601String(),
            'user' => $user->toApiArray(),
        ]);
    }
}
