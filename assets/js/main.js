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
     * Hero slideshow: automatic continuous crossfade every 5 seconds.
     * ------------------------------------------------------------ */
    var heroSlider = document.querySelector('[data-hero-slider]');
    if (heroSlider) {
        var heroSlides = Array.prototype.slice.call(heroSlider.querySelectorAll('.hero-slide'));
        var heroIndex = 0;
        var heroTimer = null;
        var heroInterval = parseInt(heroSlider.getAttribute('data-interval'), 10) || 5000;
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function ensureHeroSlideLoaded(index) {
            if (!heroSlides.length) return;
            var normalizedIndex = (index + heroSlides.length) % heroSlides.length;
            var slide = heroSlides[normalizedIndex];
            var background = slide.getAttribute('data-hero-bg');
            if (background) {
                slide.style.backgroundImage = 'url("' + background.replace(/"/g, '%22') + '")';
                slide.removeAttribute('data-hero-bg');
            }
        }

        function showHeroSlide(nextIndex) {
            if (!heroSlides.length) return;
            heroIndex = (nextIndex + heroSlides.length) % heroSlides.length;
            ensureHeroSlideLoaded(heroIndex);
            ensureHeroSlideLoaded(heroIndex + 1);
            heroSlides.forEach(function (slide, index) {
                slide.classList.toggle('is-active', index === heroIndex);
            });
        }

        function stopHeroAutoplay() {
            if (heroTimer) window.clearInterval(heroTimer);
            heroTimer = null;
        }

        function startHeroAutoplay() {
            stopHeroAutoplay();
            if (reduceMotion || heroSlides.length < 2 || document.hidden) return;
            heroTimer = window.setInterval(function () {
                showHeroSlide(heroIndex + 1);
            }, heroInterval);
        }

        document.addEventListener('visibilitychange', startHeroAutoplay);
        showHeroSlide(0);
        startHeroAutoplay();
    }

    /* --------------------------------------------------------------
     * Lightweight parallax: one requestAnimationFrame update per scroll frame.
     * ------------------------------------------------------------ */
    var parallaxEls = Array.prototype.slice.call(document.querySelectorAll('.hero, .page-header, .technology-page-hero, .section--content-bg'));
    var parallaxReduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (parallaxEls.length && !parallaxReduceMotion) {
        var parallaxTicking = false;

        function updateParallax() {
            var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            parallaxEls.forEach(function (element) {
                var rect = element.getBoundingClientRect();
                if (rect.bottom < -120 || rect.top > viewportHeight + 120) return;
                var distanceFromCenter = (rect.top + rect.height / 2) - viewportHeight / 2;
                var maxOffset = element.classList.contains('section--content-bg') ? 28 : 52;
                var offset = Math.max(-maxOffset, Math.min(maxOffset, distanceFromCenter * -0.11));
                element.style.setProperty('--parallax-y', offset.toFixed(1) + 'px');
                element.style.setProperty('--parallax-content-y', (offset * -0.26).toFixed(1) + 'px');
            });
            parallaxTicking = false;
        }

        function requestParallaxUpdate() {
            if (parallaxTicking) return;
            parallaxTicking = true;
            window.requestAnimationFrame(updateParallax);
        }

        updateParallax();
        window.addEventListener('scroll', requestParallaxUpdate, { passive: true });
        window.addEventListener('resize', requestParallaxUpdate, { passive: true });
    }

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
     * Technology accordion: each row reveals its complete detail
     * ------------------------------------------------------------ */
    document.querySelectorAll('[data-tech-accordion]').forEach(function (accordion) {
        var toggles = Array.prototype.slice.call(accordion.querySelectorAll('[data-tech-toggle]'));

        function setTechItem(toggle, shouldOpen) {
            var item = toggle.closest('.tech-item');
            var panelId = toggle.getAttribute('aria-controls');
            var panel = panelId ? document.getElementById(panelId) : null;
            if (!item || !panel) return;
            item.classList.toggle('is-open', shouldOpen);
            toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            panel.hidden = !shouldOpen;
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                var shouldOpen = toggle.getAttribute('aria-expanded') !== 'true';
                toggles.forEach(function (otherToggle) {
                    setTechItem(otherToggle, otherToggle === toggle && shouldOpen);
                });
            });
        });

        if (/^#technology-\d+$/.test(window.location.hash)) {
            var linkedItem = accordion.querySelector(window.location.hash);
            var linkedToggle = linkedItem && linkedItem.querySelector('[data-tech-toggle]');
            if (linkedToggle) setTechItem(linkedToggle, true);
        }
    });

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
        }, { threshold: 0.05, rootMargin: '0px 0px -30px 0px' });

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
        var inquiryType = contactForm.querySelector('[name="inquiry_type"]');
        var orderFields = contactForm.querySelector('[data-order-fields]');
        var orderRequiredFields = orderFields ? orderFields.querySelectorAll('[data-order-required]') : [];

        function syncOrderFields() {
            if (!inquiryType || !orderFields) return;
            var showDetails = ['product', 'quote', 'order'].indexOf(inquiryType.value) !== -1;
            var isOrder = inquiryType.value === 'order';
            orderFields.hidden = !showDetails;
            orderFields.classList.toggle('is-visible', showDetails);
            orderFields.classList.toggle('is-order', isOrder);
            orderRequiredFields.forEach(function (field) {
                field.required = isOrder;
                if (!isOrder) field.classList.remove('is-invalid');
            });
        }

        if (inquiryType) inquiryType.addEventListener('change', syncOrderFields);
        syncOrderFields();

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
