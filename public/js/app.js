document.addEventListener('DOMContentLoaded', () => {
    // 1. Interactive 3D Card Tilt on Hover
    const tiltCards = document.querySelectorAll('.product-card, .stat-card, .service-strip > div');
    const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    if (!isTouch) {
        tiltCards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -10;
                const rotateY = ((x - centerX) / centerX) * 10;

                card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-8px)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    }

    // 2. Scroll Reveal IntersectionObserver
    const reveals = document.querySelectorAll('.reveal, .product-card, .home-categories a, .service-strip > div');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        });

        reveals.forEach(el => {
            el.classList.add('reveal');
            observer.observe(el);
        });
    }

    // 3. Hero Mouse Parallax
    const hero = document.querySelector('.storefront-hero');
    const heroProduct = document.querySelector('.hero-product');
    if (hero && heroProduct && !isTouch) {
        hero.addEventListener('mousemove', (e) => {
            const rect = hero.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            heroProduct.style.transform = `perspective(1400px) rotateY(${(-6 + x * 14).toFixed(2)}deg) rotateX(${(2 - y * 14).toFixed(2)}deg) translateZ(10px)`;
        });

        hero.addEventListener('mouseleave', () => {
            heroProduct.style.transform = '';
        });
    }

    // 4. Banner carousel
    const carousel = document.querySelector('.banner-carousel');
    if (carousel) {
        const slides = Array.from(carousel.querySelectorAll('.banner-slide'));
        const dots = Array.from(carousel.querySelectorAll('.banner-dots button'));
        const prev = carousel.querySelector('.banner-prev');
        const next = carousel.querySelector('.banner-next');
        let current = 0;
        let timer = null;

        const show = (index) => {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => slide.classList.toggle('is-active', i === current));
            dots.forEach((dot, i) => dot.classList.toggle('is-active', i === current));
        };

        const restart = () => {
            clearInterval(timer);
            timer = setInterval(() => show(current + 1), 5000);
        };

        prev.addEventListener('click', () => { show(current - 1); restart(); });
        next.addEventListener('click', () => { show(current + 1); restart(); });
        dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); restart(); }));
        carousel.addEventListener('mouseenter', () => clearInterval(timer));
        carousel.addEventListener('mouseleave', restart);

        restart();
    }

    // 5. Hot deal countdown
    const countdown = document.querySelector('.countdown');
    if (countdown) {
        const deadline = new Date(countdown.dataset.deadline).getTime();
        const units = ['days', 'hours', 'minutes', 'seconds'].map((unit) => countdown.querySelector(`[data-unit="${unit}"]`));

        const tick = () => {
            const remaining = Math.max(0, deadline - Date.now());
            const totalSeconds = Math.floor(remaining / 1000);
            units[0].textContent = String(Math.floor(totalSeconds / 86400)).padStart(2, '0');
            units[1].textContent = String(Math.floor(totalSeconds / 3600) % 24).padStart(2, '0');
            units[2].textContent = String(Math.floor(totalSeconds / 60) % 60).padStart(2, '0');
            units[3].textContent = String(totalSeconds % 60).padStart(2, '0');
        };

        tick();
        setInterval(tick, 1000);
    }
});
