<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\WeeklyPlanner;
use Illuminate\Http\Request;

class WeeklyPlannerController extends Controller
{
    private function teacherId(): int
    {
        return auth('teacher')->id();
    }

    public function index()
    {
        $planners = WeeklyPlanner::with('schoolClass')
            ->where('teacher_id', $this->teacherId())
            ->orderByDesc('start_date')
            ->paginate(15);
        return view('teacher.weekly-planners.index', compact('planners'));
    }

    public function create()
    {
        $classes      = SchoolClass::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $defaultStart = now()->toDateString();
        $defaultEnd   = now()->addWeek()->toDateString();
        return view('teacher.weekly-planners.create', compact('classes', 'defaultStart', 'defaultEnd'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id'   => 'nullable|exists:classes,id',
            'title'      => 'nullable|string|max:255',
            'image'      => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        $path = uploadImage('assets/uploads/weekly-planners', $request->file('image'));

        WeeklyPlanner::create([
            'teacher_id' => $this->teacherId(),
            'class_id'   => $request->input('class_id'),
            'title'      => $request->input('title'),
            'image'      => $path,
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('teacher.weekly-planners.index')
            ->with('success', 'تمت إضافة المفكرة الأسبوعية بنجاح.');
    }

    public function edit(WeeklyPlanner $weeklyPlanner)
    {
        abort_unless($weeklyPlanner->teacher_id === $this->teacherId(), 403);
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('teacher.weekly-planners.edit', compact('weeklyPlanner', 'classes'));
    }

    public function update(Request $request, WeeklyPlanner $weeklyPlanner)
    {
        abort_unless($weeklyPlanner->teacher_id === $this->teacherId(), 403);

        $request->validate([
            'class_id'   => 'nullable|exists:classes,id',
            'title'      => 'nullable|string|max:255',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        $data = [
            'class_id'   => $request->input('class_id'),
            'title'      => $request->input('title'),
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'is_active'  => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = uploadImage('assets/uploads/weekly-planners', $request->file('image'));
        }

        $weeklyPlanner->update($data);

        return redirect()->route('teacher.weekly-planners.index')
            ->with('success', 'تم تحديث المفكرة الأسبوعية بنجاح.');
    }

    public function destroy(WeeklyPlanner $weeklyPlanner)
    {
        abort_unless($weeklyPlanner->teacher_id === $this->teacherId(), 403);
        $weeklyPlanner->delete();
        return back()->with('success', 'تم حذف المفكرة.');
    }
}
