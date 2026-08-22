/**
 * Yamato Welding Industries — front-end interactions
 * Vanilla JS only. No frameworks, no build step.
 */
(function () {
    'use strict';

    /* --------------------------------------------------------------
     * Sticky header: transparent on hero, solid on scroll
     * ------------------------------------------------------------ */
    var header = document.querySelector('.site-header');
    function updateHeader() {
        if (!header) return;
        if (window.scrollY > 40) {
            header.classList.add('is-solid');
        } else {
            header.classList.remove('is-solid');
        }
    }
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    /* --------------------------------------------------------------
     * Mobile hamburger menu
     * ------------------------------------------------------------ */
    var hamburger = document.querySelector('.hamburger');
    var mobileNav = document.querySelector('.mobile-nav');

    if (hamburger && mobileNav) {
        hamburger.addEventListener('click', function () {
            var isOpen = mobileNav.classList.toggle('is-open');
            hamburger.classList.toggle('is-open', isOpen);
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            document.body.style.overflow = isOpen ? 'hidden' : '';
        });

        mobileNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                mobileNav.classList.remove('is-open');
                hamburger.classList.remove('is-open');
                document.body.style.overflow = '';
            });
        });
    }

    /* --------------------------------------------------------------
     * Scroll reveal (fade up) via IntersectionObserver
     * ------------------------------------------------------------ */
    var revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealEls.length) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

        revealEls.forEach(function (el) { revealObserver.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    }

    /* --------------------------------------------------------------
     * Number counter animation for stats section
     * ------------------------------------------------------------ */
    var counters = document.querySelectorAll('[data-counter]');
    if ('IntersectionObserver' in window && counters.length) {
        var counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            });
        }, { threshold: 0.4 });

        counters.forEach(function (el) { counterObserver.observe(el); });
    }

    function animateCounter(el) {
        var raw = el.getAttribute('data-counter') || '';
        var match = raw.match(/^(\d+)(.*)$/);
        if (!match) { return; }

        var target = parseInt(match[1], 10);
        var suffix = match[2] || '';
        var duration = 1400;
        var start = null;

        function step(timestamp) {
            if (!start) start = timestamp;
            var progress = Math.min((timestamp - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var current = Math.round(eased * target);
            el.textContent = current + suffix;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                el.textContent = target + suffix;
            }
        }
        window.requestAnimationFrame(step);
    }

    /* --------------------------------------------------------------
     * Smooth in-page anchor scrolling (offset for fixed header)
     * ------------------------------------------------------------ */
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var id = this.getAttribute('href');
            if (id.length < 2) return;
            var target = document.querySelector(id);
            if (!target) return;
            e.preventDefault();
            var headerH = header ? header.offsetHeight : 0;
            var top = target.getBoundingClientRect().top + window.scrollY - headerH - 20;
            window.scrollTo({ top: top, behavior: 'smooth' });
        });
    });

    /* --------------------------------------------------------------
     * Basic client-side contact form validation (server re-validates)
     * ------------------------------------------------------------ */
    var contactForm = document.querySelector('#contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            var requiredFields = contactForm.querySelectorAll('[required]');
            var valid = true;
            requiredFields.forEach(function (field) {
                field.classList.remove('is-invalid');
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('is-invalid');
                }
            });
            if (!valid) {
                e.preventDefault();
            }
        });
    }
})();
