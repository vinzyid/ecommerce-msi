@php
    $field = 'w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
    $label = 'block text-sm font-semibold text-ink-700';
@endphp

@if ($errors->any())
    <div class="rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600">{{ $errors->first() }}</div>
@endif

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="code" class="{{ $label }}">Kode voucher</label>
        <input id="code" name="code" value="{{ old('code', $voucher?->code) }}" maxlength="30" placeholder="GAMER10" autofocus
               class="{{ $field }} mt-1.5 uppercase">
        @error('code')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="type" class="{{ $label }}">Jenis</label>
        <select id="type" name="type" class="{{ $field }} mt-1.5">
            <option value="percent" @selected(old('type', $voucher?->type ?? 'percent') === 'percent')>Persen (%)</option>
            <option value="fixed" @selected(old('type', $voucher?->type) === 'fixed')>Potongan tetap (Rp)</option>
        </select>
        @error('type')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-4">
    <label for="description" class="{{ $label }}">Deskripsi</label>
    <input id="description" name="description" value="{{ old('description', $voucher?->description) }}" maxlength="150" class="{{ $field }} mt-1.5">
    @error('description')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2">
    <div>
        <label for="value" class="{{ $label }}">Nilai</label>
        <input id="value" name="value" type="number" min="1" step="1" value="{{ old('value', $voucher?->value) }}" class="{{ $field }} mt-1.5">
        @error('value')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="min_spend" class="{{ $label }}">Minimum belanja</label>
        <input id="min_spend" name="min_spend" type="number" min="0" step="1" value="{{ old('min_spend', $voucher?->min_spend ?? 0) }}" class="{{ $field }} mt-1.5">
        @error('min_spend')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2">
    <div>
        <label for="max_discount" class="{{ $label }}">Batas potongan <span class="font-normal text-ink-400">(opsional)</span></label>
        <input id="max_discount" name="max_discount" type="number" min="0" step="1" value="{{ old('max_discount', $voucher?->max_discount) }}" class="{{ $field }} mt-1.5">
        @error('max_discount')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="usage_limit" class="{{ $label }}">Batas pemakaian <span class="font-normal text-ink-400">(opsional)</span></label>
        <input id="usage_limit" name="usage_limit" type="number" min="1" step="1" value="{{ old('usage_limit', $voucher?->usage_limit) }}" class="{{ $field }} mt-1.5">
        @error('usage_limit')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-4">
    <label for="expires_at" class="{{ $label }}">Berlaku sampai <span class="font-normal text-ink-400">(opsional)</span></label>
    <input id="expires_at" name="expires_at" type="date" value="{{ old('expires_at', $voucher?->expires_at?->format('Y-m-d')) }}" class="{{ $field }} mt-1.5">
    @error('expires_at')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
</div>

<label class="mt-5 flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-ink-700">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $voucher?->is_active ?? true)) class="h-4.5 w-4.5 rounded accent-brand-600">
    Aktifkan voucher
</label>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-700">{{ $submitLabel }}</button>
    <a href="{{ route('admin.vouchers.index') }}" class="rounded-xl border border-ink-200 bg-white px-6 py-3 text-sm font-bold text-ink-600 transition hover:border-ink-300">Batal</a>
</div>
