/* VW Dealer theme – main.js (vanilla, không phụ thuộc jQuery) */
(function () {
	'use strict';

	/* ---------- Mobile nav ---------- */
	var toggle = document.getElementById('nav-toggle');
	var nav = document.getElementById('main-nav');
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			document.body.classList.toggle('nav-open', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		nav.querySelectorAll('.menu-item-has-children > a').forEach(function (a) {
			a.addEventListener('click', function (e) {
				if (window.innerWidth <= 860) {
					e.preventDefault();
					a.parentElement.classList.toggle('is-open');
				}
			});
		});
	}

	/* ---------- Hero slider ---------- */
	var hero = document.getElementById('hero');
	if (hero) {
		var slides = hero.querySelectorAll('.hero__slide');
		var dots = hero.querySelectorAll('.hero__dots button');
		var idx = 0, timer;
		var go = function (n) {
			if (slides.length < 2) return;
			slides[idx].classList.remove('is-active');
			if (dots[idx]) dots[idx].classList.remove('is-active');
			idx = (n + slides.length) % slides.length;
			slides[idx].classList.add('is-active');
			if (dots[idx]) dots[idx].classList.add('is-active');
		};
		var restart = function () { clearInterval(timer); timer = setInterval(function () { go(idx + 1); }, 6000); };
		hero.querySelectorAll('.hero__arrow').forEach(function (b) {
			b.addEventListener('click', function () { go(idx + parseInt(b.dataset.dir, 10)); restart(); });
		});
		dots.forEach(function (d) {
			d.addEventListener('click', function () { go(parseInt(d.dataset.index, 10)); restart(); });
		});
		var sx = 0;
		hero.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
		hero.addEventListener('touchend', function (e) {
			var dx = e.changedTouches[0].clientX - sx;
			if (Math.abs(dx) > 50) { go(idx + (dx < 0 ? 1 : -1)); restart(); }
		});
		restart();
	}

	/* ---------- Filter tabs ---------- */
	document.querySelectorAll('.filter-tabs[data-filter-target]').forEach(function (tabs) {
		var grid = document.querySelector(tabs.dataset.filterTarget);
		if (!grid) return;
		tabs.querySelectorAll('button').forEach(function (b) {
			b.addEventListener('click', function () {
				tabs.querySelectorAll('button').forEach(function (x) { x.classList.remove('is-active'); });
				b.classList.add('is-active');
				var f = b.dataset.filter;
				grid.querySelectorAll('[data-type]').forEach(function (item) {
					item.classList.toggle('is-hidden', f !== '*' && item.dataset.type !== f);
				});
			});
		});
	});

	/* ---------- Floating form ---------- */
	var ft = document.getElementById('float-form-toggle');
	var ff = document.getElementById('float-form');
	var fc = document.getElementById('float-form-close');
	if (ft && ff) {
		ft.addEventListener('click', function () {
			var open = ff.hasAttribute('hidden');
			if (open) ff.removeAttribute('hidden'); else ff.setAttribute('hidden', '');
			ft.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		if (fc) fc.addEventListener('click', function () { ff.setAttribute('hidden', ''); ft.setAttribute('aria-expanded', 'false'); });
	}

	/* ---------- Lightbox ---------- */
	document.querySelectorAll('[data-lightbox]').forEach(function (g) {
		var links = Array.prototype.slice.call(g.querySelectorAll('a'));
		var cur = 0, box;
		var show = function (i) {
			cur = (i + links.length) % links.length;
			box.querySelector('img').src = links[cur].href;
		};
		var close = function () { if (box) { box.remove(); box = null; } };
		links.forEach(function (a, i) {
			a.addEventListener('click', function (e) {
				e.preventDefault();
				box = document.createElement('div');
				box.className = 'lightbox';
				box.innerHTML = '<button class="lightbox__close" aria-label="Đóng">×</button><button class="lightbox__nav lightbox__nav--prev">‹</button><img alt=""><button class="lightbox__nav lightbox__nav--next">›</button>';
				document.body.appendChild(box);
				box.querySelector('.lightbox__close').addEventListener('click', close);
				box.querySelector('.lightbox__nav--prev').addEventListener('click', function () { show(cur - 1); });
				box.querySelector('.lightbox__nav--next').addEventListener('click', function () { show(cur + 1); });
				box.addEventListener('click', function (ev) { if (ev.target === box) close(); });
				show(i);
			});
		});
		document.addEventListener('keydown', function (e) {
			if (!box) return;
			if (e.key === 'Escape') close();
			if (e.key === 'ArrowLeft') show(cur - 1);
			if (e.key === 'ArrowRight') show(cur + 1);
		});
	});

	/* ---------- Installment calculator ---------- */
	var calc = document.getElementById('calc');
	if (calc) {
		var $ = function (id) { return document.getElementById(id); };
		var fmt = function (n) { return Math.round(n).toLocaleString('vi-VN') + ' ₫'; };
		var update = function () {
			var price = parseFloat($('calc-price').value) || 0;
			var ratio = parseFloat($('calc-ratio').value) / 100;
			var years = parseInt($('calc-years').value, 10);
			var rate = parseFloat($('calc-rate').value) / 100;
			$('calc-ratio-out').textContent = Math.round(ratio * 100) + '%';
			$('calc-years-out').textContent = years + ' năm';
			var loan = price * ratio;
			var months = years * 12;
			var principal = months ? loan / months : 0;
			var firstMonth = principal + loan * rate / 12;
			var interest = 0;
			for (var m = 0; m < months; m++) { interest += (loan - principal * m) * rate / 12; }
			$('r-loan').textContent = fmt(loan);
			$('r-down').textContent = fmt(price - loan);
			$('r-month').textContent = fmt(firstMonth);
			$('r-interest').textContent = fmt(interest);
		};
		$('calc-car').addEventListener('change', function () {
			if (parseFloat(this.value) > 0) $('calc-price').value = this.value;
			update();
		});
		['calc-price', 'calc-ratio', 'calc-years', 'calc-rate'].forEach(function (id) { $(id).addEventListener('input', update); });
		update();
	}

	/* ---------- Pre-select car from ?car= ---------- */
	var params = new URLSearchParams(location.search);
	if (params.get('car')) {
		document.querySelectorAll('.vwd-form select[name="car"]').forEach(function (s) {
			for (var i = 0; i < s.options.length; i++) {
				if (s.options[i].value === params.get('car')) { s.selectedIndex = i; break; }
			}
		});
	}

	/* ---------- Toast auto remove ---------- */
	var toast = document.querySelector('.vwd-toast');
	if (toast) { setTimeout(function () { toast.remove(); }, 6500); if (history.replaceState) { params.delete('vwd'); history.replaceState(null, '', location.pathname + (params.toString() ? '?' + params : '') + location.hash); } }
})();
