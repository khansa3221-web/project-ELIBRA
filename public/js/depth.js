/* =====================================================================
   E-LIBRA — Interaksi KEDALAMAN (progressive enhancement)
   Menambah: spotlight kartu, miring 3D, parallax hero, tombol magnetik,
   kursor bercahaya. Jika file ini gagal dimuat, web tetap normal.
   ===================================================================== */
(function () {
  'use strict';

  var doc = document;
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hasHover = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  function $all(sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); }
  function ready(fn) {
    if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  /* 1. Spotlight + miring 3D pada kartu -------------------------------- */
  var SPOT = '.feat-card, .kb-card, .stat-card, .panel, .mini-catalog, .step';
  var TILT = { '.kb-card': 5, '.mini-catalog': 6, '.feat-card': 6, '.stat-card': 4, '.step': 5 };

  function tiltAmount(el) {
    for (var sel in TILT) { if (el.matches(sel)) return TILT[sel]; }
    return 0;
  }

  function initCards() {
    if (!hasHover) return;
    $all(SPOT).forEach(function (el) {
      var amt = reduceMotion ? 0 : tiltAmount(el);
      el.classList.add('dp-spot');
      if (amt) el.classList.add('dp-tilt');

      var raf = 0, ev = null;
      function paint() {
        raf = 0;
        if (!ev) return;
        var r = el.getBoundingClientRect();
        var px = (ev.clientX - r.left) / r.width;
        var py = (ev.clientY - r.top) / r.height;
        el.style.setProperty('--mx', (px * 100).toFixed(1) + '%');
        el.style.setProperty('--my', (py * 100).toFixed(1) + '%');
        if (amt) {
          el.style.setProperty('--ry', ((px - 0.5) * amt * 2).toFixed(2) + 'deg');
          el.style.setProperty('--rx', ((0.5 - py) * amt * 2).toFixed(2) + 'deg');
        }
      }
      el.addEventListener('pointermove', function (e) {
        ev = e;
        if (amt) el.classList.add('is-tilting');
        if (!raf) raf = requestAnimationFrame(paint);
      });
      el.addEventListener('pointerleave', function () {
        ev = null;
        el.classList.remove('is-tilting');
        el.style.removeProperty('--rx');
        el.style.removeProperty('--ry');
      });
    });
  }

  /* 2. Parallax hero: orb & ornamen bergeser mengikuti mouse + scroll --- */
  function initParallax() {
    if (reduceMotion) return;
    var layers = $all('[data-depth]');
    if (!layers.length) return;

    var mx = 0, my = 0, sy = 0, tx = 0, ty = 0, running = false;

    function frame() {
      // easing: nilai mengejar target dengan halus
      tx += (mx - tx) * 0.08;
      ty += (my - ty) * 0.08;
      layers.forEach(function (el) {
        var d = parseFloat(el.getAttribute('data-depth')) || 0;
        var x = tx * d * 400;
        var y = ty * d * 400 + sy * d * 2.2;
        el.style.translate = x.toFixed(1) + 'px ' + y.toFixed(1) + 'px';
      });
      if (Math.abs(mx - tx) > 0.0005 || Math.abs(my - ty) > 0.0005) requestAnimationFrame(frame);
      else running = false;
    }
    function kick() { if (!running) { running = true; requestAnimationFrame(frame); } }

    if (hasHover) {
      window.addEventListener('pointermove', function (e) {
        mx = e.clientX / window.innerWidth - 0.5;
        my = e.clientY / window.innerHeight - 0.5;
        kick();
      }, { passive: true });
    }
    window.addEventListener('scroll', function () {
      sy = Math.min(window.pageYOffset, 900);
      kick();
    }, { passive: true });
  }

  /* 3. Tombol magnetik: sedikit tertarik ke arah kursor ---------------- */
  function initMagnetic() {
    if (reduceMotion || !hasHover) return;
    $all('.hero-actions .btn-solid, .hero-actions .btn-ghost, .cta-band .btn-white').forEach(function (el) {
      el.addEventListener('pointermove', function (e) {
        var r = el.getBoundingClientRect();
        var x = (e.clientX - r.left - r.width / 2) * 0.18;
        var y = (e.clientY - r.top - r.height / 2) * 0.28;
        el.style.translate = x.toFixed(1) + 'px ' + y.toFixed(1) + 'px';
      });
      el.addEventListener('pointerleave', function () { el.style.translate = ''; });
    });
  }

  /* 4. Kursor bercahaya (halaman publik saja, agar dashboard tetap bersih) */
  function initGlow() {
    if (reduceMotion || !hasHover) return;
    if (!doc.querySelector('.site-page')) return;
    var g = doc.createElement('div');
    g.className = 'dp-glow';
    g.setAttribute('aria-hidden', 'true');
    doc.body.appendChild(g);

    var x = 0, y = 0, cx = 0, cy = 0, run = false;
    function loop() {
      cx += (x - cx) * 0.12;
      cy += (y - cy) * 0.12;
      g.style.transform = 'translate3d(' + cx.toFixed(1) + 'px,' + cy.toFixed(1) + 'px,0)';
      if (Math.abs(x - cx) > 0.5 || Math.abs(y - cy) > 0.5) requestAnimationFrame(loop);
      else run = false;
    }
    window.addEventListener('pointermove', function (e) {
      x = e.clientX; y = e.clientY;
      g.classList.add('on');
      if (!run) { run = true; requestAnimationFrame(loop); }
    }, { passive: true });
    doc.addEventListener('pointerleave', function () { g.classList.remove('on'); });
  }

  ready(function () {
    initCards();
    initParallax();
    initMagnetic();
    initGlow();
  });
})();
