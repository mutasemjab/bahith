<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyPlanner extends Model
{
    protected $fillable = ['title', 'image', 'start_date', 'end_date', 'is_active'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)->where('end_date', '>=', $today);
    }
}
