@extends('admin.layouts.app')
@section('title', __('messages.classes_title'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.classes_title') }}</h1>
        <p class="page-sub">{{ __('messages.manage_classes_desc') }}</p>
    </div>
    <a href="{{ route('admin.school-classes.create') }}" class="btn-primary-sm">
        <i class="bi bi-plus-circle"></i> {{ __('messages.add_class') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-10">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm" placeholder="{{ __('messages.search_class_ph') }}">
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn-primary-sm w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.class_name') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $class)
                <tr>
                    <td style="color:var(--muted)">{{ $class->id }}</td>
                    <td>
                        <div style="font-weight:600">{{ $class->name }}</div>
                    </td>
                    <td>
                        <span class="pill {{ $class->is_active ? 'pill-success' : 'pill-neutral' }}">
                            {{ $class->is_active ? __('messages.Active') : __('messages.Inactive') }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.school-classes.edit', $class->id) }}"
                               class="btn-outline-sm" style="padding:4px 8px">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.school-classes.destroy', $class->id) }}" method="POST"
                                  onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="btn-outline-sm" style="padding:4px 8px;color:#dc2626;border-color:#fecaca">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4" style="color:var(--muted)">
                        {{ __('messages.no_classes_yet') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $classes->withQueryString()->links() }}</div>
    </div>
</div>

@endsection
