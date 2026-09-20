@extends('teacher.layouts.app')
@section('title', 'تقدم الطلاب — ' . ($course->title_ar ?: $course->title_en))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">تقدم الطلاب</h1>
        <p class="page-sub">{{ $course->title_ar ?: $course->title_en }} · {{ $students->count() }} طالب · {{ $totalLessons }} درس</p>
    </div>
    <a href="{{ route('teacher.courses.show', $course->id) }}" class="btn-outline-sm">
        <i class="bi bi-arrow-left"></i> رجوع للكورس
    </a>
</div>

@php
    $avgPct = $students->count()
        ? round($students->avg(fn($s) => $enrollmentsByStudent[$s->id]->progress_percentage
            ?? ($totalLessons ? round(($completedByStudent[$s->id] ?? 0) / $totalLessons * 100) : 0)))
        : 0;
    $completedCount = $students->filter(fn($s) => ($enrollmentsByStudent[$s->id]->is_completed ?? false))->count();
@endphp

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="panel-card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:var(--primary)">{{ $students->count() }}</div>
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
            <div style="font-size:1.6rem;font-weight:700;color:#d97706">{{ $avgPct }}%</div>
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

{{-- Table --}}
<div class="panel-card">
    <div class="panel-card-body p-0" style="overflow-x:auto">
        @if($students->isEmpty())
        <div class="text-center py-5" style="color:var(--muted)">
            <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>
            لا يوجد طلاب مرتبطون بهذا الكورس بعد.
        </div>
        @else
        <table class="data-table" style="white-space:nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الطالب</th>
                    <th>الدروس المنجزة</th>
                    <th>التقدم</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                @php
                    $enrollment       = $enrollmentsByStudent[$student->id] ?? null;
                    $completedLessons = $completedByStudent[$student->id] ?? 0;
                    $pct = $enrollment
                        ? $enrollment->progress_percentage
                        : ($totalLessons ? round($completedLessons / $totalLessons * 100) : 0);
                    $barColor = $pct >= 100 ? '#059669' : ($pct >= 50 ? '#d97706' : 'var(--primary)');
                    $isCompleted = $enrollment?->is_completed ?? false;
                @endphp
                <tr>
                    <td style="color:var(--muted)">{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight:500">{{ $student->name }}</div>
                        <div style="font-size:.75rem;color:var(--muted)">{{ $student->phone ?? '' }}</div>
                    </td>
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
                    <td>
                        @if($isCompleted)
                            <span class="pill pill-success">مكتمل</span>
                        @elseif($completedLessons > 0)
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
