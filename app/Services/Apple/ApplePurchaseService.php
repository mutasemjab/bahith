<?php

namespace App\Services\Apple;

use App\Contracts\AppleTransactionVerifier;
use App\Exceptions\ApplePurchaseException;
use App\Models\ApplePurchase;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ApplePurchaseService
{
    public function __construct(
        private AppleTransactionVerifier $verifier,
        private ApplePurchaseToken $tokens
    ) {
    }

    /** @param  array<string, mixed>  $input */
    public function verifyAndGrant(Student $student, array $input): ApplePurchase
    {
        $student->ensureAppAccountToken();

        $course = Course::query()
            ->whereKey((int) $input['course_id'])
            ->where('is_published', true)
            ->where('is_free', false)
            ->first();
        if (! $course) {
            throw new ApplePurchaseException('الدورة المدفوعة غير متاحة.', 404);
        }

        $verified = $this->verifier->verify((string) $input['signed_transaction']);
        $expectedPurchaseToken = $this->tokens->forCourse(
            (string) $student->app_account_token,
            $course->id
        );

        if (! hash_equals($verified['product_id'], (string) $input['product_id'])
            || ! hash_equals($verified['transaction_id'], (string) $input['transaction_id'])
            || ! hash_equals($expectedPurchaseToken, strtolower((string) $input['purchase_token']))
            || ! hash_equals($expectedPurchaseToken, strtolower($verified['app_account_token']))) {
            throw new ApplePurchaseException('بيانات عملية الشراء لا تطابق الحساب أو الدورة.', 422);
        }

        try {
            return DB::transaction(function () use (
                $student,
                $course,
                $input,
                $verified,
                $expectedPurchaseToken
            ): ApplePurchase {
                $existing = ApplePurchase::query()
                    ->where('transaction_id', $verified['transaction_id'])
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    $this->assertSameOwner($existing, $student, $course, $expectedPurchaseToken);

                    $enrollment = $this->ensureEnrollment($student, $course);
                    if ((int) $existing->enrollment_id !== (int) $enrollment->id) {
                        $existing->update(['enrollment_id' => $enrollment->id]);
                    }

                    return $existing;
                }

                $enrollment = $this->ensureEnrollment($student, $course);

                return ApplePurchase::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'enrollment_id' => $enrollment->id,
                    'product_id' => $verified['product_id'],
                    'transaction_id' => $verified['transaction_id'],
                    'original_transaction_id' => $verified['original_transaction_id'],
                    'student_app_account_token' => strtolower((string) $student->app_account_token),
                    'purchase_token' => $expectedPurchaseToken,
                    'environment' => $verified['environment'],
                    'quantity' => $verified['quantity'],
                    'purchased_at' => $this->fromMilliseconds($verified['purchase_date_ms']),
                    'signed_at' => $this->fromMilliseconds($verified['signed_date_ms']),
                    'verified_at' => now(),
                    'signed_transaction' => (string) $input['signed_transaction'],
                    'verified_payload' => $verified['payload'],
                ]);
            }, 3);
        } catch (QueryException $exception) {
            // A concurrent retry may win the unique transaction-id insert.
            $existing = ApplePurchase::where('transaction_id', $verified['transaction_id'])->first();
            if ($existing) {
                $this->assertSameOwner($existing, $student, $course, $expectedPurchaseToken);

                return $existing;
            }
            throw $exception;
        }
    }

    private function ensureEnrollment(Student $student, Course $course): Enrollment
    {
        $enrollment = Enrollment::firstOrCreate(
            ['student_id' => $student->id, 'course_id' => $course->id],
            [
                'enrolled_at' => now(),
                'is_active' => true,
                'is_completed' => false,
                'progress_percentage' => 0,
            ]
        );

        if (! $enrollment->is_active) {
            $enrollment->update(['is_active' => true]);
        }

        return $enrollment;
    }

    private function assertSameOwner(
        ApplePurchase $purchase,
        Student $student,
        Course $course,
        string $purchaseToken
    ): void {
        if ((int) $purchase->student_id !== (int) $student->id
            || (int) $purchase->course_id !== (int) $course->id
            || ! hash_equals(strtolower($purchase->purchase_token), strtolower($purchaseToken))) {
            throw new ApplePurchaseException(
                'تم استخدام معاملة App Store هذه لحساب أو دورة أخرى.',
                409
            );
        }
    }

    private function fromMilliseconds(int $milliseconds): Carbon
    {
        return Carbon::createFromTimestampMs($milliseconds, 'UTC');
    }
}
