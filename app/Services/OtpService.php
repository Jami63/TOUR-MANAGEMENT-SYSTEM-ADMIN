<?php

namespace App\Services;

use App\Mail\OtpVerificationMail;
use App\Models\EmailOtpVerification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Generate a new 6-digit OTP, store payload, and dispatch email.
     */
    public function generateAndSendOtp(string $email, array $payload): array
    {
        $normalizedEmail = strtolower(trim($email));

        // Generate secure 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Delete any expired or pending unverified OTPs for this email
        EmailOtpVerification::where('email', $normalizedEmail)
            ->whereNull('verified_at')
            ->delete();

        // Create new record with 10 minutes expiry
        $record = EmailOtpVerification::create([
            'email' => $normalizedEmail,
            'otp' => $otp,
            'payload' => $payload,
            'expires_at' => now()->addMinutes(10),
        ]);

        $fullName = $payload['full_name'] ?? 'User';

        try {
            Mail::to($normalizedEmail)->send(new OtpVerificationMail($otp, $fullName));
            Log::info("SafarFly OTP successfully dispatched to {$normalizedEmail}");
        } catch (\Throwable $e) {
            // Delete the unverified OTP record so an unsent OTP cannot be verified
            $record->delete();
            Log::error("SMTP Delivery Failed for {$normalizedEmail}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Unable to send verification email: ' . $e->getMessage() . '. Please verify your SMTP email configuration in .env.',
                'email' => $normalizedEmail,
            ];
        }

        return [
            'success' => true,
            'message' => 'A 6-digit verification code has been sent to ' . $normalizedEmail . '. Please check your inbox.',
            'email' => $normalizedEmail,
            'expires_in_minutes' => 10,
        ];
    }

    /**
     * Verify the candidate OTP against the stored record.
     */
    public function verifyOtp(string $email, string $candidateOtp): ?array
    {
        $normalizedEmail = strtolower(trim($email));
        $candidate = trim($candidateOtp);

        $record = EmailOtpVerification::where('email', $normalizedEmail)
            ->whereNull('verified_at')
            ->latest('created_at')
            ->first();

        if (! $record) {
            return null;
        }

        if ($record->isExpired()) {
            return null;
        }

        if (! hash_equals($record->otp, $candidate)) {
            return null;
        }

        // Mark as verified
        $record->update(['verified_at' => now()]);

        return $record->payload;
    }

    /**
     * Resend a fresh OTP for a pending registration.
     */
    public function resendOtp(string $email): array
    {
        $normalizedEmail = strtolower(trim($email));

        $record = EmailOtpVerification::where('email', $normalizedEmail)
            ->whereNull('verified_at')
            ->latest('created_at')
            ->first();

        if (! $record || empty($record->payload)) {
            return [
                'success' => false,
                'message' => 'No pending registration found for this email. Please register again.',
            ];
        }

        // 60 seconds cooldown check
        if ($record->created_at->diffInSeconds(now()) < 60) {
            $remaining = 60 - $record->created_at->diffInSeconds(now());
            return [
                'success' => false,
                'message' => "Please wait {$remaining} seconds before requesting a new code.",
            ];
        }

        return $this->generateAndSendOtp($normalizedEmail, $record->payload);
    }
}
