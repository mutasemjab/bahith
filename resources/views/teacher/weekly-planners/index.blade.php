@extends('teacher.layouts.app')
@section('title', 'المفكرة الأسبوعية')

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">المفكرة الأسبوعية</h1>
        <p class="page-sub">إدارة صور المفكرة الأسبوعية وتواريخها</p>
    </div>
    <a href="{{ route('teacher.weekly-planners.create') }}" class="btn-primary-sm">
        <i class="bi bi-plus-circle"></i> إضافة مفكرة
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    @forelse($planners as $planner)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="panel-card h-100">
            <div style="position:relative">
                <img src="{{ asset($planner->image) }}" alt="مفكرة"
                     style="width:100%;height:180px;object-fit:cover;border-radius:12px 12px 0 0;">
                @if($planner->is_active)
                    <span class="pill pill-success" style="position:absolute;top:10px;right:10px">نشط</span>
                @else
                    <span class="pill pill-neutral" style="position:absolute;top:10px;right:10px">معطّل</span>
                @endif
            </div>
            <div class="panel-card-body">
                @if($planner->title)
                    <h3 style="font-size:.95rem;font-weight:600;margin-bottom:6px">{{ $planner->title }}</h3>
                @endif
                <div style="font-size:.8rem;color:var(--muted);display:flex;gap:10px;flex-wrap:wrap;margin-bottom:4px">
                    @if($planner->schoolClass)
                        <span><i class="bi bi-people"></i> {{ $planner->schoolClass->name }}</span>
                    @endif
                    <span><i class="bi bi-calendar-range"></i>
                        {{ $planner->start_date->format('Y-m-d') }} — {{ $planner->end_date->format('Y-m-d') }}
                    </span>
                </div>
            </div>
            <div class="panel-card-footer d-flex gap-2" style="padding:10px 16px;border-top:1px solid var(--border)">
                <a href="{{ route('teacher.weekly-planners.edit', $planner->id) }}"
                   class="btn-outline-sm" style="font-size:.8rem;padding:4px 10px">
                    <i class="bi bi-pencil"></i> تعديل
                </a>
                <form method="POST" action="{{ route('teacher.weekly-planners.destroy', $planner->id) }}">
                    @csrf @method('DELETE')
                    <button class="btn-outline-sm" style="font-size:.8rem;padding:4px 10px;color:#dc2626;border-color:#fecaca"
                            onclick="return confirm('هل أنت متأكد من الحذف؟')">
                        <i class="bi bi-trash"></i> حذف
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="panel-card">
            <div class="panel-card-body text-center py-5">
                <div style="font-size:48px;margin-bottom:12px">📅</div>
                <p style="color:var(--muted)">لا توجد مفكرات أسبوعية بعد.</p>
                <a href="{{ route('teacher.weekly-planners.create') }}" class="btn-primary-sm">
                    <i class="bi bi-plus-circle"></i> إضافة أول مفكرة
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($planners->hasPages())
    <div class="mt-3">{{ $planners->links() }}</div>
@endif

@endsection
