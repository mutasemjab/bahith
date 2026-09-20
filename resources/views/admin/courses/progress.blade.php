@extends('admin.layouts.app')
@section('title', 'تقدم الطلاب — ' . ($course->title_ar ?: $course->title_en))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">تقدم الطلاب</h1>
        <p class="page-sub">{{ $course->title_ar ?: $course->title_en }} · {{ $enrollments->count() }} طالب مسجل · {{ $totalLessons }} درس</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.courses.show', $course->id) }}" class="btn-outline-sm">
            <i class="bi bi-arrow-left"></i> رجوع للكورس
        </a>
    </div>
</div>

{{-- Stats row --}}
@php
    $avgProgress    = $enrollments->avg('progress_percentage') ?? 0;
    $completedCount = $enrollments->where('is_completed', true)->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="panel-card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:var(--primary)">{{ $enrollments->count() }}</div>
            <div style="font-size:.8rem;color:var(--muted)">إجمالي الطلاب</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="panel-card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#059669">{{ $completedCount }}</div>
            <div style="font-size:.8rem;color:var(--muted)">أتمّوا الكورس</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="panel-card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#d97706">{{ round($avgProgress) }}%</div>
            <div style="font-size:.8rem;color:var(--muted)">متوسط التقدم</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="panel-card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:var(--text)">{{ $totalLessons }}</div>
            <div style="font-size:.8rem;color:var(--muted)">إجمالي الدروس</div>
        </div>
    </div>
</div>

{{-- Students table --}}
<div class="panel-card">
    <div class="panel-card-body p-0" style="overflow-x:auto">
        @if($enrollments->isEmpty())
        <div class="text-center py-5" style="color:var(--muted)">
            <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>
            لا يوجد طلاب مسجلون في هذا الكورس بعد.
        </div>
        @else
        <table class="data-table" style="white-space:nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الطالب</th>
                    <th>الهاتف</th>
                    <th>الدروس المنجزة</th>
                    <th>التقدم</th>
                    <th>تاريخ التسجيل</th>
                    <th>ينتهي في</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrollments as $enrollment)
                @php
                    $completedLessons = $completedByStudent[$enrollment->student_id] ?? 0;
                    $pct = $enrollment->progress_percentage;
                    $barColor = $pct >= 100 ? '#059669' : ($pct >= 50 ? '#d97706' : 'var(--primary)');
                    $expired = $enrollment->expires_at && $enrollment->expires_at->isPast();
                @endphp
                <tr>
                    <td style="color:var(--muted)">{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('admin.students.show', $enrollment->student_id) }}" style="font-weight:500;color:var(--primary)">
                            {{ $enrollment->student->name ?? '—' }}
                        </a>
                    </td>
                    <td style="color:var(--muted);font-size:.82rem">{{ $enrollment->student->phone ?? '—' }}</td>
                    <td>
                        <span style="font-weight:600">{{ $completedLessons }}</span>
                        <span style="color:var(--muted)"> / {{ $totalLessons }}</span>
                    </td>
                    <td style="min-width:160px">
                        <div class="d-flex align-items-center gap-2">
                            <div style="flex:1;height:8px;background:#e2e8f0;border-radius:99px;overflow:hidden">
                                <div style="height:100%;width:{{ $pct }}%;background:{{ $barColor }};border-radius:99px"></div>
                            </div>
                            <span style="font-size:.8rem;font-weight:600;min-width:36px">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td style="color:var(--muted);font-size:.82rem">{{ $enrollment->enrolled_at?->format('Y-m-d') ?? '—' }}</td>
                    <td style="font-size:.82rem">
                        @if($enrollment->expires_at)
                            <span style="color:{{ $expired ? '#dc2626' : 'var(--muted)' }}">
                                {{ $enrollment->expires_at->format('Y-m-d') }}
                                @if($expired) <span class="pill pill-warning" style="font-size:.7rem">منتهي</span> @endif
                            </span>
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                    <td>
                        @if($enrollment->is_completed)
                            <span class="pill pill-success">مكتمل</span>
                        @elseif($pct > 0)
                            <span class="pill pill-warning">جارٍ</span>
                        @else
                            <span class="pill pill-neutral">لم يبدأ</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

@endsection
