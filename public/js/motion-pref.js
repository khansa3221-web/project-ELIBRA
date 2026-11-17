/* E-LIBRA — preferensi animasi. Dimuat PERTAMA (sinkron di <head>).
   Animasi default HIDUP (tidak mengikuti pengaturan hemat-gerak OS, karena OS yang
   mematikan animasi membuat seluruh situs terlihat diam). Pengguna bisa mematikannya lewat
   tombol "Animasi" di pojok kiri bawah; pilihan disimpan di localStorage. */
(function () {
  var KEY = 'elibra-motion', off = false;
  try { off = localStorage.getItem(KEY) === 'off'; } catch (e) {}
  window.ELIBRA_REDUCE = off;
  var root = document.documentElement;
  if (off) root.classList.add('no-motion'); else root.classList.add('has-motion');

  document.addEventListener('DOMContentLoaded', function () {
    var b = document.createElement('button');
    b.type = 'button'; b.className = 'mb-toggle';
    b.setAttribute('aria-pressed', off ? 'false' : 'true');
    b.innerHTML = '<span class="mb-tg-dot"></span><span class="mb-tg-t">Animasi: ' + (off ? 'Mati' : 'Hidup') + '</span>';
    b.title = 'Hidupkan / matikan animasi';
    b.addEventListener('click', function () {
      try { localStorage.setItem(KEY, off ? 'on' : 'off'); } catch (e) {}
      location.reload();
    });
    document.body.appendChild(b);
  });
})();
