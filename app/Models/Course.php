<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'teacher_id', 'category_id', 'subject_id', 'class_id',
        'title_ar', 'title_en',
        'description_ar', 'description_en',
        'what_you_learn_ar', 'what_you_learn_en',
        'requirements_ar', 'requirements_en',
        'thumbnail', 'preview_video',
        'price', 'old_price', 'average_rating',
        'total_students', 'total_videos', 'total_pdfs', 'duration_hours',
        'difficulty_level',
        'is_live', 'is_published', 'is_featured',
        'is_trending', 'is_bestseller', 'is_free',
        'sequential_videos',
    ];

    protected $casts = [
        'price'             => 'decimal:2',
        'old_price'         => 'decimal:2',
        'average_rating'    => 'decimal:2',
        'is_live'           => 'boolean',
        'is_published'      => 'boolean',
        'is_featured'       => 'boolean',
        'is_trending'       => 'boolean',
        'is_bestseller'     => 'boolean',
        'is_free'           => 'boolean',
        'sequential_videos' => 'boolean',
    ];

    public function getTitleAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? ($this->title_ar ?? $this->title_en)
            : ($this->title_en ?? $this->title_ar);
    }

    public function getDescriptionAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? ($this->description_ar ?? $this->description_en ?? '')
            : ($this->description_en ?? $this->description_ar ?? '');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class)->orderBy('order_index');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Unit::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function publishedExams()
    {
        return $this->hasMany(Exam::class)->where('is_published', true);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (! $this->old_price || $this->old_price <= 0) {
            return 0;
        }

        return (int) round((($this->old_price - $this->price) / $this->old_price) * 100);
    }

    // Returns the IDs of lessons that are locked because sequential_videos is
    // on and an earlier lesson (by unit order, then lesson order) hasn't been
    // completed by the student yet. Empty when sequential_videos is off.
    public function sequentialLockedLessonIds(?int $studentId): \Illuminate\Support\Collection
    {
        if (! $this->sequential_videos) {
            return collect();
        }

        $ordered = Lesson::query()
            ->whereHas('unit', fn ($q) => $q->where('course_id', $this->id)->where('is_published', true))
            ->where('is_published', true)
            ->with('unit:id,order_index')
            ->get(['id', 'unit_id', 'order_index'])
            ->sort(fn ($a, $b) => [$a->unit->order_index, $a->order_index] <=> [$b->unit->order_index, $b->order_index])
            ->values();

        $completedIds = $studentId
            ? LessonProgress::where('student_id', $studentId)
                ->where('is_completed', true)
                ->whereIn('lesson_id', $ordered->pluck('id'))
                ->pluck('lesson_id')
                ->all()
            : [];

        $locked = collect();
        $allPreviousCompleted = true;

        foreach ($ordered as $lesson) {
            if (! $allPreviousCompleted) {
                $locked->push($lesson->id);
            }
            if (! in_array($lesson->id, $completedIds, true)) {
                $allPreviousCompleted = false;
            }
        }

        return $locked;
    }
}
