@extends('layouts.app')
@section('title', 'Pinjaman Saya')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Pinjaman Saya</h4>
  <a href="{{ route('home') }}" class="btn btn-primary btn-sm">+ Pinjam Buku Lain</a>
</div>

<h6 class="text-muted">Sedang dipinjam</h6>
@forelse($active as $loan)
  <div class="card border-0 shadow-sm mb-2">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <a href="{{ route('books.show', $loan->book) }}" class="fw-semibold text-decoration-none">{{ $loan->book->title }}</a>
        <div class="small text-muted">Dipinjam {{ $loan->pickup_date->format('d M Y') }} · Kembali sebelum <strong>{{ $loan->due_date->format('d M Y') }}</strong></div>
      </div>
      <div class="text-end">
        @if($loan->is_overdue)
          <span class="badge bg-danger">Terlambat {{ $loan->late_days }} hari</span>
          <div class="small text-danger">Denda sementara Rp {{ number_format($loan->current_fine, 0, ',', '.') }}</div>
        @else
          <span class="badge bg-warning text-dark">Dipinjam</span>
        @endif
      </div>
    </div>
  </div>
@empty
  <div class="card border-0 shadow-sm mb-2"><div class="card-body text-center py-4">
    <p class="text-muted mb-2">Anda belum meminjam buku apa pun.</p>
    <a href="{{ route('home') }}" class="btn btn-primary">Cari Buku</a>
  </div></div>
@endforelse
@if($active->count())
  <p class="small text-muted mt-2">Untuk mengembalikan, bawa bukunya ke petugas perpustakaan. Petugas yang akan mencatatnya di sistem.</p>
@endif

<h6 class="text-muted mt-4">Riwayat</h6>
<div class="card border-0 shadow-sm"><div class="table-responsive">
  <table class="table mb-0 align-middle">
    <thead><tr><th>Buku</th><th>Dipinjam</th><th>Dikembalikan</th><th>Denda</th></tr></thead>
    <tbody>
    @forelse($history as $loan)
      <tr>
        <td><a href="{{ route('books.show', $loan->book) }}" class="text-decoration-none">{{ $loan->book->title }}</a></td>
        <td>{{ $loan->pickup_date->format('d M Y') }}</td>
        <td>{{ $loan->returned_at ? $loan->returned_at->format('d M Y') : '-' }}</td>
        <td>{{ $loan->fine > 0 ? 'Rp ' . number_format($loan->fine, 0, ',', '.') : '-' }}</td>
      </tr>
    @empty
      <tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat.</td></tr>
    @endforelse
    </tbody>
  </table>
</div></div>
<div class="mt-3">{{ $history->links('pagination::bootstrap-5') }}</div>
@endsection
