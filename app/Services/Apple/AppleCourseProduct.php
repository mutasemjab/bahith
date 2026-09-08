<?php

namespace App\Services\Apple;

use App\Exceptions\ApplePurchaseException;

class AppleCourseProduct
{
    private const MAX_COURSE_ID = 2147483647;

    public function forCourse(int $courseId): string
    {
        if ($courseId <= 0 || $courseId > self::MAX_COURSE_ID) {
            throw new ApplePurchaseException('معرّف الدورة غير صالح.');
        }

        return (string) config('apple_iap.course_product_prefix').$courseId;
    }

    public function courseIdFrom(string $productId): ?int
    {
        $prefix = (string) config('apple_iap.course_product_prefix');
        if ($prefix === '' || ! str_starts_with($productId, $prefix)) {
            return null;
        }

        $suffix = substr($productId, strlen($prefix));
        if (! preg_match('/^[1-9][0-9]*$/D', $suffix)) {
            return null;
        }

        $courseId = filter_var($suffix, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => self::MAX_COURSE_ID],
        ]);

        return $courseId === false ? null : $courseId;
    }
}
