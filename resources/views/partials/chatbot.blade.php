{{--
    Widget chatbot konsumen.
    - Tombol melayang di kanan bawah; di ponsel panel tampil layar penuh.
    - Saran pertanyaan menyesuaikan halaman yang sedang dibuka.
    - Riwayat disimpan di localStorage browser (tanpa simpan DB per sesi).
    - Jawaban dikirim bertahap (streaming), produk yang disebut tampil sebagai kartu.
--}}
@php
    $pageProduct = request()->routeIs('products.show') ? request()->route('product') : null;
    $pageProduct = $pageProduct instanceof \App\Models\Product ? $pageProduct : null;

    $suggestions = match (true) {
        $pageProduct !== null => [
            'Stok masih ada?' => "Apakah stok {$pageProduct->name} masih tersedia?",
            'Ada yang lebih murah?' => "Ada produk mirip {$pageProduct->name} dengan harga lebih murah?",
            'Cocok untuk siapa?' => "{$pageProduct->name} cocok untuk kebutuhan seperti apa?",
        ],
        request()->routeIs('cart.index', 'checkout.create') => [
            'Ada voucher?' => 'Ada voucher yang bisa saya pakai saat checkout?',
            'Ongkos kirim?' => 'Berapa ongkos kirim dan kapan gratis ongkir?',
            'Metode pembayaran?' => 'Metode pembayaran apa saja yang tersedia?',
        ],
        request()->routeIs('promo') => [
            'Diskon terbesar?' => 'Produk apa yang diskonnya paling besar saat ini?',
            'Promo headset?' => 'Ada promo headset gaming?',
            'Promo diecast?' => 'Ada promo diecast?',
        ],
        default => [
            'Harga PS5?' => 'Berapa harga PlayStation 5?',
            'Sedang promo?' => 'Produk apa saja yang sedang promo?',
            'Rekomendasi headset' => 'Rekomendasi headset gaming yang stoknya tersedia',
        ],
    };
