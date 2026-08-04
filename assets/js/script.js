

document.addEventListener('DOMContentLoaded', () => {
    // thanks page: redirect to home after 20 seconds
    (function() {
        var path = (window.location.pathname || '').replace(/\/+$/, '');
        if (path !== '/thanks') return;
        window.setTimeout(function() {
            window.location.href = '/';
        }, 20000);
    })();

    // Внешние ссылки → target="_blank" + rel="noopener noreferrer"
    (function() {
        var host = window.location.hostname;

        document.querySelectorAll('a[href]').forEach(function(link) {
            var href = link.getAttribute('href');
            if (!href) return;

            var trimmed = href.trim();
            if (
                trimmed === '' ||
                trimmed.charAt(0) === '#' ||
                trimmed.indexOf('mailto:') === 0 ||
                trimmed.indexOf('tel:') === 0 ||
                trimmed.indexOf('javascript:') === 0
            ) {
                return;
            }

            var url;
            try {
                url = new URL(trimmed, window.location.href);
            } catch (e) {
                return;
            }

            if (url.protocol !== 'http:' && url.protocol !== 'https:') return;
            if (url.hostname === host) return;

            link.setAttribute('target', '_blank');

            var rel = (link.getAttribute('rel') || '').split(/\s+/).filter(Boolean);
            ['noopener', 'noreferrer'].forEach(function(token) {
                if (rel.indexOf(token) === -1) {
                    rel.push(token);
                }
            });
            link.setAttribute('rel', rel.join(' '));
        });
    })();

    // services panel (marketing markup)
    (function () {
        var trigger = document.querySelector('.header__bottom-service[aria-controls="header-services-panel"]');
        var panel = document.getElementById('header-services-panel');
        if (!trigger || !panel) return;

        var tabs = panel.querySelectorAll('.header-services__tab');
        var tabPanels = panel.querySelectorAll('.header-services__panel');

        function closePanel() {
            trigger.setAttribute('aria-expanded', 'false');
            trigger.classList.remove('is-active');
            var header = trigger.closest('.header');
            if (header) header.classList.remove('is-services-open');
            panel.hidden = true;
        }

        function openPanel() {
            trigger.setAttribute('aria-expanded', 'true');
            trigger.classList.add('is-active');
            var header = trigger.closest('.header');
            if (header) header.classList.add('is-services-open');
            panel.hidden = false;
        }

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            if (trigger.getAttribute('aria-expanded') === 'true') {
                closePanel();
            } else {
                openPanel();
            }
        });

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var tabIndex = tab.getAttribute('data-tab-index');
                tabs.forEach(function (item) {
                    var isActive = item === tab;
                    item.classList.toggle('is-active', isActive);
                    item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });
                tabPanels.forEach(function (tabPanel) {
                    var isActive = tabPanel.getAttribute('data-tab-index') === tabIndex;
                    tabPanel.classList.toggle('is-active', isActive);
                    tabPanel.hidden = !isActive;
                });
            });
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.header__bottom-service') && !event.target.closest('#header-services-panel')) {
                closePanel();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closePanel();
        });
    })();
    // services panel end


    // mobile menu (marketing: .header.active + body.lock)
    (function () {
        var burger = document.querySelector('.header__burger');
        var header = document.querySelector('.header');
        var mobileClose = document.querySelector('.header__mobile-close');
        if (!burger || !header) return;

        function openMobile() {
            header.classList.add('active');
            document.body.classList.add('lock');
        }
        function closeMobile() {
            header.classList.remove('active');
            document.body.classList.remove('lock');
        }
        function toggleMobile(e) {
            e.preventDefault();
            e.stopPropagation();
            if (header.classList.contains('active')) closeMobile();
            else openMobile();
        }

        burger.addEventListener('click', toggleMobile);
        if (mobileClose) {
            mobileClose.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeMobile();
            });
        }

        document.addEventListener('click', function (e) {
            if (!header.classList.contains('active')) return;
            if (e.target.closest('.header__burger')) return;
            if (e.target.closest('.header__mobile-inner')) return;
            if (e.target.closest('.header__mobile-close')) return;
            closeMobile();
        });
    })();
    // mobile menu end


    // search
    (function () {
        var searchButton = document.querySelector('.js-header-search');
        var closeSearchButton = document.querySelector('.js-header-search-close');
        if (!searchButton) return;

        function submitSearch(e) {
            var form = e.currentTarget.nextElementSibling;
            if (form) form.submit();
        }
        function showSearch(e) {
            var form = e.currentTarget.nextElementSibling;
            if (!form) return;
            form.classList.toggle('active');
            var input = form.querySelector('input');
            if (input) input.focus();
            searchButton.removeEventListener('click', showSearch);
            searchButton.addEventListener('click', submitSearch);
        }
        searchButton.addEventListener('click', showSearch);
        if (closeSearchButton) {
            closeSearchButton.addEventListener('click', function (e) {
                var form = e.target.closest('form');
                if (!form) return;
                form.classList.remove('active');
                form.reset();
                searchButton.removeEventListener('click', submitSearch);
                searchButton.addEventListener('click', showSearch);
            });
        }
    })();
    // search end
    
    // После успешной отправки CF7 — редирект на страницу «Спасибо» (без устаревшего on_sent_ok)
    document.addEventListener('wpcf7mailsent', function(ev) {
        if (!ev.detail || !ev.detail.contactFormId) return;
        var form = ev.target && ev.target.tagName === 'FORM' ? ev.target : document.querySelector('#' + (ev.detail.unitTag || '') + ' form');
        if (!form) return;
        // Модалка заявки: экран «спасибо» внутри .modal
        if (form.closest && form.closest('.modal')) {
            var modalEl = form.closest('.modal');
            modalEl.classList.add('active', 'success');
            document.body.classList.add('lock');
            return;
        }
        var container = form.closest && form.closest('[data-thanks-url]');
        if (!container) return;
        var thanksUrl = container.getAttribute('data-thanks-url');
        if (!thanksUrl) return;
        window.location.href = thanksUrl;
    }, false);

    // Gallery's
    Fancybox.bind('[data-fancybox]', {
        hideScrollbar: false,
    });

    // Модалка заявки (как в marketing)
    (function() {
        var modal = document.querySelector('.modal');
        if (!modal) return;

        function openModal(e) {
            if (e) {
                e.preventDefault();
            }
            modal.classList.add('active');
            modal.classList.remove('success');
            document.body.classList.add('lock');
        }

        function closeModal() {
            modal.classList.remove('active', 'success');
            document.body.classList.remove('lock');
        }

        function isModalTrigger(el) {
            if (!el || !el.closest) return null;
            return el.closest('a[href="#modal"], a[href="#modalUp"], .footer__audit, .js-open-modal');
        }

        document.addEventListener('click', function(e) {
            var trigger = isModalTrigger(e.target);
            if (trigger) {
                openModal(e);
                return;
            }
            if (e.target === modal) {
                closeModal();
            }
        });

        var closeBtn = modal.querySelector('.modal__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal();
            });
            closeBtn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    closeModal();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });
    })();


    // reviews: табы на CSS :has(), Swiper по типам + lightbox видео
    (function() {
        if (typeof Swiper === 'undefined') return;

        function syncReviewsOverflow(root, swiper) {
            if (!root || !swiper) return;
            root.classList.toggle('is-overflow', !swiper.isLocked);
        }

        function mountReviewsSwiper(root, options) {
            if (!root) return null;
            var el = root.querySelector('.splide__track.swiper');
            if (!el || !el.querySelector('.swiper-slide')) return null;
            if (el.swiper && typeof el.swiper.destroy === 'function') {
                el.swiper.destroy(true, true);
            }

            var base = {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 20,
                watchOverflow: true,
                observer: true,
                observeParents: true,
                updateOnWindowResize: true,
                pagination: {
                    el: root.querySelector('.splide__pagination'),
                    clickable: true,
                    enabled: true
                },
                navigation: {
                    nextEl: root.querySelector('.splide__arrow--next'),
                    prevEl: root.querySelector('.splide__arrow--prev'),
                    enabled: false
                },
                on: {
                    init: function() { syncReviewsOverflow(root, this); },
                    resize: function() { syncReviewsOverflow(root, this); },
                    update: function() { syncReviewsOverflow(root, this); },
                    breakpoint: function() { syncReviewsOverflow(root, this); },
                    lock: function() { syncReviewsOverflow(root, this); },
                    unlock: function() { syncReviewsOverflow(root, this); }
                }
            };

            var cfg = Object.assign({}, base, options || {});
            if (options && options.breakpoints) {
                cfg.breakpoints = Object.assign({}, base.breakpoints || {}, options.breakpoints);
            }
            return new Swiper(el, cfg);
        }

        var configs = [
            {
                sel: '.reviews__splide--thenks',
                opts: {
                    spaceBetween: 20,
                    breakpoints: {
                        992: {
                            slidesPerView: 5,
                            spaceBetween: 20,
                            pagination: { enabled: false },
                            navigation: { enabled: true }
                        }
                    }
                }
            },
            {
                sel: '.reviews__splide--video',
                opts: {
                    spaceBetween: 30,
                    breakpoints: {
                        992: {
                            slidesPerView: 3,
                            spaceBetween: 30,
                            pagination: { enabled: false },
                            navigation: { enabled: true }
                        }
                    }
                }
            },
            {
                sel: '.reviews__splide--text',
                opts: {
                    spaceBetween: 30,
                    breakpoints: {
                        992: {
                            slidesPerView: 2,
                            spaceBetween: 30,
                            pagination: { enabled: false },
                            navigation: { enabled: true }
                        }
                    }
                }
            },
            {
                sel: '.reviews__splide--messengers',
                opts: {
                    spaceBetween: 30,
                    breakpoints: {
                        992: {
                            slidesPerView: 4,
                            spaceBetween: 30,
                            pagination: { enabled: false },
                            navigation: { enabled: true }
                        }
                    }
                }
            }
        ];

        var instances = [];
        configs.forEach(function(item) {
            document.querySelectorAll(item.sel).forEach(function(root) {
                var swiper = mountReviewsSwiper(root, item.opts);
                if (swiper) instances.push(swiper);
            });
        });

        document.querySelectorAll('.reviews__label input[name="reviews"]').forEach(function(input) {
            input.addEventListener('change', function() {
                instances.forEach(function(swiper) {
                    if (swiper && typeof swiper.update === 'function') {
                        swiper.update();
                        syncReviewsOverflow(swiper.el && swiper.el.closest ? swiper.el.closest('.reviews__splide') : null, swiper);
                    }
                });
            });
        });
    })();

    // reviews video lightbox
    (function() {
        var triggers = document.querySelectorAll('.reviews__list-preview--embed[data-video-src]');
        if (!triggers.length) return;

        function logPosterIssue(reason, detail) {
            if (window.console && typeof console.warn === 'function') {
                console.warn('Tolstenko reviews poster: ' + reason, detail || '');
            }
        }

        function loadPosterFromServer(poster, videoSrc) {
            if (typeof tolstenkoAjax === 'undefined' || !tolstenkoAjax.ajaxUrl) {
                logPosterIssue('ajaxUrl недоступен', videoSrc);
                return;
            }
            fetch(tolstenkoAjax.ajaxUrl + '?action=tolstenko_video_poster&src=' + encodeURIComponent(videoSrc))
                .then(function(response) {
                    return response.json().then(function(payload) {
                        if (!response.ok || !payload || !payload.success) {
                            var message = payload && payload.data && payload.data.message
                                ? payload.data.message
                                : 'HTTP ' + response.status;
                            throw new Error(message);
                        }
                        return payload.data;
                    });
                })
                .then(function(data) {
                    if (data && data.poster) {
                        poster.src = data.poster;
                    } else {
                        logPosterIssue('сервер вернул пустой постер', videoSrc);
                    }
                })
                .catch(function(error) {
                    logPosterIssue(error && error.message ? error.message : 'запрос не удался', videoSrc);
                });
        }

        function loadPoster(trigger) {
            var poster = trigger.querySelector('.reviews__list-poster');
            if (!poster || poster.getAttribute('src')) return;

            var videoSrc = trigger.getAttribute('data-video-src');
            var rutubeId = trigger.getAttribute('data-rutube-id');
            if (!videoSrc) return;

            if (rutubeId) {
                fetch('https://rutube.ru/api/video/' + encodeURIComponent(rutubeId) + '/')
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Rutube API HTTP ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function(data) {
                        if (data && data.thumbnail_url) {
                            poster.src = data.thumbnail_url;
                            return;
                        }
                        throw new Error('в ответе Rutube API нет thumbnail_url');
                    })
                    .catch(function(error) {
                        // Прямой запрос к Rutube мог быть заблокирован — пробуем через сервер.
                        logPosterIssue(error && error.message ? error.message : 'Rutube API недоступен', videoSrc);
                        loadPosterFromServer(poster, videoSrc);
                    });
                return;
            }

            loadPosterFromServer(poster, videoSrc);
        }

        triggers.forEach(loadPoster);

        var modal = document.querySelector('.reviews-video-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'reviews-video-modal';
            modal.innerHTML =
                '<button class="reviews-video-modal__overlay" type="button" aria-label="Закрыть видео"></button>' +
                '<div class="reviews-video-modal__dialog" role="dialog" aria-modal="true" aria-label="Видео отзыв">' +
                '<button class="reviews-video-modal__close" type="button" aria-label="Закрыть">' +
                '<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>' +
                '</button><div class="reviews-video-modal__content"></div></div>';
            document.body.appendChild(modal);
        }

        var content = modal.querySelector('.reviews-video-modal__content');
        var overlay = modal.querySelector('.reviews-video-modal__overlay');
        var closeBtn = modal.querySelector('.reviews-video-modal__close');

        function getAutoplaySrc(src) {
            try {
                var url = new URL(src, window.location.origin);
                url.searchParams.set('autoplay', '1');
                return url.toString();
            } catch (e) {
                return src;
            }
        }

        function closeModal() {
            modal.classList.remove('is-active');
            document.body.classList.remove('lock');
            content.innerHTML = '';
        }

        function openModal(src) {
            var iframe = document.createElement('iframe');
            iframe.src = getAutoplaySrc(src);
            iframe.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture');
            iframe.setAttribute('allowfullscreen', '');
            iframe.setAttribute('title', 'Видео отзыв');
            content.innerHTML = '';
            content.appendChild(iframe);
            modal.classList.add('is-active');
            document.body.classList.add('lock');
        }

        triggers.forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                var src = trigger.getAttribute('data-video-src');
                if (src) openModal(src);
            });
        });

        if (overlay) overlay.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.classList.contains('is-active')) {
                closeModal();
            }
        });
    })();
    // reviews end

    // contacts
    var promoClose = document.querySelector('.promo-notice-close');
    if (promoClose) {
        promoClose.addEventListener('click', function(e) {
            var promo = e.target.closest('.promo-notice');
            if (promo) {
                var cookieKey = promo.getAttribute('data-promo-cookie-key') || 'tolstenko_promo_notice_closed_until';
                var expiresAt = new Date(Date.now() + 24 * 60 * 60 * 1000);
                var unixUntil = Math.floor(expiresAt.getTime() / 1000);
                document.cookie = cookieKey + '=' + unixUntil + '; expires=' + expiresAt.toUTCString() + '; path=/; SameSite=Lax';
                promo.remove();
            }
        });
    }
    // contacts end

    // guide banner (под шапкой, desktop)
    (function() {
        var banner = document.querySelector('.guide-banner');
        if (!banner) return;

        var STORAGE_KEY = 'theme_guide_banner_dismissed_at';
        var SHOW_DELAY = 10000;
        var DISMISS_DURATION = 24 * 60 * 60 * 1000;

        function isDismissed() {
            var dismissedAt = localStorage.getItem(STORAGE_KEY);
            if (!dismissedAt) return false;
            var timestamp = parseInt(dismissedAt, 10);
            if (Number.isNaN(timestamp)) {
                localStorage.removeItem(STORAGE_KEY);
                return false;
            }
            return Date.now() - timestamp < DISMISS_DURATION;
        }

        function updateBannerHeight() {
            if (banner.classList.contains('is-visible')) {
                document.documentElement.style.setProperty('--guide-banner-height', banner.offsetHeight + 'px');
            }
        }

        function hideBanner() {
            banner.classList.remove('is-visible');
            banner.setAttribute('aria-hidden', 'true');
            banner.hidden = true;
            document.body.classList.remove('guide-banner-visible');
            document.documentElement.style.removeProperty('--guide-banner-height');
        }

        function showBanner() {
            if (isDismissed() || banner.classList.contains('is-visible')) return;
            banner.hidden = false;
            banner.setAttribute('aria-hidden', 'false');
            banner.classList.add('is-visible');
            document.body.classList.add('guide-banner-visible');
            updateBannerHeight();
        }

        function dismissBanner() {
            localStorage.setItem(STORAGE_KEY, String(Date.now()));
            hideBanner();
        }

        var closeButton = banner.querySelector('.guide-banner__close');
        if (closeButton) {
            closeButton.addEventListener('click', dismissBanner);
        }

        window.addEventListener('resize', updateBannerHeight);

        if (!isDismissed()) {
            window.setTimeout(showBanner, SHOW_DELAY);
        }
    })();
    // guide banner end


    // article showmore: единая стабильная логика для обычной статьи и builder-контента
    function bindShowmoreButton(btn, text, showLabel, hideLabel) {
        if (!btn || !text || btn.dataset.bound === '1') return;
        btn.addEventListener('click', function() {
            var expanded = text.classList.toggle('active');
            btn.classList.toggle('active', expanded);
            btn.textContent = expanded ? hideLabel : showLabel;
        });
        btn.dataset.bound = '1';
    }

    function setupArticleShowmore() {
        document.querySelectorAll('.article').forEach(function(article) {
            var enabled = article.getAttribute('data-showmore-enabled') === '1';

            // Обычный тизер статьи
            var teaserText = article.querySelector('.article-teaser .article-teaser-text');
            var teaserBtn = article.querySelector('.article-teaser-showmore');
            if (teaserText) {
                if (enabled) {
                    teaserText.classList.add('article-teaser-text--collapsible');
                    if (teaserBtn) {
                        teaserBtn.style.display = '';
                        teaserBtn.textContent = teaserText.classList.contains('active') ? 'Скрыть' : 'Читать далее';
                        bindShowmoreButton(teaserBtn, teaserText, 'Читать далее', 'Скрыть');
                    }
                } else {
                    teaserText.classList.remove('article-teaser-text--collapsible', 'active');
                    if (teaserBtn) teaserBtn.style.display = 'none';
                }
            }

            // Обычный двухколоночный блок
            var colText = article.querySelector('.article-2col .article-2col-text');
            var colBtn = article.querySelector('.article-2col-showmore');
            if (colText) {
                if (enabled) {
                    colText.classList.add('article-2col-text--collapsible');
                    if (colBtn) {
                        colBtn.style.display = '';
                        colBtn.textContent = colText.classList.contains('active') ? 'Скрыть' : 'Читать далее';
                        bindShowmoreButton(colBtn, colText, 'Читать далее', 'Скрыть');
                    }
                } else {
                    colText.classList.remove('article-2col-text--collapsible', 'active');
                    if (colBtn) colBtn.style.display = 'none';
                }
            }

            // Builder 1-col
            article.querySelectorAll('.article-content-builder .article-text-1col').forEach(function(container) {
                var text = container.querySelector('.article-text-1col-text');
                if (!text) return;
                var btn = container.querySelector('.article-content-showmore');

                if (enabled) {
                    text.classList.add('article-content-collapsible');
                    if (!btn) {
                        btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'article-content-showmore';
                        btn.textContent = 'Показать ещё';
                        container.appendChild(btn);
                    }
                    btn.style.display = '';
                    btn.textContent = text.classList.contains('active') ? 'Скрыть' : 'Показать ещё';
                    bindShowmoreButton(btn, text, 'Показать ещё', 'Скрыть');
                } else {
                    text.classList.remove('article-content-collapsible', 'active');
                    if (btn) btn.remove();
                }
            });

            // Builder 2-col
            article.querySelectorAll('.article-content-builder .article-2col').forEach(function(container) {
                var text = container.querySelector('.article-2col-text');
                if (!text) return;
                var btn = container.querySelector('.article-2col-showmore');

                if (enabled) {
                    text.classList.add('article-2col-text--collapsible');
                    if (!btn) {
                        btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'article-2col-showmore article-2col-showmore-builder';
                        btn.textContent = 'Показать ещё';
                        container.appendChild(btn);
                    }
                    btn.style.display = '';
                    btn.textContent = text.classList.contains('active') ? 'Скрыть' : 'Показать ещё';
                    bindShowmoreButton(btn, text, 'Показать ещё', 'Скрыть');
                } else {
                    text.classList.remove('article-2col-text--collapsible', 'active');
                    if (btn) btn.remove();
                }
            });
        });
    }

    setupArticleShowmore();
    window.addEventListener('resize', setupArticleShowmore);
    // article end

    // CF7 acceptance links: клик по ссылке не должен переключать чекбокс
    document.querySelectorAll('.wpcf7-list-item-label a').forEach(function(link) {
        ['mousedown', 'mouseup', 'click'].forEach(function(evt) {
            link.addEventListener(evt, function(e) {
                e.stopPropagation();
            });
        });
    });


    // service end


    // Сертификаты — как Tolstenko certificatesSplideInit (1 / 5, стрелки с desk).
    // Клиенты — как clientsSplideInit (.clients__splide + .clients__smi-splide, 1 / 4).
    (function() {
        function mountThemeSplideSwiper(rootSelector, deskPerView) {
            document.querySelectorAll(rootSelector + ' .splide__track.swiper').forEach(function(el) {
                var root = el.closest(rootSelector);
                if (!root || !el.querySelector('.swiper-slide')) return;
                if (el.swiper) return;

                function syncOverflow(swiper) {
                    root.classList.toggle('is-overflow', !swiper.isLocked);
                }

                var instance = new Swiper(el, {
                    slidesPerView: 1,
                    slidesPerGroup: 1,
                    spaceBetween: 20,
                    watchOverflow: true,
                    pagination: {
                        el: root.querySelector('.splide__pagination'),
                        clickable: true,
                        enabled: true
                    },
                    navigation: {
                        nextEl: root.querySelector('.splide__arrow--next'),
                        prevEl: root.querySelector('.splide__arrow--prev'),
                        enabled: false
                    },
                    breakpoints: {
                        992: {
                            slidesPerView: deskPerView,
                            slidesPerGroup: 1,
                            spaceBetween: 20,
                            pagination: { enabled: false },
                            navigation: { enabled: true }
                        }
                    },
                    on: {
                        init: function () { syncOverflow(this); },
                        resize: function () { syncOverflow(this); },
                        update: function () { syncOverflow(this); },
                        lock: function () { root.classList.remove('is-overflow'); },
                        unlock: function () { root.classList.add('is-overflow'); }
                    }
                });
                syncOverflow(instance);
            });
        }

        mountThemeSplideSwiper('.certificates__splide', 5);
        mountThemeSplideSwiper('.clients__splide', 4);
        mountThemeSplideSwiper('.clients__smi-splide', 4);
    })();

    (function() {
        var mq = window.matchMedia('(max-width: 991.98px)');
        var instances = [];

        function syncHeight(root, swiper) {
            var track = root.querySelector('.splide__track');
            if (!track) return;
            if (!mq.matches || !swiper) {
                track.style.height = '';
                return;
            }
            var active = swiper.slides && swiper.slides[swiper.activeIndex];
            if (active) {
                track.style.height = active.offsetHeight + 'px';
            }
        }

        function destroyAll() {
            instances.forEach(function(item) {
                if (item.swiper && !item.swiper.destroyed) {
                    item.swiper.destroy(true, true);
                }
                if (item.track) {
                    item.track.style.height = '';
                }
            });
            instances = [];
        }

        function initMobile() {
            destroyAll();
            if (!mq.matches) return;

            document.querySelectorAll('.actions__splide .splide__track.swiper').forEach(function(el) {
                var root = el.closest('.actions__splide');
                if (!root || !el.querySelector('.swiper-slide')) return;

                var swiper = new Swiper(el, {
                    slidesPerView: 1,
                    slidesPerGroup: 1,
                    spaceBetween: 30,
                    watchOverflow: true,
                    pagination: {
                        el: root.querySelector('.splide__pagination'),
                        clickable: true
                    },
                    on: {
                        init: function () { syncHeight(root, this); },
                        slideChange: function () { syncHeight(root, this); },
                        slideChangeTransitionStart: function () { syncHeight(root, this); },
                        resize: function () { syncHeight(root, this); }
                    }
                });

                instances.push({ swiper: swiper, track: el, root: root });
                syncHeight(root, swiper);
            });
        }

        initMobile();

        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', initMobile);
        } else if (typeof mq.addListener === 'function') {
            mq.addListener(initMobile);
        }
    })();

    (function() {
        document.querySelectorAll('.partners__splide .splide__track.swiper').forEach(function(el) {
            var root = el.closest('.partners__splide');
            if (!root || !el.querySelector('.swiper-slide')) return;

            function syncOverflow(swiper) {
                root.classList.toggle('is-overflow', !swiper.isLocked);
            }

            var instance = new Swiper(el, {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 20,
                watchOverflow: true,
                pagination: {
                    el: root.querySelector('.splide__pagination'),
                    clickable: true,
                    enabled: true
                },
                navigation: {
                    nextEl: root.querySelector('.splide__arrow--next'),
                    prevEl: root.querySelector('.splide__arrow--prev'),
                    enabled: false
                },
                breakpoints: {
                    992: {
                        slidesPerView: 5,
                        slidesPerGroup: 1,
                        spaceBetween: 20,
                        pagination: { enabled: false },
                        navigation: { enabled: true }
                    }
                },
                on: {
                    init: function () { syncOverflow(this); },
                    resize: function () { syncOverflow(this); },
                    update: function () { syncOverflow(this); }
                }
            });
        });
    })();

    (function() {
        document.querySelectorAll('.team__splide .splide__track.swiper').forEach(function(el) {
            var root = el.closest('.team__splide');
            if (!root || !el.querySelector('.swiper-slide')) return;

            function syncOverflow(swiper) {
                root.classList.toggle('is-overflow', !swiper.isLocked);
            }

            var instance = new Swiper(el, {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 20,
                watchOverflow: true,
                pagination: {
                    el: root.querySelector('.splide__pagination'),
                    clickable: true,
                    enabled: true
                },
                navigation: {
                    nextEl: root.querySelector('.splide__arrow--next'),
                    prevEl: root.querySelector('.splide__arrow--prev'),
                    enabled: false
                },
                breakpoints: {
                    992: {
                        slidesPerView: 4,
                        slidesPerGroup: 1,
                        spaceBetween: 20,
                        pagination: { enabled: false },
                        navigation: { enabled: true }
                    }
                },
                on: {
                    init: function () { syncOverflow(this); },
                    resize: function () { syncOverflow(this); },
                    update: function () { syncOverflow(this); },
                    lock: function () { root.classList.remove('is-overflow'); },
                    unlock: function () { root.classList.add('is-overflow'); }
                }
            });
            syncOverflow(instance);
        });
    })();

    (function() {
        document.querySelectorAll('.same-vacancy__splide').forEach(function(el) {
            new Swiper(el, {
                slidesPerView: 1,
                spaceBetween: 20,
                watchOverflow: true,
                pagination: { el: el.querySelector('.splide__pagination'), clickable: true },
                navigation: {
                    nextEl: el.querySelector('.splide__arrow--next'),
                    prevEl: el.querySelector('.splide__arrow--prev')
                },
                breakpoints: {
                    992: { slidesPerView: 2, spaceBetween: 20 }
                }
            });
        });
    })();

    // Кейсы (.case-section) — .swiper на .splide__track (как у услуг) + remount после фильтра.
    function tolstenkoSyncSplideOverflowClass(root, swiper) {
        if (!root || !swiper) return;
        // Стили Splide: .splide:not(.is-overflow) .splide__bottom { display: none }
        root.classList.toggle('is-overflow', !swiper.isLocked);
    }

    function tolstenkoMountCaseSectionSwiper(target) {
        if (!target || typeof Swiper === 'undefined') return null;

        var root = target.closest('.case-section__splide') || target.parentElement;
        if (!root) return null;

        var el = target.classList.contains('swiper')
            ? target
            : root.querySelector('.splide__track.swiper');
        if (!el) return null;

        if (el.swiper && typeof el.swiper.destroy === 'function') {
            el.swiper.destroy(true, true);
        }
        if (!el.querySelector('.swiper-slide')) {
            root.classList.remove('is-overflow');
            return null;
        }

        var swiper = new Swiper(el, {
            slidesPerView: 1,
            slidesPerGroup: 1,
            spaceBetween: 20,
            watchOverflow: true,
            observer: true,
            observeParents: true,
            updateOnWindowResize: true,
            pagination: {
                el: root.querySelector('.splide__pagination'),
                clickable: true,
                enabled: true
            },
            navigation: {
                nextEl: root.querySelector('.splide__arrow--next'),
                prevEl: root.querySelector('.splide__arrow--prev'),
                enabled: false
            },
            breakpoints: {
                992: {
                    pagination: { enabled: false },
                    navigation: { enabled: true }
                }
            },
            on: {
                init: function () { tolstenkoSyncSplideOverflowClass(root, this); },
                resize: function () { tolstenkoSyncSplideOverflowClass(root, this); },
                update: function () { tolstenkoSyncSplideOverflowClass(root, this); },
                breakpoint: function () { tolstenkoSyncSplideOverflowClass(root, this); },
                lock: function () { tolstenkoSyncSplideOverflowClass(root, this); },
                unlock: function () { tolstenkoSyncSplideOverflowClass(root, this); }
            }
        });
        return swiper;
    }

    window.tolstenkoMountCaseSectionSwiper = tolstenkoMountCaseSectionSwiper;

    (function() {
        document.querySelectorAll('.case-section__splide .splide__track.swiper').forEach(function(el) {
            tolstenkoMountCaseSectionSwiper(el);
        });
    })();
    // Автор — как Tolstenko authorSplideInit / authorSpeechesSplideInit:
    // карусель только < 992 (1 слайд, gap 30, pagination, без стрелок).
    // Показатели: autoHeight ≈ sync высоты track под активный слайд.
    (function() {
        if (typeof Swiper === 'undefined') return;

        function syncAuthorOverflow(root, swiper) {
            if (!root || !swiper) return;
            var mobile = window.matchMedia('(max-width: 991.98px)').matches;
            root.classList.toggle('is-overflow', mobile && !swiper.isLocked);
        }

        function mountAuthorSwiper(el, rootSelector, withAutoHeight) {
            var root = el.closest(rootSelector);
            if (!root || !el.querySelector('.swiper-slide')) return;
            if (el.swiper) return;

            var pagEl = root.querySelector('.splide__pagination');
            new Swiper(el, {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 30,
                watchOverflow: true,
                autoHeight: !!withAutoHeight,
                pagination: pagEl ? { el: pagEl, clickable: true } : false,
                breakpoints: {
                    992: {
                        enabled: false,
                        autoHeight: false
                    }
                },
                on: {
                    init: function () { syncAuthorOverflow(root, this); },
                    resize: function () { syncAuthorOverflow(root, this); },
                    update: function () { syncAuthorOverflow(root, this); },
                    breakpoint: function () { syncAuthorOverflow(root, this); },
                    lock: function () { syncAuthorOverflow(root, this); },
                    unlock: function () { syncAuthorOverflow(root, this); }
                }
            });
        }

        document.querySelectorAll('.author__splide .splide__track.swiper').forEach(function(el) {
            mountAuthorSwiper(el, '.author__splide', true);
        });
        document.querySelectorAll('.author__speeches .splide__track.swiper').forEach(function(el) {
            mountAuthorSwiper(el, '.author__speeches', false);
        });
    })();

    // Решение (.solution) — два синхронизированных ряда
    (function() {
        if (typeof Swiper === 'undefined') return;
        document.querySelectorAll('.solution.section').forEach(function(section) {
            var first = section.querySelector('.solution__splide');
            var second = section.querySelector('.solution__splide-second');
            if (!first) return;

            function makeOpts(el, withPagination) {
                var pagEl = el.querySelector('.splide__pagination');
                return {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    watchOverflow: true,
                    pagination: withPagination && pagEl ? { el: pagEl, clickable: true } : false,
                    breakpoints: {
                        1024: {
                            slidesPerView: 'auto',
                            spaceBetween: 20
                        }
                    }
                };
            }

            var hasSecond = !!second;
            var s1 = new Swiper(first, makeOpts(first, !hasSecond));
            if (!second) return;

            var s2 = new Swiper(second, makeOpts(second, true));
            var syncing = false;
            s1.on('slideChange', function() {
                if (syncing) return;
                syncing = true;
                s2.slideTo(s1.activeIndex);
                syncing = false;
            });
            s2.on('slideChange', function() {
                if (syncing) return;
                syncing = true;
                s1.slideTo(s2.activeIndex);
                syncing = false;
            });
        });
    })();

    // Универсальный REST-фильтр секций ([data-tolstenko-filter]).
    (function() {
        var cfg = (typeof window !== 'undefined' && window.tolstenkoFilter) ? window.tolstenkoFilter : null;
        if (!cfg || !cfg.restUrl) return;

        function animateFadeIn(container) {
            if (!container) return;
            var fadeElements = container.querySelectorAll('.fade-in-element');
            fadeElements.forEach(function(el, index) {
                el.classList.remove('visible');
                window.setTimeout(function() {
                    el.classList.add('visible');
                }, index * 100);
            });
        }

        function buildUrl(section, term, page) {
            var url = new URL(cfg.restUrl, window.location.origin);
            url.searchParams.set('post_type', section.getAttribute('data-post-type') || '');
            url.searchParams.set('taxonomy', section.getAttribute('data-taxonomy') || '');
            url.searchParams.set('term', term || '');
            url.searchParams.set('posts_per_page', section.getAttribute('data-posts-per-page') || '-1');
            url.searchParams.set('card', section.getAttribute('data-card') || '');
            var postIds = section.getAttribute('data-post-ids') || '';
            if (postIds) {
                url.searchParams.set('post_ids', postIds);
            }
            if (section.getAttribute('data-tolstenko-paginate') === '1') {
                url.searchParams.set('paginate', '1');
                url.searchParams.set('paged', String(page || section.getAttribute('data-page') || 1));
            }
            return url.toString();
        }

        function getFilterContainer(section) {
            var container = section.querySelector('[data-tolstenko-filter-container]');
            if (!container) {
                var sectionId = section.getAttribute('data-section-id');
                container = sectionId ? document.getElementById(sectionId + '-container') : null;
            }
            return container;
        }

        function getActiveTerm(section) {
            var checked = section.querySelector('.tolstenko-filter-radio:checked');
            return checked ? (checked.value || '') : '';
        }

        function applyFilterResult(section, container, data) {
            container.innerHTML = (data && typeof data.html === 'string') ? data.html : '';
            animateFadeIn(container);

            var paginationWrap = section.querySelector('[data-tolstenko-pagination]');
            if (paginationWrap) {
                paginationWrap.innerHTML = (data && typeof data.pagination === 'string') ? data.pagination : '';
            }
            if (data && data.page) {
                section.setAttribute('data-page', String(data.page));
            }

            var caseRoot = container.closest('.case-section__splide');
            if (caseRoot && typeof window.tolstenkoMountCaseSectionSwiper === 'function') {
                window.tolstenkoMountCaseSectionSwiper(caseRoot);
            }
            var serviceRoot = container.closest('.service-section__splide');
            if (serviceRoot && typeof window.tolstenkoMountServiceSectionSwiper === 'function') {
                window.tolstenkoMountServiceSectionSwiper(serviceRoot);
            }
            var blogRoot = container.closest('.blog-section__splide');
            if (blogRoot && typeof window.tolstenkoMountServiceSectionSwiper === 'function') {
                window.tolstenkoMountServiceSectionSwiper(blogRoot);
            }

            if (section.getAttribute('data-tolstenko-layout') === 'tile') {
                var toggle = section.querySelector('.service-section__toggle');
                if (toggle) toggle.checked = false;
            }
        }

        function showFilterError(section, container) {
            var message = (cfg.errorText || 'Не удалось загрузить список. Попробуйте обновить страницу.');
            var existing = section.querySelector('[data-tolstenko-filter-error]');
            if (!existing) {
                existing = document.createElement('p');
                existing.setAttribute('data-tolstenko-filter-error', '');
                existing.setAttribute('role', 'alert');
                existing.className = 'tolstenko-filter-error';
                container.parentNode.insertBefore(existing, container);
            }
            existing.textContent = message;
            existing.hidden = false;
        }

        function hideFilterError(section) {
            var existing = section.querySelector('[data-tolstenko-filter-error]');
            if (existing) existing.hidden = true;
        }

        function loadFilter(section, term, page) {
            var container = getFilterContainer(section);
            if (!container) return;

            hideFilterError(section);
            container.classList.add('loading');
            fetch(buildUrl(section, term, page), {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': cfg.nonce || '',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(function(response) {
                    return response.json().then(function(payload) {
                        if (!response.ok) {
                            throw new Error(
                                payload && payload.message ? payload.message : 'HTTP ' + response.status
                            );
                        }
                        return payload;
                    });
                })
                .then(function(data) {
                    applyFilterResult(section, container, data);
                })
                .catch(function(error) {
                    console.error('Tolstenko filter error:', error);
                    showFilterError(section, container);
                })
                .finally(function() {
                    container.classList.remove('loading');
                });
        }

        document.querySelectorAll('[data-tolstenko-filter]').forEach(function(section) {
            var radios = section.querySelectorAll('.tolstenko-filter-radio');
            radios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (!this.checked) return;
                    section.setAttribute('data-page', '1');
                    loadFilter(section, this.value || '', 1);
                });
            });

            if (section.getAttribute('data-tolstenko-paginate') === '1') {
                section.addEventListener('click', function(e) {
                    var link = e.target && e.target.closest ? e.target.closest('[data-tolstenko-page]') : null;
                    if (!link || !section.contains(link)) return;
                    e.preventDefault();
                    var page = parseInt(link.getAttribute('data-tolstenko-page'), 10);
                    if (!page || page < 1) return;
                    loadFilter(section, getActiveTerm(section), page);
                    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
        });
    })();

    (function() {
        document.querySelectorAll('.accordion-top').forEach(function(element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                var item = this.closest('.accordion');
                var group = this.closest('.accordion-group');
                if (!item) return;
                if (item.classList.contains('active')) {
                    item.classList.remove('active');
                    return;
                }
                if (group) {
                    var current = group.querySelector('.accordion.active');
                    if (current && current !== item) {
                        current.classList.remove('active');
                    }
                }
                item.classList.add('active');
            });
        });
    })();

    // «Показать ещё» — раскрывает .more-content внутри секции.
    (function() {
        document.querySelectorAll('.more-btn').forEach(function(element) {
            element.addEventListener('click', function() {
                if (this.classList.contains('service-section__more')) return;
                var section = this.closest('.section');
                if (!section) return;
                if (section.classList.contains('seo-section')) {
                    section.classList.add('is-expanded');
                    return;
                }
                var moreContent = section.querySelector('.more-content');
                if (moreContent) {
                    moreContent.classList.add('active');
                }
            });
        });
    })();

    // Single blog: акции в сайдбаре.
    (function() {
        if (typeof Swiper === 'undefined') return;
        document.querySelectorAll('.single-blog__right-actions .splide__track.swiper').forEach(function(el) {
            var root = el.closest('.single-blog__right-actions');
            if (!root || !el.querySelector('.swiper-slide')) return;
            new Swiper(el, {
                slidesPerView: 1,
                spaceBetween: 16,
                watchOverflow: true,
                pagination: {
                    el: root.querySelector('.splide__pagination'),
                    clickable: true
                }
            });
        });
    })();

    // Single blog: видео в теле статьи (preview → play).
    (function() {
        document.querySelectorAll('[data-tolstenko-blog-video]').forEach(function(root) {
            var btn = root.querySelector('.video__btn');
            if (!btn) return;
            btn.addEventListener('click', function() {
                var embed = root.querySelector('.video__embed');
                var video = root.querySelector('video.video__iframe');
                var img = root.querySelector('.video__img');
                if (embed) {
                    embed.hidden = false;
                }
                if (video) {
                    video.hidden = false;
                    if (typeof video.play === 'function') {
                        video.play().catch(function() {});
                    }
                }
                if (img) img.style.display = 'none';
                btn.style.display = 'none';
                root.classList.add('is-playing');
            });
        });
    })();

    // Контакты: main + thumbs (как marketing Splide). Thumbs: ровно 4 в кадре.
    (function() {
        if (typeof Swiper === 'undefined') return;

        function unlockArrows(gallery) {
            if (!gallery) return;
            var mainRoot = gallery.querySelector('.contacts__gallery-main');
            if (!mainRoot) return;
            [mainRoot.querySelector('.splide__arrow--next'), mainRoot.querySelector('.splide__arrow--prev')].forEach(function(btn) {
                if (!btn) return;
                btn.classList.remove('swiper-button-lock');
                btn.hidden = false;
                btn.removeAttribute('disabled');
            });
        }

        function refreshGallery(gallery) {
            if (!gallery) return;
            requestAnimationFrame(function() {
                if (gallery._thumbsSwiper && typeof gallery._thumbsSwiper.update === 'function') {
                    gallery._thumbsSwiper.update();
                }
                if (gallery._mainSwiper && typeof gallery._mainSwiper.update === 'function') {
                    gallery._mainSwiper.update();
                }
                unlockArrows(gallery);
            });
        }

        function mountContactsGallery(gallery) {
            if (!gallery || gallery._mainSwiper) return;
            var mainEl = gallery.querySelector('.contacts__gallery-main .splide__track.swiper');
            if (!mainEl || !mainEl.querySelector('.swiper-slide')) return;

            var thumbsRoot = gallery.querySelector('.contacts__gallery-thumbs');
            var thumbsEl = thumbsRoot ? thumbsRoot.querySelector('.splide__track.swiper') : null;
            var thumbsSwiper = null;
            var slideCount = mainEl.querySelectorAll('.swiper-slide').length;

            if (thumbsEl && thumbsEl.querySelector('.swiper-slide')) {
                // Как Splide fixedWidth + gap; ширина слайда в CSS (88 / 110).
                thumbsSwiper = new Swiper(thumbsEl, {
                    slidesPerView: 'auto',
                    spaceBetween: 8,
                    rewind: true,
                    watchSlidesProgress: true,
                    slideToClickedSlide: true,
                    watchOverflow: true,
                    breakpoints: {
                        992: { spaceBetween: 12 }
                    }
                });
                gallery._thumbsSwiper = thumbsSwiper;
            }

            var mainRoot = gallery.querySelector('.contacts__gallery-main');
            var nextEl = mainRoot ? mainRoot.querySelector('.splide__arrow--next') : null;
            var prevEl = mainRoot ? mainRoot.querySelector('.splide__arrow--prev') : null;
            var mainOpts = {
                effect: 'fade',
                fadeEffect: { crossFade: true },
                slidesPerView: 1,
                spaceBetween: 0,
                rewind: true,
                watchOverflow: false,
                allowTouchMove: slideCount > 1,
                observer: true,
                observeParents: true
            };
            if (slideCount > 1) {
                mainOpts.navigation = { nextEl: nextEl, prevEl: prevEl };
            }
            if (thumbsSwiper) {
                mainOpts.thumbs = { swiper: thumbsSwiper };
            }
            gallery._mainSwiper = new Swiper(mainEl, mainOpts);
            if (slideCount > 1) unlockArrows(gallery);
        }

        document.querySelectorAll('section.contacts').forEach(function(section) {
            var radios = section.querySelectorAll('input[name="contacts"]');
            var galleryPanels = section.querySelectorAll('.contacts__gallery-panel');
            var infoPanels = section.querySelectorAll('.contacts__info-panel');

            section.querySelectorAll('[data-contacts-gallery]').forEach(mountContactsGallery);

            function activateTab(idx) {
                infoPanels.forEach(function(panel) {
                    var on = panel.getAttribute('data-tab-index') === idx;
                    panel.hidden = !on;
                    panel.classList.toggle('is-active', on);
                });
                galleryPanels.forEach(function(panel) {
                    var on = panel.getAttribute('data-tab-index') === idx;
                    panel.hidden = !on;
                    panel.classList.toggle('is-active', on);
                    if (on) {
                        refreshGallery(panel.querySelector('[data-contacts-gallery]'));
                    }
                });
            }

            if (!radios.length) {
                refreshGallery(section.querySelector('[data-contacts-gallery]'));
                return;
            }

            radios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (!radio.checked) return;
                    activateTab(radio.getAttribute('data-tab-index'));
                });
            });

            var checked = section.querySelector('input[name="contacts"]:checked');
            if (checked) {
                activateTab(checked.getAttribute('data-tab-index'));
            }
        });
    })();
})