@if(\App\Support\Assets::bundled())
<script src="{{ \App\Support\Assets::url('build/elibra.min.js') }}" defer></script>
@else
@foreach(\App\Support\Assets::JS as $js)
<script src="{{ \App\Support\Assets::url($js) }}" defer></script>
@endforeach
@endif
