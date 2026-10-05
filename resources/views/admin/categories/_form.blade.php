@php
    $field = 'w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
    $label = 'block text-sm font-semibold text-ink-700';
@endphp

@if ($errors->any())
    <div class="rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600">{{ $errors->first() }}</div>
@endif

<div>
    <label for="name" class="{{ $label }}">Nama kategori</label>
    <input id="name" name="name" value="{{ old('name', $category?->name) }}" maxlength="80" autofocus class="{{ $field }} mt-1.5">
    @error('name')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
</div>

<div class="mt-4">
    <label for="description" class="{{ $label }}">Deskripsi singkat</label>
    <input id="description" name="description" value="{{ old('description', $category?->description) }}" maxlength="255" class="{{ $field }} mt-1.5">
    @error('description')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
</div>

<label class="mt-5 flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-ink-700">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true)) class="h-4.5 w-4.5 rounded accent-brand-600">
    Tampilkan kategori di katalog
</label>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-700">{{ $submitLabel }}</button>
    <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-ink-200 bg-white px-6 py-3 text-sm font-bold text-ink-600 transition hover:border-ink-300">Batal</a>
</div>
