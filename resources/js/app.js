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
});
