@extends('admin.layouts.app')
@section('title', 'سجل النشاطات')

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title"><i class="bi bi-clock-history me-2"></i>سجل النشاطات</h1>
        <p class="page-sub">كل إجراء يقوم به الأدمن مسجّل هنا</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Filters --}}
<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="ابحث في الوصف...">
            </div>
            <div class="col-6 col-md-2">
                <select name="admin_id" class="form-select form-select-sm">
                    <option value="">كل الأدمن</option>
                    @foreach($admins as $a)
                    <option value="{{ $a->id }}" @selected(request('admin_id') == $a->id)>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="action" class="form-select form-select-sm">
                    <option value="">كل الإجراءات</option>
                    @foreach($actions as $act)
                    <option value="{{ $act }}" @selected(request('action') === $act)>{{ $act }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" placeholder="من تاريخ">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" placeholder="إلى تاريخ">
            </div>
            <div class="col-12 col-md-1">
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
                    <th>الأدمن</th>
                    <th>الإجراء</th>
                    <th>القسم</th>
                    <th>الوصف</th>
                    <th>IP</th>
                    <th>الوقت</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="color:var(--muted)">{{ $log->id }}</td>
                    <td>
                        <div style="font-weight:500;font-size:.85rem">{{ $log->admin_name }}</div>
                    </td>
                    <td>
                        @php
                            $badge = match($log->action) {
                                'create' => 'pill-success',
                                'update' => 'pill-info',
                                'delete' => 'pill-warning',
                                'login'  => 'pill-neutral',
                                default  => 'pill-neutral',
                            };
                            $label = match($log->action) {
                                'create' => 'إضافة',
                                'update' => 'تعديل',
                                'delete' => 'حذف',
                                'login'  => 'دخول',
                                'logout' => 'خروج',
                                default  => $log->action,
                            };
                        @endphp
                        <span class="pill {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td style="color:var(--muted);font-size:.82rem">{{ $log->module ?: '—' }}</td>
                    <td style="font-size:.85rem;max-width:320px">{{ $log->description }}</td>
                    <td style="color:var(--muted);font-size:.78rem;font-family:monospace">{{ $log->ip_address }}</td>
                    <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4" style="color:var(--muted)">لا توجد سجلات</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3 d-flex justify-content-between align-items-center">
            <div>{{ $logs->withQueryString()->links() }}</div>
            @if($logs->total() > 0)
            <form action="{{ route('admin.activity-log.destroy', $logs->last()?->id ?? 0) }}" method="POST"
                  onsubmit="return confirm('حذف كل السجلات الأقدم من الصفحة الحالية؟')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-outline-sm" style="color:#dc2626;border-color:#fecaca;font-size:.78rem">
                    <i class="bi bi-trash"></i> حذف القديم
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@endsection
