<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'image', 'class_id', 'is_active', 'published_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'published_at' => 'datetime',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // All classes this announcement targets. `class_id` stays in sync with the
    // first one (null = everyone), so code still reading the single legacy class
    // — e.g. the mobile API response — keeps working unchanged.
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'announcement_classes', 'announcement_id', 'class_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStudent($query, Student $student)
    {
        return $query->where(function ($q) use ($student) {
            $q->whereNull('class_id')
              ->orWhere('class_id', $student->class_id)
              ->orWhereHas('classes', fn ($c) => $c->where('classes.id', $student->class_id));
        });
    }
}
