<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConductDocument extends Model
{
    protected $fillable = ['title_ar', 'title_en', 'body', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function signatures()
    {
        return $this->hasMany(ConductSignature::class, 'document_id');
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->latest()->first();
    }
}
