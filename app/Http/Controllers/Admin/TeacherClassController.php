<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherClass;
use Illuminate\Http\Request;

class TeacherClassController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id'  => 'required|exists:teachers,id',
            'class_id'    => 'required|exists:classes,id',
            'subject_id'  => 'nullable|exists:subjects,id',
            'is_homeroom' => 'boolean',
        ]);

        $data['is_homeroom'] = $request->boolean('is_homeroom');

        $duplicate = TeacherClass::where('teacher_id', $data['teacher_id'])
            ->where('class_id', $data['class_id'])
            ->where('subject_id', $data['subject_id'] ?? null)
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'هذا التعيين موجود مسبقاً.');
        }

        // صف لا يمكن أن يكون له أكثر من مربي
        if ($data['is_homeroom']) {
            $alreadyHasHomeroom = TeacherClass::where('class_id', $data['class_id'])
                ->where('is_homeroom', true)
                ->exists();

            if ($alreadyHasHomeroom) {
                return back()->with('error', 'هذا الصف لديه مربي صف بالفعل.');
            }
        }

        TeacherClass::create($data);

        return back()->with('success', 'تم تعيين المعلم بنجاح.');
    }

    public function destroy(TeacherClass $teacherClass)
    {
        $teacherClass->delete();

        return back()->with('success', 'تم حذف التعيين.');
    }
}
