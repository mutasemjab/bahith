<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('class-schedule-table'))->only(['index', 'show']);
        $this->middleware($this->perm('class-schedule-add'))->only(['create', 'store']);
        $this->middleware($this->perm('class-schedule-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('class-schedule-delete'))->only(['destroy']);
    }

    public function index()
    {
        $schedules = ClassSchedule::with('schoolClass')->latest()->paginate(20);
        return view('admin.class_schedules.index', compact('schedules'));
    }

    public function create()
    {
        $classes = SchoolClass::where('is_active', true)
            ->whereDoesntHave('classSchedule')
            ->orderBy('name')
            ->get();

        return view('admin.class_schedules.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id|unique:class_schedules,class_id',
            'image'    => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $path = uploadImage('assets/uploads/class-schedules', $request->file('image'));

        ClassSchedule::create([
            'class_id' => $request->class_id,
            'image'    => $path,
        ]);

        return redirect()->route('admin.class-schedules.index')->with('success', 'تم إضافة جدول الحصص بنجاح.');
    }

    public function edit(ClassSchedule $classSchedule)
    {
        $classes = SchoolClass::where('is_active', true)
            ->where(function ($q) use ($classSchedule) {
                $q->whereDoesntHave('classSchedule')->orWhere('id', $classSchedule->class_id);
            })
            ->orderBy('name')
            ->get();

        return view('admin.class_schedules.edit', compact('classSchedule', 'classes'));
    }

    public function update(Request $request, ClassSchedule $classSchedule)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id|unique:class_schedules,class_id,' . $classSchedule->id,
            'image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data = ['class_id' => $request->class_id];

        if ($request->hasFile('image')) {
            $data['image'] = uploadImage('assets/uploads/class-schedules', $request->file('image'));
        }

        $classSchedule->update($data);

        return redirect()->route('admin.class-schedules.index')->with('success', 'تم تحديث جدول الحصص بنجاح.');
    }

    public function destroy(ClassSchedule $classSchedule)
    {
        $classSchedule->delete();
        return back()->with('success', 'تم حذف جدول الحصص.');
    }
}
