<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['nullable', 'in:user,admin'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'user',
            'password' => Hash::make($data['password']),
        ]);

        $this->sendOtp($user);

        return response()->json([
            'message' => 'Account created. Please verify the OTP sent to your email.',
            'user' => $user->fresh(),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Email not verified. Please complete OTP verification.',
            ], 423);
        }

        Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $credentials['remember'] ?? false
        );

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'Account not found.'], 404);
        }

        if ($user->otp_code !== $data['otp']) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        if ($user->otp_expires_at && $user->otp_expires_at->isPast()) {
            return response()->json(['message' => 'OTP code has expired.'], 410);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        return response()->json([
            'message' => 'Email verification complete.',
            'user' => $user->fresh(),
        ]);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        $this->sendOtp($user);

        return response()->json(['message' => 'A new OTP has been sent to your email.']);
    }

    protected function sendOtp(User $user): string
    {
        $otp = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_last_sent_at' => now(),
            'email_verified_at' => null,
        ])->save();

        Mail::to($user->email)->send(new OtpMail($otp));

        return $otp;
    }
}
