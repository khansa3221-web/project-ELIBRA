/* E-LIBRA — Dreamy: split-text, parallax ringan, indikator kata sandi. Progressive enhancement. */
(function () {
  'use strict';
  var doc = document;
  var reduce = window.ELIBRA_REDUCE === true; /* hanya true bila tombol Animasi dimatikan */
  function all(s, c) { return Array.prototype.slice.call((c || doc).querySelectorAll(s)); }
  function ready(fn) { doc.readyState === 'loading' ? doc.addEventListener('DOMContentLoaded', fn) : fn(); }

  /* 1. Split text: bungkus tiap kata agar muncul bergantian */
  function splitText() {
    if (reduce) return;
    all('[data-split]').forEach(function (el) {
      var words = el.textContent.trim().split(/\s+/);
      el.setAttribute('aria-label', words.join(' '));
      el.innerHTML = words.map(function (w, i) {
        return '<span class="w" aria-hidden="true"><span style="--i:' + i + '">' + w.replace(/</g, '&lt;') + '</span></span>';
      }).join(' ');
    });
  }

  /* 2. Parallax ringan mengikuti pointer (hanya perangkat dengan mouse) */
  function parallax() {
    if (reduce || !window.matchMedia('(pointer:fine)').matches) return;
    all('.scene').forEach(function (scene) {
      all('[data-depth]', scene).forEach(function (n) { n.style.setProperty('--depth', n.getAttribute('data-depth')); });
      var raf = 0, nx = 0, ny = 0;
      var host = scene.closest('.login-right') || scene;
      host.addEventListener('pointermove', function (e) {
        var r = host.getBoundingClientRect();
        nx = (e.clientX - r.left) / r.width - 0.5; ny = (e.clientY - r.top) / r.height - 0.5;
        if (!raf) raf = requestAnimationFrame(function () {
          scene.style.setProperty('--mx', nx.toFixed(3)); scene.style.setProperty('--my', ny.toFixed(3)); raf = 0;
        });
      });
      host.addEventListener('pointerleave', function () { scene.style.setProperty('--mx', 0); scene.style.setProperty('--my', 0); });
    });
  }

  /* 3. Kekuatan kata sandi + kecocokan konfirmasi (halaman registrasi) */
  function passwordMeter() {
    var pw = doc.getElementById('reg-password'), cf = doc.getElementById('reg-password-confirm');
    var meter = doc.querySelector('.pw-meter'), hint = doc.querySelector('.pw-hint');
    if (!pw || !meter || !hint) return;
    var labels = ['', 'Lemah — tambahkan huruf besar, angka, atau simbol', 'Cukup', 'Baik', 'Kuat'];
    function score(v) {
      if (!v) return 0;
      var s = 0;
      if (v.length >= 8) s++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
      if (/\d/.test(v)) s++;
      if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) s++;
      return Math.max(s, v.length < 8 ? 1 : s);
    }
    function update() {
      var s = score(pw.value);
      meter.setAttribute('data-s', s);
      hint.className = 'pw-hint' + (s >= 3 ? ' ok' : (s ? ' bad' : ''));
      hint.textContent = pw.value.length >= 8 ? labels[s] : '';
      if (cf && cf.value) match();
    }
    function match() {
      var t = doc.getElementById('pw-match'); if (!t) return;
      var ok = cf.value === pw.value;
      t.textContent = cf.value ? (ok ? 'Kata sandi cocok' : 'Konfirmasi belum cocok') : '';
      t.className = 'pw-hint ' + (ok ? 'ok' : 'bad');
    }
    pw.addEventListener('input', update);
    if (cf) cf.addEventListener('input', match);
  }


  /* 4. Partikel lembut (hanya di wadah dekoratif, dijeda saat tak terlihat) */
  function particles() {
    if (reduce) return;
    var n = window.innerWidth < 700 ? 6 : 14;
    all('.hero, .login-right, .kat-hero, .hello-banner').forEach(function (host) {
      var wrap = doc.createElement('div'); wrap.className = 'dl-particles'; wrap.setAttribute('aria-hidden', 'true');
      for (var i = 0; i < n; i++) {
        var p = doc.createElement('i'); p.className = 'dl-pt';
        var sz = 3 + Math.random() * 6;
        p.style.cssText = 'left:' + (Math.random() * 100).toFixed(1) + '%;top:' + (30 + Math.random() * 70).toFixed(1) + '%;width:' + sz.toFixed(1) + 'px;height:' + sz.toFixed(1) + 'px;animation-duration:' + (11 + Math.random() * 12).toFixed(1) + 's;animation-delay:-' + (Math.random() * 14).toFixed(1) + 's';
        wrap.appendChild(p);
      }
      host.insertBefore(wrap, host.firstChild);
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (es) { wrap.classList.toggle('paused', !es[0].isIntersecting); }).observe(host);
      }
    });
  }

  /* 5. Tilt 3D ringan pada kartu (mouse saja) */
  function tilt() {
    if (reduce || !window.matchMedia('(pointer:fine)').matches) return;
    all('.kb-card, .dl-cat, .dl-pop a, .mini-grid a').forEach(function (el) {
      el.classList.add('dl-tilt');
      el.addEventListener('pointermove', function (e) {
        var r = el.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5, y = (e.clientY - r.top) / r.height - 0.5;
        el.style.setProperty('--ty', (x * 8).toFixed(2) + 'deg'); el.style.setProperty('--tx', (-y * 8).toFixed(2) + 'deg');
      });
      el.addEventListener('pointerleave', function () { el.style.removeProperty('--tx'); el.style.removeProperty('--ty'); });
    });
  }

  /* 6. Notifikasi sukses: ikon centang tergambar + percikan, hilang sendiri */
  function successToast() {
    all('.alert-success').forEach(function (el) {
      if (el.querySelector('.dl-check')) return;
      var c = doc.createElement('span'); c.className = 'dl-check'; c.setAttribute('aria-hidden', 'true');
      c.innerHTML = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M7 12.5l3.2 3.2L17 9"/></svg>';
      if (!reduce) for (var i = 0; i < 8; i++) { var b = doc.createElement('b'); b.style.setProperty('--a', (i * 45) + 'deg'); c.appendChild(b); }
      el.insertBefore(c, el.firstChild);
      el.classList.add('dl-toast');
      setTimeout(function () { el.classList.add('dl-toast-out'); }, 7000);
    });
  }

  /* 7. Skeleton saat pencarian/filter/pagination memuat halaman baru */
  function skeletons() {
    function busy(e) {
      // Ctrl/Cmd/Shift/klik tengah membuka tab baru & submit yang dibatalkan: halaman ini tidak berpindah
      if (e && (e.defaultPrevented || e.button > 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey)) return;
      doc.body.classList.add('dl-busy');
    }
    all('form[method="GET"], form[method="get"]').forEach(function (f) { f.addEventListener('submit', busy); });
    all('.pagination a, .kat-side a').forEach(function (a) { a.addEventListener('click', busy); });
    window.addEventListener('pageshow', function () { doc.body.classList.remove('dl-busy'); });
  }

  /* 8. Validasi real-time registrasi (server tetap menjadi penentu akhir) */
  function liveValidate() {
    var form = doc.querySelector('form[action*="register"]');
    if (!form) return;
    var rules = {
      name:  function (v) { return v.trim().length >= 3 ? '' : 'Nama minimal 3 karakter.'; },
      kelas: function (v) { return v.trim() ? '' : 'Kelas wajib diisi.'; },
      nis:   function (v) { return /^[A-Za-z0-9.\-\/]+$/.test(v.trim()) ? '' : 'NIS hanya huruf, angka, titik, strip, atau garis miring.'; },
      email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? '' : 'Format email belum benar.'; },
      password: function (v) { return v.length >= 8 ? '' : 'Kata sandi minimal 8 karakter.'; },
      password_confirmation: function (v) { var p = form.querySelector('[name=password]'); return v === (p ? p.value : '') ? '' : 'Konfirmasi belum cocok.'; }
    };
    Object.keys(rules).forEach(function (name) {
      var input = form.querySelector('[name="' + name + '"]'); if (!input) return;
      var holder = input.closest('.form-field') || input;
      var msg = doc.createElement('div'); msg.className = 'fld-msg'; msg.setAttribute('aria-live', 'polite');
      if (name !== 'password' && name !== 'password_confirmation') holder.parentNode.insertBefore(msg, holder.nextSibling);
      else { var after = name === 'password' ? doc.querySelector('.pw-hint') : doc.getElementById('pw-match'); if (after) after = after.nextSibling; holder.parentNode.insertBefore(msg, name === 'password' ? holder.nextSibling : holder.nextSibling); }
      var touched = false;
      function check() {
        if (!touched) return;
        var e = rules[name](input.value);
        msg.textContent = e; msg.className = 'fld-msg' + (e ? ' bad' : ' ok');
        input.setAttribute('aria-invalid', e ? 'true' : 'false');
        holder.classList.toggle('is-bad', !!e); holder.classList.toggle('is-ok', !e && !!input.value);
      }
      input.addEventListener('blur', function () { touched = true; check(); });
      input.addEventListener('input', check);
      if (name === 'password') input.addEventListener('input', function () { var c = form.querySelector('[name=password_confirmation]'); if (c && c.value) c.dispatchEvent(new Event('input')); });
    });
  }

  ready(function () { splitText(); parallax(); passwordMeter(); particles(); tilt(); successToast(); skeletons(); liveValidate(); });
  /* ---------- V2: indikator navbar, rotating words, highlight kata penting ---------- */
  function navIndicator() {
    var wrap = doc.querySelector('.nav-links'); if (!wrap) return;
    var links = all('a', wrap); if (!links.length) return;
    var ind = doc.createElement('i'); ind.className = 'nav-ind'; ind.setAttribute('aria-hidden', 'true'); wrap.insertBefore(ind, wrap.firstChild);
    function to(a, instant) {
      if (!a) { wrap.classList.remove('has-ind'); return; }
      if (instant) ind.style.transition = 'none';
      ind.style.width = a.offsetWidth + 'px';
      ind.style.transform = 'translateX(' + a.offsetLeft + 'px)';
      wrap.classList.add('has-ind');
      if (instant) { void ind.offsetWidth; ind.style.transition = ''; }
    }
    function home(instant) { to(wrap.querySelector('a.active'), instant); }
    links.forEach(function (a) {
      a.addEventListener('mouseenter', function () { to(a); });
      a.addEventListener('focus', function () { to(a); });
      a.addEventListener('blur', function () { home(); });
    });
    wrap.addEventListener('mouseleave', function () { home(); });
    window.addEventListener('resize', function () { home(true); });
    home(true);
    if (doc.fonts && doc.fonts.ready) doc.fonts.ready.then(function () { home(true); });
  }

  function rotators() {
    all('[data-rotate]').forEach(function (el) {
      var words = (el.getAttribute('data-rotate') || '').split('|').map(function (w) { return w.trim(); }).filter(Boolean);
      if (words.length < 2 || reduce) return;
      var i = 0, w = el.querySelector('.rot-w'); if (!w) return;
      setInterval(function () {
        if (doc.hidden) return;
        w.classList.remove('in'); w.classList.add('out');
        setTimeout(function () {
          i = (i + 1) % words.length; w.textContent = words[i];
          w.classList.remove('out'); void w.offsetWidth; w.classList.add('in');
        }, 340);
      }, 3200);
    });
  }

  function highlights() {
    var els = all('.hl'); if (!els.length) return;
    if (reduce || !('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('hl-on'); }); return; }
    var io = new IntersectionObserver(function (en) {
      en.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('hl-on'); io.unobserve(x.target); } });
    }, { threshold: 0.6 });
    els.forEach(function (e) { io.observe(e); });
    setTimeout(function () { els.forEach(function (e) { e.classList.add('hl-on'); }); }, 5000); // pengaman
  }

  ready(function () {
    [navIndicator, rotators, highlights].forEach(function (fn) { try { fn(); } catch (e) { if (window.console) console.error('[elibra-v2]', e); } });
  });
})();
