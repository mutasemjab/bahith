<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentSiblingController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('student-edit'))->only(['store', 'destroy']);
    }

    public function store(Request $request, Student $student)
    {
        $data = $request->validate([
            'sibling_id' => 'required|exists:students,id',
        ], [], ['sibling_id' => 'الأخ/الأخت']);

        $siblingId = (int) $data['sibling_id'];

        if ($siblingId === $student->id) {
            return back()->with('error', 'لا يمكن ربط الطالب بنفسه.');
        }

        if ($student->siblings()->where('students.id', $siblingId)->exists()) {
            return back()->with('error', 'هذا الطالب مرتبط بالفعل كأخ/أخت.');
        }

        DB::transaction(function () use ($student, $siblingId) {
            $student->siblings()->attach($siblingId);
            Student::find($siblingId)->siblings()->attach($student->id);
        });

        return back()->with('success', 'تم ربط الأخ/الأخت بنجاح.');
    }

    public function destroy(Student $student, Student $sibling)
    {
        DB::transaction(function () use ($student, $sibling) {
            $student->siblings()->detach($sibling->id);
            $sibling->siblings()->detach($student->id);
        });

        return back()->with('success', 'تم إلغاء الربط.');
    }
}
