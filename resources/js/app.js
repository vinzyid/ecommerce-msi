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
        const panel = mobileMenu.querySelector('[data-mobile-panel]');
        const show = () => {
            mobileMenu.classList.remove('hidden');
            mobileMenu.offsetHeight; // paksa reflow agar transisi masuk berjalan
            mobileMenu.classList.add('is-open');
            openBtn?.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        };
        const hide = () => {
            if (!mobileMenu.classList.contains('is-open')) return;
            mobileMenu.classList.remove('is-open');
            openBtn?.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            const done = () => {
                if (!mobileMenu.classList.contains('is-open')) mobileMenu.classList.add('hidden');
            };
            // Sembunyikan setelah drawer selesai bergeser keluar
            panel ? panel.addEventListener('transitionend', done, { once: true }) : done();
            setTimeout(done, 400);
        };

        openBtn?.addEventListener('click', show);
        mobileMenu.querySelector('[data-mobile-close]')?.addEventListener('click', hide);
        mobileMenu.querySelector('[data-mobile-backdrop]')?.addEventListener('click', hide);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hide();
        });
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
    // Waktu slide dikendalikan animasi pengisi titik aktif: saat animasinya selesai,
    // slide berganti. Menjeda animasi (hover, fokus, tab tersembunyi) otomatis menjeda slider.
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const viewport = carousel.querySelector('[data-carousel-viewport]') || carousel;
        const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
        const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
        if (!track || slides.length <= 1) return;

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        carousel.style.setProperty('--carousel-interval', `${parseInt(carousel.dataset.interval || '6000', 10)}ms`);

        let index = 0;
        const pause = { hover: false, focus: false, hidden: document.hidden };

        const go = (i) => {
            index = (i + slides.length) % slides.length;
            // Pakai posisi asli slide karena ada jarak (gap) antar slide
            track.style.transform = `translateX(-${slides[index].offsetLeft - slides[0].offsetLeft}px)`;
            slides.forEach((slide, si) => {
                // Slide yang tidak tampil tidak bisa difokus / dibacakan
                slide.inert = si !== index;
                slide.setAttribute('aria-hidden', String(si !== index));
            });
            dots.forEach((dot, di) => dot.setAttribute('aria-selected', String(di === index)));
        };

        const syncPause = () => {
            const paused = pause.hover || pause.focus || pause.hidden || reduceMotion.matches;
            carousel.toggleAttribute('data-paused', paused);
        };

        dots.forEach((dot, di) => {
            dot.addEventListener('click', () => go(di));
            dot.firstElementChild?.addEventListener('animationend', () => {
                // Dengan gerak dikurangi, animasi dipercepat 1ms: jangan ikut berganti slide
                if (di === index && !reduceMotion.matches && !carousel.hasAttribute('data-paused')) go(index + 1);
            });
        });
        carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => go(index + 1));
        carousel.querySelector('[data-carousel-prev]')?.addEventListener('click', () => go(index - 1));

        carousel.addEventListener('mouseenter', () => { pause.hover = true; syncPause(); });
        carousel.addEventListener('mouseleave', () => { pause.hover = false; syncPause(); });
        carousel.addEventListener('focusin', () => { pause.focus = true; syncPause(); });
        carousel.addEventListener('focusout', (e) => {
            if (!carousel.contains(e.relatedTarget)) { pause.focus = false; syncPause(); }
        });
        document.addEventListener('visibilitychange', () => { pause.hidden = document.hidden; syncPause(); });
        reduceMotion.addEventListener?.('change', syncPause);

        carousel.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') go(index + 1);
            if (e.key === 'ArrowLeft') go(index - 1);
        });

        // Geser dengan jari / mouse; kibasan cepat cukup untuk pindah slide
        let startX = null;
        let startTime = 0;
        let swiped = false;
        viewport.addEventListener('pointerdown', (e) => {
            if (e.button !== 0) return;
            startX = e.clientX;
            startTime = performance.now();
            swiped = false;
        });
        viewport.addEventListener('pointerup', (e) => {
            if (startX === null) return;
            const dx = e.clientX - startX;
            const velocity = Math.abs(dx) / Math.max(1, performance.now() - startTime);
            startX = null;
            if (Math.abs(dx) > 60 || (Math.abs(dx) > 15 && velocity > 0.4)) {
                swiped = true;
                go(index + (dx < 0 ? 1 : -1));
            }
        });
        viewport.addEventListener('pointercancel', () => { startX = null; });
        // Klik setelah menggeser jangan membuka tautan produk
        viewport.addEventListener('click', (e) => {
            if (swiped) {
                e.preventDefault();
                swiped = false;
            }
        }, true);
        viewport.addEventListener('dragstart', (e) => e.preventDefault());

        // Saat ukuran layar berubah, sesuaikan posisi tanpa animasi
        let resizeFrame = 0;
        window.addEventListener('resize', () => {
            cancelAnimationFrame(resizeFrame);
            resizeFrame = requestAnimationFrame(() => {
                track.style.transitionDuration = '0ms';
                go(index);
                track.offsetHeight;
                track.style.transitionDuration = '';
            });
        });

        go(0);
        syncPause();
    });

    /* ---------- Hitung mundur flash sale ---------- */
    // Berbasis jam server (bukan jam perangkat) dan tidak ter-reset saat halaman dimuat ulang.
    document.querySelectorAll('[data-countdown]').forEach((el) => {
        const DAY = 24 * 60 * 60 * 1000;
        let end = parseInt(el.dataset.countdownEnd, 10);
        const offset = parseInt(el.dataset.countdownNow, 10) - Date.now();
        const parts = {
            h: el.querySelector('[data-countdown-hours]'),
            m: el.querySelector('[data-countdown-minutes]'),
            s: el.querySelector('[data-countdown-seconds]'),
        };
        if (!end || !parts.h) return;

        const pad = (n) => String(n).padStart(2, '0');
        const tick = () => {
            let left = end - (Date.now() + offset);
            // Flash sale harian: lewat tengah malam langsung lanjut ke hari berikutnya
            while (left <= 0) {
                end += DAY;
                left = end - (Date.now() + offset);
            }
            const total = Math.floor(left / 1000);
            parts.h.textContent = pad(Math.floor(total / 3600));
            parts.m.textContent = pad(Math.floor((total % 3600) / 60));
            parts.s.textContent = pad(total % 60);
        };

        tick();
        setInterval(tick, 1000);
    });

    /* ---------- Reveal saat scroll ---------- */
    // Section yang berisi kartu produk tidak ikut fade, kartunya yang masuk bergiliran.
    const revealTargets = [
        ...Array.from(document.querySelectorAll('[data-reveal]')).filter((el) => !el.querySelector('[data-reveal-item]')),
        ...document.querySelectorAll('[data-reveal-item]'),
    ];
    if (revealTargets.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            const entering = entries.filter((entry) => entry.isIntersecting);
            entering.forEach((entry, i) => {
                // Stagger 40ms antar kartu, dibatasi agar tidak terasa lambat
                entry.target.style.transitionDelay = `${Math.min(i, 6) * 40}ms`;
                entry.target.classList.remove('is-pending');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

        revealTargets.forEach((el) => {
            // Yang sudah terlihat saat halaman dibuka tidak perlu dianimasikan
            if (el.getBoundingClientRect().top < window.innerHeight) return;
            el.classList.add('is-pending');
            observer.observe(el);
        });
    }

    /* ---------- Salin Kode Voucher & Teks ---------- */
    document.querySelectorAll('[data-copy-voucher]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const code = btn.dataset.copyVoucher;
            if (!code) return;
            try {
                await navigator.clipboard.writeText(code);
                const originalText = btn.textContent;
                btn.textContent = 'Tersalin';
                showToast(`Kode "${code}" berhasil disalin ke clipboard!`, 'success', 'Tersalin!');
                setTimeout(() => {
                    btn.textContent = originalText;
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
        'pointer-events-auto relative flex -translate-y-3 scale-[0.97] items-start gap-3 overflow-hidden rounded-2xl border border-ink-100 bg-white/95 p-3.5 opacity-0 shadow-2xl backdrop-blur-md transition-[opacity,translate,scale] duration-300 ease-(--ease-out)';
    toast.innerHTML =
        `<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${s.ring}">` +
        `<svg class="h-5.5 w-5.5"><use href="#${s.icon}"/></svg></span>` +
        `<div class="min-w-0 flex-1 pt-0.5">` +
        `<p class="text-sm font-extrabold text-ink-900">${s.title}</p>` +
        `<p class="mt-0.5 text-xs leading-relaxed text-ink-600">${message}</p>` +
        `</div>` +
        `<button type="button" class="shrink-0 rounded-lg p-1 text-ink-400 transition hover:bg-ink-50 hover:text-ink-700" aria-label="Tutup notifikasi">` +
        `<svg class="h-4 w-4"><use href="#i-close"/></svg></button>` +
        `<span class="absolute inset-x-0 bottom-0 h-0.5 origin-left ${s.bar}"></span>`;

    container.appendChild(toast);
    playChime(type);

    // Animasi masuk (paksa reflow dulu agar state awal ter-render)
    toast.offsetHeight;
    toast.classList.remove('-translate-y-3', 'scale-[0.97]', 'opacity-0');

    // Fungsi tutup: keluar ke arah yang sama dengan masuk, lebih cepat
    let closed = false;
    const dismiss = () => {
        if (closed) return;
        closed = true;
        document.removeEventListener('visibilitychange', onVisibility);
        toast.classList.replace('duration-300', 'duration-200');
        toast.classList.add('-translate-y-3', 'opacity-0');
        setTimeout(() => toast.remove(), 200);
    };

    // Progress bar sekaligus jadi timer, dijeda saat di-hover atau tab disembunyikan
    const bar = toast.querySelector('span.absolute');
    const timer = bar.animate([{ transform: 'scaleX(1)' }, { transform: 'scaleX(0)' }], {
        duration: 4500,
        easing: 'linear',
        fill: 'forwards',
    });
    timer.onfinish = dismiss;

    const onVisibility = () => (document.hidden ? timer.pause() : timer.play());
    document.addEventListener('visibilitychange', onVisibility);
    toast.addEventListener('mouseenter', () => timer.pause());
    toast.addEventListener('mouseleave', () => !document.hidden && timer.play());

    toast.querySelector('button')?.addEventListener('click', dismiss);
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
    const confirmBar = root.querySelector('[data-chatbot-confirm]');
    const form = root.querySelector('[data-chatbot-form]');
    const input = root.querySelector('[data-chatbot-input]');
    const sendBtn = root.querySelector('[data-chatbot-send]');
    const messages = root.querySelector('[data-chatbot-messages]');
    const iconOpen = root.querySelector('[data-chatbot-icon-open]');
    const iconClose = root.querySelector('[data-chatbot-icon-close]');
    const badge = root.querySelector('[data-chatbot-badge]');
    const ping = root.querySelector('[data-chatbot-ping]');

    const endpoint = root.dataset.chatbotUrl || '/chatbot';
    const feedbackUrl = root.dataset.chatbotFeedbackUrl || '/chatbot/feedback';
    const cartUrl = root.dataset.cartUrl || '/cart';
    const pageProduct = root.dataset.pageProduct || null;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const userId = root.dataset.userId || 'guest';
    const storeKey = `chatbot_history_u_${userId}`;
    const seenKey = 'chatbot_seen_v1';
    const mobile = window.matchMedia('(max-width: 639px)');
    const finePointer = window.matchMedia('(pointer: fine)');

    const storage = {
        get(key) {
            try { return localStorage.getItem(key); } catch { return null; }
        },
        set(key, value) {
            try { localStorage.setItem(key, value); } catch { /* mode privat / penuh */ }
        },
        remove(key) {
            try { localStorage.removeItem(key); } catch { /* abaikan */ }
        },
    };

    // Bersihkan riwayat lama yang tidak terisolasi
    storage.remove('chatbot_history_v1');

    // Setiap kali user logout, hapus riwayat chat sesi ini dari browser
    document.querySelectorAll('form[action*="logout"]').forEach((logoutForm) => {
        logoutForm.addEventListener('submit', () => storage.remove(storeKey));
    });

    // Titik hijau berkedip hanya sampai chat pernah dibuka sekali
    if (!storage.get(seenKey)) ping?.classList.remove('hidden');

    let busy = false;

    /* --- Buka / tutup panel --- */
    const isOpen = () => root.classList.contains('is-open');

    const openPanel = () => {
        root.classList.add('is-open');
        iconOpen?.classList.add('hidden');
        iconClose?.classList.remove('hidden');
        toggle?.setAttribute('aria-expanded', 'true');
        toggle?.setAttribute('aria-label', 'Tutup asisten chat');
        ping?.classList.add('hidden');
        storage.set(seenKey, '1');
        if (mobile.matches) document.body.style.overflow = 'hidden';
        messages.scrollTop = messages.scrollHeight;
        // Di layar sentuh, keyboard yang langsung muncul menutupi saran pertanyaan
        if (finePointer.matches) input?.focus();
    };

    const closePanel = () => {
        root.classList.remove('is-open');
        iconOpen?.classList.remove('hidden');
        iconClose?.classList.add('hidden');
        toggle?.setAttribute('aria-expanded', 'false');
        toggle?.setAttribute('aria-label', 'Buka asisten chat');
        document.body.style.overflow = '';
        hideConfirm();
    };

    toggle?.addEventListener('click', () => (isOpen() ? closePanel() : openPanel()));
    badge?.addEventListener('click', openPanel);
    closeBtn?.addEventListener('click', () => {
        closePanel();
        toggle?.focus();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen()) {
            closePanel();
            toggle?.focus();
        }
    });

    /* --- Hapus riwayat dengan konfirmasi di dalam panel --- */
    const hideConfirm = () => {
        if (confirmBar) confirmBar.hidden = true;
    };
    clearBtn?.addEventListener('click', () => {
        if (!confirmBar) return;
        confirmBar.hidden = !confirmBar.hidden;
        if (!confirmBar.hidden) confirmBar.querySelector('[data-chatbot-confirm-ok]')?.focus();
    });
    confirmBar?.querySelector('[data-chatbot-confirm-cancel]')?.addEventListener('click', hideConfirm);
    confirmBar?.querySelector('[data-chatbot-confirm-ok]')?.addEventListener('click', () => {
        storage.remove(storeKey);
        // Sisakan pesan sambutan pertama (beserta saran pertanyaannya)
        const welcome = messages.querySelector('[data-chatbot-welcome]');
        messages.innerHTML = '';
        if (welcome) messages.appendChild(welcome);
        hideConfirm();
        input?.focus();
    });

    /* --- Riwayat disimpan lokal di browser (tidak boros token/DB) --- */
    const loadHistory = () => {
        try {
            return JSON.parse(storage.get(storeKey) || '[]');
        } catch {
            return [];
        }
    };
    const saveHistory = (history) => {
        // Batasi 20 pesan terakhir agar tidak menumpuk.
        storage.set(storeKey, JSON.stringify(history.slice(-20)));
    };

    /* --- Render pesan --- */
    const escapeHtml = (text) =>
        String(text ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // Tautan hanya boleh ke halaman toko sendiri
    const safeHref = (raw) => {
        const href = raw.replace(/&amp;/g, '&');
        try {
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return null;
            return url.pathname + url.search + url.hash;
        } catch {
            return null;
        }
    };

    const formatInline = (line) =>
        line
            .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold text-ink-900">$1</strong>')
            .replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, (match, label, href) => {
                const safe = safeHref(href);
                return safe
                    ? `<a href="${escapeHtml(safe)}" class="font-semibold text-brand-700 underline decoration-brand-300 underline-offset-2 hover:decoration-brand-600">${label}</a>`
                    : label;
            });

    const renderTable = (rows) => {
        let html = '<div class="my-2 overflow-x-auto rounded-xl border border-ink-200 bg-ink-50/70 p-2 text-xs"><table class="w-full text-left"><tbody>';
        rows.forEach((row, ri) => {
            html += `<tr class="${ri === 0 ? 'font-bold border-b border-ink-200 text-ink-900' : 'border-b border-ink-100/60'}">`;
            row.forEach((cell) => { html += `<td class="p-1.5 align-top">${cell}</td>`; });
            html += '</tr>';
        });
        return `${html}</tbody></table></div>`;
    };

    const formatText = (text) => {
        const lines = formatInline(escapeHtml(text)).split('\n');
        const formatted = [];
        let tableRows = [];

        const flushTable = () => {
            if (tableRows.length) formatted.push(renderTable(tableRows));
            tableRows = [];
        };

        lines.forEach((raw) => {
            const line = raw.trim();
            // Fallback bila AI tetap membuat tabel markdown
            if (line.startsWith('|') && line.endsWith('|')) {
                if (!/^\|[\s\-:|]+\|$/.test(line)) {
                    tableRows.push(line.split('|').slice(1, -1).map((c) => c.trim()));
                }
                return;
            }
            flushTable();
            if (line.startsWith('- ')) {
                formatted.push(`<li class="ml-4 list-disc text-ink-700">${line.slice(2)}</li>`);
            } else if (/^\d+\.\s/.test(line)) {
                formatted.push(`<p class="mt-2 font-bold text-ink-900">${line}</p>`);
            } else if (line) {
                formatted.push(`<p class="mt-1 first:mt-0">${line}</p>`);
            }
        });
        flushTable();

        return formatted.join('');
    };

    const nearBottom = () => messages.scrollHeight - messages.scrollTop - messages.clientHeight < 80;
    const scrollToEnd = (force = false) => {
        if (force || nearBottom()) messages.scrollTop = messages.scrollHeight;
    };

    const timeLabel = (ts) =>
        new Date(ts || Date.now()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const botAvatar =
        '<span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-ink-900 text-white">' +
        '<svg class="h-4 w-4" aria-hidden="true"><use href="#i-headset"/></svg></span>';

    const userBubble = (text, ts) => {
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-end gap-2';
        wrap.innerHTML =
            '<div class="flex max-w-[85%] flex-col items-end">' +
            `<div class="rounded-2xl rounded-tr-md bg-brand-600 px-3.5 py-2 text-sm leading-relaxed break-words text-white">${formatText(text)}</div>` +
            `<span class="mt-1 text-[10px] text-ink-400">${timeLabel(ts)}</span>` +
            '</div>';
        messages.appendChild(wrap);
        scrollToEnd(true);
    };

    const botBubble = (ts) => {
        const wrap = document.createElement('div');
        wrap.className = 'flex gap-2';
        wrap.innerHTML =
            botAvatar +
            '<div class="flex min-w-0 max-w-[85%] flex-col">' +
            '<div data-content class="rounded-2xl rounded-tl-md bg-ink-50 px-3.5 py-2.5 leading-relaxed break-words text-ink-800"></div>' +
            '<div data-extras></div>' +
            `<span data-meta class="mt-1 text-[10px] text-ink-400">Asisten &bull; ${timeLabel(ts)}</span>` +
            '</div>';
        messages.appendChild(wrap);
        return {
            wrap,
            content: wrap.querySelector('[data-content]'),
            extras: wrap.querySelector('[data-extras]'),
            meta: wrap.querySelector('[data-meta]'),
        };
    };

    const typingIndicator = () => {
        const wrap = document.createElement('div');
        wrap.className = 'flex gap-2';
        wrap.setAttribute('aria-label', 'Asisten sedang mengetik');
        wrap.innerHTML =
            botAvatar +
            '<div class="chatbot-typing flex items-center gap-1 rounded-2xl rounded-tl-md bg-ink-50 px-3.5 py-3">' +
            '<span></span><span></span><span></span></div>';
        messages.appendChild(wrap);
        scrollToEnd(true);
        return wrap;
    };

    /* --- Kartu produk di bawah jawaban --- */
    const productCards = (products) => {
        if (!products?.length) return '';
        const cards = products.map((p) => {
            const name = escapeHtml(p.name);
            const url = escapeHtml(safeHref(p.url) || '#');
            const initials = escapeHtml(String(p.name).trim().slice(0, 2).toUpperCase());
            const image = p.image_url
                ? `<img src="${escapeHtml(p.image_url)}" alt="" loading="lazy" class="relative h-full w-full object-contain p-1 mix-blend-multiply" onerror="this.remove()">`
                : '';
            const compare = p.compare_at_price
                ? `<span class="text-[10px] text-ink-400 line-through">${escapeHtml(p.compare_at_price)}</span>` +
                  `<span class="rounded bg-danger-500/10 px-1 text-[10px] font-bold text-danger-600">-${Number(p.discount_percent)}%</span>`
                : '';
            const action = p.in_stock
                ? `<form method="POST" action="${escapeHtml(cartUrl)}" class="shrink-0">` +
                  `<input type="hidden" name="_token" value="${escapeHtml(csrf)}">` +
                  `<input type="hidden" name="product_id" value="${Number(p.id)}">` +
                  '<input type="hidden" name="quantity" value="1">' +
                  `<button type="submit" class="grid h-8 w-8 place-items-center rounded-full bg-ink-900 text-white hover:bg-ink-700" aria-label="Tambah ${name} ke keranjang" title="Tambah ke keranjang">` +
                  '<svg class="h-4 w-4" aria-hidden="true"><use href="#i-cart"/></svg></button></form>'
                : '<span class="shrink-0 rounded-md bg-ink-100 px-1.5 py-1 text-[10px] font-semibold text-ink-500">Stok habis</span>';

            return (
                '<div class="flex items-center gap-2.5 rounded-2xl border border-ink-100 bg-white p-2">' +
                `<a href="${url}" class="relative grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-ink-50 text-xs font-semibold text-ink-400" tabindex="-1" aria-hidden="true">` +
                `<span class="absolute">${initials}</span>${image}</a>` +
                '<div class="min-w-0 flex-1">' +
                `<a href="${url}" class="line-clamp-2 text-xs font-semibold leading-snug text-ink-900 hover:text-brand-700">${name}</a>` +
                `<div class="mt-0.5 flex flex-wrap items-center gap-x-1.5 tabular-nums"><span class="text-xs font-extrabold text-ink-900">${escapeHtml(p.price)}</span>${compare}</div>` +
                '</div>' +
                action +
                '</div>'
            );
        });
        return `<div class="mt-2 space-y-1.5">${cards.join('')}</div>`;
    };

    /* --- Tombol penilaian 👍 / 👎 --- */
    const feedbackRow = (entry) => {
        const row = document.createElement('div');
        row.className = 'mt-1.5 flex items-center gap-1 text-[11px] text-ink-400';

        const render = () => {
            if (entry.rating) {
                row.innerHTML = `<span>${entry.rating > 0 ? '👍' : '👎'} Terima kasih atas penilaiannya!</span>`;
                return;
            }
            row.innerHTML =
                '<span class="mr-1">Membantu?</span>' +
                '<button type="button" data-rate="1" class="rounded-md px-1.5 py-0.5 text-sm hover:bg-ink-100" aria-label="Jawaban membantu">👍</button>' +
                '<button type="button" data-rate="-1" class="rounded-md px-1.5 py-0.5 text-sm hover:bg-ink-100" aria-label="Jawaban tidak membantu">👎</button>';
            row.querySelectorAll('[data-rate]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const rating = Number(btn.dataset.rate);
                    row.querySelectorAll('button').forEach((b) => { b.disabled = true; });
                    try {
                        const res = await fetch(feedbackUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                rating,
                                question: entry.question || '-',
                                answer: entry.text,
                                message_id: entry.messageId || null,
                            }),
                        });
                        if (!res.ok) throw new Error();
                        entry.rating = rating;
                        const history = loadHistory();
                        const saved = history.find((m) => m.ts === entry.ts && m.who === 'bot');
                        if (saved) saved.rating = rating;
                        saveHistory(history);
                        render();
                    } catch {
                        row.querySelectorAll('button').forEach((b) => { b.disabled = false; });
                        showToast('Penilaian gagal dikirim. Coba lagi ya.', 'error');
                    }
                });
            });
        };

        render();
        return row;
    };

    // Tombol pesanan dibuat dari data server, bukan dari tulisan AI
    const orderLinks = (orders) =>
        (orders || []).map((o) => {
            const url = safeHref(o.url);
            if (!url) return '';
            return `<a href="${escapeHtml(url)}" class="mt-1.5 flex items-center justify-between gap-2 rounded-xl border border-ink-200 bg-white px-3 py-2 text-xs hover:border-ink-400">` +
                `<span class="min-w-0 truncate font-semibold text-ink-900">${escapeHtml(o.number)}</span>` +
                `<span class="shrink-0 text-ink-500">${escapeHtml(o.status)} &rsaquo;</span></a>`;
        }).join('');

    const finishBot = (bubbleEls, entry) => {
        bubbleEls.content.classList.remove('chatbot-streaming');
        bubbleEls.content.innerHTML = formatText(entry.text);
        bubbleEls.extras.innerHTML = orderLinks(entry.orders) + productCards(entry.products);
        if (!entry.blocked && entry.question) bubbleEls.meta.after(feedbackRow(entry));
    };

    /* --- Pulihkan riwayat --- */
    loadHistory().forEach((m) => {
        if (m.who === 'user') {
            userBubble(m.text, m.ts);
        } else {
            finishBot(botBubble(m.ts), m);
        }
    });

    /* --- Baca jawaban bertahap (NDJSON) --- */
    const readStream = async (res, onEvent) => {
        if (!res.body?.getReader) {
            const data = await res.json();
            onEvent({ type: 'done', ...data });
            return;
        }
        const reader = res.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        for (;;) {
            const { value, done } = await reader.read();
            if (done) break;
            buffer += decoder.decode(value, { stream: true });
            let newline;
            while ((newline = buffer.indexOf('\n')) >= 0) {
                const line = buffer.slice(0, newline).trim();
                buffer = buffer.slice(newline + 1);
                if (line) onEvent(JSON.parse(line));
            }
        }
        if (buffer.trim()) onEvent(JSON.parse(buffer));
    };

    /* --- Kirim pertanyaan --- */
    const send = async (question) => {
        const text = (question ?? input.value).trim();
        if (text.length < 2 || busy) return;

        busy = true;
        sendBtn.disabled = true;
        if (!question) input.value = '';
        input.style.height = 'auto';
        hideConfirm();

        const history = loadHistory();
        const conversation = history.slice(-8).map((m) => ({
            role: m.who === 'user' ? 'user' : 'assistant',
            content: String(m.text).slice(0, 1000),
        }));

        const userEntry = { who: 'user', text, ts: Date.now() };
        history.push(userEntry);
        saveHistory(history);
        userBubble(text, userEntry.ts);

        let typing = typingIndicator();
        let bubbleEls = null;
        let answer = '';
        let frame = 0;
        const entry = { who: 'bot', text: '', question: text, ts: Date.now() };

        const paint = () => {
            frame = 0;
            bubbleEls.content.innerHTML = formatText(answer);
            scrollToEnd();
        };

        const showError = (message) => {
            typing?.remove();
            typing = null;
            const els = bubbleEls || botBubble();
            els.content.classList.remove('chatbot-streaming');
            els.content.innerHTML = formatText(message);
        };

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/x-ndjson',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ question: text, history: conversation, product: pageProduct }),
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                const message = res.status === 429
                    ? 'Pertanyaanmu banyak sekali 😅 Tunggu sebentar lalu coba lagi ya.'
                    : data.error || data.message || 'Maaf, terjadi kesalahan. Coba lagi ya.';
                showError(message);
                return;
            }

            let finished = false;
            await readStream(res, (event) => {
                if (event.type === 'delta') {
                    if (!bubbleEls) {
                        typing?.remove();
                        typing = null;
                        bubbleEls = botBubble(entry.ts);
                        bubbleEls.content.classList.add('chatbot-streaming');
                    }
                    answer += event.text;
                    if (!frame) frame = requestAnimationFrame(paint);
                } else if (event.type === 'done') {
                    finished = true;
                    if (frame) cancelAnimationFrame(frame);
                    typing?.remove();
                    typing = null;
                    bubbleEls = bubbleEls || botBubble(entry.ts);
                    Object.assign(entry, {
                        text: event.answer || answer,
                        products: event.products || [],
                        orders: event.orders || [],
                        messageId: event.message_id || null,
                        blocked: Boolean(event.blocked),
                    });
                    finishBot(bubbleEls, entry);
                    scrollToEnd();
                    playChime('info');
                    const h2 = loadHistory();
                    h2.push(entry);
                    saveHistory(h2);
                } else if (event.type === 'error') {
                    finished = true;
                    showError(event.error || 'Maaf, terjadi kesalahan. Coba lagi ya.');
                }
            });

            if (!finished) showError('Koneksi terputus sebelum jawaban selesai. Coba kirim ulang ya.');
        } catch {
            showError('Koneksi bermasalah. Periksa internetmu lalu coba lagi.');
        } finally {
            typing?.remove();
            busy = false;
            sendBtn.disabled = false;
            if (finePointer.matches) input.focus();
        }
    };

    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        send();
    });

    /* --- Enter kirim, Shift+Enter baris baru --- */
    input?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
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
    messages.querySelectorAll('[data-chatbot-suggest]').forEach((btn) => {
        btn.addEventListener('click', () => send(btn.dataset.chatbotSuggest));
    });
}

