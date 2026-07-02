<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:200'],
            'email'      => ['required', 'email', 'max:200', 'unique:students,email'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'password'   => ['required', 'confirmed', Password::min(8)],
            'class_id'   => ['nullable', 'exists:classes,id'],
        ]);

        $student = Student::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'password'  => $validated['password'],
            'class_id'  => $validated['class_id'] ?? null,
            'is_active' => true,
        ]);

        $token = $student->createToken('student-app')->plainTextToken;

        return $this->success([
            'token'   => $token,
            'student' => $this->studentData($student),
        ], 'Registered successfully', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $student = Student::where('email', $request->email)->first();

        if (! $student || ! Hash::check($request->password, $student->password)) {
            return $this->error('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
        }

        if (! $student->is_active) {
            return $this->error('الحساب موقوف، تواصل مع الإدارة', 403);
        }

        $token = $student->createToken('student-app')->plainTextToken;

        return $this->success([
            'token'   => $token,
            'student' => $this->studentData($student->load('schoolClass')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'تم تسجيل الخروج');
    }

    private function studentData(Student $student): array
    {
        return [
            'id'         => $student->id,
            'name'       => $student->name,
            'email'      => $student->email,
            'phone'      => $student->phone,
            'avatar'     => $student->avatar ? asset('assets/uploads/' . $student->avatar) : null,
            'class'      => $student->schoolClass?->name,
            'class_id'   => $student->class_id,
            'gender'     => $student->gender,
            'is_active'  => $student->is_active,
        ];
    }
}
