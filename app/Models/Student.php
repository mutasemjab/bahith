<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $guard = 'student';

    protected $fillable = [
        'name', 'email', 'phone', 'national_id', 'fcm_token', 'deviceId', 'password', 'avatar',
        'date_of_birth', 'nationality', 'gender',
        'class_id', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Student $student): void {
            if (! $student->app_account_token) {
                $student->app_account_token = (string) Str::uuid();
            }
        });
    }

    public function ensureAppAccountToken(): string
    {
        if ($this->app_account_token) {
            return strtolower((string) $this->app_account_token);
        }

        $token = (string) Str::uuid();
        static::query()
            ->whereKey($this->getKey())
            ->whereNull('app_account_token')
            ->update(['app_account_token' => $token]);

        $this->refresh();

        return strtolower((string) $this->app_account_token);
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function applePurchases()
    {
        return $this->hasMany(ApplePurchase::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments');
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function siblings()
    {
        return $this->belongsToMany(Student::class, 'student_siblings', 'student_id', 'sibling_id')
            ->withTimestamps();
    }
}
