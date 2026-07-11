@extends('admin.layouts.app')
@section('title', __('messages.teachers_title'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.teachers_title') }}</h1><p class="page-sub">{{ __('messages.manage_teachers_desc') }}</p></div>
    <a href="{{ route('admin.teachers.create') }}" class="btn-primary-sm"><i class="bi bi-plus-circle"></i> {{ __('messages.add_teacher') }}</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('messages.search_name_email_ph') }}">
            </div>
            <div class="col-6 col-md-3">
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All Status') }}</option>
                    <option value="1" @selected(request('is_active') === '1')>{{ __('messages.Active') }}</option>
                    <option value="0" @selected(request('is_active') === '0')>{{ __('messages.Inactive') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="btn-primary-sm w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0">
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>{{ __('messages.teacher') }}</th><th>{{ __('messages.specialization') }}</th><th>{{ __('messages.courses') }}</th><th>{{ __('messages.rating') }}</th><th>{{ __('messages.Status') }}</th><th>{{ __('messages.Actions') }}</th></tr>
            </thead>
            <tbody>
                @forelse($teachers as $teacher)
                <tr>
                    <td style="color:var(--muted)">{{ $teacher->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($teacher->avatar)
                                <img src="{{ asset('assets/uploads/teachers/'.$teacher->avatar) }}" class="avatar avatar-sm" alt="">
                            @else
                                <div class="avatar avatar-sm" style="background:#ecfdf5;color:#059669">{{ strtoupper(substr($teacher->name,0,1)) }}</div>
                            @endif
                            <div>
                                <div style="font-weight:500">{{ $teacher->name }}</div>
                                <div style="font-size:.75rem;color:var(--muted)">{{ $teacher->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--muted)">{{ $teacher->specialization_en ?: $teacher->specialization_ar ?: '—' }}</td>
                    <td>{{ $teacher->courses_count }}</td>
                    <td>
                        <span style="color:#ea580c;font-weight:600">
                            <i class="bi bi-star-fill" style="font-size:.75rem"></i> {{ number_format($teacher->average_rating, 1) }}
                        </span>
                    </td>
                    <td>
                        <span class="pill {{ $teacher->is_active ? 'pill-success' : 'pill-neutral' }}">
                            {{ $teacher->is_active ? __('messages.Active') : __('messages.Inactive') }}
                        </span>
                        @if($teacher->is_verified)<span class="pill pill-info">{{ __('messages.verified') }}</span>@endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn-outline-sm" style="padding:4px 8px"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.teachers.destroy', $teacher->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_teacher_confirm') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-outline-sm" style="padding:4px 8px;color:#dc2626;border-color:#fecaca"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4" style="color:var(--muted)">{{ __('messages.no_teachers_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $teachers->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
