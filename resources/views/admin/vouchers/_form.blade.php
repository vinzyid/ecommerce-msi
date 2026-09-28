@if ($errors->any())<div class="alert" role="alert">{{ $errors->first() }}</div>@endif
<div class="field-row">
    <div class="field">
        <label for="code">Kode voucher</label>
        <input id="code" name="code" value="{{ old('code', $voucher?->code) }}" maxlength="30" placeholder="HEMAT10" autofocus>
        @error('code')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="type">Jenis</label>
        <select id="type" name="type">
            <option value="percent" @selected(old('type', $voucher?->type ?? 'percent') === 'percent')>Persen (%)</option>
            <option value="fixed" @selected(old('type', $voucher?->type) === 'fixed')>Potongan tetap (Rp)</option>
        </select>
        @error('type')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
<div class="field">
    <label for="description">Deskripsi</label>
    <input id="description" name="description" value="{{ old('description', $voucher?->description) }}" maxlength="150">
    @error('description')<p class="field-error">{{ $message }}</p>@enderror
</div>
<div class="field-row">
    <div class="field">
        <label for="value">Nilai</label>
        <input id="value" name="value" type="number" min="1" step="1" value="{{ old('value', $voucher?->value) }}">
        @error('value')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="min_spend">Minimum belanja</label>
        <input id="min_spend" name="min_spend" type="number" min="0" step="1" value="{{ old('min_spend', $voucher?->min_spend ?? 0) }}">
        @error('min_spend')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
<div class="field-row">
    <div class="field">
        <label for="max_discount">Batas potongan <small>opsional</small></label>
        <input id="max_discount" name="max_discount" type="number" min="0" step="1" value="{{ old('max_discount', $voucher?->max_discount) }}">
        @error('max_discount')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="usage_limit">Batas pemakaian <small>opsional</small></label>
        <input id="usage_limit" name="usage_limit" type="number" min="1" step="1" value="{{ old('usage_limit', $voucher?->usage_limit) }}">
        @error('usage_limit')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
<div class="field">
    <label for="expires_at">Berlaku sampai <small>opsional</small></label>
    <input id="expires_at" name="expires_at" type="date" value="{{ old('expires_at', $voucher?->expires_at?->format('Y-m-d')) }}">
    @error('expires_at')<p class="field-error">{{ $message }}</p>@enderror
</div>
<label class="check-option">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $voucher?->is_active ?? true))>
    <span>Aktifkan voucher</span>
</label>
<button class="primary-button" type="submit">{{ $submitLabel }}</button>
