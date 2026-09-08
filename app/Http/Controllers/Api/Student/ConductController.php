<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\ConductDocument;
use App\Models\ConductSignature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConductController extends Controller
{
    use ApiResponse;

    public function show(): JsonResponse
    {
        $document = ConductDocument::active();

        if (! $document) {
            return $this->error('لا توجد مدونة سلوك منشورة حالياً', 404);
        }

        return $this->success([
            'id'       => $document->id,
            'title_ar' => $document->title_ar,
            'title_en' => $document->title_en,
            'body'     => $document->body,
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $document = ConductDocument::active();

        if (! $document) {
            return $this->success(['signed' => false, 'document_id' => null]);
        }

        $signed = ConductSignature::where('student_id', $request->user()->id)
            ->where('document_id', $document->id)
            ->exists();

        return $this->success([
            'signed'      => $signed,
            'document_id' => $document->id,
        ]);
    }

    public function sign(Request $request): JsonResponse
    {
        $document = ConductDocument::active();

        if (! $document) {
            return $this->error('لا توجد مدونة سلوك منشورة حالياً', 404);
        }

        $request->validate([
            'guardian_name' => ['required', 'string', 'max:200'],
        ]);

        $already = ConductSignature::where('student_id', $request->user()->id)
            ->where('document_id', $document->id)
            ->exists();

        if ($already) {
            return $this->error('لقد وقّعت على مدونة السلوك مسبقاً', 422);
        }

        ConductSignature::create([
            'student_id'   => $request->user()->id,
            'document_id'  => $document->id,
            'guardian_name' => $request->guardian_name,
            'signed_at'    => now(),
        ]);

        return $this->success(null, 'تم التوقيع على مدونة السلوك بنجاح', 201);
    }
}
