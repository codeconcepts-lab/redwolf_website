// ==========================================
// Tailwind Config
// ==========================================
tailwind.config = {
    theme: {
        extend: {
            colors: {
                redwolf: {
                    DEFAULT: '#972f1e',
                    dark: '#5e160a',
                    gold: '#ebaf0b',
                    black: '#0a0a0a',
                    gray: '#171717',
                    red: '#972f1e',
                    lightred: '#DC2626',
                    darkgold: '#9a7702'
                }
            },
            fontFamily: {
                sans: ['Open Sans', 'sans-serif'],
                display: ['Montserrat', 'sans-serif'],
            },
            animation: {
                'fade-in-up': 'fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
            },
            keyframes: {
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                }
            }
        }
    }
}

// ==========================================
// DOMContentLoaded — all interactivity
// ==========================================
document.addEventListener('DOMContentLoaded', () => {

    // ----------------------------------------
    // Scroll Reveal via IntersectionObserver
    // ----------------------------------------
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale')
        .forEach(el => revealObserver.observe(el));

    // ----------------------------------------
    // Animated Number Counters
    // Looks for elements with data-counter="<number>" and data-suffix="<str>"
    // ----------------------------------------
    function animateCounter(el) {
        const raw = el.dataset.counter;
        const suffix = el.dataset.suffix || '';
        const num = parseFloat(raw);
        if (isNaN(num)) { el.textContent = raw + suffix; return; }

        const duration = 1400;
        const startTime = performance.now();

        function tick(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const ease = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(num * ease);
            el.textContent = value + suffix;
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.6 });

    document.querySelectorAll('[data-counter]').forEach(el => counterObserver.observe(el));

    // ----------------------------------------
    // Back to Top
    // ----------------------------------------
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('visible', window.scrollY > 500);
        }, { passive: true });

        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ----------------------------------------
    // Mobile Sticky CTA Bar
    // ----------------------------------------
    const mobileCta = document.getElementById('mobile-cta-bar');
    if (mobileCta) {
        window.addEventListener('scroll', () => {
            mobileCta.classList.toggle('visible', window.scrollY > 350);
        }, { passive: true });
    }

    // ----------------------------------------
    // Nav Scroll Shadow
    // ----------------------------------------
    const nav = document.querySelector('nav');
    if (nav) {
        window.addEventListener('scroll', () => {
            nav.classList.toggle('nav-scrolled', window.scrollY > 10);
        }, { passive: true });
    }

    // ----------------------------------------
    // Mobile Menu: toggle, outside click, link click
    // ----------------------------------------
    const mobileMenu = document.getElementById('mobile-menu');
    const menuToggle = document.getElementById('mobile-menu-toggle');

    if (menuToggle && mobileMenu) {
        // Toggle on button click
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });

        // Close on any nav link click
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => mobileMenu.classList.add('hidden'));
        });
    }

});
