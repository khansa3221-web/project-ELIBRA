<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bukti Peminjaman {{ $r['trx'] }} - E-LIBRA</title>
<style>
  :root{ --navy:#12294B; --teal:#17707A; --gold:#C99A4B; --gray:#5B6B7E; --line:#D9E1E6; --soft:#F2F6F7; }
  *{ box-sizing:border-box; }
  body{ margin:0; background:#EEF2F4; font-family:'Manrope','Segoe UI',Arial,sans-serif; color:var(--navy); }
  .toolbar{ max-width:820px; margin:22px auto 0; padding:0 16px; display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; }
  .toolbar .left, .toolbar .right{ display:flex; gap:10px; flex-wrap:wrap; }
  .btn{ display:inline-block; padding:11px 18px; border-radius:10px; font-size:13px; font-weight:800; text-decoration:none; border:1.5px solid var(--navy); color:var(--navy); background:#fff; cursor:pointer; font-family:inherit; }
  .btn.primary{ background:var(--teal); border-color:var(--teal); color:#fff; }
  .btn.dark{ background:var(--navy); color:#fff; }
  .sheet{ max-width:820px; margin:16px auto 40px; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 18px 40px -22px rgba(18,41,75,.45); }
  .head{ background:var(--navy); color:#fff; padding:30px 40px 26px; display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; border-bottom:4px solid var(--gold); }
  .head h1{ margin:0; font-size:28px; letter-spacing:.5px; }
  .head p{ margin:4px 0 0; font-size:12px; color:#C9D6E3; }
  .head .doc{ text-align:right; }
  .head .doc b{ font-size:16px; display:block; }
  .body{ padding:28px 40px 12px; }
  .trx{ display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; padding-bottom:18px; border-bottom:1px solid var(--line); }
  .lbl{ font-size:10px; font-weight:800; letter-spacing:1px; color:var(--gray); }
  .trx .no{ font-size:26px; font-weight:800; margin-top:4px; }
  .pill{ color:#fff; font-size:12px; font-weight:800; padding:9px 18px; border-radius:8px; text-transform:uppercase; letter-spacing:.5px; }
  .pill.approved,.pill.active{ background:#17707A; } .pill.late{ background:#C0392B; } .pill.returned{ background:#2F8F67; }
  h2{ font-size:11px; letter-spacing:1px; color:var(--teal); margin:26px 0 4px; padding-bottom:6px; border-bottom:1.5px solid var(--teal); }
  .row{ display:flex; gap:16px; padding:10px 0; border-bottom:1px solid #E8EEF1; font-size:14px; }
  .row .k{ width:190px; flex:none; color:var(--gray); }
  .row .v{ font-weight:800; }
  .terms{ margin:26px 0 0; background:var(--soft); border-left:4px solid var(--teal); padding:16px 20px; font-size:12.5px; line-height:1.7; }
  .terms b{ color:var(--teal); font-size:11px; letter-spacing:1px; }
  .terms ol{ margin:6px 0 0; padding-left:18px; }
  .sign{ display:flex; justify-content:space-between; margin:34px 30px 10px; font-size:12px; color:var(--gray); text-align:center; }
  .sign div{ width:200px; } .sign .line{ border-bottom:1px solid var(--navy); height:62px; margin-bottom:6px; } .sign b{ color:var(--navy); }
  .foot{ text-align:center; font-size:11px; color:var(--gray); padding:16px 40px 22px; border-top:1px solid var(--line); margin-top:18px; }
  @media (max-width:600px){ .head,.body{ padding-left:20px; padding-right:20px; } .row{ flex-direction:column; gap:2px; } .row .k{ width:auto; } .sign{ margin:30px 0 10px; } }
  @page{ size:A4; margin:10mm; }
  @media print{
    body{ background:#fff; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .toolbar{ display:none !important; }
    .sheet{ margin:0; box-shadow:none; border-radius:0; max-width:none; }
  }
</style>
</head>
<body>
  <div class="toolbar">
    <div class="left"><a class="btn" href="{{ auth()->user()->role === 'admin' ? route('admin.loans') : route('student.loans') }}">&larr; Kembali</a></div>
    <div class="right">
      <button type="button" class="btn dark" onclick="window.print()">Cetak</button>
      <a class="btn primary" href="{{ route('loans.receipt.pdf', $loan) }}">Download PDF</a>
    </div>
  </div>

  <div class="sheet">
    <div class="head">
      <div>
        <h1>E-LIBRA</h1>
        <p>Perpustakaan Digital - SMK Negeri 5 Surakarta</p>
      </div>
      <div class="doc"><b>BUKTI PEMINJAMAN BUKU</b><p>Dokumen resmi peminjaman perpustakaan</p></div>
    </div>

    <div class="body">
      <div class="trx">
        <div><div class="lbl">NOMOR TRANSAKSI</div><div class="no">{{ $r['trx'] }}</div></div>
        <div><div class="lbl" style="text-align:right;margin-bottom:6px;">STATUS</div><span class="pill {{ $r['status_key'] }}">{{ $r['status'] }}</span></div>
      </div>

      <h2>DATA PEMINJAM</h2>
      <div class="row"><div class="k">Nama Siswa</div><div class="v">{{ $r['nama'] }}</div></div>
      <div class="row"><div class="k">NIS</div><div class="v">{{ $r['nis'] }}</div></div>
      <div class="row"><div class="k">Kelas</div><div class="v">{{ $r['kelas'] }}</div></div>

      <h2>DATA BUKU</h2>
      <div class="row"><div class="k">Judul Buku</div><div class="v">{{ $r['judul'] }}</div></div>
      <div class="row"><div class="k">Penulis</div><div class="v">{{ $r['penulis'] }}</div></div>
      <div class="row"><div class="k">Kategori</div><div class="v">{{ $r['kategori'] }}</div></div>
      @if($r['isbn'] !== '-')<div class="row"><div class="k">ISBN</div><div class="v">{{ $r['isbn'] }}</div></div>@endif

      <h2>DETAIL PEMINJAMAN</h2>
      <div class="row"><div class="k">Tanggal Pinjam</div><div class="v">{{ $r['tgl_pinjam'] }}</div></div>
      <div class="row"><div class="k">Lama Peminjaman</div><div class="v">{{ $r['lama'] }}</div></div>
      <div class="row"><div class="k">Tanggal Pengembalian</div><div class="v">{{ $r['tgl_kembali'] }}</div></div>
      <div class="row"><div class="k">Dikembalikan Pada</div><div class="v">{{ $r['dikembalikan'] }}</div></div>
      <div class="row"><div class="k">Status</div><div class="v">{{ $r['status'] }}</div></div>
      @if($r['denda'] > 0)<div class="row"><div class="k">Denda</div><div class="v">Rp{{ number_format($r['denda'], 0, ',', '.') }}</div></div>@endif

      <div class="terms">
        <b>KETENTUAN</b>
        <ol>
          <li>Tunjukkan bukti ini kepada petugas saat mengambil dan mengembalikan buku.</li>
          <li>Kembalikan buku paling lambat pada Tanggal Pengembalian di atas.</li>
          <li>Keterlambatan dikenakan denda Rp500 per hari. Jaga buku dari kerusakan dan kehilangan.</li>
        </ol>
      </div>

      <div class="sign">
        <div><span>Peminjam</span><div class="line"></div><b>{{ $r['nama'] }}</b></div>
        <div><span>Petugas Perpustakaan</span><div class="line"></div><b>&nbsp;</b></div>
      </div>
    </div>

    <div class="foot">Dicetak otomatis oleh sistem E-LIBRA pada {{ $r['dicetak'] }}</div>
  </div>
</body>
</html>
