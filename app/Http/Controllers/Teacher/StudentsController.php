<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;

class StudentsController extends Controller
{
    public function index()
    {
        $teacher = auth()->guard('teacher')->user();

        // الصفوف اللي المعلم معين فيها (بدون تكرار)
        $classIds = $teacher->teacherClasses()->distinct()->pluck('class_id');

        if ($classIds->isEmpty()) {
            return view('teacher.students.index', [
                'classes'        => collect(),
                'subjectsByClass' => collect(),
                'totalStudents'  => 0,
                'homeroomClassId' => null,
            ]);
        }

        $classes = SchoolClass::whereIn('id', $classIds)
            ->with(['students' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        // المواد اللي بدرّسها في كل صف
        $subjectsByClass = $teacher->teacherClasses()
            ->whereNotNull('subject_id')
            ->with('subject')
            ->get()
            ->groupBy('class_id')
            ->map(fn ($items) => $items->pluck('subject')->filter());

        $homeroomClassId = $teacher->teacherClasses()
            ->where('is_homeroom', true)
            ->value('class_id');

        $totalStudents = $classes->sum(fn ($c) => $c->students->count());

        return view('teacher.students.index', compact(
            'classes',
            'subjectsByClass',
            'totalStudents',
            'homeroomClassId'
        ));
    }
}
