<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('question-bank-table'))->only(['index', 'show']);
        $this->middleware($this->perm('question-bank-add'))->only(['create', 'store']);
        $this->middleware($this->perm('question-bank-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('question-bank-delete'))->only(['destroy']);
    }

    public function index()
    {
        $questionBanks = QuestionBank::with('subject')
            ->latest()
            ->paginate(20);

        return view('admin.question_banks.index',
            compact('questionBanks')
        );
    }

    public function create()
    {
        $subjects = $this->subjectsWithPath();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('admin.question_banks.create', compact('subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:classes,id',

            'title_ar' => 'required',
            'title_en' => 'nullable',

            'tag_ar' => 'nullable',
            'tag_en' => 'nullable',

            'pages' => 'nullable|integer',
            'file_size' => 'nullable|numeric',

            'sort_order' => 'nullable|integer',

            'status' => 'required|boolean',

            'pdf_file' => 'required|mimes:pdf|max:20480',
        ]);

        $pdf = uploadImage('assets/uploads/questionBank', $request->file('pdf_file'));

        QuestionBank::create([
            'subject_id' => $request->subject_id,
            'class_id' => $request->class_id,

            'title_ar' => $request->title_ar,
            'title_en' => $request->title_en ?: $request->title_ar,

            'tag_ar' => $request->tag_ar,
            'tag_en' => $request->tag_en,

            'pdf_file' => $pdf,

            'pages' => $request->pages,
            'file_size' => $request->file_size,

            'sort_order' => $request->sort_order ?? 0,

            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.question-banks.index')
            ->with('success', 'Created Successfully');
    }

    public function edit(QuestionBank $questionBank)
    {
        $subjects = $this->subjectsWithPath();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('admin.question_banks.edit', compact('questionBank', 'subjects', 'classes'));
    }

    public function update(Request $request, QuestionBank $questionBank)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:classes,id',

            'title_ar' => 'required',
            'title_en' => 'nullable',

            'tag_ar' => 'nullable',
            'tag_en' => 'nullable',

            'pages' => 'nullable|integer',
            'file_size' => 'nullable|numeric',

            'sort_order' => 'nullable|integer',

            'status' => 'required|boolean',

            'pdf_file' => 'nullable|mimes:pdf|max:20480',
        ]);

        if ($request->hasFile('pdf_file')) {


            $questionBank->pdf_file = uploadImage('assets/uploads/questionBank', $request->file('pdf_file'));
        }

        $questionBank->update([
            'subject_id' => $request->subject_id,
            'class_id' => $request->class_id,

            'title_ar' => $request->title_ar,
            'title_en' => $request->title_en ?: $request->title_ar,

            'tag_ar' => $request->tag_ar,
            'tag_en' => $request->tag_en,

            'pages' => $request->pages,
            'file_size' => $request->file_size,

            'sort_order' => $request->sort_order ?? 0,

            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.question-banks.index')
            ->with('success', 'Updated Successfully');
    }

    public function destroy(QuestionBank $questionBank)
    {
        $questionBank->delete();

        return redirect()
            ->route('admin.question-banks.index')
            ->with('success', 'Deleted Successfully');
    }

    private function subjectsWithPath(): \Illuminate\Support\Collection
    {
        return Subject::with(['category.parent.parent'])
            ->get()
            ->sortBy(fn ($s) => $s->full_path)
            ->values();
    }
}
