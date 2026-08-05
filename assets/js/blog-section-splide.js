/**
 * Блок «Статьи» — Splide как tolstenko.marketing (blogSectionSplideInit.js).
 * Desktop: статичная сетка. Mobile (<992): слайдер на .blog-section__splide.
 * «Слайдер статей» (.blog-section--same) — отдельная логика в service-section-swiper.js.
 */
(function () {
	function destroySplide(root) {
		if (!root || !root.splide) {
			return;
		}
		try {
			root.splide.destroy(true);
		} catch (e) {
			/* ignore */
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

		if (window.matchMedia('(min-width: 992px)').matches) {
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

	window.tolstenkoMountBlogSectionSplide = function (target) {
		if (!target) {
			return null;
		}
		var root = target.classList.contains('blog-section__splide')
			? target
			: target.querySelector('.blog-section__splide');
		return mountMainBlogSection(root);
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	window.addEventListener('resize', function () {
		document.querySelectorAll('.blog-section:not(.blog-section--same) .blog-section__splide').forEach(function (root) {
			if (window.matchMedia('(min-width: 992px)').matches) {
				destroySplide(root);
			} else if (!root.splide) {
				mountMainBlogSection(root);
			}
		});
	});
})();
