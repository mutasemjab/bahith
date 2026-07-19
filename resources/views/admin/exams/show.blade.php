@extends('admin.layouts.app')
@section('title', $exam->title_en ?: $exam->title_ar)

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $exam->title_en ?: $exam->title_ar }}</h1>
        <p class="page-sub">{{ $exam->questions->count() }} {{ __('messages.questions') }} · {{ $exam->duration_minutes }} {{ __('messages.min_label') }} · {{ __('messages.pass_of_total') }}: {{ $exam->pass_marks }}/{{ $exam->total_marks }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn-outline-sm"><i class="bi bi-pencil"></i> {{ __('messages.Edit') }}</a>
        <a href="{{ route('admin.exams.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-left"></i> {{ __('messages.Back') }}</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-3">

    {{-- Questions list --}}
    <div class="col-12 col-xl-7">
        <div class="panel-card">
            <div class="panel-card-header">
                <h2 class="panel-card-title">{{ __('messages.questions') }} ({{ $exam->questions->count() }})</h2>
            </div>
            <div class="panel-card-body">
                @forelse($exam->questions as $q)
                <div class="mb-4 pb-3" style="border-bottom:1px solid var(--border)">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div style="font-weight:500;flex:1">
                            <span style="color:var(--primary)">Q{{ $loop->iteration }}.</span>
                            {{ $q->question_ar ?: $q->question_en }}
                        </div>
                        <form action="{{ route('admin.exams.questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_question_confirm') }}')">
                            @csrf @method('DELETE')
                            <button class="btn-outline-sm" style="padding:3px 7px;color:#dc2626;border-color:#fecaca;flex-shrink:0"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    @if($q->image)
                    <div class="mb-2">
                        <img src="{{ asset('assets/uploads/questions/'.$q->image) }}"
                             alt="question image"
                             style="max-height:160px;border-radius:8px;border:1px solid #e2e8f0;cursor:pointer"
                             onclick="this.style.maxHeight=this.style.maxHeight==='none'?'160px':'none'">
                    </div>
                    @endif
                    <div class="d-flex gap-2 mb-2">
                        <span class="pill pill-neutral">{{ __('messages.'.($q->question_type === 'true_false' ? 'true_false' : ($q->question_type === 'short_answer' ? 'short_answer' : 'mcq'))) }}</span>
                        <span class="pill pill-info">{{ $q->marks }} {{ $q->marks > 1 ? __('messages.marks_suffix') : __('messages.mark_suffix') }}</span>
                        <span class="pill pill-{{ $q->difficulty === 'easy' ? 'success' : ($q->difficulty === 'hard' ? 'warning' : 'neutral') }}">{{ __('messages.'.$q->difficulty) }}</span>
                    </div>
                    @if($q->options->count())
                    <div class="ps-3">
                        @foreach($q->options as $opt)
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-{{ $opt->is_correct ? 'check-circle-fill' : 'circle' }}" style="color:{{ $opt->is_correct ? '#059669' : 'var(--muted)' }}"></i>
                            <span style="font-size:.85rem">{{ $opt->option_text_ar ?: $opt->option_text_en }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <p style="color:var(--muted)">{{ __('messages.no_questions_yet_add') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Add Question form --}}
    <div class="col-12 col-xl-5">
        <div class="panel-card">
            <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.add_question') }}</h2></div>
            <div class="panel-card-body">
                <form action="{{ route('admin.exams.questions.store', $exam->id) }}" method="POST" enctype="multipart/form-data" id="question-form">
                @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.question_ar') }} <span class="text-danger">*</span></label>
                        <textarea name="question_text_ar" rows="2" class="form-control" dir="rtl" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.question_en') }}</label>
                        <textarea name="question_text_en" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.question_image') }} <span style="color:var(--muted);font-size:.8rem">({{ __('messages.optional') }})</span></label>
                        <input type="file" name="question_image" accept="image/*" class="form-control form-control-sm"
                               onchange="previewQImg(this)">
                        <img id="qimg-preview" src="" alt="" style="display:none;max-height:120px;margin-top:8px;border-radius:8px;border:1px solid #e2e8f0">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.type_label') }}</label>
                            <select name="question_type" class="form-select form-select-sm" onchange="toggleOptions(this.value)">
                                <option value="mcq">{{ __('messages.mcq') }}</option>
                                <option value="true_false">{{ __('messages.true_false') }}</option>
                                <option value="short_answer">{{ __('messages.short_answer') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.difficulty') }}</label>
                            <select name="difficulty" class="form-select form-select-sm">
                                <option value="easy">{{ __('messages.easy') }}</option>
                                <option value="medium" selected>{{ __('messages.medium') }}</option>
                                <option value="hard">{{ __('messages.hard') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.marks_label') }}</label>
                            <input type="number" name="marks" value="1" min="1" class="form-control form-control-sm">
                        </div>
                    </div>

                    {{-- Options section --}}
                    <div id="options-section">
                        <label class="form-label">{{ __('messages.options_label') }}</label>

                        {{-- MCQ: 4 text inputs --}}
                        <div id="mcq-rows">
                            @for($i = 0; $i < 4; $i++)
                            <div class="option-row d-flex align-items-center gap-2 mb-2">
                                <input type="radio" name="correct_option" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }}>
                                <input type="text" name="options[{{ $i }}][text_ar]" class="form-control form-control-sm" placeholder="{{ str_replace(':n', $i+1, __('messages.option_ar')) }}" dir="rtl">
                                <input type="text" name="options[{{ $i }}][text_en]" class="form-control form-control-sm" placeholder="{{ __('messages.option_en') }}">
                                <input type="hidden" name="options[{{ $i }}][correct]" value="{{ $i === 0 ? '1' : '0' }}" class="correct-flag">
                            </div>
                            @endfor
                        </div>

                        {{-- True/False: 2 preset options --}}
                        <div id="tf-rows" style="display:none">
                            <div class="option-row d-flex align-items-center gap-2 mb-2">
                                <input type="radio" name="correct_option" value="0" checked>
                                <span class="form-control form-control-sm" style="background:#f0fdf4;border-color:#86efac;color:#166534;font-weight:600">✓ صح (True)</span>
                                <input type="hidden" name="options[0][text_ar]" value="صح">
                                <input type="hidden" name="options[0][text_en]" value="True">
                                <input type="hidden" name="options[0][correct]" value="1" class="correct-flag">
                            </div>
                            <div class="option-row d-flex align-items-center gap-2 mb-2">
                                <input type="radio" name="correct_option" value="1">
                                <span class="form-control form-control-sm" style="background:#fef2f2;border-color:#fca5a5;color:#dc2626;font-weight:600">✗ خطأ (False)</span>
                                <input type="hidden" name="options[1][text_ar]" value="خطأ">
                                <input type="hidden" name="options[1][text_en]" value="False">
                                <input type="hidden" name="options[1][correct]" value="0" class="correct-flag">
                            </div>
                        </div>
                    </div>

                    <script>
                    // Update correct flag when radio changes
                    document.addEventListener('change', function(e) {
                        if (e.target.name !== 'correct_option') return;
                        var activeSection = document.getElementById('mcq-rows').style.display !== 'none'
                            ? document.getElementById('mcq-rows')
                            : document.getElementById('tf-rows');
                        activeSection.querySelectorAll('.correct-flag').forEach(function(el) { el.value = '0'; });
                        e.target.closest('.option-row').querySelector('.correct-flag').value = '1';
                    });

                    function toggleOptions(type) {
                        var optSection = document.getElementById('options-section');
                        var mcqRows    = document.getElementById('mcq-rows');
                        var tfRows     = document.getElementById('tf-rows');

                        if (type === 'short_answer') {
                            optSection.style.display = 'none';
                            mcqRows.querySelectorAll('input').forEach(function(el){ el.disabled = true; });
                            tfRows.querySelectorAll('input').forEach(function(el){ el.disabled = true; });
                            return;
                        }

                        optSection.style.display = '';

                        if (type === 'mcq') {
                            mcqRows.style.display = '';
                            tfRows.style.display  = 'none';
                            mcqRows.querySelectorAll('input').forEach(function(el){ el.disabled = false; });
                            tfRows.querySelectorAll('input').forEach(function(el){ el.disabled = true; });
                            // Reset first option as correct
                            mcqRows.querySelectorAll('.correct-flag').forEach(function(el, i){ el.value = i === 0 ? '1' : '0'; });
                            mcqRows.querySelector('input[type="radio"]').checked = true;
                        } else if (type === 'true_false') {
                            mcqRows.style.display = 'none';
                            tfRows.style.display  = '';
                            mcqRows.querySelectorAll('input').forEach(function(el){ el.disabled = true; });
                            tfRows.querySelectorAll('input').forEach(function(el){ el.disabled = false; });
                            // Reset صح as correct by default
                            tfRows.querySelectorAll('.correct-flag').forEach(function(el, i){ el.value = i === 0 ? '1' : '0'; });
                            tfRows.querySelector('input[type="radio"]').checked = true;
                        }
                    }
                    </script>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.explanation_ar') }}</label>
                        <textarea name="explanation_ar" rows="2" class="form-control" dir="rtl"></textarea>
                    </div>

                    <button type="submit" class="btn-primary-sm w-100 justify-content-center">
                        <i class="bi bi-plus-circle"></i> {{ __('messages.add_question') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@push('scripts')
<script>
function previewQImg(input) {
    const img = document.getElementById('qimg-preview');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }
}
</script>
@endpush
@endsection
