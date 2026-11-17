/* =====================================================================
   E-LIBRA — Animasi teks, banner sapaan, dan menu responsif
   Progressive enhancement: jika gagal dimuat, web tetap normal.
   ===================================================================== */
(function () {
  'use strict';

  var doc = document;
  var reduce = window.ELIBRA_REDUCE === true; /* hanya true bila tombol Animasi dimatikan */

  function all(sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); }
  function ready(fn) { if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', fn); else fn(); }

  /* ---------------------------------------------------------------
     1. Judul: kata naik satu per satu
     --------------------------------------------------------------- */
  var HEADINGS = '.hero h1, .kat-hero h1, .login-form-wrap h2, .app-topbar h1, .detail-info h1, ' +
                 '.section-head h2, .cta-band h2, .hb-title';

  function splitWords(el) {
    var n = 0;
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (c) {
        if (c.nodeType === 3) {
          var parts = c.nodeValue.split(/(\s+)/);
          var frag = doc.createDocumentFragment();
          parts.forEach(function (p) {
            if (!p) return;
            if (/^\s+$/.test(p)) { frag.appendChild(doc.createTextNode(' ')); return; }
            var w = doc.createElement('span'); w.className = 'tw';
            var i = doc.createElement('span'); i.className = 'tw-i';
            i.style.setProperty('--i', n++);
            i.textContent = p; w.appendChild(i); frag.appendChild(w);
          });
          c.parentNode.replaceChild(frag, c);
        } else if (c.nodeType === 1 && !/^(svg|script|style)$/i.test(c.tagName)) {
          walk(c);
        }
      });
    })(el);
    return n;
  }

  function initHeadings() {
    var els = all(HEADINGS).filter(function (el) { return !el.hasAttribute('data-no-split'); });
    if (reduce) return;
    var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('tw-go'); io.unobserve(en.target); }
      });
    }, { threshold: 0.2 }) : null;

    els.forEach(function (el) {
      if (el.querySelector('.tw')) return;
      el.setAttribute('aria-label', el.textContent.replace(/\s+/g, ' ').trim());
      splitWords(el);
      // semua kata disembunyikan dari pembaca layar; label di atas yang dibaca
      all('.tw', el).forEach(function (t) { t.setAttribute('aria-hidden', 'true'); });
      if (io) io.observe(el); else el.classList.add('tw-go');
    });
    // pengaman
    setTimeout(function () { els.forEach(function (el) { el.classList.add('tw-go'); }); }, 3500);
  }

  /* ---------------------------------------------------------------
     2. Teks pendek diketik (subjudul)
     --------------------------------------------------------------- */
  function typeInto(el, text, speed, startDelay, done) {
    var i = 0;
    el.classList.add('ex-typing');
    setTimeout(function tick() {
      i++;
      el.textContent = text.slice(0, i);
      if (i < text.length) setTimeout(tick, speed + Math.random() * 14);
      else { el.classList.remove('ex-typing'); el.classList.add('ex-typing', 'ex-typed-done'); if (done) done(); }
    }, startDelay);
  }

  function initTyping() {
    if (reduce) return;
    var targets = all('.hero p.lead, .kat-hero > p, .login-form-wrap p.sub, .app-topbar p');
    targets.forEach(function (el, idx) {
      if (el.children.length) return;           // hanya teks polos
      var text = el.textContent.trim();
      if (!text || text.length > 200) return;
      el.setAttribute('aria-label', text);
      el.style.minHeight = el.offsetHeight + 'px'; // jaga tinggi supaya layout tidak loncat
      el.textContent = '';
      typeInto(el, text, 16, 600 + idx * 80);
    });
  }

  /* ---------------------------------------------------------------
     3. Banner sapaan: salam, tanggal, jam, tips berganti
     --------------------------------------------------------------- */
  function initHello() {
    var hb = doc.querySelector('.hello-banner');
    if (!hb) return;
    var hour = new Date().getHours();
    var greet = hour < 11 ? 'pagi' : hour < 15 ? 'siang' : hour < 18 ? 'sore' : 'malam';
    var g = hb.querySelector('[data-greet]'); if (g) g.textContent = greet;

    var d = hb.querySelector('[data-today]');
    if (d) {
      try { d.textContent = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }
      catch (e) { /* biarkan teks bawaan */ }
    }

    var clock = hb.querySelector('[data-clock]');
    if (clock) {
      (function tick() {
        var t = new Date();
        clock.textContent = ('0' + t.getHours()).slice(-2) + ':' + ('0' + t.getMinutes()).slice(-2);
        setTimeout(tick, 15000);
      })();
    }

    var tipEl = hb.querySelector('[data-typed]');
    if (tipEl) {
      var tips = [];
      try { tips = JSON.parse(tipEl.getAttribute('data-typed')) || []; } catch (e) {}
      if (tips.length > 1 && !reduce) {
        var k = 0;
        (function next() {
          typeInto(tipEl, tips[k % tips.length], 24, 300, function () {
            k++; tipEl.classList.remove('ex-typing', 'ex-typed-done');
            setTimeout(function () { tipEl.textContent = ''; next(); }, 3800);
          });
        })();
      }
    }
  }

  /* ---------------------------------------------------------------
     4. Menu responsif: hamburger navbar & sidebar dashboard
     --------------------------------------------------------------- */
  function makeBurger(label) {
    var b = doc.createElement('button');
    b.type = 'button'; b.className = 'ex-burger';
    b.setAttribute('aria-label', label); b.setAttribute('aria-expanded', 'false');
    b.innerHTML = '<span></span>';
    return b;
  }

  function initNav() {
    var nav = doc.querySelector('.nav');
    if (!nav) return;
    var b = makeBurger('Buka menu');
    var brand = nav.querySelector('.brand');
    if (brand && brand.nextSibling) nav.insertBefore(b, brand.nextSibling); else nav.appendChild(b);
    b.addEventListener('click', function () {
      var open = nav.classList.toggle('ex-open');
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  function initSidebar() {
    var side = doc.querySelector('.app-side');
    var top = doc.querySelector('.app-topbar');
    if (!side || !top) return;
    var b = makeBurger('Buka menu samping');
    top.insertBefore(b, top.firstChild);
    var scrim = doc.createElement('div'); scrim.className = 'ex-scrim'; doc.body.appendChild(scrim);

    function set(open) {
      side.classList.toggle('open', open);
      scrim.classList.toggle('show', open);
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    b.addEventListener('click', function () { set(!side.classList.contains('open')); });
    scrim.addEventListener('click', function () { set(false); });
    doc.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
    window.addEventListener('resize', function () { if (window.innerWidth > 900) set(false); });
  }

  /* tabel lebar → bisa digeser */
  function initTables() {
    all('table').forEach(function (t) {
      var p = t.parentNode;
      if (!p || p.classList.contains('ex-scroll')) return;
      var w = doc.createElement('div'); w.className = 'ex-scroll';
      p.insertBefore(w, t); w.appendChild(t);
    });
  }

  ready(function () {
    try { initNav(); } catch (e) {}
    try { initSidebar(); } catch (e) {}
    try { initTables(); } catch (e) {}
    try { initHello(); } catch (e) {}
    try { initHeadings(); } catch (e) {}
    try { initTyping(); } catch (e) {}
  });
})();
