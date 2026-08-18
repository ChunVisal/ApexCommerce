<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    public function showPhoneForm()
    {
        // check if user already login
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();

        // Only admin can use forgot password
        if (!$user || $user->role !== 'admin') {
            return back()->withErrors([
                'phone' => 'Password reset is only available for administrators.',
            ]);
        }

        // generate a random 6-digit code
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // TEMPORARY - "sends" the OTP by writing it to the log instead of real SMS
        Log::info('OTP for password reset', [
            'phone' => $user->phone,
            'otp' => $otp,
        ]);

        // remember this phone number for the next step (verify OTP page)
        session(['reset_phone' => $request->phone]);

        return redirect()->route('password.verify-otp')->with('status', 'A code has been sent to your phone.');
    }

    public function showOtpForm()
    {

        // If already logged in normally, don't allow password-reset OTP page
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $phone = session('reset_phone');

        // If dont have phone session go to login
        if (!session('reset_phone')) {
            return redirect()->route('login');
        }

        // Find the user whose phone number equals the phone stored in the session
        $user = User::where('phone', $phone)->first();

        // Check Only admin number can access OTP verification
        if (!$user || $user->role !== 'admin') {
            session()->forget('reset_phone');
        }

        // NEW — if there's no active OTP for this user anymore, the session value is stale
        if (!$user->otp_code) {
            session()->forget('reset_phone');
        }

        return view('auth.verify-otp', [
            'otpExpiresAt' => $user->otp_expires_at,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $phone = session('reset_phone');
        $user = User::where('phone', $phone)->first();

        // Only admin can reset password
        if (!$user || $user->role !== 'admin') {
            session()->forget('reset_phone');

            return redirect()->route('login');
        }

        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        if ($user->otp_code !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'This code has expired. Please request a new one.']);
        }
        // OTP verified — log the admin in, clear the OTP so it can't be reused
        Auth::login($user);
        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        session()->forget('reset_phone');

        return redirect()->route('password.reset-or-skip');
    }

    public function showResetForm()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.dashboard')->with('status', 'Password updated successfully.');
    }
}
