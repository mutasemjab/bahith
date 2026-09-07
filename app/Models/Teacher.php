<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Teacher extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $guard = 'teacher';

    protected $fillable = [
        'name', 'national_id', 'email', 'phone', 'password', 'avatar',
        'specialization_ar', 'specialization_en',
        'bio_ar', 'bio_en',
        'qualification_ar', 'qualification_en',
        'years_of_experience', 'gender', 'nationality',
        'average_rating', 'total_students', 'total_courses',
        'is_verified', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'is_active'           => 'boolean',
        'is_verified'         => 'boolean',
 
    ];

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function getSpecializationAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? ($this->specialization_ar ?? $this->specialization_en ?? '')
            : ($this->specialization_en ?? $this->specialization_ar ?? '');
    }

    public function getBioAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? ($this->bio_ar ?? $this->bio_en ?? '')
            : ($this->bio_en ?? $this->bio_ar ?? '');
    }

    public function getQualificationAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? ($this->qualification_ar ?? $this->qualification_en ?? '')
            : ($this->qualification_en ?? $this->qualification_ar ?? '');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects');
    }

    // الصفوف والمواد اللي بدرّسها المعلم
    public function teacherClasses()
    {
        return $this->hasMany(TeacherClass::class);
    }

    // الصفوف اللي بدرّس فيها (بدون تكرار)
    public function taughtClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_classes', 'teacher_id', 'class_id')
            ->withPivot('subject_id', 'is_homeroom')
            ->withTimestamps()
            ->distinct();
    }

    // الصف اللي المعلم مربيه (إن وجد)
    public function homeroomClass()
    {
        return $this->hasOneThrough(
            SchoolClass::class,
            TeacherClass::class,
            'teacher_id',
            'id',
            'id',
            'class_id'
        )->where('teacher_classes.is_homeroom', true);
    }

    // كل طلاب الصفوف اللي بدرّسها المعلم
    public function students()
    {
        $classIds = $this->teacherClasses()->distinct()->pluck('class_id');
        return Student::whereIn('class_id', $classIds);
    }
}
