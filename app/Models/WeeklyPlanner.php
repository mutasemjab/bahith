<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyPlanner extends Model
{
    protected $fillable = ['teacher_id', 'class_id', 'title', 'image', 'start_date', 'end_date', 'is_active'];

    public function teacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(\App\Models\SchoolClass::class, 'class_id');
    }

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Only hides planners whose start_date hasn't arrived yet.
    // Planners whose end_date has already passed are still returned
    // (the API always shows the latest one that has actually started).
    public function scopeReached($query)
    {
        return $query->where('start_date', '<=', now()->toDateString());
    }
}