@endphp
<div data-chatbot
     data-chatbot-url="{{ route('chatbot.ask') }}"
     data-chatbot-feedback-url="{{ route('chatbot.feedback') }}"
     data-cart-url="{{ route('cart.store') }}"
     data-user-id="{{ auth()->id() ?? 'guest' }}"
     @if ($pageProduct) data-page-product="{{ $pageProduct->slug }}" @endif
     class="fixed bottom-20 right-4 z-50 flex flex-col items-end sm:bottom-6 sm:right-6">
    <!-- Panel chat -->
    <div data-chatbot-panel
         class="mb-3 flex h-[68vh] max-h-[580px] w-[calc(100vw-2rem)] max-w-[390px] flex-col overflow-hidden rounded-[1.5rem] border border-ink-100 bg-white shadow-2xl sm:w-[390px]"
         role="dialog" aria-modal="false" aria-labelledby="chatbot-title">
        <!-- Header -->
        <div class="relative border-b border-ink-100 bg-white px-4 py-3 pt-[max(0.75rem,env(safe-area-inset-top))] text-ink-900">
            <div class="relative flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="relative grid h-10 w-10 shrink-0 place-items-center rounded-full bg-ink-900 text-white">
                        <svg class="h-5 w-5" aria-hidden="true"><use href="#i-headset"/></svg>
                        <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-success-500"></span>
                    </span>
                    <div class="min-w-0 leading-tight">
                        <p id="chatbot-title" class="truncate text-sm font-semibold">Asisten {{ config('app.name') }}</p>
                        <p class="text-xs text-ink-500">
                            Biasanya membalas dalam hitungan detik
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    <button type="button" data-chatbot-clear
                            class="grid h-9 w-9 place-items-center rounded-full text-ink-500 hover:bg-ink-50 hover:text-ink-900"
                            aria-label="Hapus riwayat chat" title="Hapus riwayat">
                        <svg class="h-5 w-5" aria-hidden="true"><use href="#i-trash"/></svg>
                    </button>
                    <button type="button" data-chatbot-close
                            class="grid h-9 w-9 place-items-center rounded-full text-ink-500 hover:bg-ink-50 hover:text-ink-900"
                            aria-label="Tutup chat" title="Tutup">
                        <svg class="h-5 w-5" aria-hidden="true"><use href="#i-close"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Konfirmasi hapus riwayat (pengganti confirm() bawaan browser) -->
        <div data-chatbot-confirm hidden
             class="flex items-center justify-between gap-3 border-b border-danger-500/20 bg-danger-500/5 px-4 py-2.5 text-xs">
            <span class="font-semibold text-ink-700">Hapus semua riwayat percakapan?</span>
            <span class="flex shrink-0 gap-1.5">
                <button type="button" data-chatbot-confirm-cancel class="rounded-lg px-2.5 py-1.5 font-semibold text-ink-600 hover:bg-ink-100">Batal</button>
                <button type="button" data-chatbot-confirm-ok class="rounded-lg bg-danger-600 px-2.5 py-1.5 font-bold text-white hover:bg-danger-500">Hapus</button>
            </span>
        </div>

        <!-- Area pesan -->
        <div data-chatbot-messages aria-live="polite" aria-relevant="additions"
             class="flex-1 space-y-3 overflow-y-auto overscroll-contain bg-white px-4 py-4 text-sm">
            <!-- Pesan sambutan + saran (saran otomatis hilang setelah percakapan dimulai) -->
            <div class="flex gap-2" data-chatbot-welcome>
                <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-ink-900 text-white">
                    <svg class="h-4 w-4" aria-hidden="true"><use href="#i-headset"/></svg>
                </span>
                <div class="min-w-0 max-w-[85%]">
                    <div class="rounded-2xl rounded-tl-md bg-ink-50 px-3.5 py-2.5 text-ink-800">
                        @if ($pageProduct)
                            Hai! 👋 Ada yang ingin ditanyakan soal <strong class="font-bold text-ink-900">{{ \Illuminate\Support\Str::limit($pageProduct->name, 60) }}</strong>? Saya juga bisa bantu cari produk lain.
                        @else
                            Hai! 👋 Saya asisten {{ config('app.name') }}. Silakan tanya harga, ketersediaan stok, atau rekomendasi produk gaming, diecast &amp; hobi.
                        @endif
                    </div>
                    <div data-chatbot-suggestions class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($suggestions as $label => $question)
                            <button type="button" data-chatbot-suggest="{{ $question }}"
                                    class="rounded-full border border-ink-200 bg-white px-3 py-1.5 text-xs font-medium text-ink-800 hover:border-ink-900">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <form data-chatbot-form class="flex items-end gap-2 border-t border-ink-100 bg-white px-3 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <label for="chatbot-input" class="sr-only">Tulis pertanyaan</label>
            <textarea id="chatbot-input" data-chatbot-input rows="1" placeholder="Tulis pertanyaan…" maxlength="500"
                      class="max-h-24 flex-1 resize-none rounded-2xl border border-ink-200 px-3.5 py-2 text-base text-ink-800 outline-none transition placeholder:text-ink-400 focus:border-ink-400 focus:ring-4 focus:ring-ink-100 sm:text-sm"></textarea>
            <button type="submit" data-chatbot-send
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-ink-900 text-white hover:bg-ink-700 disabled:cursor-not-allowed disabled:opacity-40"
                    aria-label="Kirim">
                <svg class="h-5 w-5" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            </button>
        </form>
    </div>

    <!-- Label & Tombol FAB -->
    <div data-chatbot-launcher class="flex items-center gap-2.5">
        <!-- Label (desktop) -->
        <button type="button" data-chatbot-badge
                class="hidden items-center gap-2 rounded-full border border-ink-100 bg-white px-4 py-2 text-xs shadow-card transition-[translate,box-shadow,border-color,opacity] duration-200 ease-(--ease-out) hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lift sm:flex">
            <span class="flex h-2 w-2 shrink-0 rounded-full bg-success-500"></span>
            <span class="font-medium text-ink-900">Tanya asisten toko</span>
        </button>

        <!-- Tombol FAB bulat -->
        <button type="button" data-chatbot-toggle aria-expanded="false"
                class="relative grid h-14 w-14 shrink-0 place-items-center rounded-full bg-ink-900 text-white shadow-lift transition-[scale,background-color,opacity] duration-160 ease-(--ease-out) hover:scale-105 hover:bg-ink-700 active:scale-95"
                aria-label="Buka asisten chat">
            <svg class="h-6 w-6" data-chatbot-icon-open aria-hidden="true"><use href="#i-headset"/></svg>
            <svg class="hidden h-6 w-6" data-chatbot-icon-close aria-hidden="true"><use href="#i-close"/></svg>
            <span class="absolute right-0.5 top-0.5 flex h-3 w-3">
                {{-- Kedip hanya untuk pengunjung yang belum pernah membuka chat --}}
                <span data-chatbot-ping class="absolute hidden h-full w-full animate-ping rounded-full bg-success-500 opacity-75"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full border-2 border-ink-900 bg-success-500"></span>
            </span>
        </button>
    </div>
</div>
