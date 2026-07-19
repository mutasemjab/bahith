@extends('admin.layouts.app')
@section('title', 'إضافة مفكرة أسبوعية')

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">إضافة مفكرة أسبوعية</h1>
        <p class="page-sub">رفع صورة المفكرة وتحديد الفترة الزمنية</p>
    </div>
    <a href="{{ route('admin.weekly-planners.index') }}" class="btn-outline-sm">
        <i class="bi bi-arrow-left"></i> رجوع
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="row g-3">
<div class="col-12 col-xl-7">
<form action="{{ route('admin.weekly-planners.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="panel-card">
    <div class="panel-card-header"><h2 class="panel-card-title">بيانات المفكرة</h2></div>
    <div class="panel-card-body">
        <div class="row g-3">

            <div class="col-12">
                <label class="form-label">العنوان <span class="text-muted">(اختياري)</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="form-control @error('title') is-invalid @enderror"
                       placeholder="مثال: مفكرة الأسبوع الأول — يوليو 2026">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">من تاريخ <span class="text-danger">*</span></label>
                <input type="date" name="start_date" value="{{ old('start_date', $defaultStart) }}"
                       class="form-control @error('start_date') is-invalid @enderror" required>
                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">إلى تاريخ <span class="text-danger">*</span></label>
                <input type="date" name="end_date" value="{{ old('end_date', $defaultEnd) }}"
                       class="form-control @error('end_date') is-invalid @enderror" required>
                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">صورة المفكرة <span class="text-danger">*</span></label>
                <input type="file" name="image" accept="image/*"
                       class="form-control @error('image') is-invalid @enderror"
                       onchange="previewImage(this)" required>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div id="imgPreview" class="mt-2" style="display:none">
                    <img id="previewEl" src="" alt="معاينة"
                         style="max-width:100%;max-height:300px;border-radius:8px;object-fit:contain;">
                </div>
            </div>

            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="form-check-input" {{ old('is_active', '1') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">نشط (ظاهر للطلاب)</label>
                </div>
            </div>

        </div>
    </div>
    <div class="panel-card-footer d-flex gap-2">
        <button type="submit" class="btn-primary-sm">
            <i class="bi bi-check-circle"></i> حفظ
        </button>
        <a href="{{ route('admin.weekly-planners.index') }}" class="btn-outline-sm">إلغاء</a>
    </div>
</div>

</form>
</div>
</div>

@endsection

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewEl').src = e.target.result;
            document.getElementById('imgPreview').style.display = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
