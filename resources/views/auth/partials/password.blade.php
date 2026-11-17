{{-- Input kata sandi + tombol tampilkan. $id, $name, $label, $placeholder, $autocomplete, $attrs --}}
<label class="au-label" for="{{ $id }}">{{ $label }}</label>
<div class="au-field @error($name) invalid @enderror">
    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V8a4 4 0 018 0v3"/></svg>
    <input type="password" id="{{ $id }}" name="{{ $name }}" required autocomplete="{{ $autocomplete }}" placeholder="{{ $placeholder }}" {!! $attrs ?? '' !!}>
    <button type="button" class="au-eye" data-au-eye="{{ $id }}" aria-pressed="false" aria-label="Tampilkan kata sandi">
        <svg class="on" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg class="off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.9 10.9 0 0112 20c-7 0-10-8-10-8a18.4 18.4 0 015.06-5.94M9.9 4.24A10.9 10.9 0 0112 4c7 0 10 8 10 8a18.5 18.5 0 01-2.16 3.19M14.12 14.12a3 3 0 11-4.24-4.24M1 1l22 22"/></svg>
    </button>
</div>
