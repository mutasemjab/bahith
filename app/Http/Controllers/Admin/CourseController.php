<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Category, Course, Enrollment, LessonProgress, SchoolClass, Subject, Teacher};
use App\Models\AdminActivityLog;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(private CourseService $courses)
    {
        $this->middleware($this->perm('course-table'))->only(['index', 'show']);
        $this->middleware($this->perm('course-add'))->only(['create', 'store']);
        $this->middleware($this->perm('course-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('course-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $courses = $this->courses->list($request->only(['search', 'category_id', 'teacher_id', 'is_published']));
        $categories = Category::active()->get();
        $teachers   = Teacher::where('is_active', true)->get();

        return view('admin.courses.index', compact('courses', 'categories', 'teachers'));
    }

    public function create()
    {
        $categories = Category::roots()->active()->get();
        $subjects   = $this->subjectsWithPath();
        $teachers   = Teacher::where('is_active', true)->get();
        $classes    = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('admin.courses.create', compact('categories', 'subjects', 'teachers', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id'       => 'required|exists:teachers,id',
            'category_id'      => 'nullable|exists:categories,id',
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
            'is_featured'       => 'boolean',
            'is_free'           => 'boolean',
            'sequential_videos' => 'boolean',
            'thumbnail'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $classIds = $data['class_ids'] ?? [];
        unset($data['class_ids']);

        $data['is_published']      = $request->boolean('is_published');
        $data['is_featured']       = $request->boolean('is_featured');
        $data['is_free']           = $request->boolean('is_free');
        $data['sequential_videos'] = $request->boolean('sequential_videos');

        $course = $this->courses->create($data, $request->file('thumbnail'), $classIds);
        AdminActivityLog::log('create', "إضافة دورة: {$data['title_ar']}", 'courses', $course->id);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function show(int $id)
    {
        $course = $this->courses->find($id);

        return view('admin.courses.show', compact('course'));
    }

    public function edit(int $id)
    {
        $course     = Course::findOrFail($id);
        $categories = Category::roots()->active()->get();
        $subjects   = $this->subjectsWithPath();
        $teachers   = Teacher::where('is_active', true)->get();
        $classes    = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('admin.courses.edit', compact('course', 'categories', 'subjects', 'teachers', 'classes'));
    }

    public function update(Request $request, int $id)
    {
        $course = Course::findOrFail($id);

        $data = $request->validate([
            'teacher_id'       => 'required|exists:teachers,id',
            'category_id'      => 'nullable|exists:categories,id',
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
            'is_featured'       => 'boolean',
            'is_free'           => 'boolean',
            'sequential_videos' => 'boolean',
            'thumbnail'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $classIds = $data['class_ids'] ?? [];
        unset($data['class_ids']);

        $data['is_published']      = $request->boolean('is_published');
        $data['is_featured']       = $request->boolean('is_featured');
        $data['is_free']           = $request->boolean('is_free');
        $data['sequential_videos'] = $request->boolean('sequential_videos');

        $this->courses->update($course, $data, $request->file('thumbnail'), $classIds);
        AdminActivityLog::log('update', "تعديل دورة: {$course->title_ar}", 'courses', $course->id);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function subjectsWithPath(): \Illuminate\Database\Eloquent\Collection
    {
        return Subject::with(['category.parent.parent.parent'])
            ->active()
            ->get()
            ->sortBy(fn ($s) => $s->full_path);
    }

    public function progress(int $id)
    {
        $course = Course::with(['units.lessons', 'classes'])->findOrFail($id);

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

        return view('admin.courses.progress', compact('course', 'students', 'enrollmentsByStudent', 'completedByStudent', 'totalLessons'));
    }

    public function destroy(int $id)
    {
        $course = Course::findOrFail($id);
        AdminActivityLog::log('delete', "حذف دورة: {$course->title_ar}", 'courses', $course->id);
        $this->courses->delete($course);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
