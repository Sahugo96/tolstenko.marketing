/**
 * Слайдер услуг — как Tolstenko serviceSectionSplideInit.
 * .swiper на .splide__track; стрелки/пагинация в .splide__bottom.
 * Desk: 3, стрелки; <992: 1, пагинация.
 */
(function () {
	function mount(el) {
		if (!el || typeof Swiper === 'undefined') {
			return null;
		}

		var root = el.closest('.service-section__splide')
			|| el.closest('.blog-section__splide')
			|| el.parentElement;
		if (!root) {
			return null;
		}

		if (el.swiper && typeof el.swiper.destroy === 'function') {
			el.swiper.destroy(true, true);
		}
		if (!el.querySelector('.swiper-slide')) {
			root.classList.remove('is-overflow');
			return null;
		}

		function syncOverflow(swiper) {
			root.classList.toggle('is-overflow', !swiper.isLocked);
		}

		var instance = new Swiper(el, {
			slidesPerView: 1,
			slidesPerGroup: 1,
			spaceBetween: 20,
			watchOverflow: true,
			observer: true,
			observeParents: true,
			updateOnWindowResize: true,
			speed: 400,
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
					slidesPerView: 3,
					slidesPerGroup: 1,
					spaceBetween: 20,
					pagination: { enabled: false },
					navigation: { enabled: true }
				}
			},
			on: {
				init: function () {
					syncOverflow(this);
				},
				resize: function () {
					syncOverflow(this);
				},
				update: function () {
					syncOverflow(this);
				},
				breakpoint: function () {
					syncOverflow(this);
				},
				lock: function () {
					root.classList.remove('is-overflow');
				},
				unlock: function () {
					root.classList.add('is-overflow');
				}
			}
		});

		syncOverflow(instance);
		return instance;
	}

	function initAll() {
		document.querySelectorAll(
			'.service-section__splide .splide__track.swiper, .blog-section--same .blog-section__splide .splide__track.swiper'
		).forEach(mount);
	}

	window.tolstenkoMountServiceSectionSwiper = function (target) {
		if (!target) {
			return null;
		}
		var el = target.classList.contains('swiper')
			? target
			: target.querySelector('.splide__track.swiper');
		return mount(el);
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}
})();
