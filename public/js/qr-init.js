/* E-LIBRA — render QR (elemen [data-qr]) + tombol unduh SVG/PNG. Butuh qr.js. */
(function () {
  function render(el) {
    try {
      el.innerHTML = ElibraQR.svg(el.getAttribute('data-qr'), { margin: parseInt(el.getAttribute('data-margin') || '2', 10) });
      el.classList.add('ready');
    } catch (e) { el.textContent = 'QR gagal dibuat'; }
  }
  function save(blob, name) {
    var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = name;
    document.body.appendChild(a); a.click(); a.remove(); setTimeout(function () { URL.revokeObjectURL(a.href); }, 1500);
  }
  function download(btn) {
    var box = document.querySelector(btn.getAttribute('data-target')); if (!box) return;
    var svg = box.querySelector('svg'); if (!svg) return;
    var name = btn.getAttribute('data-filename') || 'qr-buku', fmt = btn.getAttribute('data-qr-download');
    var xml = new XMLSerializer().serializeToString(svg);
    if (fmt === 'svg') { save(new Blob([xml], { type: 'image/svg+xml' }), name + '.svg'); return; }
    var img = new Image(), size = 1024;
    img.onload = function () {
      var c = document.createElement('canvas'); c.width = c.height = size;
      var x = c.getContext('2d'); x.imageSmoothingEnabled = false; x.fillStyle = '#fff'; x.fillRect(0, 0, size, size);
      x.drawImage(img, 0, 0, size, size); c.toBlob(function (b) { save(b, name + '.png'); }, 'image/png');
    };
    img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
  }
  document.querySelectorAll('[data-qr]').forEach(render);
  document.querySelectorAll('[data-qr-download]').forEach(function (b) { b.addEventListener('click', function () { download(b); }); });
  document.querySelectorAll('[data-print]').forEach(function (b) { b.addEventListener('click', function () { window.print(); }); });
  document.querySelectorAll('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = b.getAttribute('data-copy'), old = b.textContent;
      (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(function () { b.textContent = 'Tersalin ✓'; setTimeout(function () { b.textContent = old; }, 1600); }, function () { window.prompt('Salin tautan:', t); });
    });
  });
})();
