# Panduan Pengguna E-LIBRA 2.0

## Untuk siswa
**Daftar & masuk.** Buka halaman *Daftar*, isi nama, kelas, NIS (opsional), email, kata sandi min. 8 karakter. Masuk memakai email, NIS, atau nama (nama hanya jika tidak kembar).
Setelah 5 kali salah, login dikunci sebentar (±1 menit).

**Mencari buku.** Menu *Katalog Buku* → ketik judul/penulis/ISBN → pilih kategori, centang *Hanya yang tersedia*, atur urutan (Terpopuler, Stok terbanyak, Judul A–Z, Terbaru). Menekan kartu membuka detail buku; QR di detail bisa dipindai dari HP.

**Mengajukan peminjaman.** Di detail buku isi tanggal ambil & lama pinjam (1–30 hari) → *Ajukan Peminjaman*. Stok baru berkurang setelah petugas menyetujui. Satu buku hanya boleh diajukan sekali selama masih berjalan.

**Melihat status.** *Peminjaman Saya* (semua), *Riwayat* (sudah kembali/ditolak, total denda), *Notifikasi* (disetujui, ditolak + alasan, jatuh tempo, terlambat). Dashboard merangkum pinjaman aktif.

**Mengembalikan buku.** Bawa buku ke petugas; petugas menekan *Kembalikan*. Terlambat dikenai denda Rp500/hari (angka bisa diubah admin di kode: `Loan::FINE_PER_DAY`).

## Untuk admin / petugas
- **Kelola Buku:** tambah, ubah, hapus (diblokir jika masih ada pinjaman berjalan), *Impor CSV* (unduh template dulu), tombol **QR** (lihat/unduh PNG-SVG/cetak) dan *lembar QR semua buku*.
- **Pengajuan:** *Setujui* atau *Tolak* (alasan opsional). Pengajuan yang sudah diproses tidak bisa diproses lagi.
- **Peminjaman:** tab Sedang dipinjam / Terlambat / Dikembalikan / Ditolak / Semua, pencarian siswa/NIS/judul, tombol *Kembalikan* (stok & denda otomatis).
- **Anggota** dan **Dashboard** (statistik dari data sebenarnya).

## Catatan QR
Tautan QR memakai alamat situs saat halaman QR dibuka. Cetak label setelah situs dibuka lewat alamat yang dipakai siswa (domain/IP sekolah), bukan `localhost`.


**Bukti Peminjaman.** Di *Peminjaman Saya* atau *Riwayat*, klik **Bukti** pada peminjaman yang sudah disetujui. Halaman bukti berisi nomor transaksi, nama siswa, judul buku, tanggal pinjam, tanggal pengembalian, dan status. Tombol **Cetak** mencetak langsung, tombol **Download PDF** mengunduh file PDF-nya. Pengajuan yang masih menunggu atau ditolak belum punya bukti.
