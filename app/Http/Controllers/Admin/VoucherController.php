<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) ($request->validate(['q' => 'nullable|string|max:50'])['q'] ?? ''));

        $vouchers = Voucher::query()
            ->when($search, fn ($query, $search) => $query->where('code', 'like', "%{$search}%"))
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.vouchers.index', compact('vouchers', 'search'));
    }

    public function create(): View
    {
        return view('admin.vouchers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateVoucher($request);
        $validated['is_active'] = $request->boolean('is_active');

        Voucher::query()->create($validated);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher dibuat.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $validated = $this->validateVoucher($request, $voucher);
        $validated['is_active'] = $request->boolean('is_active');
        $voucher->update($validated);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher diperbarui.');
    }

    private function validateVoucher(Request $request, ?Voucher $voucher = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('vouchers')->ignore($voucher)],
            'description' => 'nullable|string|max:150',
            'type' => ['required', Rule::in(Voucher::TYPES)],
            'value' => 'required|integer|min:1|max:10000000',
            'min_spend' => 'nullable|integer|min:0|max:99999999',
            'max_discount' => 'nullable|integer|min:0|max:99999999',
            'usage_limit' => 'nullable|integer|min:1|max:1000000',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ], [
            'code.unique' => 'Kode voucher sudah dipakai.',
            'value.required' => 'Nilai voucher wajib diisi.',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['min_spend'] = $validated['min_spend'] ?? 0;
        $validated['max_discount'] = $validated['max_discount'] ?? null;
        $validated['usage_limit'] = $validated['usage_limit'] ?? null;

        return $validated;
    }
}
