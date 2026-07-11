<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\CardNumber;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseActivationController extends Controller
{
    use ApiResponse;

    // POST /courses/{id}/activate
    public function activate(Request $request, int $courseId): JsonResponse
    {
        $request->validate([
            'card_code' => ['required', 'string', 'max:100'],
        ]);

        $course = Course::where('is_published', true)->findOrFail($courseId);
        $student = $request->user();

        // Check if already enrolled
        $existing = Enrollment::where('student_id', $student->id)
            ->where('course_id', $courseId)
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return $this->error('أنت مسجل في هذه الدورة بالفعل', 422);
        }

        // Find valid card: active, not used, sold
        $card = CardNumber::where('number', trim($request->card_code))
            ->where('activate', 1)
            ->where('status', 2)
            ->where('sell', 2)
            ->first();

        if (! $card) {
            return $this->error('رمز البطاقة غير صحيح أو تم استخدامه مسبقاً', 422);
        }

        DB::transaction(function () use ($student, $courseId, $card) {
            Enrollment::create([
                'student_id'          => $student->id,
                'course_id'           => $courseId,
                'enrolled_at'         => now(),
                'is_active'           => true,
                'is_completed'        => false,
                'progress_percentage' => 0,
            ]);

            $card->update([
                'status'           => 1,
                'sell'           => 1,
                'assigned_user_id' => $student->id,
            ]);
        });

        return $this->success([
            'course_id'   => $courseId,
            'course_name' => $course->title,
        ], 'تم تفعيل الدورة بنجاح! يمكنك البدء الآن', 201);
    }
}
