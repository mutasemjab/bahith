<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Course, Exam, ExamAttempt, Question, SchoolClass, Subject};
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

    private function teacherHasClass(int $classId): bool
    {
        return $this->teacher()->teacherClasses()->where('class_id', $classId)->exists();
    }

    private function myClasses(): \Illuminate\Database\Eloquent\Collection
    {
        return SchoolClass::where('is_active', true)
            ->whereIn('id', $this->teacher()->teacherClasses()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    public function create()
    {
        $courses  = Course::where('teacher_id', $this->teacher()->id)->get();
        $subjects = $this->teacherSubjects();
        $classes  = $this->myClasses();

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

        if (! empty($data['class_id'])) {
            abort_unless($this->teacherHasClass($data['class_id']), 403);
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
        $classes  = $this->myClasses();

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

        if (! empty($data['class_id'])) {
            abort_unless($this->teacherHasClass($data['class_id']), 403);
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

        $this->authorizeOwnership($exam);

        $data = $request->validate([
            'question_text_ar'  => 'required|string',
            'question_text_en'  => 'nullable|string',
            'question_image'    => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'question_type'     => 'required|in:mcq,true_false,short_answer,essay',
            'difficulty'        => 'nullable|in:easy,medium,hard',
            'marks'             => 'required|integer|min:1',
            'explanation_ar'    => 'nullable|string',
            'options'           => 'required_if:question_type,mcq,true_false|array|min:2',
            'options.*.text_ar' => 'required_with:options|string',
            'options.*.text_en' => 'nullable|string',
            'options.*.correct' => 'nullable',
        ]);

        $imagePath = null;
        if ($request->hasFile('question_image')) {
            $imagePath = uploadImage('assets/uploads/questions', $request->file('question_image'));
        }

        $options = collect($data['options'] ?? [])->map(fn ($o, $i) => [
            'option_text_ar' => $o['text_ar'],
            'option_text_en' => $o['text_en'] ?? null,
            'is_correct'     => ! empty($o['correct']),
            'order_index'    => $i,
        ])->all();

        $this->exams->addQuestion($exam, [
            'question_ar'    => $data['question_text_ar'],
            'question_en'    => $data['question_text_en'] ?? null,
            'image'          => $imagePath,
            'question_type'  => $data['question_type'],
            'difficulty'     => $data['difficulty'] ?? 'medium',
            'marks'          => $data['marks'],
            'explanation_ar' => $data['explanation_ar'] ?? null,
        ], $options);

        return back()->with('success', 'Question added.');
    }

    public function updateQuestion(Request $request, int $questionId)
    {
        $question = Question::with('exam')->findOrFail($questionId);

        $this->authorizeOwnership($question->exam);

        $data = $request->validate([
            'question_text_ar'  => 'required|string',
            'question_text_en'  => 'nullable|string',
            'question_image'    => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'remove_image'      => 'nullable|boolean',
            'question_type'     => 'required|in:mcq,true_false,short_answer,essay',
            'difficulty'        => 'nullable|in:easy,medium,hard',
            'marks'             => 'required|integer|min:1',
            'explanation_ar'    => 'nullable|string',
            'options'           => 'required_if:question_type,mcq,true_false|array|min:2',
            'options.*.text_ar' => 'required_with:options|string',
            'options.*.text_en' => 'nullable|string',
            'options.*.correct' => 'nullable',
        ]);

        $imagePath = $question->image;
        if ($request->hasFile('question_image')) {
            $imagePath = uploadImage('assets/uploads/questions', $request->file('question_image'));
        } elseif ($request->boolean('remove_image')) {
            $imagePath = null;
        }

        $options = collect($data['options'] ?? [])->map(fn ($o, $i) => [
            'option_text_ar' => $o['text_ar'],
            'option_text_en' => $o['text_en'] ?? null,
            'is_correct'     => ! empty($o['correct']),
            'order_index'    => $i,
        ])->all();

        $this->exams->updateQuestion($question, [
            'question_ar'    => $data['question_text_ar'],
            'question_en'    => $data['question_text_en'] ?? null,
            'image'          => $imagePath,
            'question_type'  => $data['question_type'],
            'difficulty'     => $data['difficulty'] ?? 'medium',
            'marks'          => $data['marks'],
            'explanation_ar' => $data['explanation_ar'] ?? null,
        ], $options);

        return back()->with('success', 'Question updated.');
    }

    public function destroyQuestion(int $questionId)
    {
        $question = Question::with('exam')->findOrFail($questionId);

        $this->authorizeOwnership($question->exam);

        $this->exams->deleteQuestion($question);

        return back()->with('success', 'Question deleted.');
    }

    public function results(int $id)
    {
        $exam = $this->exams->find($id);

        $this->authorizeOwnership($exam);

        $attempts = ExamAttempt::with(['student', 'answers.question', 'answers.selectedOption'])
            ->where('exam_id', $id)
            ->where('status', 'submitted')
            ->orderByDesc('submitted_at')
            ->get();

        return view('teacher.exams.results', compact('exam', 'attempts'));
    }
}
