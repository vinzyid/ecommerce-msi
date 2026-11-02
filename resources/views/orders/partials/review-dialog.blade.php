{{-- Jendela ulasan pesanan selesai: satu rating & komentar untuk tiap barang. Butuh $order dan $reviews (per product_id). --}}
@php
    $reviewable = $order->items->filter(fn ($item) => $item->product)->unique('product_id');
    $hasUnreviewed = $reviewable->contains(fn ($item) => ! $reviews->has($item->product_id));
@endphp
@if ($reviewable->isNotEmpty())
    <dialog id="review-order-{{ $order->id }}" aria-labelledby="review-title-{{ $order->id }}"
            class="m-auto max-h-[90dvh] w-[calc(100%-2rem)] max-w-lg overflow-y-auto rounded-2xl bg-white p-0 shadow-2xl backdrop:bg-ink-900/50">
        <form method="POST" action="{{ route('orders.reviews.store', $order) }}" enctype="multipart/form-data">
            @csrf
            <header class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-ink-100 bg-white px-5 py-4">
                <div class="min-w-0">
                    <h2 id="review-title-{{ $order->id }}" class="text-base font-semibold text-ink-900">{{ $hasUnreviewed ? 'Nilai Barang' : 'Ubah Penilaian' }}</h2>
                    <p class="truncate font-mono text-xs text-ink-500">{{ $order->order_number }}</p>
                </div>
                <button type="button" onclick="this.closest('dialog').close()" aria-label="Tutup"
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-ink-500 hover:bg-ink-50 hover:text-ink-900">
                    <svg class="h-5 w-5" aria-hidden="true"><use href="#i-close"/></svg>
                </button>
            </header>

            <div class="divide-y divide-ink-100 px-5">
                @foreach ($reviewable as $item)
                    @php $current = $reviews->get($item->product_id); @endphp
                    <fieldset class="py-4">
                        <legend class="contents">
                            <span class="flex items-center gap-3">
                                <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-ink-50">
                                    @if ($item->product->image_url)
                                        <img src="{{ $item->product->image_url }}" alt="" loading="lazy" class="h-full w-full object-contain p-1 mix-blend-multiply">
                                    @endif
                                </span>
                                <span class="min-w-0 text-sm font-medium text-ink-900">{{ $item->product_name }}</span>
                            </span>
                        </legend>

                        {{-- Urutan terbalik agar bintang sebelum pilihan ikut menyala tanpa JavaScript --}}
                        <div class="mt-3 flex flex-row-reverse justify-end gap-1">
                            @for ($star = 5; $star >= 1; $star--)
                                <input type="radio" id="rating-{{ $order->id }}-{{ $item->product_id }}-{{ $star }}"
                                       name="items[{{ $item->product_id }}][rating]" value="{{ $star }}"
                                       @checked($current?->rating === $star) class="peer sr-only">
                                <label for="rating-{{ $order->id }}-{{ $item->product_id }}-{{ $star }}"
                                       class="cursor-pointer rounded text-ink-200 transition-colors peer-checked:text-amber-400 peer-focus-visible:ring-2 peer-focus-visible:ring-ink-400">
                                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6-4.9-4.6 6.6-.8z"/></svg>
                                    <span class="sr-only">{{ $star }} bintang</span>
                                </label>
                            @endfor
                        </div>

                        <label for="comment-{{ $order->id }}-{{ $item->product_id }}" class="sr-only">Komentar untuk {{ $item->product_name }}</label>
                        <textarea id="comment-{{ $order->id }}-{{ $item->product_id }}" name="items[{{ $item->product_id }}][comment]" rows="2" maxlength="500"
                                  placeholder="Ceritakan kualitas barang dan pengemasannya (opsional)"
                                  class="mt-3 w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-base outline-none transition focus:border-ink-400 focus:ring-4 focus:ring-ink-100 sm:text-sm">{{ $current?->comment }}</textarea>

                        <label for="photo-{{ $order->id }}-{{ $item->product_id }}" class="mt-2 block text-xs text-ink-500">
                            {{ $current?->photo_path ? 'Ganti foto' : 'Tambah foto' }} (opsional, JPG, PNG, atau WEBP, maks. 2 MB)
                        </label>
                        <input id="photo-{{ $order->id }}-{{ $item->product_id }}" name="items[{{ $item->product_id }}][photo]" type="file" accept="image/jpeg,image/png,image/webp"
                               class="mt-1 block w-full text-xs text-ink-600 file:mr-3 file:rounded-full file:border file:border-ink-200 file:bg-white file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-ink-800">
                    </fieldset>
                @endforeach
            </div>

            <footer class="sticky bottom-0 flex justify-end gap-2 border-t border-ink-100 bg-white px-5 py-4">
                <button type="button" onclick="this.closest('dialog').close()"
                        class="rounded-full border border-ink-200 px-4 py-2 text-sm font-medium text-ink-800 hover:border-ink-900">Nanti saja</button>
                <button type="submit" class="rounded-full bg-ink-900 px-5 py-2 text-sm font-medium text-white hover:bg-ink-700">Kirim Penilaian</button>
            </footer>
        </form>
    </dialog>
@endif
