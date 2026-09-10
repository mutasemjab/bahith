@extends('admin.layouts.app')
@section('title', 'إضافة جدول حصص')

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">إضافة جدول حصص</h1></div>
    <a href="{{ route('admin.class-schedules.index') }}" class="btn-outline-sm">
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
<form action="{{ route('admin.class-schedules.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="panel-card">
    <div class="panel-card-header"><h2 class="panel-card-title">بيانات الجدول</h2></div>
    <div class="panel-card-body">
        <div class="row g-3">

            <div class="col-12">
                <label class="form-label">الصف <span class="text-danger">*</span></label>
                <select name="class_id" class="form-control @error('class_id') is-invalid @enderror" required>
                    <option value="">— اختر الصف —</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">صورة الجدول <span class="text-danger">*</span></label>
                <input type="file" name="image" id="imageInput" accept="image/*"
                       class="form-control @error('image') is-invalid @enderror"
                       onchange="previewImage(this)" required>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12" id="previewBox" style="display:none">
                <label class="form-label">معاينة</label>
                <img id="previewImg" src="#" alt="preview"
                     style="width:100%;max-height:320px;object-fit:contain;border-radius:10px;border:1px solid #e2e8f0">
            </div>

            <div class="col-12">
                <button type="submit" class="btn-primary-sm">
                    <i class="bi bi-save"></i> حفظ
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
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewBox').style.display = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
