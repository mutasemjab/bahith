<?php

namespace App\Services;

use App\Http\Controllers\Admin\FCMController;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\StudentNotification;

class ContentNotificationService
{
    /**
     * Notify all active enrolled students when new content is added to a course.
     */
    public static function notifyCourse(int $courseId, string $title, string $body): void
    {
        $studentIds = Enrollment::where('course_id', $courseId)
            ->where('is_active', true)
            ->pluck('student_id');

        if ($studentIds->isEmpty()) {
            return;
        }

        foreach ($studentIds as $studentId) {
            StudentNotification::create([
                'student_id' => $studentId,
                'title'      => $title,
                'body'       => $body,
                'type'       => 'course_content',
            ]);
        }

        // Push FCM to students who have tokens
        \App\Models\Student::whereIn('id', $studentIds)
            ->where('is_active', true)
            ->whereNotNull('fcm_token')
            ->each(function ($student) use ($title, $body) {
                FCMController::sendToToken($title, $body, $student->fcm_token, 'course_content');
            });
    }

    public static function onNewUnit(Course $course, string $unitTitle): void
    {
        static::notifyCourse(
            $course->id,
            'وحدة جديدة في ' . ($course->title_ar ?: $course->title_en),
            'تمت إضافة وحدة جديدة: ' . $unitTitle
        );
    }

    public static function onNewLesson(Course $course, string $lessonTitle, string $lessonType): void
    {
        $typeLabel = $lessonType === 'pdf' ? 'ملف PDF' : 'فيديو';
        static::notifyCourse(
            $course->id,
            "{$typeLabel} جديد في " . ($course->title_ar ?: $course->title_en),
            "تمت إضافة {$typeLabel} جديد: {$lessonTitle}"
        );
    }

    public static function onNewMaterial(Course $course, string $materialTitle): void
    {
        static::notifyCourse(
            $course->id,
            'ملف جديد في ' . ($course->title_ar ?: $course->title_en),
            'تمت إضافة مادة جديدة: ' . $materialTitle
        );
    }
}
