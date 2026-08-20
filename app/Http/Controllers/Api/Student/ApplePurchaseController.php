<?php

namespace App\Http\Controllers\Api\Student;

use App\Exceptions\ApplePurchaseException;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\Apple\ApplePurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplePurchaseController extends Controller
{
    use ApiResponse;

    public function __construct(private ApplePurchaseService $purchases)
    {
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'min:1'],
            'product_id' => [
                'required',
                'string',
                'max:255',
            ],
            'transaction_id' => ['required', 'string', 'regex:/^\d{6,30}$/'],
            'signed_transaction' => ['required', 'string', 'max:50000'],
            'purchase_token' => ['required', 'uuid'],
            'transaction_date' => ['nullable', 'string', 'max:30'],
            'source' => ['nullable', Rule::in(['app_store'])],
        ]);

        try {
            $purchase = $this->purchases->verifyAndGrant($request->user(), $validated);
        } catch (ApplePurchaseException $exception) {
            return $this->error($exception->getMessage(), $exception->httpStatus());
        }

        return $this->success([
            'course_id' => (int) $purchase->course_id,
            'transaction_id' => $purchase->transaction_id,
            'is_enrolled' => true,
            'environment' => $purchase->environment,
        ], 'تم التحقق من عملية الشراء وتفعيل الدورة');
    }
}
