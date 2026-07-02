<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class StudentAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('home');
        }

        return view('front.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('student')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __('front.auth_login_error')]);
    }

    public function showRegister()
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('home');
        }

        return view('front.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:200'],
            'email'                 => ['required', 'email', 'max:200', 'unique:students,email'],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'password'              => ['required', 'confirmed', Password::min(8)],
            'grade_level'           => ['nullable', 'integer', 'min:1', 'max:12'],
            'terms'                 => ['accepted'],
        ]);

        $student = Student::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'phone'       => $validated['phone'] ?? null,
            'password'    => $validated['password'],
            'grade_level' => $validated['grade_level'] ?? null,
            'is_active'   => true,
        ]);

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('register_success', __('front.auth_register_success'));
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
