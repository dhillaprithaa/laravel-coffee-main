<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard.index');
        }

        return view('auth.index');
    }

    /**
     * Handle login form submission.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        if (Auth::attempt($request->only('username', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard.index'));
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->only('username'));
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.index');
    }
}
