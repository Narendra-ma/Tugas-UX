<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->isOwner() ? route('owner.dashboard') : route('garages.index'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    // Pendaftaran publik hanya untuk penyewa. Akun pemilik dibuat lewat seeder / tinker.
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = new User($request->validated());
        $user->role = 'tenant';
        $user->save();

        Auth::login($user);

        return redirect()->route('garages.index')->with('ok', 'Akun berhasil dibuat.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('garages.index');
    }
}
