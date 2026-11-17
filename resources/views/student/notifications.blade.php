@extends('layouts.panel')
@section('title', 'Notifikasi')
@section('heading', 'Notifikasi')
@section('subheading', 'Status peminjaman dan pengingat jatuh tempo.')
@section('action')
    @if($items->contains(fn ($n) => $n->isUnread()))
        <form method="POST" action="{{ route('student.notifications.read') }}">
            @csrf
            <button type="submit" class="btn-solid" style="border:none;cursor:pointer;font-family:inherit;">Tandai semua dibaca</button>
        </form>
    @endif
@endsection
@section('panel')
    <div class="panel">
        @forelse($items as $n)
            @php
                $tone = match($n->type) {
                    'approved', 'returned' => 'ok', 'rejected', 'overdue' => 'bad', 'due_soon' => 'warn', default => 'info',
                };
            @endphp
            <div class="notif-item {{ $tone }} {{ $n->isUnread() ? 'unread' : '' }}">
                <span class="dot"></span>
                <div class="body">
                    <div class="t">{{ $n->title }}</div>
                    <div class="m">{{ $n->message }}</div>
                    <div class="w">{{ $n->created_at->diffForHumans() }}</div>
                </div>
            </div>
        @empty
            @include('partials.empty', ['type'=>'bell','title'=>'Belum ada notifikasi','text'=>'Status pengajuan dan pengingat tenggat akan muncul di sini.'])
        @endforelse
    </div>
    <div class="pagination">
        {{ $items->links('vendor.pagination.elibra') }}
    </div>
@endsection
