<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConductDocument;
use App\Models\ConductSignature;
use Illuminate\Http\Request;

class ConductDocumentController extends Controller
{
    public function index()
    {
        $document = ConductDocument::active() ?? ConductDocument::latest()->first();
        return view('admin.conduct.index', compact('document'));
    }

    public function edit()
    {
        $document = ConductDocument::active() ?? new ConductDocument([
            'title_ar' => 'مدونة السلوك والانضباط الداخلي للطلبة',
            'title_en' => 'Student Code of Conduct',
            'body'     => '',
            'is_active' => true,
        ]);
        return view('admin.conduct.edit', compact('document'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'title_ar'  => 'required|string|max:300',
            'title_en'  => 'required|string|max:300',
            'body'      => 'required|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $document = ConductDocument::active() ?? new ConductDocument();
        $document->fill($data)->save();

        return redirect()->route('admin.conduct.index')
            ->with('success', __('messages.conduct_updated'));
    }

    public function signatures(Request $request)
    {
        $document = ConductDocument::active() ?? ConductDocument::latest()->first();

        $signatures = ConductSignature::with('student')
            ->when($document, fn ($q) => $q->where('document_id', $document->id))
            ->when($request->search, fn ($q, $s) => $q->whereHas('student', fn ($sq) =>
                $sq->where('name', 'like', "%{$s}%")
            ))
            ->orderByDesc('signed_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.conduct.signatures', compact('document', 'signatures'));
    }
}
