@extends('admin.layouts.app')
@section('title', __('messages.conduct_signatures'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.conduct_signatures') }}</h1>
        <p class="page-sub">{{ $document?->title_ar }}</p>
    </div>
    <a href="{{ route('admin.conduct.index') }}" class="btn-outline-sm">
        <i class="bi bi-arrow-left"></i> {{ __('messages.Back') }}
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
                       class="form-control form-control-sm" placeholder="{{ __('messages.search_student_ph') }}">
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
                    <th>{{ __('messages.student') }}</th>
                    <th>{{ __('messages.conduct_guardian_name') }}</th>
                    <th>{{ __('messages.conduct_signed_at') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($signatures as $sig)
                <tr>
                    <td style="color:var(--muted)">{{ $sig->id }}</td>
                    <td>
                        <div style="font-weight:600">{{ $sig->student?->name ?? '—' }}</div>
                        <div style="font-size:.8rem;color:var(--muted)">{{ $sig->student?->national_id }}</div>
                    </td>
                    <td>{{ $sig->guardian_name }}</td>
                    <td style="color:var(--muted);font-size:.85rem">{{ $sig->signed_at?->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4" style="color:var(--muted)">
                        {{ __('messages.conduct_no_signatures') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $signatures->withQueryString()->links() }}</div>
    </div>
</div>

@endsection
