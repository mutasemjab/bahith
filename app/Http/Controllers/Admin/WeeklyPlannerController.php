<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WeeklyPlanner;
use Illuminate\Http\Request;

class WeeklyPlannerController extends Controller
{
    public function index()
    {
        $planners = WeeklyPlanner::orderByDesc('start_date')->paginate(15);
        return view('admin.weekly-planners.index', compact('planners'));
    }

    public function create()
    {
        $defaultStart = now()->toDateString();
        $defaultEnd   = now()->addWeek()->toDateString();
        return view('admin.weekly-planners.create', compact('defaultStart', 'defaultEnd'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'nullable|string|max:255',
            'image'      => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        $path = uploadImage('assets/uploads/weekly-planners', $request->file('image'));

        WeeklyPlanner::create([
            'title'      => $request->input('title'),
            'image'      => $path,
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.weekly-planners.index')
            ->with('success', 'تمت إضافة المفكرة الأسبوعية بنجاح.');
    }

    public function edit(WeeklyPlanner $weeklyPlanner)
    {
        return view('admin.weekly-planners.edit', compact('weeklyPlanner'));
    }

    public function update(Request $request, WeeklyPlanner $weeklyPlanner)
    {
        $request->validate([
            'title'      => 'nullable|string|max:255',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        $data = [
            'title'      => $request->input('title'),
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'is_active'  => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = uploadImage('assets/uploads/weekly-planners', $request->file('image'));
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
