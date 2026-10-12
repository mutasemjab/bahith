@extends('admin.layouts.app')
@section('title', __('messages.worksheets'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.worksheets') }}</h1>
        <p class="page-sub">{{ __('messages.worksheets_sub') }}</p>
    </div>
    <a href="{{ route('admin.worksheets.create') }}" class="btn-primary-sm">
        <i class="bi bi-plus-circle"></i> {{ __('messages.add_new') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Filters --}}
<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm" placeholder="بحث...">
            </div>
            <div class="col-6 col-md-2">
                <select name="subject_id" class="form-select form-select-sm">
                    <option value="">كل المواد</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>
                            {{ $subject->full_path }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">كل الصفوف</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="year" class="form-select form-select-sm">
                    <option value="">كل السنوات</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-1">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All Status') }}</option>
                    <option value="1" @selected(request('status') === '1')>{{ __('messages.Active') }}</option>
                    <option value="0" @selected(request('status') === '0')>{{ __('messages.Inactive') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <button type="submit" class="btn-primary-sm w-100"><i class="bi bi-search"></i></button>
            </div>
            @if(request()->hasAny(['search','subject_id','class_id','year','status']))
            <div class="col-6 col-md-1">
                <a href="{{ route('admin.worksheets.index') }}" class="btn-outline-sm w-100"><i class="bi bi-x-lg"></i></a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0" style="overflow-x:auto">
        <table class="data-table" style="white-space:nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.title_ar_short') }}</th>
                    <th>{{ __('messages.subject') }}</th>
                    <th>الصف</th>
                    <th>{{ __('messages.year_label') }}</th>
                    <th>{{ __('messages.pdf_file_label') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($worksheets as $ws)
                <tr>
                    <td style="color:var(--muted)">{{ $ws->id }}</td>
                    <td>
                        <div style="font-weight:500">{{ $ws->title_ar }}</div>
                        @if($ws->title_en && $ws->title_en !== $ws->title_ar)
                            <div style="font-size:.75rem;color:var(--muted)">{{ $ws->title_en }}</div>
                        @endif
                    </td>
                    <td>
                        @if($ws->subject)
                            <span class="pill pill-info" style="font-size:.72rem">{{ $ws->subject->name_ar }}</span>
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                    <td style="color:var(--muted);font-size:.85rem">{{ $ws->schoolClass?->name ?? '—' }}</td>
                    <td style="color:var(--muted)">{{ $ws->year ?? '—' }}</td>
                    <td>
                        <a href="{{ asset('assets/uploads/worksheets/'.$ws->pdf_file) }}" target="_blank" class="btn-outline-sm" style="padding:3px 8px">
                            <i class="bi bi-file-earmark-pdf" style="color:#dc2626"></i> {{ __('messages.view_pdf') }}
                        </a>
                    </td>
                    <td>
                        {{ $ws->status
                            ? '<span class="pill pill-success">'.__('messages.Active').'</span>'
                            : '<span class="pill pill-neutral">'.__('messages.Inactive').'</span>' }}
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.worksheets.edit', $ws->id) }}" class="btn-outline-sm" style="padding:4px 8px">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.worksheets.destroy', $ws->id) }}"
                                  onsubmit="return confirm('{{ __('messages.delete_confirm') }}')">
                                @csrf @method('DELETE')
                                <button class="btn-outline-sm" style="padding:4px 8px;color:#dc2626;border-color:#fecaca">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4" style="color:var(--muted)">{{ __('messages.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $worksheets->links() }}</div>
    </div>
</div>

@endsection
