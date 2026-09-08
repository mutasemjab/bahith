<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClass extends Model
{
    protected $table = 'teacher_classes';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'is_homeroom',
    ];

    protected $casts = [
        'is_homeroom' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
