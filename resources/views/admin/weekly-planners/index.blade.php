@extends('admin.layouts.app')
@section('title', 'المفكرة الأسبوعية')

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">المفكرة الأسبوعية</h1>
        <p class="page-sub">إدارة صور المفكرة الأسبوعية وتواريخها</p>
    </div>
    <a href="{{ route('admin.weekly-planners.create') }}" class="btn-primary-sm">
        <i class="bi bi-plus-circle"></i> إضافة مفكرة
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصورة</th>
                    <th>العنوان</th>
                    <th>من تاريخ</th>
                    <th>إلى تاريخ</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($planners as $planner)
                <tr>
                    <td>{{ $planner->id }}</td>
                    <td>
                        <img src="{{ asset($planner->image) }}" alt="مفكرة"
                             style="width:80px;height:55px;object-fit:cover;border-radius:6px;">
                    </td>
                    <td>{{ $planner->title ?: '—' }}</td>
                    <td>{{ $planner->start_date->format('Y-m-d') }}</td>
                    <td>{{ $planner->end_date->format('Y-m-d') }}</td>
                    <td>
                        @if($planner->is_active)
                            <span class="badge bg-success">نشط</span>
                        @else
                            <span class="badge bg-secondary">معطّل</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.weekly-planners.edit', $planner) }}"
                               class="btn-icon-sm btn-edit" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.weekly-planners.destroy', $planner) }}"
                                  method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon-sm btn-delete" title="حذف">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">لا توجد مفكرات بعد.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($planners->hasPages())
    <div class="panel-card-body border-top">
        {{ $planners->links() }}
    </div>
    @endif
</div>

@endsection
