/* E-LIBRA — loading state & cegah kirim ganda pada form (mis. dobel klik "Setujui"/"Ajukan"). */
(function () {
  function reset() {
    document.querySelectorAll('button.is-loading,input.is-loading').forEach(function (b) {
      b.disabled = false; b.classList.remove('is-loading');
      if (b.dataset.label) { b.innerHTML = b.dataset.label; delete b.dataset.label; }
    });
  }
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (e.defaultPrevented || !(f instanceof HTMLFormElement) || (f.method || '').toLowerCase() !== 'post') return;
    var btn = f.querySelector('button[type=submit],button:not([type]),input[type=submit]');
    if (!btn || btn.disabled) return;
    setTimeout(function () {            // ditunda agar data form tetap terkirim
      btn.dataset.label = btn.innerHTML; btn.classList.add('is-loading'); btn.disabled = true;
      if (btn.tagName === 'BUTTON') btn.innerHTML = '<span class="spin"></span> Memproses…';
    }, 0);
  });
  window.addEventListener('pageshow', function (e) { if (e.persisted) reset(); });   // tombol Back
})();
