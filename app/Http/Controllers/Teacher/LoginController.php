<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('teacher.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'national_id' => ['required', 'string'],
            'password'    => ['required', 'string', 'min:6'],
        ]);

        $teacher = Teacher::where('national_id', $request->national_id)->first();

        if (! $teacher || ! Hash::check($request->password, $teacher->password)) {
            return back()
                ->withInput($request->only('national_id', 'remember'))
                ->withErrors(['national_id' => 'الرقم الوطني أو كلمة المرور غير صحيحة']);
        }

        if (! $teacher->is_active) {
            return back()->withErrors(['national_id' => 'الحساب موقوف، تواصل مع الإدارة']);
        }

        auth()->guard('teacher')->login($teacher, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('teacher.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->guard('teacher')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('teacher.showlogin');
    }
}
