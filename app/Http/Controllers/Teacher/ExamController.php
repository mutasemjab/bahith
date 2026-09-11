<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Course, Exam, Question, SchoolClass, Subject};
use App\Services\ExamService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(private ExamService $exams) {}

    private function teacher()
    {
        return auth()->guard('teacher')->user();
    }

    // Ownership check: prefer the direct teacher_id column (always set on
    // exams created after this field existed); fall back to the linked
    // course's owner for older exams created before teacher_id existed.
    private function authorizeOwnership(Exam $exam): void
    {
        if ($exam->teacher_id) {
            abort_if($exam->teacher_id !== $this->teacher()->id, 403);
            return;
        }

        if ($exam->course_id) {
            abort_if($exam->course->teacher_id !== $this->teacher()->id, 403);
        }
    }

    public function index(Request $request)
    {
        $filters               = $request->only(['search', 'course_id', 'exam_type', 'is_published']);
        $filters['teacher_id'] = $this->teacher()->id;

        $exams   = $this->exams->list($filters);
        $courses = Course::where('teacher_id', $this->teacher()->id)->get();

        return view('teacher.exams.index', compact('exams', 'courses'));
    }

    private function teacherSubjects(): \Illuminate\Support\Collection
    {
        return Subject::whereIn('id', $this->teacher()->teacherClasses()->whereNotNull('subject_id')->pluck('subject_id'))
            ->with(['category.parent.parent'])
            ->get()
            ->sortBy(fn($s) => $s->full_path)
            ->values();
    }

    private function teacherHasSubject(int $subjectId): bool
    {
        return $this->teacher()->teacherClasses()->where('subject_id', $subjectId)->exists();
    }

    public function create()
    {
        $courses  = Course::where('teacher_id', $this->teacher()->id)->get();
        $subjects = $this->teacherSubjects();
        $classes  = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('teacher.exams.create', compact('courses', 'subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id'               => 'nullable|exists:courses,id',
            'subject_id'              => 'nullable|exists:subjects,id',
            'class_id'                => 'nullable|exists:classes,id',
            'title_ar'                => 'required|string|max:255',
            'title_en'                => 'required|string|max:255',
            'description_ar'          => 'nullable|string',
            'description_en'          => 'nullable|string',
            'exam_type'               => 'required|in:mock,unit,final,practice,previous_years,placement',
            'duration_minutes'        => 'required|integer|min:1',
            'total_marks'             => 'required|integer|min:1',
            'pass_marks'              => 'required|integer|min:1',
            'difficulty_level'        => 'nullable|in:easy,medium,hard,mixed',
            'is_published'            => 'boolean',
            'shuffle_questions'       => 'boolean',
            'shuffle_options'         => 'boolean',
            'show_result_immediately' => 'boolean',
        ]);

        if (! empty($data['course_id'])) {
            $course = Course::findOrFail($data['course_id']);
            abort_if($course->teacher_id !== $this->teacher()->id, 403);
        }

        if (! empty($data['subject_id'])) {
            abort_unless($this->teacherHasSubject($data['subject_id']), 403);
        }

        $data['teacher_id']              = $this->teacher()->id;
        $data['is_published']            = $request->boolean('is_published');
        $data['shuffle_questions']       = $request->boolean('shuffle_questions');
        $data['shuffle_options']         = $request->boolean('shuffle_options');
        $data['show_result_immediately'] = $request->boolean('show_result_immediately');

        $exam = $this->exams->create($data);

        return redirect()->route('teacher.exams.show', $exam->id)
            ->with('success', 'Exam created. Add questions below.');
    }

    public function show(int $id)
    {
        $exam = $this->exams->find($id);

        $this->authorizeOwnership($exam);

        return view('teacher.exams.show', compact('exam'));
    }

    public function edit(int $id)
    {
        $exam = Exam::findOrFail($id);

        $this->authorizeOwnership($exam);

        $courses  = Course::where('teacher_id', $this->teacher()->id)->get();
        $subjects = $this->teacherSubjects();
        $classes  = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('teacher.exams.edit', compact('exam', 'courses', 'subjects', 'classes'));
    }

    public function update(Request $request, int $id)
    {
        $exam = Exam::findOrFail($id);

        $this->authorizeOwnership($exam);

        $data = $request->validate([
            'course_id'               => 'nullable|exists:courses,id',
            'subject_id'              => 'nullable|exists:subjects,id',
            'class_id'                => 'nullable|exists:classes,id',
            'title_ar'                => 'required|string|max:255',
            'title_en'                => 'required|string|max:255',
            'description_ar'          => 'nullable|string',
            'description_en'          => 'nullable|string',
            'exam_type'               => 'required|in:mock,unit,final,practice,previous_years,placement',
            'duration_minutes'        => 'required|integer|min:1',
            'total_marks'             => 'required|integer|min:1',
            'pass_marks'              => 'required|integer|min:1',
            'difficulty_level'        => 'nullable|in:easy,medium,hard,mixed',
            'is_published'            => 'boolean',
            'shuffle_questions'       => 'boolean',
            'shuffle_options'         => 'boolean',
            'show_result_immediately' => 'boolean',
        ]);

        if (! empty($data['subject_id'])) {
            abort_unless($this->teacherHasSubject($data['subject_id']), 403);
        }

        $data['is_published']            = $request->boolean('is_published');
        $data['shuffle_questions']       = $request->boolean('shuffle_questions');
        $data['shuffle_options']         = $request->boolean('shuffle_options');
        $data['show_result_immediately'] = $request->boolean('show_result_immediately');

        $this->exams->update($exam, $data);

        return redirect()->route('teacher.exams.show', $id)
            ->with('success', 'Exam updated.');
    }

    public function destroy(int $id)
    {
        $exam = Exam::findOrFail($id);

        $this->authorizeOwnership($exam);

        $this->exams->delete($exam);

        return redirect()->route('teacher.exams.index')
            ->with('success', 'Exam deleted.');
    }

    public function storeQuestion(Request $request, int $examId)
    {
        $exam = Exam::findOrFail($examId);

        $data = $request->validate([
            'question_text_ar'  => 'required|string',
            'question_text_en'  => 'nullable|string',
            'question_type'     => 'required|in:mcq,true_false,short_answer,essay',
            'difficulty'        => 'nullable|in:easy,medium,hard',
            'marks'             => 'required|integer|min:1',
            'explanation_ar'    => 'nullable|string',
            'options'           => 'required_if:question_type,mcq,true_false|array|min:2',
            'options.*.text_ar' => 'required_with:options|string',
            'options.*.text_en' => 'nullable|string',
            'options.*.correct' => 'nullable',
        ]);

        $options = collect($data['options'] ?? [])->map(fn ($o, $i) => [
            'option_text_ar' => $o['text_ar'],
            'option_text_en' => $o['text_en'] ?? null,
            'is_correct'     => ! empty($o['correct']),
            'order_index'    => $i,
        ])->all();

        $this->exams->addQuestion($exam, [
            'question_ar'    => $data['question_text_ar'],
            'question_en'    => $data['question_text_en'] ?? null,
            'question_type'  => $data['question_type'],
            'difficulty'     => $data['difficulty'] ?? 'medium',
            'marks'          => $data['marks'],
            'explanation_ar' => $data['explanation_ar'] ?? null,
        ], $options);

        return back()->with('success', 'Question added.');
    }

    public function destroyQuestion(int $questionId)
    {
        $question = Question::findOrFail($questionId);
        $this->exams->deleteQuestion($question);

        return back()->with('success', 'Question deleted.');
    }
}
