<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('class-table'))->only(['index']);
        $this->middleware($this->perm('class-add'))->only(['create', 'store']);
        $this->middleware($this->perm('class-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('class-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $classes = SchoolClass::when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.school-classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.school-classes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:200',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        SchoolClass::create($data);

        return redirect()->route('admin.school-classes.index')
            ->with('success', __('messages.class_created'));
    }

    public function edit(SchoolClass $schoolClass)
    {
        return view('admin.school-classes.edit', compact('schoolClass'));
    }

    public function update(Request $request, SchoolClass $schoolClass)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:200',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $schoolClass->update($data);

        return redirect()->route('admin.school-classes.index')
            ->with('success', __('messages.class_updated'));
    }

    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();

        return redirect()->back()
            ->with('success', __('messages.class_deleted'));
    }
}
