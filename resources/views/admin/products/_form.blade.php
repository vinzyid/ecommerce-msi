@if ($errors->any())<div class="alert" role="alert">{{ $errors->first() }}</div>@endif
<div class="field-row">
    <div class="field">
        <label for="name">Nama produk</label>
        <input id="name" name="name" value="{{ old('name', $product?->name) }}" maxlength="120" autofocus>
        @error('name')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="sku">SKU</label>
        <input id="sku" name="sku" value="{{ old('sku', $product?->sku) }}" maxlength="50">
        @error('sku')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
<div class="field">
    <label for="category_id">Kategori</label>
    <select id="category_id" name="category_id">
        <option value="">Pilih kategori</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (nonaktif)' }}</option>
        @endforeach
    </select>
    @error('category_id')<p class="field-error">{{ $message }}</p>@enderror
</div>
<div class="field">
    <label for="description">Deskripsi</label>
    <textarea id="description" name="description" rows="5">{{ old('description', $product?->description) }}</textarea>
    @error('description')<p class="field-error">{{ $message }}</p>@enderror
</div>
<div class="field-row">
    <div class="field">
        <label for="price">Harga jual</label>
        <input id="price" name="price" type="number" min="0" step="1" value="{{ old('price', $product?->price) }}">
        @error('price')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="compare_at_price">Harga sebelum diskon <small>opsional</small></label>
        <input id="compare_at_price" name="compare_at_price" type="number" min="0" step="1" value="{{ old('compare_at_price', $product?->compare_at_price) }}">
        @error('compare_at_price')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
<div class="field-row">
    <div class="field">
        <label for="stock">Stok</label>
        <input id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', $product?->stock ?? 0) }}">
        @error('stock')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="badge">Label produk <small>opsional</small></label>
        <input id="badge" name="badge" value="{{ old('badge', $product?->badge) }}" maxlength="30" placeholder="Best Seller, Promo, Editor Pilihan">
        @error('badge')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
<div class="field-row">
    <div class="field">
        <label for="weight_grams">Berat <small>gram, opsional</small></label>
        <input id="weight_grams" name="weight_grams" type="number" min="0" step="1" value="{{ old('weight_grams', $product?->weight_grams) }}">
        @error('weight_grams')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="material">Material <small>opsional</small></label>
        <input id="material" name="material" value="{{ old('material', $product?->material) }}" maxlength="100">
        @error('material')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
<div class="field-row">
    <div class="field">
        <label for="color">Warna <small>opsional</small></label>
        <input id="color" name="color" value="{{ old('color', $product?->color) }}" maxlength="60">
        @error('color')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="dimensions">Dimensi <small>opsional</small></label>
        <input id="dimensions" name="dimensions" value="{{ old('dimensions', $product?->dimensions) }}" maxlength="80" placeholder="7 x 7 x 24 cm">
        @error('dimensions')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
<div class="field">
    <label for="image_url">URL gambar <small>opsional</small></label>
    <input id="image_url" name="image_url" type="url" value="{{ old('image_url', $product?->image_url) }}" maxlength="500">
    @error('image_url')<p class="field-error">{{ $message }}</p>@enderror
</div>
<div class="check-row">
    <label class="check-option"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))><span>Tampilkan di katalog</span></label>
    <label class="check-option"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product?->is_featured ?? false))><span>Tandai rekomendasi</span></label>
</div>
<button class="primary-button" type="submit">{{ $submitLabel }}</button>
