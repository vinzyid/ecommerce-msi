import './bootstrap';

/**
 * Interaksi ringan storefront: dropdown akun, menu mobile, stepper qty,
 * carousel banner, dan animasi reveal saat scroll.
 */
document.addEventListener('DOMContentLoaded', () => {
    /* ---------- Dropdown akun ---------- */
    document.querySelectorAll('[data-dropdown]').forEach((root) => {
        const trigger = root.querySelector('[data-dropdown-trigger]');
        const menu = root.querySelector('[data-dropdown-menu]');
        if (!trigger || !menu) return;

        const close = () => {
            trigger.setAttribute('aria-expanded', 'false');
            menu.classList.add('invisible', 'opacity-0', 'scale-95');
            menu.classList.remove('visible', 'opacity-100', 'scale-100');
        };

        const open = () => {
            trigger.setAttribute('aria-expanded', 'true');
            menu.classList.remove('invisible', 'opacity-0', 'scale-95');
            menu.classList.add('visible', 'opacity-100', 'scale-100');
        };

        close();
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            trigger.getAttribute('aria-expanded') === 'true' ? close() : open();
        });

        document.addEventListener('click', (e) => {
            if (!root.contains(e.target)) close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });
    });

    /* ---------- Menu mobile ---------- */
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    if (mobileMenu) {
        const openBtn = document.querySelector('[data-mobile-toggle]');
        const show = () => {
            mobileMenu.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };
        const hide = () => {
            mobileMenu.classList.add('hidden');
            document.body.style.overflow = '';
        };

        openBtn?.addEventListener('click', show);
        mobileMenu.querySelector('[data-mobile-close]')?.addEventListener('click', hide);
        mobileMenu.querySelector('[data-mobile-backdrop]')?.addEventListener('click', hide);
    }

    /* ---------- Stepper kuantitas ---------- */
    document.querySelectorAll('[data-quantity]').forEach((wrap) => {
        const input = wrap.querySelector('[data-quantity-input]');
        if (!input) return;
        const min = parseInt(input.min || '1', 10);
        const max = parseInt(input.max || '999', 10);

        wrap.querySelectorAll('[data-quantity-step]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const dir = parseInt(btn.dataset.quantityStep, 10);
                let value = parseInt(input.value || '1', 10) + dir;
                value = Math.max(min, Math.min(max, value));
                input.value = value;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    });

    /* ---------- Carousel banner ---------- */
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
        const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
        if (!track || slides.length <= 1) return;

        let index = 0;
        let timer = null;

        const go = (i) => {
            index = (i + slides.length) % slides.length;
            track.style.transform = `translateX(-${index * 100}%)`;
            dots.forEach((dot, di) => {
                dot.classList.toggle('bg-white', di === index);
                dot.classList.toggle('bg-white/40', di !== index);
            });
        };

        const start = () => {
            stop();
            timer = setInterval(() => go(index + 1), 5000);
        };
        const stop = () => timer && clearInterval(timer);

        carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => { go(index + 1); start(); });
        carousel.querySelector('[data-carousel-prev]')?.addEventListener('click', () => { go(index - 1); start(); });
        dots.forEach((dot, di) => dot.addEventListener('click', () => { go(di); start(); }));

        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', start);

        go(0);
        start();
    });

    /* ---------- Reveal saat scroll ---------- */
    const revealTargets = document.querySelectorAll('[data-reveal]');
    if (revealTargets.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('opacity-0', 'translate-y-4');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });

        revealTargets.forEach((el) => {
            el.classList.add('transition', 'duration-500', 'opacity-0', 'translate-y-4');
            observer.observe(el);
        });
    }

    /* ---------- Flash Sale Countdown Timer (Shopee / Tokopedia Style) ---------- */
    const timerEl = document.querySelector('[data-flash-sale-timer]');
    if (timerEl) {
        let totalSeconds = 2 * 3600 + 45 * 60 + 30; // 02:45:30
        const hEl = timerEl.querySelector('[data-hours]');
        const mEl = timerEl.querySelector('[data-minutes]');
        const sEl = timerEl.querySelector('[data-seconds]');

        setInterval(() => {
            if (totalSeconds > 0) totalSeconds--;
            const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(totalSeconds % 60).padStart(2, '0');
            if (hEl) hEl.textContent = hours;
            if (mEl) mEl.textContent = minutes;
            if (sEl) sEl.textContent = seconds;
        }, 1000);
    }

    /* ---------- Salin Kode Voucher & Teks ---------- */
    document.querySelectorAll('[data-copy-voucher]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const code = btn.dataset.copyVoucher;
            if (!code) return;
            try {
                await navigator.clipboard.writeText(code);
                const originalText = btn.textContent;
                btn.textContent = 'Tersalin!';
                btn.classList.add('bg-emerald-600', 'text-white');
                showToast(`Kode "${code}" berhasil disalin ke clipboard!`, 'success', 'Tersalin!');
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.classList.remove('bg-emerald-600', 'text-white');
                }, 2000);
            } catch {
                showToast(`Gagal menyalin teks: ${code}`, 'error');
            }
        });
    });

    /* ---------- Toggle Lihat/Sembunyikan Password (Tombol Mata) ---------- */
    document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const wrap = btn.closest('.relative');
            const input = wrap?.querySelector('[data-password-input]') || wrap?.querySelector('input');
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            const eyeOpen = btn.querySelector('[data-eye-open]');
            const eyeClosed = btn.querySelector('[data-eye-closed]');

            if (eyeOpen && eyeClosed) {
                eyeOpen.classList.toggle('hidden', isPassword);
                eyeClosed.classList.toggle('hidden', !isPassword);
            }

            btn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });

    /* ---------- Sistem Notifikasi Toast + Suara ---------- */
    initToasts();

    /* ---------- Chatbot konsumen ---------- */
    initChatbot();
});

