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

    /* ---------- Chatbot konsumen ---------- */
    initChatbot();
});

function initChatbot() {
    const root = document.querySelector('[data-chatbot]');
    if (!root) return;

    const panel = root.querySelector('[data-chatbot-panel]');
    const toggle = root.querySelector('[data-chatbot-toggle]');
    const closeBtn = root.querySelector('[data-chatbot-close]');
    const form = root.querySelector('[data-chatbot-form]');
    const input = root.querySelector('[data-chatbot-input]');
    const sendBtn = root.querySelector('[data-chatbot-send]');
    const messages = root.querySelector('[data-chatbot-messages]');
    const suggestions = root.querySelector('[data-chatbot-suggestions]');
    const iconOpen = root.querySelector('[data-chatbot-icon-open]');
    const iconClose = root.querySelector('[data-chatbot-icon-close]');

    const endpoint = root.dataset.chatbotUrl || '/chatbot';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const storeKey = 'chatbot_history_v1';

    let busy = false;

    /* --- Buka / tutup panel --- */
    const openPanel = () => {
        panel.classList.remove('pointer-events-none', 'opacity-0', 'translate-y-4', 'translate-y-2');
        panel.classList.add('pointer-events-auto', 'opacity-100', 'translate-y-0');
        iconOpen?.classList.add('hidden');
        iconClose?.classList.remove('hidden');
        toggle?.setAttribute('aria-label', 'Tutup asisten chat');
        input?.focus();
    };
    const closePanel = () => {
        panel.classList.add('pointer-events-none', 'opacity-0', 'translate-y-4', 'translate-y-2');
        panel.classList.remove('pointer-events-auto', 'opacity-100', 'translate-y-0');
        iconOpen?.classList.remove('hidden');
        iconClose?.classList.add('hidden');
        toggle?.setAttribute('aria-label', 'Buka asisten chat');
    };
    const isOpen = () => panel.classList.contains('opacity-100');

    toggle?.addEventListener('click', () => (isOpen() ? closePanel() : openPanel()));
    closeBtn?.addEventListener('click', closePanel);
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

    const formatText = (text) =>
        escapeHtml(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

    const bubble = (text, who) => {
        const wrap = document.createElement('div');
        wrap.className = 'flex gap-2';

        if (who === 'user') {
            wrap.className += ' justify-end';
            wrap.innerHTML = `<div class="max-w-[85%] rounded-2xl rounded-tr-sm bg-brand-600 px-3 py-2 text-white shadow-sm">${formatText(text)}</div>`;
        } else {
            wrap.innerHTML =
                `<span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-100 text-brand-700"><svg class="h-4 w-4"><use href="#i-headset"/></svg></span>` +
                `<div class="max-w-[85%] rounded-2xl rounded-tl-sm border border-ink-100 bg-white px-3 py-2 text-ink-700 shadow-sm">${formatText(text)}</div>`;
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
                body: JSON.stringify({ question: text }),
            });

            const data = await res.json().catch(() => ({}));

            loader.remove();

            if (!res.ok || data.error) {
                bubble(data.error || 'Maaf, terjadi kesalahan. Coba lagi ya.', 'bot');
                return;
            }

            bubble(data.answer, 'bot');
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
