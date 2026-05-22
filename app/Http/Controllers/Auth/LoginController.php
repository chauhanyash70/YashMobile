<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Stage 1: Validate credentials and send OTP.
     */
    public function loginPreCheck(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        // Verify credentials using Laravel's Auth guard validator
        if (!Auth::validate(['email' => $email, 'password' => $password])) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }

        // Store pre-auth state in session
        session([
            'otp_user_id' => $user->id,
            'otp_remember' => $request->filled('remember')
        ]);

        // Trigger OTP dispatch
        return $this->send2FaOtp($user);
    }

    /**
     * Resend OTP code.
     */
    public function resendOtp(Request $request)
    {
        $userId = session('otp_user_id');
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication session has expired. Please enter your email and password again.'
            ], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found. Please log in again.'
            ], 400);
        }

        return $this->send2FaOtp($user, true);
    }

    /**
     * Helper: Generate, cache, and send OTP email.
     */
    protected function send2FaOtp(User $user, $isResend = false)
    {
        $cooldownKey = 'otp_cooldown_' . $user->id;

        // Enforce 60 seconds rate limit
        if (Cache::has($cooldownKey)) {
            $timeLeft = Cache::get($cooldownKey) - time();
            if ($timeLeft > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Please wait {$timeLeft} seconds before requesting a new code."
                ], 429);
            }
        }

        try {
            // Generate 4-digit secure code
            $otp = random_int(1000, 9999);

            // Cache OTP for 10 minutes (600 seconds)
            Cache::put('otp_code_' . $user->id, $otp, now()->addMinutes(10));

            // Set cooldown timestamp
            Cache::put($cooldownKey, time() + 60, now()->addSeconds(60));

            // Send Mail
            Mail::to($user->email)->send(new SendOtpMail($otp, $user));

            $message = $isResend 
                ? 'A new verification code has been successfully sent to your email.'
                : 'Credentials verified! A 4-digit verification code has been sent to your email.';

            return response()->json([
                'success' => true,
                'requires_otp' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('2FA OTP Send Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again.'
            ], 500);
        }
    }

    /**
     * Stage 2: Verify OTP and log user in fully.
     */
    public function verify2FaOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:4',
        ]);

        $userId = session('otp_user_id');
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication session has expired. Please enter your email and password again.'
            ], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found. Please log in again.'
            ], 400);
        }

        $otpInput = $request->otp;
        $cachedOtp = Cache::get('otp_code_' . $userId);

        if (!$cachedOtp || $cachedOtp != $otpInput) {
            return response()->json([
                'success' => false,
                'message' => 'The entered verification code is incorrect or has expired.'
            ], 422);
        }

        // Authenticate the user
        Auth::loginUsingId($userId, session('otp_remember', false));

        // Clear pre-auth session and cache keys
        session()->forget(['otp_user_id', 'otp_remember']);
        Cache::forget('otp_code_' . $userId);
        Cache::forget('otp_cooldown_' . $userId);

        // Regenerate session to protect against session hijacking
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Verification successful! Redirecting to dashboard...',
            'redirect' => route('home')
        ]);
    }
}

