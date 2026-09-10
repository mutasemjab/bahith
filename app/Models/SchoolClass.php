<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function educationalNotes()
    {
        return $this->hasMany(EducationalNote::class, 'class_id');
    }

    // كل assignments المعلمين لهذا الصف
    public function teacherClasses()
    {
        return $this->hasMany(TeacherClass::class, 'class_id');
    }

    // المعلمين اللي بدرّسون في هذا الصف
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_classes', 'class_id', 'teacher_id')
            ->withPivot('subject_id', 'is_homeroom')
            ->withTimestamps();
    }

    // مربي الصف
    public function homeroomTeacher()
    {
        return $this->teachers()->wherePivot('is_homeroom', true)->first();
    }

    public function classSchedule()
    {
        return $this->hasOne(ClassSchedule::class, 'class_id');
    }

    public function examSchedule()
    {
        return $this->hasOne(ExamSchedule::class, 'class_id');
    }
}