/* ---------- Riwayat & saran pencarian ---------- */
document.addEventListener('DOMContentLoaded', () => {
    const storeKey = 'search_history';
    const maxHistory = 6;

    const loadHistory = () => {
        try {
            const items = JSON.parse(localStorage.getItem(storeKey) || '[]');
            return Array.isArray(items) ? items.filter((item) => typeof item === 'string') : [];
        } catch {
            return [];
        }
    };

    const saveHistory = (items) => {
        try {
            localStorage.setItem(storeKey, JSON.stringify(items));
        } catch { /* mode privat / penuh */ }
    };

    document.querySelectorAll('[data-search-form]').forEach((form, formIndex) => {
        const input = form.querySelector('input[name="q"]');
        if (!input) return;

        const anchor = form.querySelector('[data-search-anchor]') || form;
        const panel = document.createElement('div');
        panel.id = `search-panel-${formIndex}`;
        panel.setAttribute('role', 'listbox');
        panel.setAttribute('aria-label', 'Saran pencarian');
        panel.hidden = true;
        panel.className = 'absolute inset-x-0 top-full z-50 mt-2 max-h-96 overflow-y-auto rounded-2xl border border-ink-100 bg-white py-2 text-sm shadow-lift';
        anchor.append(panel);
        input.setAttribute('aria-controls', panel.id);

        let controller = null;
        let timer = 0;
        let activeIndex = -1;

        const options = () => [...panel.querySelectorAll('[role="option"]')];

        const close = () => {
            panel.hidden = true;
            activeIndex = -1;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        };

        const open = () => {
            panel.hidden = panel.childElementCount === 0;
            input.setAttribute('aria-expanded', String(!panel.hidden));
        };

        const heading = (text, action) => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between px-4 pb-1 pt-2 text-xs text-ink-500';
            const label = document.createElement('span');
            label.textContent = text;
            row.append(label);
            if (action) row.append(action);
            panel.append(row);
        };

        const addOption = (href, text, image) => {
            const link = document.createElement('a');
            link.href = href;
            link.id = `${panel.id}-option-${options().length}`;
            link.setAttribute('role', 'option');
            link.className = 'flex items-center gap-3 px-4 py-2 text-ink-800 hover:bg-ink-50 aria-selected:bg-ink-50';
            if (image) {
                const img = document.createElement('img');
                img.src = image;
                img.alt = '';
                img.loading = 'lazy';
                img.className = 'h-9 w-9 shrink-0 rounded-lg bg-ink-50 object-contain p-0.5 mix-blend-multiply';
                link.append(img);
            }
            const label = document.createElement('span');
            label.className = 'min-w-0 truncate';
            label.textContent = text;
            link.append(label);
            panel.append(link);
        };

        const searchUrl = (term) => {
            const url = new URL(form.action);
            url.searchParams.set('q', term);
            return url.href;
        };

        const render = (term, suggestions = []) => {
            panel.replaceChildren();
            activeIndex = -1;
            input.removeAttribute('aria-activedescendant');

            const lower = term.toLowerCase();
            const history = loadHistory().filter((item) => item.toLowerCase().includes(lower));

            if (history.length > 0) {
                const clear = document.createElement('button');
                clear.type = 'button';
                clear.className = 'font-medium text-brand-600 hover:underline';
                clear.textContent = 'Hapus';
                clear.addEventListener('click', () => {
                    saveHistory([]);
                    render(input.value.trim(), suggestions);
                    input.focus();
                });
                heading('Pencarian terakhir', clear);
                history.forEach((item) => addOption(searchUrl(item), item));
            }

            if (suggestions.length > 0) {
                heading('Produk');
                suggestions.forEach((item) => addOption(item.url, item.name, item.image_url));
            }

            open();
        };

        const fetchSuggestions = (term) => {
            controller?.abort();
            if (term.length < 2) {
                render(term);
                return;
            }

            controller = new AbortController();
            const signal = controller.signal;
            fetch(`${form.dataset.suggestUrl}?q=${encodeURIComponent(term)}`, {
                headers: { Accept: 'application/json' },
                signal,
            })
                .then((res) => (res.ok ? res.json() : { suggestions: [] }))
                .then((data) => {
                    if (input.value.trim() === term) render(term, data.suggestions || []);
                })
                .catch(() => { /* permintaan dibatalkan atau koneksi gagal */ });
        };

        input.addEventListener('focus', () => render(input.value.trim()));
        input.addEventListener('input', () => {
            clearTimeout(timer);
            const term = input.value.trim();
            render(term);
            timer = setTimeout(() => fetchSuggestions(term), 200);
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                close();
                return;
            }
            if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') {
                if (e.key === 'Enter' && activeIndex >= 0) {
                    e.preventDefault();
                    options()[activeIndex]?.click();
                }
                return;
            }

            const items = options();
            if (panel.hidden || items.length === 0) return;
            e.preventDefault();
            items[activeIndex]?.setAttribute('aria-selected', 'false');
            activeIndex = (activeIndex + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
            items[activeIndex].setAttribute('aria-selected', 'true');
            items[activeIndex].scrollIntoView({ block: 'nearest' });
            input.setAttribute('aria-activedescendant', items[activeIndex].id);
        });

        // Klik di dalam panel jangan sampai menutup panel lebih dulu.
        panel.addEventListener('mousedown', (e) => e.preventDefault());
        input.addEventListener('blur', close);

        form.addEventListener('submit', () => {
            const term = input.value.trim();
            if (term === '') return;
            saveHistory([term, ...loadHistory().filter((item) => item.toLowerCase() !== term.toLowerCase())].slice(0, maxHistory));
        });
    });
});
