<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EducationalNote;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class EducationalNoteController extends Controller
{
    private function teacherId(): int
    {
        return auth('teacher')->id();
    }

    private function myClasses(): \Illuminate\Database\Eloquent\Collection
    {
        return SchoolClass::where('is_active', true)
            ->whereIn('id', auth('teacher')->user()->teacherClasses()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    public function index()
    {
        $notes = EducationalNote::with('schoolClass')
            ->where('teacher_id', $this->teacherId())
            ->latest()
            ->paginate(20);
        return view('teacher.educational_notes.index', compact('notes'));
    }

    public function create()
    {
        $classes = $this->myClasses();
        return view('teacher.educational_notes.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
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
            'teacher_id'  => $this->teacherId(),
            'class_id'    => $request->class_id,
            'type'        => $request->type,
            'title'       => $request->title,
            'description' => $request->description,
            'attachment'  => $images[0] ?? null,
            'images'      => $images,
            'date'        => $request->date,
        ]);

        return redirect()->route('teacher.educational-notes.index')
            ->with('success', __('messages.created_successfully'));
    }

    public function edit(EducationalNote $educationalNote)
    {
        abort_unless($educationalNote->teacher_id === $this->teacherId(), 403);
        $classes = $this->myClasses();
        return view('teacher.educational_notes.edit', compact('educationalNote', 'classes'));
    }

    public function update(Request $request, EducationalNote $educationalNote)
    {
        abort_unless($educationalNote->teacher_id === $this->teacherId(), 403);

        $request->validate([
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
            'class_id'    => $request->class_id,
            'type'        => $request->type,
            'title'       => $request->title,
            'description' => $request->description,
            'attachment'  => $allImages[0] ?? null,
            'images'      => $allImages,
            'date'        => $request->date,
        ];

        $educationalNote->update($data);

        return redirect()->route('teacher.educational-notes.index')
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(EducationalNote $educationalNote)
    {
        abort_unless($educationalNote->teacher_id === $this->teacherId(), 403);
        $educationalNote->delete();
        return redirect()->route('teacher.educational-notes.index')
            ->with('success', __('messages.deleted_successfully'));
    }
}