/**
 * Suara "centung" memakai Web Audio API (tanpa file eksternal).
 */
function playChime(type = 'success') {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();

        const play = (freq, start, duration, gain = 0.16) => {
            const osc = ctx.createOscillator();
            const vol = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, ctx.currentTime + start);
            vol.gain.setValueAtTime(0.0001, ctx.currentTime + start);
            vol.gain.exponentialRampToValueAtTime(gain, ctx.currentTime + start + 0.01);
            vol.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + start + duration);
            osc.connect(vol);
            vol.connect(ctx.destination);
            osc.start(ctx.currentTime + start);
            osc.stop(ctx.currentTime + start + duration);
        };

        if (type === 'success') {
            play(880, 0, 0.14);   // A5
            play(1318.5, 0.09, 0.22); // E6 (centung naik, cerah)
        } else if (type === 'error') {
            play(440, 0, 0.16);
            play(330, 0.1, 0.24);  // turun, menandakan gagal
        } else {
            play(1046.5, 0, 0.12);
            play(1568, 0.08, 0.18);
        }

        setTimeout(() => ctx.close(), 800);
    } catch {
        // diamkan bila browser memblokir autoplay audio
    }
}

/**
 * Tampilkan toast pop-up modern + suara.
 */
function showToast(message, type = 'success', title = null) {
    const container = document.getElementById('toast-container');
    if (!container || !message) return;

    const styles = {
        success: {
            icon: 'i-check-circle',
            ring: 'bg-emerald-100 text-emerald-600',
            bar: 'bg-emerald-500',
            title: title || 'Berhasil!',
        },
        error: {
            icon: 'i-info',
            ring: 'bg-danger-500/15 text-danger-600',
            bar: 'bg-danger-500',
            title: title || 'Gagal!',
        },
        info: {
            icon: 'i-bolt',
            ring: 'bg-brand-100 text-brand-600',
            bar: 'bg-brand-500',
            title: title || 'Pemberitahuan',
        },
    };

    const s = styles[type] || styles.success;

    const toast = document.createElement('div');
    toast.className =
        'pointer-events-auto relative flex translate-y-[-12px] items-start gap-3 overflow-hidden rounded-2xl border border-ink-100 bg-white/95 p-3.5 opacity-0 shadow-2xl backdrop-blur-md transition-all duration-300 ease-out';
    toast.innerHTML =
        `<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${s.ring}">` +
        `<svg class="h-5.5 w-5.5"><use href="#${s.icon}"/></svg></span>` +
        `<div class="min-w-0 flex-1 pt-0.5">` +
        `<p class="text-sm font-extrabold text-ink-900">${s.title}</p>` +
        `<p class="mt-0.5 text-xs leading-relaxed text-ink-600">${message}</p>` +
        `</div>` +
        `<button type="button" class="shrink-0 rounded-lg p-1 text-ink-400 transition hover:bg-ink-50 hover:text-ink-700" aria-label="Tutup notifikasi">` +
        `<svg class="h-4 w-4"><use href="#i-close"/></svg></button>` +
        `<span class="absolute bottom-0 left-0 h-0.5 ${s.bar}" style="width:100%"></span>`;

    container.appendChild(toast);
    playChime(type);

    // Animasi masuk
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-[-12px]', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    });

    // Progress bar mengecil
    const bar = toast.querySelector('span.absolute');
    if (bar) {
        requestAnimationFrame(() => {
            bar.style.transition = 'width 4.5s linear';
            bar.style.width = '0%';
        });
    }

    // Fungsi tutup
    const dismiss = () => {
        toast.classList.add('translate-y-[-12px]', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    };

    toast.querySelector('button')?.addEventListener('click', dismiss);
    setTimeout(dismiss, 4500);
}

/**
 * Baca pesan flash dari session & pasang listener global.
 */
function initToasts() {
    const flash = document.getElementById('flash-data');
    if (flash) {
        const success = flash.dataset.success;
        const error = flash.dataset.error;
        if (success) setTimeout(() => showToast(success, 'success', 'Berhasil!'), 250);
        if (error) setTimeout(() => showToast(error, 'error', 'Perhatian!'), 400);
    }

    window.showToast = showToast;
    window.playChime = playChime;
}

function initChatbot() {
    const root = document.querySelector('[data-chatbot]');
    if (!root) return;

    const panel = root.querySelector('[data-chatbot-panel]');
    const toggle = root.querySelector('[data-chatbot-toggle]');
    const closeBtn = root.querySelector('[data-chatbot-close]');
    const clearBtn = root.querySelector('[data-chatbot-clear]');
    const form = root.querySelector('[data-chatbot-form]');
    const input = root.querySelector('[data-chatbot-input]');
    const sendBtn = root.querySelector('[data-chatbot-send]');
    const messages = root.querySelector('[data-chatbot-messages]');
    const suggestions = root.querySelector('[data-chatbot-suggestions]');
    const iconOpen = root.querySelector('[data-chatbot-icon-open]');
    const iconClose = root.querySelector('[data-chatbot-icon-close]');
    const badge = root.querySelector('[data-chatbot-badge]');
    const dot = root.querySelector('[data-chatbot-dot]');

    const endpoint = root.dataset.chatbotUrl || '/chatbot';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const userId = root.dataset.userId || 'guest';
    const storeKey = `chatbot_history_u_${userId}`;

    // Bersihkan riwayat lama yang tidak terisolasi
    localStorage.removeItem('chatbot_history_v1');

    // Setiap kali user logout, hapus riwayat chat sesi ini dari browser
    document.querySelectorAll('form[action*="logout"]').forEach((form) => {
        form.addEventListener('submit', () => {
            localStorage.removeItem(storeKey);
        });
    });

    let busy = false;

    /* --- Buka / tutup panel --- */
    const openPanel = () => {
        panel.classList.remove('pointer-events-none', 'opacity-0', 'translate-y-4', 'translate-y-2');
        panel.classList.add('pointer-events-auto', 'opacity-100', 'translate-y-0');
        iconOpen?.classList.add('hidden');
        iconClose?.classList.remove('hidden');
        badge?.classList.add('hidden');
        dot?.classList.add('hidden');
        toggle?.setAttribute('aria-label', 'Tutup asisten chat');
        input?.focus();
    };
    const closePanel = () => {
        panel.classList.add('pointer-events-none', 'opacity-0', 'translate-y-4', 'translate-y-2');
        panel.classList.remove('pointer-events-auto', 'opacity-100', 'translate-y-0');
        iconOpen?.classList.remove('hidden');
        iconClose?.classList.add('hidden');
        badge?.classList.remove('hidden');
        dot?.classList.remove('hidden');
        toggle?.setAttribute('aria-label', 'Buka asisten chat');
    };
    const isOpen = () => panel.classList.contains('opacity-100');

    toggle?.addEventListener('click', () => (isOpen() ? closePanel() : openPanel()));
    badge?.addEventListener('click', openPanel);
    closeBtn?.addEventListener('click', closePanel);
    clearBtn?.addEventListener('click', () => {
        if (!confirm('Hapus riwayat percakapan?')) return;
        localStorage.removeItem(storeKey);
        // Sisakan pesan sambutan pertama
        const welcome = messages.firstElementChild;
        messages.innerHTML = '';
        if (welcome) messages.appendChild(welcome);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen()) closePanel();
    });

    /* --- Riwayat disimpan lokal di browser (tidak boros token/DB) --- */
    const loadHistory = () => {
        try {
            return JSON.parse(localStorage.getItem(storeKey) || '[]');
        } catch {
            return [];
        }
    };
    const saveHistory = (history) => {
        // Batasi 20 pesan terakhir agar tidak menumpuk.
        localStorage.setItem(storeKey, JSON.stringify(history.slice(-20)));
    };

    /* --- Render pesan --- */
    const escapeHtml = (text) =>
        text.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const formatText = (text) => {
        let clean = escapeHtml(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong class="font-bold text-ink-900">$1</strong>');

        // Deteksi dan rapikan baris markdown table (fallback jika AI tetap membuat tabel)
        const lines = clean.split('\n');
        const formatted = [];
        let inTable = false;
        let tableRows = [];

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();
            if (line.startsWith('|') && line.endsWith('|')) {
                // Abaikan baris separator |---|---|
                if (/^\|[\s\-:]+\|\s*$/.test(line)) continue;
                inTable = true;
                const cells = line.split('|').slice(1, -1).map((c) => c.trim());
                tableRows.push(cells);
            } else {
                if (inTable && tableRows.length) {
                    // Render tabel sebagai card responsif yang bersih
                    let html = '<div class="my-2 overflow-x-auto rounded-xl border border-ink-200 bg-ink-50/70 p-2 text-xs"><table class="w-full text-left"><tbody>';
                    tableRows.forEach((row, ri) => {
                        html += `<tr class="${ri === 0 ? 'font-bold border-b border-ink-200 text-ink-900' : 'border-b border-ink-100/60'}">`;
                        row.forEach((cell) => { html += `<td class="p-1.5 align-top">${cell}</td>`; });
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                    formatted.push(html);
                    tableRows = [];
                    inTable = false;
                }
                if (line.startsWith('- ')) {
                    formatted.push(`<li class="ml-4 list-disc text-ink-700">${line.slice(2)}</li>`);
                } else if (/^\d+\.\s/.test(line)) {
                    formatted.push(`<p class="mt-2 font-bold text-ink-900">${line}</p>`);
                } else if (line) {
                    formatted.push(`<p class="mt-1">${line}</p>`);
                }
            }
        }

        if (inTable && tableRows.length) {
            let html = '<div class="my-2 overflow-x-auto rounded-xl border border-ink-200 bg-ink-50/70 p-2 text-xs"><table class="w-full text-left"><tbody>';
            tableRows.forEach((row, ri) => {
                html += `<tr class="${ri === 0 ? 'font-bold border-b border-ink-200 text-ink-900' : 'border-b border-ink-100/60'}">`;
                row.forEach((cell) => { html += `<td class="p-1.5 align-top">${cell}</td>`; });
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            formatted.push(html);
        }

        return formatted.join('');
    };

    const bubble = (text, who) => {
        const wrap = document.createElement('div');
        wrap.className = 'flex gap-2';

        const now = new Date();
        const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        if (who === 'user') {
            wrap.className += ' justify-end';
            wrap.innerHTML =
                `<div class="flex flex-col items-end max-w-[85%]">` +
                `<div class="rounded-2xl rounded-tr-sm bg-brand-600 px-3.5 py-2 text-white shadow-sm text-sm leading-relaxed">${formatText(text)}</div>` +
                `<span class="mt-1 text-[10px] text-ink-400">${time}</span>` +
                `</div>`;
        } else {
            wrap.innerHTML =
                `<span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-100 text-brand-700 shadow-xs"><svg class="h-4 w-4"><use href="#i-headset"/></svg></span>` +
                `<div class="flex flex-col max-w-[85%]">` +
                `<div class="rounded-2xl rounded-tl-sm border border-ink-100 bg-white px-3.5 py-2.5 text-ink-700 shadow-sm leading-relaxed">${formatText(text)}</div>` +
                `<span class="mt-1 text-[10px] text-ink-400">Asisten &bull; ${time}</span>` +
                `</div>`;
        }

        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
        return wrap;
    };

    const loading = () => {
        const wrap = document.createElement('div');
        wrap.className = 'flex gap-2';
        wrap.innerHTML =
            `<span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-100 text-brand-700"><svg class="h-4 w-4"><use href="#i-headset"/></svg></span>` +
            `<div class="rounded-2xl rounded-tl-sm border border-ink-100 bg-white px-3 py-2 text-ink-400 shadow-sm">Sedang mengetik…</div>`;
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
        return wrap;
    };

    /* --- Pulihkan riwayat --- */
    loadHistory().forEach((m) => bubble(m.text, m.who));

    /* --- Kirim pertanyaan --- */
    const send = async (question) => {
        const text = (question ?? input.value).trim();
        if (!text || busy) return;

        busy = true;
        sendBtn.disabled = true;
        if (!question) input.value = '';
        input.style.height = 'auto';

        const history = loadHistory();
        const conversation = history.slice(-8).map((m) => ({
            role: m.who === 'user' ? 'user' : 'assistant',
            content: m.text,
        }));

        history.push({ who: 'user', text });
        saveHistory(history);
        bubble(text, 'user');

        const loader = loading();

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ question: text, history: conversation }),
            });

            const data = await res.json().catch(() => ({}));

            loader.remove();

            if (!res.ok || data.error) {
                bubble(data.error || 'Maaf, terjadi kesalahan. Coba lagi ya.', 'bot');
                return;
            }

            bubble(data.answer, 'bot');
            playChime('info');
            const h2 = loadHistory();
            h2.push({ who: 'bot', text: data.answer });
            saveHistory(h2);
        } catch {
            loader.remove();
            bubble('Koneksi bermasalah. Periksa internetmu lalu coba lagi.', 'bot');
        } finally {
            busy = false;
            sendBtn.disabled = false;
            input.focus();
        }
    };

    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        send();
    });

    /* --- Enter kirim, Shift+Enter baris baru --- */
    input?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            send();
        }
    });

    /* --- Auto-tinggi textarea --- */
    input?.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 96)}px`;
    });

    /* --- Saran cepat --- */
    suggestions?.querySelectorAll('[data-chatbot-suggest]').forEach((btn) => {
        btn.addEventListener('click', () => send(btn.dataset.chatbotSuggest));
    });
}
