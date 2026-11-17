@if(session('success'))
    <div class="alert alert-success" style="margin:0 0 18px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error" style="margin:0 0 18px;">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error" style="margin:0 0 18px;">{{ $errors->first() }}</div>
@endif
