@extends('admin.layouts.app')
@section('title', __('messages.educational_notes'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.educational_notes') }}</h1>
        <p class="page-sub">{{ __('messages.educational_notes_sub') }}</p>
    </div>
    <a href="{{ route('admin.educational-notes.create') }}" class="btn-primary-sm">
        <i class="bi bi-plus-circle"></i> {{ __('messages.add_new') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:.78rem">{{ __('messages.class_label') }}</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">— {{ __('messages.select_class') }} —</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:.78rem">{{ __('messages.teacher') }}</label>
                <select name="teacher_id" class="form-select form-select-sm">
                    <option value="">— {{ __('messages.select_teacher') }} —</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:.78rem">{{ __('messages.date_label') }}</label>
                <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn-primary-sm w-100 justify-content-center"><i class="bi bi-search"></i> {{ __('messages.Search') }}</button>
                @if(request()->anyFilled(['class_id', 'teacher_id', 'date']))
                    <a href="{{ route('admin.educational-notes.index') }}" class="btn-outline-sm" title="{{ __('messages.Reset') }}"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.note_type') }}</th>
                        <th>{{ __('messages.title') }}</th>
                        <th>{{ __('messages.descriptions') }}</th>
                        <th>{{ __('messages.teacher') }}</th>
                        <th>{{ __('messages.class_label') }}</th>
                        <th>{{ __('messages.date_label') }}</th>
                        <th>{{ __('messages.attachment_label') }}</th>
                        <th width="150">{{ __('messages.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notes as $note)
                    <tr>
                        <td>{{ $note->id }}</td>
                        <td>
                            @if($note->type === 'lesson')
                                <span class="pill pill-info">{{ __('messages.note_type_lesson') }}</span>
                            @else
                                <span class="pill pill-warning">{{ __('messages.note_type_homework') }}</span>
                            @endif
                        </td>
                        <td>{{ $note->title }}</td>
                        <td style="max-width:260px;white-space:normal">{{ Str::limit($note->description, 120) ?: '—' }}</td>
                        <td>{{ $note->teacher?->name ?? '—' }}</td>
                        <td>{{ $note->schoolClass?->name ?? '—' }}</td>
                        <td>{{ $note->date?->format('Y-m-d') }}</td>
                        <td>
                            @if($note->image_list)
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($note->image_list as $img)
                                        <a href="{{ asset('assets/uploads/educational_notes/'.$img) }}" target="_blank" title="{{ __('messages.view_attachment') }}">
                                            <img src="{{ asset('assets/uploads/educational_notes/'.$img) }}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid var(--border)">
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.educational-notes.edit', $note->id) }}" class="btn btn-warning btn-sm">
                                {{ __('messages.Edit') }}
                            </a>
                            <form method="POST" action="{{ route('admin.educational-notes.destroy', $note->id) }}" style="display:inline-block">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('{{ __('messages.delete_confirm') }}')">
                                    {{ __('messages.Delete') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4" style="color:var(--muted)">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $notes->links() }}</div>
    </div>
</div>

@endsection
