<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalNote extends Model
{
    protected $fillable = [
        'teacher_id', 'class_id',
        'type', 'title', 'description', 'attachment', 'images', 'date',
    ];

    protected $casts = [
        'date'   => 'date',
        'images' => 'array',
    ];

    // Normalized list of image filenames, regardless of whether this note
    // still only has the legacy single `attachment` or the newer `images` array.
    public function getImageListAttribute(): array
    {
        if (! empty($this->images)) {
            return $this->images;
        }

        return $this->attachment ? [$this->attachment] : [];
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
