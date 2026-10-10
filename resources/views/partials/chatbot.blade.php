{{--
    Widget chatbot konsumen.
    - Tombol melayang di kanan bawah.
    - Panel chat muncul saat diklik.
    - Riwayat disimpan di localStorage browser (tanpa simpan DB per sesi).
    - Jawaban dari server (di-cache 1 jam supaya hemat token).
--}}
<div data-chatbot data-chatbot-url="{{ route('chatbot.ask') }}" data-user-id="{{ auth()->id() ?? 'guest' }}"
     class="fixed bottom-20 right-4 z-50 flex flex-col items-end sm:bottom-6 sm:right-6">
    <!-- Panel chat -->
    <div data-chatbot-panel
         class="pointer-events-none mb-3 flex h-[68vh] max-h-[560px] w-[calc(100vw-2rem)] max-w-[390px] translate-y-4 flex-col overflow-hidden rounded-2xl border border-ink-200 bg-white opacity-0 shadow-2xl transition-all duration-200 sm:w-[390px] sm:translate-y-2"
         role="dialog" aria-modal="true" aria-label="Asisten belanja {{ config('app.name') }}">
        <!-- Header -->
        <div class="relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-700 to-brand-800 px-4 py-3.5 text-white">
            <div class="pointer-events-none absolute -right-8 -top-10 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="relative grid h-10 w-10 place-items-center rounded-full bg-white/15 ring-1 ring-white/25">
                        <svg class="h-5 w-5"><use href="#i-headset"/></svg>
                        <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-brand-700 bg-emerald-400"></span>
                    </span>
                    <div class="leading-tight">
                        <p class="text-sm font-bold">Asisten {{ config('app.name') }}</p>
                        <p class="flex items-center gap-1 text-[11px] text-white/80">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Online &bull; siap bantu 24/7
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" data-chatbot-clear
                            class="grid h-8 w-8 place-items-center rounded-lg text-white/90 transition hover:bg-white/15"
                            aria-label="Hapus riwayat chat" title="Hapus riwayat">
                        <svg class="h-5 w-5"><use href="#i-trash"/></svg>
                    </button>
                    <button type="button" data-chatbot-close
                            class="grid h-8 w-8 place-items-center rounded-lg text-white/90 transition hover:bg-white/15"
                            aria-label="Tutup chat">
                        <svg class="h-5 w-5"><use href="#i-close"/></svg>
                    </button>
                </div>
            </div>
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

    <!-- Tooltip & Tombol FAB -->
    <div class="flex items-center gap-2.5">
        <!-- Tooltip bubble (desktop) -->
        <button type="button" data-chatbot-badge
                class="hidden items-center gap-2 rounded-2xl border border-ink-200/80 bg-white px-3.5 py-2 text-xs shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lift sm:flex">
            <span class="flex h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
            <span class="font-bold text-ink-800">Tanya Asisten Toko</span>
            <span class="text-ink-300">|</span>
            <span class="text-[11px] font-semibold text-brand-600">Online</span>
        </button>

        <!-- Tombol FAB bulat -->
        <button type="button" data-chatbot-toggle
                class="relative grid h-14 w-14 shrink-0 place-items-center rounded-full bg-brand-600 text-white shadow-lift ring-4 ring-brand-500/15 transition-all duration-200 hover:scale-105 hover:bg-brand-700 active:scale-95"
                aria-label="Buka asisten chat">
            <svg class="h-6 w-6" data-chatbot-icon-open><use href="#i-headset"/></svg>
            <svg class="hidden h-6 w-6" data-chatbot-icon-close><use href="#i-close"/></svg>
            <span data-chatbot-dot class="absolute right-0.5 top-0.5 flex h-3 w-3">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full border-2 border-brand-600 bg-emerald-400"></span>
            </span>
        </button>
    </div>
</div>
