@extends('admin.layouts.app')
@section('title', __('messages.conduct_edit'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.conduct_edit') }}</h1></div>
    <a href="{{ route('admin.conduct.index') }}" class="btn-outline-sm">
        <i class="bi bi-arrow-left"></i> {{ __('messages.Back') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.conduct.update') }}" method="POST">
@csrf @method('PUT')

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="panel-card mb-3">
            <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.conduct_info') }}</h2></div>
            <div class="panel-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.conduct_title_ar') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title_ar" value="{{ old('title_ar', $document->title_ar) }}"
                               class="form-control @error('title_ar') is-invalid @enderror" dir="rtl" required>
                        @error('title_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.conduct_title_en') }}</label>
                        <input type="text" name="title_en" value="{{ old('title_en', $document->title_en) }}"
                               class="form-control @error('title_en') is-invalid @enderror">
                        @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.conduct_body') }} <span class="text-danger">*</span></label>
                        <textarea name="body" rows="30" dir="rtl"
                                  class="form-control @error('body') is-invalid @enderror"
                                  style="font-size:.9rem;line-height:1.8">{{ old('body', $document->body) }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   id="is_active" @checked(old('is_active', $document->is_active ?? true))>
                            <label class="form-check-label" for="is_active">{{ __('messages.Active') }}</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn-primary-sm">
                            <i class="bi bi-save"></i> {{ __('messages.save_changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection
