<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationalNote;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\Request;

class EducationalNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('educational-note-table'))->only(['index', 'show']);
        $this->middleware($this->perm('educational-note-add'))->only(['create', 'store']);
        $this->middleware($this->perm('educational-note-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('educational-note-delete'))->only(['destroy']);
    }

    private function formData(): array
    {
        $teachers = Teacher::orderBy('name')->get();
        $classes  = SchoolClass::where('is_active', true)->orderBy('name')->get();
        return compact('teachers', 'classes');
    }

    public function index()
    {
        $notes = EducationalNote::with(['teacher', 'schoolClass'])
            ->latest()->paginate(20);
        return view('admin.educational_notes.index', compact('notes'));
    }

    public function create()
    {
        extract($this->formData());
        return view('admin.educational_notes.create', compact('teachers', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id'  => 'nullable|exists:teachers,id',
            'class_id'    => 'nullable|exists:classes,id',
            'type'        => 'required|in:lesson,homework',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'images'      => 'nullable|array',
            'images.*'    => 'file|image|max:20480',
            'date'        => 'required|date',
        ]);

        $images = collect($request->file('images', []))
            ->map(fn ($file) => uploadImage('assets/uploads/educational_notes', $file))
            ->values()
            ->all();

        EducationalNote::create([
            'teacher_id'  => $request->teacher_id,
            'class_id'    => $request->class_id,
            'type'        => $request->type,
            'title'       => $request->title,
            'description' => $request->description,
            'attachment'  => $images[0] ?? null,
            'images'      => $images,
            'date'        => $request->date,
        ]);

        return redirect()->route('admin.educational-notes.index')
            ->with('success', __('messages.created_successfully'));
    }

    public function edit(EducationalNote $educationalNote)
    {
        extract($this->formData());
        return view('admin.educational_notes.edit', compact('educationalNote', 'teachers', 'classes'));
    }

    public function update(Request $request, EducationalNote $educationalNote)
    {
        $request->validate([
            'teacher_id'     => 'nullable|exists:teachers,id',
            'class_id'       => 'nullable|exists:classes,id',
            'type'           => 'required|in:lesson,homework',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'images'         => 'nullable|array',
            'images.*'       => 'file|image|max:20480',
            'remove_images'  => 'nullable|array',
            'date'           => 'required|date',
        ]);

        $currentImages = $educationalNote->image_list;
        $keptImages    = array_values(array_diff($currentImages, $request->input('remove_images', [])));

        $newImages = collect($request->file('images', []))
            ->map(fn ($file) => uploadImage('assets/uploads/educational_notes', $file))
            ->values()
            ->all();

        $allImages = array_values(array_merge($keptImages, $newImages));

        $data = [
            'teacher_id'  => $request->teacher_id,
            'class_id'    => $request->class_id,
            'type'        => $request->type,
            'title'       => $request->title,
            'description' => $request->description,
            'attachment'  => $allImages[0] ?? null,
            'images'      => $allImages,
            'date'        => $request->date,
        ];

        $educationalNote->update($data);

        return redirect()->route('admin.educational-notes.index')
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(EducationalNote $educationalNote)
    {
        $educationalNote->delete();
        return redirect()->route('admin.educational-notes.index')
            ->with('success', __('messages.deleted_successfully'));
    }
}
