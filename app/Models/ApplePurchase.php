<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplePurchase extends Model
{
    protected $fillable = [
        'student_id', 'course_id', 'enrollment_id', 'product_id',
        'transaction_id', 'original_transaction_id',
        'student_app_account_token', 'purchase_token', 'environment',
        'quantity', 'purchased_at', 'signed_at', 'verified_at',
        'revoked_at', 'signed_transaction', 'verified_payload',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'signed_at' => 'datetime',
        'verified_at' => 'datetime',
        'revoked_at' => 'datetime',
        'verified_payload' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
