@extends('admin.layouts.app')
@section('title', __('messages.add_card'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.add_card') }}</h1></div>
    <a href="{{ route('admin.cards.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-left"></i> {{ __('messages.Back') }}</a>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
<div class="col-12 col-xl-7">
<form action="{{ route('admin.cards.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="panel-card">
    <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.card_info') }}</h2></div>
    <div class="panel-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.name_ar') }} <span class="text-danger">*</span></label>
                <input type="text" name="name_ar" value="{{ old('name_ar') }}"
                       class="form-control @error('name_ar') is-invalid @enderror" dir="rtl" required>
                @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.name_en') }} <span class="text-danger">*</span></label>
                <input type="text" name="name_en" value="{{ old('name_en') }}"
                       class="form-control @error('name_en') is-invalid @enderror" required>
                @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('messages.pos_optional') }}</label>
                <select name="pos_id" class="form-select @error('pos_id') is-invalid @enderror">
                    <option value="">{{ __('messages.no_pos_option') }}</option>
                    @foreach($posList as $pos)
                    <option value="{{ $pos->id }}" @selected(old('pos_id') == $pos->id)>
                        {{ $pos->name_en }} — {{ $pos->city->name_en ?? '' }}
                    </option>
                    @endforeach
                </select>
                @error('pos_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.selling_price') }} <span class="text-danger">*</span></label>
                <input type="number" name="selling_price" value="{{ old('selling_price', 0) }}"
                       step="0.01" min="0" class="form-control @error('selling_price') is-invalid @enderror" required>
                @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.number_of_cards') }} <span class="text-danger">*</span></label>
                <input type="number" name="number_of_cards" value="{{ old('number_of_cards', 0) }}"
                       min="0" class="form-control @error('number_of_cards') is-invalid @enderror" required>
                @error('number_of_cards')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('messages.photo_label') }}</label>
                <input type="file" name="photo" accept="image/*" class="form-control @error('photo') is-invalid @enderror">
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <button type="submit" class="btn-primary-sm"><i class="bi bi-save"></i> {{ __('messages.save_card') }}</button>
            </div>
        </div>
    </div>
</div>
</form>
</div>
</div>

@endsection
