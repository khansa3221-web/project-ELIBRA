/* E-LIBRA — interaksi halaman Login & Registrasi (tampilkan sandi, kekuatan sandi, transisi) */
(function () {
  'use strict';
  var doc = document;
  function $(s, c) { return (c || doc).querySelector(s); }
  function $all(s, c) { return Array.prototype.slice.call((c || doc).querySelectorAll(s)); }

  function init() {
    // Tampilkan / sembunyikan kata sandi
    $all('[data-au-eye]').forEach(function (btn) {
      var input = doc.getElementById(btn.getAttribute('data-au-eye'));
      if (!input) return;
      btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        input.focus();
      });
    });

    // Indikator kekuatan kata sandi + kecocokan konfirmasi (hanya di halaman registrasi)
    var pw = $('[data-au-strength]'), meter = $('[data-au-meter]'), conf = $('[data-au-confirm]'), hint = $('[data-au-match]');
    function score(v) {
      var s = 0;
      if (v.length >= 8) s++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
      if (/\d/.test(v)) s++;
      if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) s++;
      return v.length ? Math.max(1, s) : 0;
    }
    function match() {
      if (!conf || !hint) return;
      if (!conf.value) { hint.textContent = ''; hint.className = 'au-hint'; return; }
      var ok = conf.value === pw.value;
      hint.textContent = ok ? 'Kata sandi cocok ✓' : 'Konfirmasi belum sama dengan kata sandi';
      hint.className = 'au-hint ' + (ok ? 'ok' : 'bad');
    }
    if (pw && meter) {
      var labels = ['', 'Lemah', 'Cukup', 'Baik', 'Kuat'];
      pw.addEventListener('input', function () {
        var s = score(pw.value);
        meter.classList.toggle('show', s > 0);
        meter.setAttribute('data-s', s);
        var t = $('small', meter); if (t) t.textContent = 'Kekuatan kata sandi: ' + labels[s] + (pw.value.length < 8 ? ' (minimal 8 karakter)' : '');
        match();
      });
      if (conf) conf.addEventListener('input', match);
    }

    // Transisi halus saat pindah antara login <-> registrasi
    $all('a[data-au-nav]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        if (e.metaKey || e.ctrlKey || e.shiftKey) return;
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        e.preventDefault();
        doc.body.classList.add('au-leaving');
        setTimeout(function () { location.href = a.href; }, 200);
      });
    });
    window.addEventListener('pageshow', function () { doc.body.classList.remove('au-leaving'); });
  }
  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', init); else init();
})();
