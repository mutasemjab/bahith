@extends('admin.layouts.app')
@section('title', 'تعديل جدول امتحانات')

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">تعديل جدول امتحانات</h1></div>
    <a href="{{ route('admin.exam-schedules.index') }}" class="btn-outline-sm">
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
<form action="{{ route('admin.exam-schedules.update', $examSchedule->id) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="panel-card">
    <div class="panel-card-header"><h2 class="panel-card-title">بيانات الجدول</h2></div>
    <div class="panel-card-body">
        <div class="row g-3">

            <div class="col-12">
                <label class="form-label">الصف <span class="text-danger">*</span></label>
                <select name="class_id" class="form-control @error('class_id') is-invalid @enderror" required>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id', $examSchedule->class_id) == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">الصورة الحالية</label><br>
                <img src="{{ asset('assets/uploads/exam-schedules/' . $examSchedule->image) }}"
                     id="previewImg"
                     style="width:100%;max-height:320px;object-fit:contain;border-radius:10px;border:1px solid #e2e8f0">
            </div>

            <div class="col-12">
                <label class="form-label">تغيير الصورة (اتركه فارغاً للإبقاء على الحالية)</label>
                <input type="file" name="image" accept="image/*"
                       class="form-control @error('image') is-invalid @enderror"
                       onchange="previewNew(this)">
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <button type="submit" class="btn-primary-sm">
                    <i class="bi bi-save"></i> حفظ التغييرات
                </button>
            </div>

        </div>
    </div>
</div>
</form>
</div>
</div>

@endsection

@push('scripts')
<script>
function previewNew(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('previewImg').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
