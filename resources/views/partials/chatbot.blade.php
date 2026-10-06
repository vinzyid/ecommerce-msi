{{--
    Widget chatbot konsumen.
    - Tombol melayang di kanan bawah.
    - Panel chat muncul saat diklik.
    - Riwayat disimpan di localStorage browser (tanpa simpan DB per sesi).
    - Jawaban dari server (di-cache 1 jam supaya hemat token).
--}}
<div data-chatbot data-chatbot-url="{{ route('chatbot.ask') }}" class="fixed inset-x-0 bottom-0 z-40 flex justify-end sm:inset-x-auto sm:right-5 sm:bottom-5">
    <!-- Panel chat -->
    <div data-chatbot-panel
         class="pointer-events-none mb-0 flex h-[70vh] max-h-[560px] w-full translate-y-4 flex-col overflow-hidden rounded-t-2xl border border-ink-200 bg-white opacity-0 shadow-lift transition-all duration-200 sm:mb-3 sm:w-96 sm:translate-y-2 sm:rounded-2xl"
         role="dialog" aria-modal="true" aria-label="Asisten belanja {{ config('app.name') }}">
        <!-- Header -->
        <div class="flex items-center justify-between gap-3 bg-brand-600 px-4 py-3 text-white">
            <div class="flex items-center gap-2">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-white/15">
                    <svg class="h-5 w-5"><use href="#i-headset"/></svg>
                </span>
                <div class="leading-tight">
                    <p class="text-sm font-bold">Asisten {{ config('app.name') }}</p>
                    <p class="text-[11px] text-white/80">Tanya harga, stok &amp; produk</p>
                </div>
            </div>
            <button type="button" data-chatbot-close
                    class="grid h-8 w-8 place-items-center rounded-lg text-white/90 transition hover:bg-white/15"
                    aria-label="Tutup chat">
                <svg class="h-5 w-5"><use href="#i-close"/></svg>
            </button>
        </div>

        <!-- Area pesan -->
        <div data-chatbot-messages class="no-scrollbar flex-1 space-y-3 overflow-y-auto bg-ink-50/60 px-4 py-4 text-sm">
            <!-- Pesan sambutan -->
            <div class="flex gap-2">
                <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-100 text-brand-700">
                    <svg class="h-4 w-4"><use href="#i-headset"/></svg>
                </span>
                <div class="max-w-[85%] rounded-2xl rounded-tl-sm border border-ink-100 bg-white px-3 py-2 text-ink-700 shadow-sm">
                    Hai! 👋 Saya asisten {{ config('app.name') }}. Silakan tanya harga, ketersediaan stok, atau rekomendasi produk gaming, diecast &amp; hobi.
                </div>
            </div>
        </div>

        <!-- Saran cepat -->
        <div data-chatbot-suggestions class="flex flex-wrap gap-2 border-t border-ink-100 bg-white px-3 pt-3">
            <button type="button" data-chatbot-suggest="Berapa harga PlayStation 5?" class="rounded-full border border-ink-200 px-3 py-1.5 text-xs font-medium text-ink-600 transition hover:border-brand-300 hover:text-brand-700">Harga PS5?</button>
            <button type="button" data-chatbot-suggest="Produk apa saja yang sedang promo?" class="rounded-full border border-ink-200 px-3 py-1.5 text-xs font-medium text-ink-600 transition hover:border-brand-300 hover:text-brand-700">Sedang promo?</button>
            <button type="button" data-chatbot-suggest="Rekomendasi headset gaming yang stoknya tersedia" class="rounded-full border border-ink-200 px-3 py-1.5 text-xs font-medium text-ink-600 transition hover:border-brand-300 hover:text-brand-700">Rekomendasi headset</button>
        </div>

        <!-- Input -->
        <form data-chatbot-form class="flex items-end gap-2 border-t border-ink-100 bg-white px-3 py-3">
            <textarea data-chatbot-input rows="1" placeholder="Tulis pertanyaan…" maxlength="500"
                      class="no-scrollbar max-h-24 flex-1 resize-none rounded-xl border border-ink-200 px-3 py-2 text-sm text-ink-800 outline-none transition placeholder:text-ink-400 focus:border-brand-400 focus:ring-2 focus:ring-brand-100"></textarea>
            <button type="submit" data-chatbot-send
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-600 text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
                    aria-label="Kirim">
                <svg class="h-5 w-5"><use href="#i-arrow-right"/></svg>
            </button>
        </form>
    </div>

    <!-- Tombol melayang -->
    <button type="button" data-chatbot-toggle
            class="absolute right-4 bottom-4 grid h-14 w-14 place-items-center rounded-full bg-brand-600 text-white shadow-lift transition hover:bg-brand-700 sm:static sm:h-14 sm:w-14"
            aria-label="Buka asisten chat">
        <svg class="h-6 w-6" data-chatbot-icon-open><use href="#i-headset"/></svg>
        <svg class="hidden h-6 w-6" data-chatbot-icon-close><use href="#i-close"/></svg>
    </button>
</div>
