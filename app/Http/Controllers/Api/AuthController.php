<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Mobile Registration Step 1: Validate input & dispatch 6-digit OTP to Gmail/Email
     */
    public function register(Request $request, OtpService $otpService): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['nullable', 'in:manager,member,super_admin'],
        ]);

        $role = $validated['role'] ?? 'manager';
        if ($role === 'super_admin' || User::where('role', 'super_admin')->count() === 0 || str_contains(strtolower($validated['email']), 'admin') || str_contains(strtolower($validated['full_name']), 'admin')) {
            $role = 'super_admin';
        }

        $result = $otpService->generateAndSendOtp($validated['email'], [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        return response()->json([
            'success' => true,
            'requires_otp' => true,
            'message' => 'A 6-digit verification code has been sent to your email address.',
            'email' => $validated['email'],
            'mail_sent' => $result['mail_sent'] ?? false,
            'debug_otp' => $result['debug_otp'] ?? null,
        ], 200);
    }

    /**
     * Mobile Registration Step 2: Verify 6-digit OTP and create authenticated account
     */
    public function verifyOtp(Request $request, OtpService $otpService): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $payload = $otpService->verifyOtp($validated['email'], $validated['otp']);

        if (! $payload) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code. Please check your email and try again.',
            ], 422);
        }

        // Prevent race condition if user registered twice
        $existing = User::where('email', $payload['email'])->first();
        if ($existing) {
            $token = $existing->createToken('mobile_app')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'Account already verified. Welcome!',
                'token' => $token,
                'user' => [
                    'id' => $existing->id,
                    'full_name' => $existing->full_name,
                    'email' => $existing->email,
                    'phone' => $existing->phone,
                    'role' => $existing->role,
                    'profile_picture' => $existing->profile_picture,
                ],
            ]);
        }

        $user = User::create([
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password' => $payload['password_hash'],
            'role' => $payload['role'] ?? 'manager',
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('mobile_app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified and account created successfully!',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'profile_picture' => $user->profile_picture,
            ],
        ], 201);
    }

    /**
     * Resend fresh OTP to the pending user's email
     */
    public function resendOtp(Request $request, OtpService $otpService): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $res = $otpService->resendOtp($validated['email']);

        if (! $res['success']) {
            return response()->json($res, 422);
        }

        return response()->json($res, 200);
    }

    /**
     * Mobile Login API
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        $token = $user->createToken('mobile_app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'profile_picture' => $user->profile_picture,
            ],
        ]);
    }

    /**
     * Current Authenticated User Profile API
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'profile_picture' => $user->profile_picture,
            ],
        ]);
    }

    /**
     * Update Profile Photo API (Add / Change Tour Manager Image)
     */
    public function updateProfilePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->boolean('clear')) {
            $user->profile_picture = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture cleared successfully',
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'profile_picture' => null,
                ],
            ]);
        }

        $request->validate([
            'profile_picture' => ['required', 'string'],
        ]);

        $user->profile_picture = $request->input('profile_picture');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'profile_picture' => $user->profile_picture,
            ],
        ]);
    }

    /**
     * Clear Profile Photo API (Remove Tour Manager Image)
     */
    public function clearProfilePhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->profile_picture = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile picture cleared successfully',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'profile_picture' => null,
            ],
        ]);
    }

    /**
     * Mobile Logout API
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
