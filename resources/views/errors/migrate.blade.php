<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Database perlu diperbarui — E-LIBRA</title>
<style>body{margin:0;font-family:system-ui,sans-serif;background:#FFF3F8;color:#39232E;display:flex;min-height:100vh;align-items:center;justify-content:center;padding:20px}
.c{max-width:560px;background:#fff;border:1px solid #FFDEEB;border-radius:20px;padding:28px;box-shadow:0 24px 50px -30px rgba(158,46,92,.5)}
h1{margin:0 0 8px;font-size:22px}code{background:#FFEAF2;padding:2px 7px;border-radius:6px}li{margin:3px 0;font-size:14px}</style></head>
<body><div class="c">
<h1>Database perlu diperbarui</h1>
<p>Kode E-LIBRA memerlukan struktur database yang lebih baru. Data lama Anda tidak dihapus.</p>
<p><b>Yang belum ada:</b></p>
<ul>@foreach($missing as $m)<li>{{ $m }}</li>@endforeach</ul>
<p><b>Perbaikan:</b> cadangkan <code>database/database.sqlite</code>, lalu jalankan <code>php artisan migrate</code> (atau klik dua kali <code>JALANKAN.bat</code> / <code>PERBAIKI-LOGIN.bat</code>), kemudian muat ulang halaman ini.</p>
</div></body></html>
