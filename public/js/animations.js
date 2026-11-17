/* =====================================================================
   E-LIBRA — Animasi tambahan (progressive enhancement)
   File ini hanya MENAMBAH perilaku. Jika gagal dimuat, web tetap normal.
   ===================================================================== */
(function () {
  'use strict';

  var doc = document;
  var root = doc.documentElement;
  var reduceMotion = window.ELIBRA_REDUCE === true; /* hanya true bila tombol Animasi dimatikan */

  function $all(sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); }

  function ready(fn) {
    if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  /* ---------------------------------------------------------------
     1. Reveal saat scroll
     Format: [selector, varian, jeda-antar-elemen(detik), jeda-awal]
     --------------------------------------------------------------- */
  var REVEAL = [
    // Beranda
    ['.hero-badge', '', 0, 0.05],
    ['.hero h1', '', 0, 0.15],
    ['.hero p.lead', '', 0, 0.25],
    ['.hero-actions', '', 0, 0.35],
    ['.hero-stats', '', 0, 0.45],
    ['.hero .mini-catalog', 'rv-right', 0, 0.3],
    ['.section-head', '', 0, 0],
    ['.feat-card', 'rv-zoom', 0.09, 0],
    ['.steps-wrap', 'rv-zoom', 0, 0],
    ['.step', '', 0.14, 0.1],
    ['.cp-text', 'rv-left', 0, 0],
    ['.cp-item', 'rv-left', 0.08, 0.1],
    ['.catalog-preview .mini-catalog', 'rv-right', 0, 0],
    ['.cta-band', 'rv-zoom', 0, 0],
    ['.footer-grid > *', '', 0.08, 0],
    // Katalog
    ['.kat-hero h1', '', 0, 0],
    ['.kat-hero p', '', 0, 0.08],
    ['.kat-searchbar', '', 0, 0.16],
    ['.kat-side', 'rv-left', 0, 0.1],
    ['.kat-toolbar', '', 0, 0.1],
    ['.kb-card', '', 0.06, 0],
    ['.pagination', '', 0, 0],
    // Login / daftar
    ['.login-form-wrap > *', '', 0.07, 0.05],
    ['.lr-quote', 'rv-left', 0, 0.2],
    ['.lr-mini', 'rv-zoom', 0.1, 0.35],
    // Dashboard & panel
    ['.app-side', 'rv-left', 0, 0],
    ['.app-topbar', '', 0, 0.05],
    ['.stat-card', '', 0.08, 0.1],
    ['.panel', '', 0.07, 0.1],
    ['.loan-row', 'rv-left', 0.06, 0.1],
    ['.dtable tbody tr', '', 0.04, 0.05],
    ['.bar-chart .bar', 'rv-bar', 0.09, 0.15],
    // Halaman panel (riwayat, notifikasi, peminjaman, dll.)
    ['.app-topbar h1', '', 0, 0.05],
    ['.app-topbar p', '', 0, 0.15],
    ['.panel-head h3', '', 0.05, 0.1],
    ['.empty-state', 'rv-zoom', 0, 0.1],
    // Detail buku
    ['.detail-cover', 'rv-zoom', 0, 0],
    ['.detail-info > *', '', 0.06, 0.1],
    ['.borrow-panel', 'rv-right', 0, 0.2]
  ];

  function initReveal() {
    if (reduceMotion || !('IntersectionObserver' in window)) return;

    var items = [];
    var tagged = typeof WeakSet !== 'undefined' ? new WeakSet() : null;

    REVEAL.forEach(function (cfg) {
      var els = $all(cfg[0]);
      els.forEach(function (el, i) {
        if (tagged && tagged.has(el)) return;       // jangan tag dua kali
        if (tagged) tagged.add(el);
        var delay = cfg[3] + Math.min(i, 8) * cfg[2]; // batasi jeda agar tidak terlalu lama
        el.classList.add('rv');
        if (cfg[1]) el.classList.add(cfg[1]);
        el.style.setProperty('--rv-delay', delay.toFixed(2) + 's');
        items.push({ el: el, delay: delay });
      });
    });

    if (!items.length) return;
    root.classList.add('el-ready');

    var byEl = typeof Map !== 'undefined' ? new Map() : null;
    items.forEach(function (it) { if (byEl) byEl.set(it.el, it); });

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        io.unobserve(el);
        var it = byEl ? byEl.get(el) : null;
        var delay = it ? it.delay : 0;
        el.classList.add('rv-in');
        // Setelah animasi selesai, lepas kelas supaya efek hover bawaan bebas bekerja
        setTimeout(function () {
          el.classList.remove('rv', 'rv-in', 'rv-left', 'rv-right', 'rv-zoom', 'rv-bar');
          el.classList.add('rv-done');
          el.style.removeProperty('--rv-delay');
        }, (delay + 1.1) * 1000);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    items.forEach(function (it) { io.observe(it.el); });

    // Pengaman: jika karena alasan apa pun elemen tak pernah terlihat, tampilkan setelah 4 dtk
    setTimeout(function () {
      items.forEach(function (it) {
        if (it.el.classList.contains('rv') && !it.el.classList.contains('rv-in')) {
          it.el.classList.add('rv-in');
        }
      });
    }, 4000);
  }

  /* ---------------------------------------------------------------
     2. Hitung naik angka statistik
     --------------------------------------------------------------- */
  function initCountUp() {
    if (reduceMotion || !('IntersectionObserver' in window)) return;
    var targets = $all('.hstat .v, .stat-card .sv, .lr-mini .v');
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        io.unobserve(entry.target);
        countUp(entry.target);
      });
    }, { threshold: 0.4 });

    targets.forEach(function (el) {
      var txt = (el.textContent || '').trim();
      // hanya angka murni, mis. 1250 atau 1,250 atau 1.250
      if (/^\d{1,3}([.,]\d{3})+$|^\d+$/.test(txt)) io.observe(el);
    });
  }

  function countUp(el) {
    var original = el.textContent.trim();
    var sepMatch = original.match(/[.,]/);
    var sep = sepMatch ? sepMatch[0] : '';
    var end = parseInt(original.replace(/[.,]/g, ''), 10);
    if (!isFinite(end) || end <= 0) return;

    var duration = Math.min(1600, 700 + String(end).length * 140);
    var start = null;

    function fmt(n) {
      var s = String(n);
      return sep ? s.replace(/\B(?=(\d{3})+(?!\d))/g, sep) : s;
    }
    function step(ts) {
      if (start === null) start = ts;
      var p = Math.min(1, (ts - start) / duration);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = fmt(Math.round(end * eased));
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = original; // pastikan nilai akhir persis seperti aslinya
    }
    el.textContent = fmt(0);
    requestAnimationFrame(step);
  }

  /* ---------------------------------------------------------------
     3. Ripple klik pada tombol
     --------------------------------------------------------------- */
  function initRipple() {
    if (reduceMotion) return;
    var SEL = '.btn-solid,.btn-primary-lg,.btn-white,.kat-search-btn,.btn-sm,.btn-line,.act-btn';
    doc.addEventListener('click', function (e) {
      var btn = e.target.closest ? e.target.closest(SEL) : null;
      if (!btn) return;
      var rect = btn.getBoundingClientRect();
      var size = Math.max(rect.width, rect.height);
      var r = doc.createElement('span');
      r.className = 'el-ripple';
      r.style.width = r.style.height = size + 'px';
      r.style.left = (e.clientX - rect.left - size / 2) + 'px';
      r.style.top = (e.clientY - rect.top - size / 2) + 'px';
      btn.appendChild(r);
      setTimeout(function () { if (r.parentNode) r.parentNode.removeChild(r); }, 700);
    });
  }

  /* ---------------------------------------------------------------
     4. Progress scroll, bayangan navbar, tombol ke atas
     --------------------------------------------------------------- */
  function initScrollUI() {
    var bar = doc.createElement('div');
    bar.className = 'el-progress';
    bar.setAttribute('aria-hidden', 'true');
    doc.body.appendChild(bar);

    var top = doc.createElement('button');
    top.type = 'button';
    top.className = 'el-top';
    top.setAttribute('aria-label', 'Kembali ke atas');
    top.innerHTML = '<svg class="ic" width="18" height="18" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
    top.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
    doc.body.appendChild(top);

    var nav = doc.querySelector('.nav');
    var ticking = false;

    function update() {
      var y = window.pageYOffset || root.scrollTop || 0;
      var max = (root.scrollHeight - window.innerHeight) || 1;
      bar.style.transform = 'scaleX(' + Math.min(1, Math.max(0, y / max)) + ')';
      top.classList.toggle('show', y > 520);
      if (nav) nav.classList.toggle('is-scrolled', y > 8);
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });
    window.addEventListener('resize', update);
    update();
  }

  /* ---------------------------------------------------------------
     5. Pesan sukses hilang sendiri (pesan error tetap tampil)
     --------------------------------------------------------------- */
  function initAlerts() {
    $all('.alert-success').forEach(function (a) {
      var timer = setTimeout(hide, 6000);
      a.addEventListener('mouseenter', function () { clearTimeout(timer); });
      a.addEventListener('mouseleave', function () { timer = setTimeout(hide, 2500); });
      function hide() {
        a.style.maxHeight = a.offsetHeight + 'px';
        void a.offsetHeight; // paksa reflow agar transisi max-height jalan
        a.classList.add('el-out');
        setTimeout(function () { if (a.parentNode) a.parentNode.removeChild(a); }, 900);
      }
    });
  }

  /* ---------------------------------------------------------------
     6. Efek miring 3D pada sampul buku di halaman detail
     --------------------------------------------------------------- */
  function initTilt() {
    if (reduceMotion || !window.matchMedia('(hover: hover)').matches) return;
    $all('.detail-cover').forEach(function (el) {
      el.addEventListener('mousemove', function (e) {
        var r = el.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5;
        var y = (e.clientY - r.top) / r.height - 0.5;
        el.style.transform = 'perspective(700px) rotateY(' + (x * 14).toFixed(2) + 'deg) rotateX(' + (-y * 14).toFixed(2) + 'deg) translateY(-4px)';
      });
      el.addEventListener('mouseleave', function () { el.style.transform = ''; });
    });
  }

  /* ---------------------------------------------------------------
     7. (Dipindah) Loading tombol submit kini HANYA ditangani polish.js agar
        hanya ada satu indikator loading. Fungsi dibiarkan kosong supaya
        pemanggilnya tidak berubah.
     --------------------------------------------------------------- */
  function initSubmitLoading() {}

  /* ---------------------------------------------------------------
     8. Transisi halus antar halaman (fade keluar sebelum pindah)
     --------------------------------------------------------------- */
  function initPageTransition() {
    if (reduceMotion) return;
    doc.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var a = e.target.closest ? e.target.closest('a[href]') : null;
      if (!a) return;
      if (a.target && a.target !== '_self') return;
      if (a.hasAttribute('download') || a.hasAttribute('data-no-transition')) return;
      var href = a.getAttribute('href');
      if (!href || href.charAt(0) === '#' || /^(javascript:|mailto:|tel:)/i.test(href)) return;
      if (a.origin !== window.location.origin) return;
      if (a.pathname === window.location.pathname && a.search === window.location.search) return;
      // Tautan yang sudah memakai skeleton (pagination/filter katalog) tidak diberi fade lagi:
      // cukup satu indikator loading, dan pindah halaman langsung tanpa jeda 180 ms.
      if (doc.body.classList.contains('dl-busy') || (a.closest && a.closest('.pagination, .kat-side'))) return;

      e.preventDefault();
      doc.body.classList.add('el-leaving');
      setTimeout(function () { window.location.href = a.href; }, 180);
    });
    // kembali via tombol Back: pastikan halaman tidak tetap transparan
    window.addEventListener('pageshow', function () { doc.body.classList.remove('el-leaving'); });
  }

  /* Jalankan setelah semua deklarasi di atas terdefinisi */
  ready(function () {
    try { initReveal(); } catch (e) { root.classList.remove("el-ready"); if (window.console) console.error("[elibra-anim]", e); }
    try { initCountUp(); } catch (e) {}
    try { initRipple(); } catch (e) {}
    try { initScrollUI(); } catch (e) {}
    try { initAlerts(); } catch (e) {}
    try { initTilt(); } catch (e) {}
    try { initSubmitLoading(); } catch (e) {}
    try { initPageTransition(); } catch (e) {}
  });
})();
