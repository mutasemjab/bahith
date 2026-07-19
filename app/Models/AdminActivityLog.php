<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_id', 'admin_name', 'action', 'module',
        'record_id', 'description', 'old_values', 'new_values', 'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public static function log(string $action, string $description, string $module = null, int $recordId = null, array $old = null, array $new = null): void
    {
        $admin = auth()->guard('admin')->user();

        static::create([
            'admin_id'   => $admin?->id,
            'admin_name' => $admin?->name ?? 'System',
            'action'     => $action,
            'module'     => $module,
            'record_id'  => $recordId,
            'description'=> $description,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
        ]);
    }
}
