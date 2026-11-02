{{-- Filter harga + urutan + grid produk + paginasi; dipakai beranda dan mode hasil pencarian/kategori --}}
<form method="GET" action="{{ route('home') }}#produk" class="mt-6 flex flex-wrap items-end gap-2">
    @foreach (array_filter(['q' => request('q'), 'category' => request('category'), 'sort' => request('sort')]) as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <div>
        <label for="min_price" class="block text-xs text-ink-500">Harga minimum</label>
        <input id="min_price" name="min_price" type="number" min="0" step="1000" value="{{ request('min_price') }}" placeholder="Rp0"
               class="mt-1 w-32 rounded-full border border-ink-200 px-3.5 py-1.5 text-base outline-none focus:border-ink-400 sm:text-xs">
    </div>
    <div>
        <label for="max_price" class="block text-xs text-ink-500">Harga maksimum</label>
        <input id="max_price" name="max_price" type="number" min="0" step="1000" value="{{ request('max_price') }}" placeholder="Rp10.000.000"
               class="mt-1 w-32 rounded-full border border-ink-200 px-3.5 py-1.5 text-base outline-none focus:border-ink-400 sm:text-xs">
    </div>
    <button type="submit" class="rounded-full border border-ink-900 px-3.5 py-1.5 text-xs font-medium text-ink-900 hover:bg-ink-900 hover:text-white">Terapkan</button>
</form>

@if ($products->isNotEmpty())
    <div class="no-scrollbar -mx-4 mt-6 flex items-center gap-2 overflow-x-auto px-4 md:mx-0 md:px-0">
        <span class="shrink-0 text-xs text-ink-500">Urutkan</span>
        @foreach ($sortOptions as $key => $label)
            <a href="{{ route('home', array_merge($baseQuery, ['sort' => $key])) }}#produk"
               @class([
                   'shrink-0 rounded-full border px-3.5 py-1.5 text-xs transition-colors',
                   'border-ink-900 bg-ink-900 font-medium text-white' => $currentSort === $key,
                   'border-ink-200 text-ink-700 hover:border-ink-400' => $currentSort !== $key,
               ])>{{ $label }}</a>
        @endforeach
    </div>

    <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">
        @foreach ($products as $product)
            <x-product-card :product="$product" :wishlisted-product-ids="$wishlistedProductIds" />
        @endforeach
    </div>

    <div class="mt-12">
        {{ $products->links() }}
    </div>
@else
    <div class="mt-8 rounded-[1.75rem] bg-ink-50 px-6 py-20 text-center">
        <svg class="mx-auto h-10 w-10 text-ink-300" aria-hidden="true"><use href="#i-search"/></svg>
        <p class="mt-4 text-lg font-medium text-ink-900">Produk tidak ditemukan</p>
        <p class="mt-1 text-sm text-ink-500">Coba kata kunci lain atau pilih kategori di atas.</p>
        <a href="{{ route('home') }}" class="press mt-6 inline-block rounded-full bg-ink-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-ink-700">Kembali ke beranda</a>
    </div>
@endif
