<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Admin\ActivityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    public function pinLogin(Request $request)
    {
        $request->validate(['pin' => 'required|digits:4']);

        $user = User::where('pin', $request->pin)
            ->where('role', 'cashier')
            ->where('status', 'active')
            ->first();

        if (!$user) {
            return back()->with('error', 'Invalid PIN');
        }

        Auth::login($user);
        $request->session()->regenerate();

        ActivityService::log('user_login', ' logged in', 'Auth', 'info');

        return redirect('/cashier/pos');
    }

    public function authenticated(Request $request, $user)
    {
        $user->update([
            'is_online' => true,
            'last_login' => now(),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Check if user is inactive
        $user = User::where('email', $request->email)->first();
        if ($user && $user->status === 'inactive') {
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Contact admin.',
            ]);
        }

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();
        // Set user online

        Auth::user()->update(['last_login' => now()]);

        Cache::put('user-online-' . Auth::id(), true, now()->addMinutes(1));


        ActivityService::log('user_login', Auth::user()->name . ' logged in', 'Auth', 'info');

        // Redirect based on role
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('cashier.pos');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            Cache::forget('user-online-' . $user->id);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        ActivityService::log('user_logout', ' logged out', 'Auth', 'info');

        return redirect('/');
    }
}
