@props(['user', 'class' => 'av'])
<div class="{{ $class }}">
    @if($user->photo_url)
        <img src="{{ $user->photo_url }}" alt="Foto {{ $user->name }}">
    @else
        {{ $user->initials }}
    @endif
</div>
