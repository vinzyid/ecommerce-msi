@if ($errors->any())<div class="alert" role="alert">{{ $errors->first() }}</div>@endif
<div class="field">
    <label for="name">Nama kategori</label>
    <input id="name" name="name" value="{{ old('name', $category?->name) }}" maxlength="80" autofocus>
    @error('name')<p class="field-error">{{ $message }}</p>@enderror
</div>
<div class="field">
    <label for="description">Deskripsi singkat</label>
    <input id="description" name="description" value="{{ old('description', $category?->description) }}" maxlength="255">
    @error('description')<p class="field-error">{{ $message }}</p>@enderror
</div>
<label class="check-option">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true))>
    <span>Tampilkan kategori di katalog</span>
</label>
<button class="primary-button" type="submit">{{ $submitLabel }}</button>
