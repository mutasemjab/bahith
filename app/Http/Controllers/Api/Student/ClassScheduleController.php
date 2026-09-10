<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\ClassSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    use ApiResponse;

    // GET /class-schedule — returns the schedule image for the logged-in student's class
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        if (! $student->class_id) {
            return $this->success(null, 'لا يوجد صف محدد لهذا الطالب');
        }

        $schedule = ClassSchedule::where('class_id', $student->class_id)->first();

        if (! $schedule) {
            return $this->success(null, 'لم يتم رفع جدول الحصص لهذا الصف بعد');
        }

        return $this->success([
            'id'       => $schedule->id,
            'class_id' => $schedule->class_id,
            'image'    => asset('assets/uploads/class-schedules/' . $schedule->image),
        ]);
    }
}
