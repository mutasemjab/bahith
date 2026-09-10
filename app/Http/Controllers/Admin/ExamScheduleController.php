<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ExamScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('exam-schedule-table'))->only(['index', 'show']);
        $this->middleware($this->perm('exam-schedule-add'))->only(['create', 'store']);
        $this->middleware($this->perm('exam-schedule-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('exam-schedule-delete'))->only(['destroy']);
    }

    public function index()
    {
        $schedules = ExamSchedule::with('schoolClass')->latest()->paginate(20);
        return view('admin.exam_schedules.index', compact('schedules'));
    }

    public function create()
    {
        $classes = SchoolClass::where('is_active', true)
            ->whereDoesntHave('examSchedule')
            ->orderBy('name')
            ->get();

        return view('admin.exam_schedules.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id|unique:exam_schedules,class_id',
            'image'    => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $path = uploadImage('assets/uploads/exam-schedules', $request->file('image'));

        ExamSchedule::create([
            'class_id' => $request->class_id,
            'image'    => $path,
        ]);

        return redirect()->route('admin.exam-schedules.index')->with('success', 'تم إضافة جدول الامتحانات بنجاح.');
    }

    public function edit(ExamSchedule $examSchedule)
    {
        $classes = SchoolClass::where('is_active', true)
            ->where(function ($q) use ($examSchedule) {
                $q->whereDoesntHave('examSchedule')->orWhere('id', $examSchedule->class_id);
            })
            ->orderBy('name')
            ->get();

        return view('admin.exam_schedules.edit', compact('examSchedule', 'classes'));
    }

    public function update(Request $request, ExamSchedule $examSchedule)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id|unique:exam_schedules,class_id,' . $examSchedule->id,
            'image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data = ['class_id' => $request->class_id];

        if ($request->hasFile('image')) {
            $data['image'] = uploadImage('assets/uploads/exam-schedules', $request->file('image'));
        }

        $examSchedule->update($data);

        return redirect()->route('admin.exam-schedules.index')->with('success', 'تم تحديث جدول الامتحانات بنجاح.');
    }

    public function destroy(ExamSchedule $examSchedule)
    {
        $examSchedule->delete();
        return back()->with('success', 'تم حذف جدول الامتحانات.');
    }
}
