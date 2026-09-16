@extends('teacher.layouts.app')
@section('title', __('messages.educational_notes'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.edit_note') }}</h1></div>
    <a href="{{ route('teacher.educational-notes.index') }}" class="btn-outline-sm">
        <i class="bi bi-arrow-left"></i> {{ __('messages.Back') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
@endif

<form action="{{ route('teacher.educational-notes.update', $educationalNote->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="panel-card">
                <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.note_details') }}</h2></div>
                <div class="panel-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.note_type') }} <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type-lesson" value="lesson"
                                           {{ old('type', $educationalNote->type) === 'lesson' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type-lesson">
                                        <i class="bi bi-book"></i> {{ __('messages.note_type_lesson') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type-homework" value="homework"
                                           {{ old('type', $educationalNote->type) === 'homework' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type-homework">
                                        <i class="bi bi-pencil-square"></i> {{ __('messages.note_type_homework') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $educationalNote->title) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.description') }}</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description', $educationalNote->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="panel-card mb-3">
                <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.additional_info') }}</h2></div>
                <div class="panel-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.class_label') }}</label>
                            <select name="class_id" class="form-control">
                                <option value="">— {{ __('messages.select_class') }} —</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}"
                                        {{ old('class_id', $educationalNote->class_id) == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.date_label') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', $educationalNote->date?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.attachment_label') }}</label>
                            @if($educationalNote->image_list)
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    @foreach($educationalNote->image_list as $img)
                                        <div class="text-center" style="font-size:.75rem">
                                            <a href="{{ asset('assets/uploads/educational_notes/'.$img) }}" target="_blank">
                                                <img src="{{ asset('assets/uploads/educational_notes/'.$img) }}" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
                                            </a>
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" name="remove_images[]" value="{{ $img }}" id="rm-{{ $loop->index }}">
                                                <label class="form-check-label" for="rm-{{ $loop->index }}">{{ __('messages.remove_image') }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted" style="font-size:.75rem">{{ __('messages.attachment_hint') }}</small>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-primary-sm w-100 justify-content-center" style="padding:12px">
                <i class="bi bi-save"></i> {{ __('messages.Save') }}
            </button>
        </div>
    </div>
</form>

@endsection
