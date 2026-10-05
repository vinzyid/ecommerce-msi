@php
    $field = 'w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
    $label = 'block text-sm font-semibold text-ink-700';
@endphp

@if ($errors->any())
    <div class="rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600">{{ $errors->first() }}</div>
@endif

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="name" class="{{ $label }}">Nama produk</label>
        <input id="name" name="name" value="{{ old('name', $product?->name) }}" maxlength="120" autofocus class="{{ $field }} mt-1.5">
        @error('name')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="sku" class="{{ $label }}">SKU</label>
        <input id="sku" name="sku" value="{{ old('sku', $product?->sku) }}" maxlength="50" class="{{ $field }} mt-1.5">
        @error('sku')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-4">
    <label for="category_id" class="{{ $label }}">Kategori</label>
    <select id="category_id" name="category_id" class="{{ $field }} mt-1.5">
        <option value="">Pilih kategori</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>
                {{ $category->name }}{{ $category->is_active ? '' : ' (nonaktif)' }}
            </option>
        @endforeach
    </select>
    @error('category_id')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
</div>

<div class="mt-4">
    <label for="description" class="{{ $label }}">Deskripsi</label>
    <textarea id="description" name="description" rows="5" class="{{ $field }} mt-1.5">{{ old('description', $product?->description) }}</textarea>
    @error('description')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2">
    <div>
        <label for="price" class="{{ $label }}">Harga jual</label>
        <input id="price" name="price" type="number" min="0" step="1" value="{{ old('price', $product?->price) }}" class="{{ $field }} mt-1.5">
        @error('price')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="compare_at_price" class="{{ $label }}">Harga sebelum diskon <span class="font-normal text-ink-400">(opsional)</span></label>
        <input id="compare_at_price" name="compare_at_price" type="number" min="0" step="1" value="{{ old('compare_at_price', $product?->compare_at_price) }}" class="{{ $field }} mt-1.5">
        @error('compare_at_price')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2">
    <div>
        <label for="stock" class="{{ $label }}">Stok</label>
        <input id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', $product?->stock ?? 0) }}" class="{{ $field }} mt-1.5">
        @error('stock')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="badge" class="{{ $label }}">Label produk <span class="font-normal text-ink-400">(opsional)</span></label>
        <input id="badge" name="badge" value="{{ old('badge', $product?->badge) }}" maxlength="30" placeholder="Best Seller, Promo, Editor Pilihan" class="{{ $field }} mt-1.5">
        @error('badge')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2">
    <div>
        <label for="weight_grams" class="{{ $label }}">Berat <span class="font-normal text-ink-400">(gram)</span></label>
        <input id="weight_grams" name="weight_grams" type="number" min="0" step="1" value="{{ old('weight_grams', $product?->weight_grams) }}" class="{{ $field }} mt-1.5">
        @error('weight_grams')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="material" class="{{ $label }}">Material <span class="font-normal text-ink-400">(opsional)</span></label>
        <input id="material" name="material" value="{{ old('material', $product?->material) }}" maxlength="100" class="{{ $field }} mt-1.5">
        @error('material')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2">
    <div>
        <label for="color" class="{{ $label }}">Warna <span class="font-normal text-ink-400">(opsional)</span></label>
        <input id="color" name="color" value="{{ old('color', $product?->color) }}" maxlength="60" class="{{ $field }} mt-1.5">
        @error('color')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="dimensions" class="{{ $label }}">Dimensi <span class="font-normal text-ink-400">(opsional)</span></label>
        <input id="dimensions" name="dimensions" value="{{ old('dimensions', $product?->dimensions) }}" maxlength="80" placeholder="7 x 7 x 24 cm" class="{{ $field }} mt-1.5">
        @error('dimensions')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-4">
    <label for="image_url" class="{{ $label }}">URL gambar <span class="font-normal text-ink-400">(opsional)</span></label>
    <input id="image_url" name="image_url" type="text" value="{{ old('image_url', $product?->image_url) }}" maxlength="500"
           placeholder="/images/products/mechanical-keyboard.svg" class="{{ $field }} mt-1.5">
    @error('image_url')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
    <p class="mt-1.5 text-xs text-ink-400">Gunakan path lokal seperti <code class="rounded bg-ink-100 px-1 py-0.5">/images/products/nama-file.svg</code> atau URL lengkap.</p>
</div>

<div class="mt-5 flex flex-wrap gap-5 border-t border-ink-100 pt-4">
    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-ink-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true)) class="h-4.5 w-4.5 rounded accent-brand-600">
        Tampilkan di katalog
    </label>
    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-ink-700">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product?->is_featured ?? false)) class="h-4.5 w-4.5 rounded accent-brand-600">
        Tandai rekomendasi
    </label>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-700">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('admin.products.index') }}"
       class="rounded-xl border border-ink-200 bg-white px-6 py-3 text-sm font-bold text-ink-600 transition hover:border-ink-300">
        Batal
    </a>
</div>
