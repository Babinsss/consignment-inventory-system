<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $sampleUsers = User::where('is_active', true)->orderBy('id')->get();
        return view('auth.login', compact('sampleUsers'));
    }

    /**
     * Authenticate credentials
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            return redirect()->intended(route('dashboard'))
                ->with('success', "Welcome back, {$user->name}! Logged in as {$user->role_label}.");
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our hospital staff records.',
        ])->onlyInput('email');
    }

    /**
     * Log user out
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out securely.');
    }

    /**
     * 1-Click Fast Role Switcher (For evaluation & multi-role operations)
     */
    public function switchRole(Request $request)
    {
        $role = $request->input('role');
        $validRoles = [User::ROLE_ADMIN, User::ROLE_MEDICAL_SUPPLY, User::ROLE_PHARMACY, User::ROLE_FINANCE];

        if (!in_array($role, $validRoles, true)) {
            return back()->with('error', 'Invalid role requested.');
        }

        $targetUser = User::where('role', $role)->where('is_active', true)->first();

        if (!$targetUser) {
            return back()->with('error', "No active staff account found for role: {$role}");
        }

        Auth::login($targetUser);
        $request->session()->regenerate();

        return back()->with('success', "Switched active staff persona to {$targetUser->name} ({$targetUser->role_label}) • {$targetUser->department}");
    }
}
