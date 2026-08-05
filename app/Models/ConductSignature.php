<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConductSignature extends Model
{
    protected $fillable = ['student_id', 'document_id', 'guardian_name', 'signed_at'];

    protected $casts = ['signed_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function document()
    {
        return $this->belongsTo(ConductDocument::class, 'document_id');
    }
}
