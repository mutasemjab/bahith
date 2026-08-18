<?php

namespace App\Services\Apple;

use App\Exceptions\ApplePurchaseException;

class ApplePurchaseToken
{
    private const CONTEXT = 'com.baheth.school.course.access';

    private const MAX_COURSE_ID = 2147483647;

    public function forCourse(string $studentToken, int $courseId): string
    {
        if (! $this->isUuid($studentToken)) {
            throw new ApplePurchaseException('حساب الطالب غير مجهز للشراء من App Store.');
        }

        if ($courseId <= 0 || $courseId > self::MAX_COURSE_ID) {
            throw new ApplePurchaseException('معرّف الدورة غير صالح.');
        }

        $namespace = hex2bin(str_replace('-', '', strtolower($studentToken)));
        if ($namespace === false || strlen($namespace) !== 16) {
            throw new ApplePurchaseException('معرّف حساب الشراء غير صالح.');
        }

        $bytes = substr(hash('sha1', $namespace.self::CONTEXT, true), 0, 16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x50);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        $bytes = substr($bytes, 0, 12).pack('N', $courseId);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x80);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    public function isUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        ) === 1;
    }
}
