<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Category, Course, Enrollment, LessonProgress, SchoolClass, Subject};
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(private CourseService $courses) {}

    private function teacher()
    {
        return auth()->guard('teacher')->user();
    }

    public function index(Request $request)
    {
        $filters             = $request->only(['search', 'category_id', 'is_published']);
        $filters['teacher_id'] = $this->teacher()->id;

        $courses    = $this->courses->list($filters);
        $categories = $this->teacherCategories();

        return view('teacher.courses.index', compact('courses', 'categories'));
    }

    private function teacherSubjects(): \Illuminate\Support\Collection
    {
        return Subject::whereIn('id', $this->teacher()->teacherClasses()->whereNotNull('subject_id')->pluck('subject_id'))
            ->with(['category.parent.parent'])
            ->get()
            ->sortBy(fn($s) => $s->full_path)
            ->values();
    }

    private function teacherCategories(): \Illuminate\Support\Collection
    {
        return $this->teacherSubjects()
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->sortBy(fn($c) => $c->full_path)
            ->values();
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
        $categories = $this->teacherCategories();
        $subjects   = $this->teacherSubjects();
        $classes    = $this->myClasses();

        return view('teacher.courses.create', compact('categories', 'subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'subject_id'       => 'nullable|exists:subjects,id',
            'class_ids'        => 'nullable|array',
            'class_ids.*'      => 'exists:classes,id',
            'title_ar'         => 'required|string|max:255',
            'title_en'         => 'required|string|max:255',
            'description_ar'   => 'nullable|string',
            'description_en'   => 'nullable|string',
            'what_you_learn_ar'=> 'nullable|string',
            'what_you_learn_en'=> 'nullable|string',
            'requirements_ar'  => 'nullable|string',
            'requirements_en'  => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'old_price'        => 'nullable|numeric|min:0',
            'duration_hours'   => 'nullable|integer|min:0',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
            'is_published'      => 'boolean',
            'is_free'           => 'boolean',
            'sequential_videos' => 'boolean',
            'thumbnail'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $classIds = $data['class_ids'] ?? [];
        unset($data['class_ids']);

        $data['teacher_id']         = $this->teacher()->id;
        $data['is_published']       = $request->boolean('is_published');
        $data['is_free']            = $request->boolean('is_free');
        $data['sequential_videos']  = $request->boolean('sequential_videos');

        $course = $this->courses->create($data, $request->file('thumbnail'), $classIds);

        return redirect()->route('teacher.courses.show', $course->id)
            ->with('success', 'Course created. Now add units and lessons.');
    }

    public function show(int $id)
    {
        $course = $this->courses->find($id);

        abort_if($course->teacher_id !== $this->teacher()->id, 403);

        return view('teacher.courses.show', compact('course'));
    }

    public function edit(int $id)
    {
        $course = Course::findOrFail($id);

        abort_if($course->teacher_id !== $this->teacher()->id, 403);

        $categories = $this->teacherCategories();
        $subjects   = $this->teacherSubjects();
        $classes    = $this->myClasses();

        return view('teacher.courses.edit', compact('course', 'categories', 'subjects', 'classes'));
    }

    public function update(Request $request, int $id)
    {
        $course = Course::findOrFail($id);

        abort_if($course->teacher_id !== $this->teacher()->id, 403);

        $data = $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'subject_id'       => 'nullable|exists:subjects,id',
            'class_ids'        => 'nullable|array',
            'class_ids.*'      => 'exists:classes,id',
            'title_ar'         => 'required|string|max:255',
            'title_en'         => 'required|string|max:255',
            'description_ar'   => 'nullable|string',
            'description_en'   => 'nullable|string',
            'what_you_learn_ar'=> 'nullable|string',
            'what_you_learn_en'=> 'nullable|string',
            'requirements_ar'  => 'nullable|string',
            'requirements_en'  => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'old_price'        => 'nullable|numeric|min:0',
            'duration_hours'   => 'nullable|integer|min:0',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
            'is_published'      => 'boolean',
            'is_free'           => 'boolean',
            'sequential_videos' => 'boolean',
            'thumbnail'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $classIds = $data['class_ids'] ?? [];
        unset($data['class_ids']);

        $data['is_published']      = $request->boolean('is_published');
        $data['is_free']           = $request->boolean('is_free');
        $data['sequential_videos'] = $request->boolean('sequential_videos');

        $this->courses->update($course, $data, $request->file('thumbnail'), $classIds);

        return redirect()->route('teacher.courses.show', $id)
            ->with('success', 'Course updated successfully.');
    }

    public function progress(int $id)
    {
        $course = Course::with(['units.lessons', 'classes'])->findOrFail($id);

        abort_if($course->teacher_id !== $this->teacher()->id, 403);

        $lessonIds    = $course->units->flatMap->lessons->pluck('id');
        $totalLessons = $lessonIds->count();

        // 1) students in linked school classes (course_classes pivot + direct class_id)
        $classIds = $course->classes->pluck('id');
        if ($course->class_id && ! $classIds->contains($course->class_id)) {
            $classIds->push($course->class_id);
        }
        $classStudentIds = $classIds->isNotEmpty()
            ? \App\Models\Student::whereIn('class_id', $classIds)->where('is_active', true)->pluck('id')
            : collect();

        // 2) students who already have lesson progress (catch-all for free access)
        $progressStudentIds = $lessonIds->isNotEmpty()
            ? LessonProgress::whereIn('lesson_id', $lessonIds)->distinct()->pluck('student_id')
            : collect();

        // 3) enrolled students (for paid courses)
        $enrolledIds = Enrollment::where('course_id', $id)->pluck('student_id');

        $allStudentIds = $classStudentIds
            ->merge($progressStudentIds)
            ->merge($enrolledIds)
            ->unique()->filter()->values();

        $students = \App\Models\Student::whereIn('id', $allStudentIds)->orderBy('name')->get();

        // Enrollment record per student (progress_percentage / is_completed)
        $enrollmentsByStudent = $enrolledIds->isNotEmpty()
            ? Enrollment::where('course_id', $id)->whereIn('student_id', $allStudentIds)->get()->keyBy('student_id')
            : collect();

        // Completed lesson count per student
        $completedByStudent = $lessonIds->isNotEmpty()
            ? LessonProgress::whereIn('lesson_id', $lessonIds)
                ->whereIn('student_id', $allStudentIds)
                ->where('is_completed', true)
                ->selectRaw('student_id, COUNT(*) as cnt')
                ->groupBy('student_id')
                ->pluck('cnt', 'student_id')
            : collect();

        return view('teacher.courses.progress', compact('course', 'students', 'enrollmentsByStudent', 'completedByStudent', 'totalLessons'));
    }

    public function destroy(int $id)
    {
        $course = Course::findOrFail($id);

        abort_if($course->teacher_id !== $this->teacher()->id, 403);

        $this->courses->delete($course);

        return redirect()->route('teacher.courses.index')
            ->with('success', 'Course deleted.');
    }
}
