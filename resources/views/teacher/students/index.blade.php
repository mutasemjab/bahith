@extends('teacher.layouts.app')
@section('title', 'طلابي')

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">طلابي</h1>
        <p class="page-sub">الطلاب في الصفوف التي تدرّسها</p>
    </div>
    <div style="font-size:.85rem;color:var(--muted);background:var(--surface);padding:8px 16px;border-radius:8px;border:1px solid var(--border)">
        <i class="bi bi-people-fill me-1" style="color:var(--primary)"></i>
        إجمالي الطلاب: <strong>{{ $totalStudents }}</strong>
    </div>
</div>

@if($classes->isEmpty())
    <div class="panel-card">
        <div class="panel-card-body text-center py-5">
            <i class="bi bi-person-x" style="font-size:3rem;color:var(--muted);display:block;margin-bottom:12px"></i>
            <p style="color:var(--muted);margin:0">لم يتم تعيينك لأي صف بعد.<br>تواصل مع الإدارة لتعيين الصفوف.</p>
        </div>
    </div>
@else
    @foreach($classes as $class)
    @php
        $subjects   = $subjectsByClass->get($class->id, collect());
        $isHomeroom = $homeroomClassId == $class->id;
        $students   = $class->students;
    @endphp

    <div class="panel-card mb-3">
        <div class="panel-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#7c3aed,#6d28d9);display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-building" style="color:#fff;font-size:.9rem"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:1rem">{{ $class->name }}</div>
                    <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                        @if($isHomeroom)
                            <span class="pill pill-success" style="font-size:.72rem"><i class="bi bi-star-fill"></i> مربي الصف</span>
                        @endif
                        @foreach($subjects as $subject)
                            <span class="pill pill-info" style="font-size:.72rem">{{ $subject->name_ar }}</span>
                        @endforeach
                        @if($subjects->isEmpty() && !$isHomeroom)
                            <span style="font-size:.78rem;color:var(--muted)">بدون مادة محددة</span>
                        @endif
                    </div>
                </div>
            </div>
            <span style="font-size:.83rem;color:var(--muted)">
                <i class="bi bi-people"></i> {{ $students->count() }} طالب
            </span>
        </div>

        <div class="panel-card-body p-0">
            @if($students->isEmpty())
                <div class="text-center py-4" style="color:var(--muted);font-size:.85rem">
                    لا يوجد طلاب في هذا الصف
                </div>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>رقم الهوية</th>
                            <th>الجنس</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $student)
                        <tr>
                            <td style="color:var(--muted);font-size:.83rem">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($student->avatar)
                                        <img src="{{ asset('assets/uploads/students/'.$student->avatar) }}"
                                             class="rounded-circle" style="width:32px;height:32px;object-fit:cover">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                             style="width:32px;height:32px;background:linear-gradient(135deg,#0ea5e9,#0284c7);flex-shrink:0">
                                            <span style="color:#fff;font-size:.8rem;font-weight:700">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:500;font-size:.88rem">{{ $student->name }}</div>
                                        @if($student->phone)
                                            <div style="font-size:.75rem;color:var(--muted)">{{ $student->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:.83rem;color:var(--muted)">{{ $student->national_id ?? '—' }}</td>
                            <td style="font-size:.83rem">
                                @if($student->gender === 'male')
                                    <span style="color:#0284c7"><i class="bi bi-gender-male"></i> ذكر</span>
                                @elseif($student->gender === 'female')
                                    <span style="color:#db2777"><i class="bi bi-gender-female"></i> أنثى</span>
                                @else
                                    <span style="color:var(--muted)">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="pill {{ $student->is_active ? 'pill-success' : 'pill-neutral' }}" style="font-size:.75rem">
                                    {{ $student->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @endforeach
@endif

@endsection
