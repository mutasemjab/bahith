@extends('teacher.layouts.app')
@section('title', 'نتائج: ' . ($exam->title_ar ?: $exam->title_en))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">نتائج الطلاب</h1>
        <p class="page-sub">{{ $exam->title_ar ?: $exam->title_en }} · {{ $attempts->count() }} طالب أجرى الاختبار</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('teacher.exams.show', $exam->id) }}" class="btn-outline-sm"><i class="bi bi-arrow-left"></i> رجوع للاختبار</a>
    </div>
</div>

@if($attempts->isEmpty())
    <div class="panel-card">
        <div class="panel-card-body text-center py-5" style="color:var(--muted)">
            <i class="bi bi-inbox" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>
            لم يُجرِ أي طالب هذا الاختبار بعد.
        </div>
    </div>
@else

{{-- Summary table --}}
<div class="panel-card mb-4">
    <div class="panel-card-header">
        <h2 class="panel-card-title">ملخص النتائج</h2>
    </div>
    <div class="panel-card-body p-0" style="overflow-x:auto">
        <table class="data-table" style="white-space:nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الطالب</th>
                    <th>الدرجة</th>
                    <th>النسبة</th>
                    <th>الحالة</th>
                    <th>وقت التقديم</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($attempts as $attempt)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $attempt->student->name ?? '—' }}</td>
                    <td>{{ $attempt->score }} / {{ $attempt->total_marks }}</td>
                    <td>{{ number_format($attempt->percentage, 1) }}%</td>
                    <td>
                        @if($attempt->is_passed)
                            <span class="pill pill-success">ناجح</span>
                        @else
                            <span class="pill pill-warning">راسب</span>
                        @endif
                    </td>
                    <td>{{ $attempt->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>
                        <a href="#attempt-{{ $attempt->id }}" class="btn-outline-sm" style="padding:3px 8px">
                            <i class="bi bi-eye"></i> الإجابات
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Per-student answer breakdown --}}
@foreach($attempts as $attempt)
<div class="panel-card mb-4" id="attempt-{{ $attempt->id }}">
    <div class="panel-card-header">
        <h2 class="panel-card-title">
            {{ $attempt->student->name ?? 'طالب #'.$attempt->student_id }}
            <span class="pill pill-{{ $attempt->is_passed ? 'success' : 'warning' }}" style="margin-inline-start:8px">
                {{ $attempt->score }}/{{ $attempt->total_marks }} ({{ number_format($attempt->percentage, 1) }}%)
            </span>
        </h2>
    </div>
    <div class="panel-card-body">
        @forelse($attempt->answers as $answer)
        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--border)">
            <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                <div style="font-weight:500;flex:1">
                    <span style="color:var(--primary)">س{{ $loop->iteration }}.</span>
                    {{ $answer->question->question_ar ?? $answer->question->question_en ?? '—' }}
                </div>
                <div style="flex-shrink:0">
                    @if($answer->is_correct)
                        <span class="pill pill-success"><i class="bi bi-check-lg"></i> صحيح</span>
                    @else
                        <span class="pill pill-warning"><i class="bi bi-x-lg"></i> خطأ</span>
                    @endif
                    <span class="pill pill-info ms-1">{{ $answer->marks_earned ?? 0 }} علامة</span>
                </div>
            </div>

            @if($answer->question && $answer->question->options->count())
            <div class="ps-3">
                @foreach($answer->question->options as $opt)
                @php
                    $isSelected = $answer->selected_option_id === $opt->id;
                @endphp
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-{{ $opt->is_correct ? 'check-circle-fill' : ($isSelected && !$opt->is_correct ? 'x-circle-fill' : 'circle') }}"
                       style="color:{{ $opt->is_correct ? '#059669' : ($isSelected && !$opt->is_correct ? '#dc2626' : 'var(--muted)') }};flex-shrink:0"></i>
                    <span style="font-size:.85rem;{{ $isSelected ? 'font-weight:600' : '' }}">
                        {{ $opt->option_text_ar ?: $opt->option_text_en }}
                        @if($isSelected && !$opt->is_correct)
                            <span style="color:#dc2626;font-size:.78rem"> (إجابة الطالب)</span>
                        @elseif($isSelected && $opt->is_correct)
                            <span style="color:#059669;font-size:.78rem"> (إجابة الطالب ✓)</span>
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
            @elseif($answer->selectedOption)
            <div class="ps-3" style="font-size:.85rem;color:var(--muted)">
                إجابة الطالب: <strong style="color:var(--text)">{{ $answer->selectedOption->option_text_ar }}</strong>
            </div>
            @endif
        </div>
        @empty
        <p style="color:var(--muted);font-size:.9rem">لا توجد إجابات مسجلة.</p>
        @endforelse
    </div>
</div>
@endforeach

@endif

@endsection
