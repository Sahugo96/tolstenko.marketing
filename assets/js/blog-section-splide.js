/**
 * Блок «Статьи» — Splide как tolstenko.marketing (blogSectionSplideInit.js).
 * Desktop (>=992): статичная сетка 1+3. Mobile: слайдер на .blog-section__splide.
 * «Слайдер статей» (.blog-section--same) — отдельная логика в service-section-swiper.js.
 */
(function () {
	var DESK_MQ = window.matchMedia('(min-width: 992px)');

	function resolveRoot(target) {
		if (!target) {
			return null;
		}
		if (target.classList && target.classList.contains('blog-section__splide')) {
			return target;
		}
		if (target.querySelector) {
			return target.querySelector('.blog-section__splide');
		}
		return null;
	}

	function countSlides(root) {
		if (!root) {
			return 0;
		}
		return root.querySelectorAll('.splide__slide').length;
	}

	function destroySplide(root) {
		if (!root || !root.splide) {
			return;
		}
		try {
			root.splide.destroy(true);
		} catch (e) {
			/* ignore stale instance after AJAX replace */
		}
		root.splide = null;
		root.classList.remove('splide--initialized', 'is-initialized');
	}

	function mountMainBlogSection(root) {
		if (typeof Splide === 'undefined' || !root) {
			return null;
		}
		if (root.closest('.blog-section--same')) {
			return null;
		}

		destroySplide(root);

		if (DESK_MQ.matches) {
			return null;
		}

		if (countSlides(root) < 2) {
			return null;
		}

		var instance = new Splide(root, {
			perPage: 1,
			perMove: 1,
			gap: '20px',
			pagination: true,
			arrows: false,
			updateOnMove: true
		});
		instance.mount();
		root.splide = instance;
		root.classList.add('splide--initialized');
		return instance;
	}

	function initAll() {
		document.querySelectorAll('.blog-section:not(.blog-section--same) .blog-section__splide').forEach(mountMainBlogSection);
	}

	window.tolstenkoDestroyBlogSectionSplide = function (target) {
		destroySplide(resolveRoot(target));
	};

	window.tolstenkoMountBlogSectionSplide = function (target) {
		return mountMainBlogSection(resolveRoot(target));
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	var resizeTimer;
	window.addEventListener('resize', function () {
		window.clearTimeout(resizeTimer);
		resizeTimer = window.setTimeout(function () {
			document.querySelectorAll('.blog-section:not(.blog-section--same) .blog-section__splide').forEach(function (root) {
				if (DESK_MQ.matches) {
					destroySplide(root);
					return;
				}
				if (!root.splide) {
					mountMainBlogSection(root);
				} else if (typeof root.splide.refresh === 'function') {
					root.splide.refresh();
				}
			});
		}, 150);
	});
})();
