/* E-LIBRA — Motion Boost: ikon melayang, muncul bertahap, dekorasi detail buku. Hanya MENAMBAH elemen dekoratif. */
(function () {
  'use strict';
  if (window.ELIBRA_REDUCE === true) return;
  var doc = document;
  function ready(fn){ if (doc.readyState !== 'loading') fn(); else doc.addEventListener('DOMContentLoaded', fn); }

  var ICONS = {
    book: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20V3H6.5A2.5 2.5 0 004 5.5z"/><path d="M8 7h8M8 11h5"/></svg>',
    mark: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12a1 1 0 011 1v19l-7-4.5L5 22V3a1 1 0 011-1z"/></svg>',
    star: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.9 6.6 7.1.6-5.4 4.7 1.6 7L12 17.3 5.8 20.9l1.6-7L2 9.2l7.1-.6z"/></svg>',
    spark: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1l2.4 8.6L23 12l-8.6 2.4L12 23l-2.4-8.6L1 12l8.6-2.4z"/></svg>'
  };

  function floaters() {
    if (doc.querySelector('.scene')) return;                 // login/registrasi sudah punya ilustrasi sendiri
    var layer = doc.createElement('div'); layer.className = 'mb-floaters'; layer.setAttribute('aria-hidden', 'true');
    var spec = [
      ['book', '2%', '14%', '46px', '7s', '0s'], ['mark', '95%', '22%', '36px', '8.5s', '-2s'], ['star', '4%', '58%', '34px', '6s', '-1s', 'alt'],
      ['spark', '93%', '64%', '30px', '3.2s', '-.5s', 'tw'], ['book', '96%', '86%', '40px', '9s', '-4s', 'alt'], ['mark', '1.5%', '84%', '32px', '7.5s', '-3s'],
      ['star', '90%', '8%', '28px', '5.5s', '-2.5s', 'alt'], ['spark', '8%', '36%', '24px', '3.6s', '-1.4s', 'tw']
    ];
    spec.forEach(function (s) {
      var d = doc.createElement('span'); d.className = 'mf' + (s[6] ? ' ' + s[6] : '');
      d.style.cssText = '--x:' + s[1] + ';--y:' + s[2] + ';--s:' + s[3] + ';--d:' + s[4] + ';--dl:' + s[5];
      d.innerHTML = ICONS[s[0]]; layer.appendChild(d);
    });
    doc.body.appendChild(layer);
  }

  function indexAll() {   // nomor urut per induk -> jeda animasi bertahap & berselang-seling
    ['.kb-card', '.stat-card', '.feat-card', '.bar-chart .bar'].forEach(function (sel) {
      var groups = new Map();
      doc.querySelectorAll(sel).forEach(function (el) {
        var p = el.parentElement && el.parentElement.parentElement; var n = groups.get(p) || 0;
        el.style.setProperty('--mb-i', n % 8); groups.set(p, n + 1);
      });
    });
  }

  function reveal() {
    if (!('IntersectionObserver' in window)) return;
    var sel = '.stat-card,.kb-card,.panel,.feat-card,.loan-row,.rv-item,.dm,.notif-item,.step-card';
    var els = [].slice.call(doc.querySelectorAll(sel)).filter(function (e) { return !e.classList.contains('rv') && !e.closest('.rv'); });
    var idx = new Map();
    els.forEach(function (e) {
      var p = e.parentElement; var n = idx.get(p) || 0; idx.set(p, n + 1);
      e.style.setProperty('--mb-i', Math.min(n, 8)); e.classList.add('mb-prep');
    });
    var io = new IntersectionObserver(function (list) {
      list.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('mb-in'); io.unobserve(en.target); } });
    }, { threshold: 0.08, rootMargin: '0px 0px -4% 0px' });
    els.forEach(function (e) { io.observe(e); });
    setTimeout(function () { els.forEach(function (e) { if (!e.classList.contains('mb-in')) { var r = e.getBoundingClientRect(); if (r.top < innerHeight) e.classList.add('mb-in'); } }); }, 2500);
  }

  function detailDeco() {
    var w = doc.querySelector('.detail-wrap'); if (!w) return;
    var o = doc.createElement('i'); o.className = 'mb-orbit'; o.setAttribute('aria-hidden', 'true'); w.insertBefore(o, w.firstChild);
    [['-6px', '30px', '0s'], ['250px', '-6px', '-.9s'], ['300px', '300px', '-1.7s']].forEach(function (p) {
      var s = doc.createElement('span'); s.className = 'mb-sp'; s.setAttribute('aria-hidden', 'true');
      s.style.cssText = 'left:' + p[0] + ';top:' + p[1] + ';--dl:' + p[2]; s.innerHTML = ICONS.spark; w.appendChild(s);
    });
  }

  ready(function () { try { indexAll(); floaters(); reveal(); detailDeco(); } catch (e) { if (window.console) console.warn('motion-boost', e); } });
})();
