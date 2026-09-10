@extends('admin.layouts.app')
@section('title', 'جدول الحصص')

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">جدول الحصص</h1>
        <p class="page-sub">صورة جدول الحصص لكل صف</p>
    </div>
    <a href="{{ route('admin.class-schedules.create') }}" class="btn-primary-sm">
        <i class="bi bi-plus-lg"></i> إضافة جدول
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        @if($schedules->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar3" style="font-size:2.5rem;opacity:.3"></i>
                <p class="mt-2">لا توجد جداول بعد</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>الصف</th>
                        <th>الصورة</th>
                        <th style="width:160px">تاريخ الإضافة</th>
                        <th style="width:130px">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                    <tr>
                        <td>{{ $schedule->id }}</td>
                        <td>{{ $schedule->schoolClass->name ?? '—' }}</td>
                        <td>
                            <img src="{{ asset('assets/uploads/class-schedules/' . $schedule->image) }}"
                                 alt="class schedule"
                                 style="height:60px;width:160px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0">
                        </td>
                        <td>{{ $schedule->created_at->format('Y-m-d') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.class-schedules.edit', $schedule->id) }}" class="btn-outline-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.class-schedules.destroy', $schedule->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الجدول؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $schedules->links() }}
        @endif
    </div>
</div>

@endsection
