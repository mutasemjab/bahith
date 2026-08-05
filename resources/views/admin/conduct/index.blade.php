@extends('admin.layouts.app')
@section('title', __('messages.conduct_title'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.conduct_title') }}</h1>
        <p class="page-sub">{{ __('messages.conduct_desc') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.conduct.signatures') }}" class="btn-outline-sm">
            <i class="bi bi-pen"></i> {{ __('messages.conduct_signatures') }}
            @if($document)
                <span class="badge bg-primary ms-1">{{ $document->signatures()->count() }}</span>
            @endif
        </a>
        <a href="{{ route('admin.conduct.edit') }}" class="btn-primary-sm">
            <i class="bi bi-pencil-square"></i> {{ __('messages.conduct_edit') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(! $document)
    <div class="panel-card">
        <div class="panel-card-body text-center py-5" style="color:var(--muted)">
            <i class="bi bi-file-earmark-text" style="font-size:3rem"></i>
            <p class="mt-3">{{ __('messages.conduct_no_document') }}</p>
            <a href="{{ route('admin.conduct.edit') }}" class="btn-primary-sm mt-2">
                <i class="bi bi-plus-circle"></i> {{ __('messages.conduct_create') }}
            </a>
        </div>
    </div>
@else
    <div class="panel-card">
        <div class="panel-card-header d-flex align-items-center justify-content-between">
            <h2 class="panel-card-title">{{ $document->title_ar }}</h2>
            <span class="pill {{ $document->is_active ? 'pill-success' : 'pill-neutral' }}">
                {{ $document->is_active ? __('messages.Active') : __('messages.Inactive') }}
            </span>
        </div>
        <div class="panel-card-body" style="white-space:pre-wrap;line-height:2;direction:rtl;font-size:.95rem">{{ $document->body }}</div>
    </div>
@endif

@endsection
