@if(session('success'))
    <div class="au-alert ok" role="status"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.5"/></svg><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="au-alert err" role="alert"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg><div>{{ session('error') }}</div></div>
@endif
@if($errors->any())
    <div class="au-alert err" role="alert"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
        <div>@if($errors->count() === 1){{ $errors->first() }}@else Periksa kembali isian berikut:<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif</div></div>
@endif
