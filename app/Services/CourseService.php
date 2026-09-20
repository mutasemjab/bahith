<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CourseService
{
    private string $uploadFolder = 'assets/uploads/courses';

    private function withStudentCount($query)
    {
        return $query->addSelect([
            'class_students_count' => DB::table('students')
                ->selectRaw('COUNT(DISTINCT students.id)')
                ->where('students.is_active', true)
                ->whereNull('students.deleted_at')
                ->where(function ($q) {
                    $q->whereColumn('students.class_id', 'courses.class_id')
                      ->orWhereIn('students.class_id', function ($sub) {
                          $sub->select('class_id')
                              ->from('course_classes')
                              ->whereColumn('course_id', 'courses.id');
                      });
                }),
        ]);
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Course::with(['teacher', 'category', 'subject', 'schoolClass'])
            ->withCount('enrollments');

        $this->withStudentCount($query);

        if (! empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }
        if (! empty($filters['class_id'])) {
            $query->whereHas('classes', fn ($q) => $q->where('classes.id', $filters['class_id']));
        }
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }
        if (isset($filters['is_published']) && $filters['is_published'] !== '') {
            $query->where('is_published', $filters['is_published']);
        }
        if (! empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(fn ($q) => $q
                ->where('title_ar', 'like', "%{$s}%")
                ->orWhere('title_en', 'like', "%{$s}%")
            );
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Course
    {
        $query = Course::with([
            'teacher', 'category', 'subject', 'schoolClass',
            'units.lessons', 'units.materials', 'units.exam',
        ])->withCount('enrollments');

        $this->withStudentCount($query);

        return $query->findOrFail($id);
    }

    public function create(array $data, $thumbnail = null, array $classIds = []): Course
    {
        if ($thumbnail) {
            $data['thumbnail'] = uploadImage($this->uploadFolder, $thumbnail);
        }

        $data['class_id'] = $classIds[0] ?? null;

        $course = Course::create($data);
        $course->classes()->sync($classIds);

        return $course;
    }

    public function update(Course $course, array $data, $thumbnail = null, array $classIds = []): Course
    {
        if ($thumbnail) {
            $this->deleteThumbnail($course->getRawOriginal('thumbnail'));
            $data['thumbnail'] = uploadImage($this->uploadFolder, $thumbnail);
        }

        $data['class_id'] = $classIds[0] ?? null;

        $course->update($data);
        $course->classes()->sync($classIds);

        return $course->fresh();
    }

    public function delete(Course $course): void
    {
        $this->deleteThumbnail($course->getRawOriginal('thumbnail'));
        $course->delete();
    }

    private function deleteThumbnail(?string $filename): void
    {
        if ($filename) {
            $path = base_path("{$this->uploadFolder}/{$filename}");
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
