<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardNumber;
use App\Models\POS;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $cards = Card::with('pos.city')
            ->withCount('cardNumbers')
            ->when($request->search, fn ($q, $s) => $q
                ->where('name_ar', 'like', "%{$s}%")
                ->orWhere('name_en', 'like', "%{$s}%")
            )
            ->when($request->pos_id, fn ($q, $p) => $q->where('pos_id', $p))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $posList = POS::orderBy('name_en')->get();

        return view('admin.cards.index', compact('cards', 'posList'));
    }

    public function create()
    {
        $posList = POS::with('city')->orderBy('name_en')->get();

        return view('admin.cards.create', compact('posList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pos_id'          => 'nullable|exists:p_o_s,id',
            'name_ar'         => 'required|string|max:200',
            'name_en'         => 'required|string|max:200',
            'selling_price'   => 'required|numeric|min:0',
            'number_of_cards' => 'required|integer|min:1|max:1000',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'code_length'     => 'nullable|integer|min:8|max:32',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = uploadImage('assets/uploads/cards', $request->file('photo'));
        }

        $generateCount = $data['number_of_cards'];
        $codeLength    = $data['code_length'] ?? 16;
        unset($data['code_length']);

        $card = Card::create($data);

        // Auto-generate card numbers
        $generated = 0;
        $attempts  = 0;
        while ($generated < $generateCount && $attempts < $generateCount * 5) {
            $number = strtoupper(substr(bin2hex(random_bytes((int) ceil($codeLength / 2))), 0, $codeLength));
            if (! CardNumber::where('number', $number)->exists()) {
                CardNumber::create([
                    'card_id'  => $card->id,
                    'number'   => $number,
                    'activate' => 1,
                    'status'   => 2,
                    'sell'     => 2,
                ]);
                $generated++;
            }
            $attempts++;
        }

        return redirect()->route('admin.card-numbers.index', ['card_id' => $card->id])
            ->with('success', "تم إنشاء البطاقة وتوليد {$generated} رقم بطاقة بنجاح.");
    }

    public function edit(Card $card)
    {
        $posList = POS::with('city')->orderBy('name_en')->get();

        return view('admin.cards.edit', compact('card', 'posList'));
    }

    public function update(Request $request, Card $card)
    {
        $data = $request->validate([
            'pos_id'          => 'nullable|exists:p_o_s,id',
            'name_ar'         => 'required|string|max:200',
            'name_en'         => 'required|string|max:200',
            'selling_price'   => 'required|numeric|min:0',
            'number_of_cards' => 'required|numeric|min:0',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = uploadImage('assets/uploads/cards', $request->file('photo'));
        }

        $card->update($data);

        return redirect()->route('admin.cards.index')
            ->with('success', 'Card updated successfully.');
    }

    public function destroy(Card $card)
    {
        $card->delete();

        return redirect()->route('admin.cards.index')
            ->with('success', 'Card deleted successfully.');
    }
}
