<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\WeeklyPlanner;
use Illuminate\Http\Request;

class WeeklyPlannerController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('weekly-planner-table'))->only(['index', 'show']);
        $this->middleware($this->perm('weekly-planner-add'))->only(['create', 'store']);
        $this->middleware($this->perm('weekly-planner-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('weekly-planner-delete'))->only(['destroy']);
    }

    private function formData(): array
    {
        $teachers = Teacher::orderBy('name')->get(['id', 'name']);
        $classes  = SchoolClass::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return compact('teachers', 'classes');
    }

    public function index()
    {
        $planners = WeeklyPlanner::with(['teacher', 'schoolClass'])
            ->orderByDesc('start_date')
            ->paginate(15);
        return view('admin.weekly-planners.index', compact('planners'));
    }

    public function create()
    {
        $defaultStart = now()->toDateString();
        $defaultEnd   = now()->addWeek()->toDateString();
        extract($this->formData());
        return view('admin.weekly-planners.create', compact('defaultStart', 'defaultEnd', 'teachers', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'nullable|exists:teachers,id',
            'class_id'   => 'nullable|exists:classes,id',
            'title'      => 'nullable|string|max:255',
            'image_ar'   => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'image_en'   => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        $imageAr = uploadImage('assets/uploads/weekly-planners', $request->file('image_ar'));
        $imageEn = $request->hasFile('image_en')
            ? uploadImage('assets/uploads/weekly-planners', $request->file('image_en'))
            : null;

        WeeklyPlanner::create([
            'teacher_id' => $request->input('teacher_id'),
            'class_id'   => $request->input('class_id'),
            'title'      => $request->input('title'),
            'image'      => $imageAr,
            'image_ar'   => $imageAr,
            'image_en'   => $imageEn,
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.weekly-planners.index')
            ->with('success', 'تمت إضافة المفكرة الأسبوعية بنجاح.');
    }

    public function edit(WeeklyPlanner $weeklyPlanner)
    {
        extract($this->formData());
        return view('admin.weekly-planners.edit', compact('weeklyPlanner', 'teachers', 'classes'));
    }

    public function update(Request $request, WeeklyPlanner $weeklyPlanner)
    {
        $request->validate([
            'teacher_id' => 'nullable|exists:teachers,id',
            'class_id'   => 'nullable|exists:classes,id',
            'title'      => 'nullable|string|max:255',
            'image_ar'   => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'image_en'   => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        $data = [
            'teacher_id' => $request->input('teacher_id'),
            'class_id'   => $request->input('class_id'),
            'title'      => $request->input('title'),
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'is_active'  => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image_ar')) {
            $data['image_ar'] = uploadImage('assets/uploads/weekly-planners', $request->file('image_ar'));
            $data['image']    = $data['image_ar']; // keep legacy column in sync
        }
        if ($request->hasFile('image_en')) {
            $data['image_en'] = uploadImage('assets/uploads/weekly-planners', $request->file('image_en'));
        }

        $weeklyPlanner->update($data);

        return redirect()->route('admin.weekly-planners.index')
            ->with('success', 'تم تحديث المفكرة الأسبوعية بنجاح.');
    }

    public function destroy(WeeklyPlanner $weeklyPlanner)
    {
        $weeklyPlanner->delete();
        return back()->with('success', 'تم حذف المفكرة.');
    }
}
