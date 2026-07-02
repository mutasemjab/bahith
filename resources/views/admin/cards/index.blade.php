@extends('admin.layouts.app')
@section('title', __('messages.cards_title'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.cards_title') }}</h1>
        <p class="page-sub">{{ __('messages.manage_cards_desc') }}</p>
    </div>
    <a href="{{ route('admin.cards.create') }}" class="btn-primary-sm"><i class="bi bi-plus-circle"></i> {{ __('messages.add_card') }}</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2">
            <div class="col-12 col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('messages.search_cards_ph') }}">
            </div>
            <div class="col-12 col-md-4">
                <select name="pos_id" class="form-select form-select-sm">
                    <option value="">{{ __('messages.all_pos') }}</option>
                    @foreach($posList as $pos)
                    <option value="{{ $pos->id }}" @selected(request('pos_id') == $pos->id)>{{ $pos->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn-primary-sm w-100"><i class="bi bi-search"></i> {{ __('messages.Search') }}</button>
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
                    <th>{{ __('messages.photo_label') }}</th>
                    <th>{{ __('messages.card_name') }}</th>
                    <th>{{ __('messages.pos_label') }}</th>
                    <th>{{ __('messages.selling_price') }}</th>
                    <th>{{ __('messages.number_of_cards') }}</th>
                    <th>{{ __('messages.numbers_label') }}</th>
                    <th>{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cards as $card)
                <tr>
                    <td style="color:var(--muted)">{{ $card->id }}</td>
                    <td>
                        @if($card->photo)
                            <img src="{{ asset('uploads/cards/'.$card->photo) }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px">
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:500">{{ $card->name_en }}</div>
                        <div style="font-size:.75rem;color:var(--muted)" dir="rtl">{{ $card->name_ar }}</div>
                    </td>
                    <td>
                        @if($card->pos)
                            <div>{{ $card->pos->name_en }}</div>
                            <small style="color:var(--muted)">{{ $card->pos->city->name_en ?? '' }}</small>
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                    <td>{{ number_format($card->selling_price, 2) }}</td>
                    <td>{{ number_format($card->number_of_cards, 0) }}</td>
                    <td>
                        <a href="{{ route('admin.card-numbers.index', ['card_id' => $card->id]) }}" class="btn-outline-sm" style="padding:3px 8px;font-size:.75rem">
                            {{ $card->card_numbers_count }} <i class="bi bi-arrow-right"></i>
                        </a>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.cards.edit', $card->id) }}" class="btn-outline-sm" style="padding:4px 8px"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.cards.destroy', $card->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_card_confirm') }}')">
                                @csrf @method('DELETE')
                                <button class="btn-outline-sm" style="padding:4px 8px;color:#dc2626;border-color:#fecaca"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4" style="color:var(--muted)">{{ __('messages.no_cards_yet') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $cards->withQueryString()->links() }}</div>
    </div>
</div>

@endsection
