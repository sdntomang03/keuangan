@props([
'action',
'method' => 'POST',
'title' => 'Apakah Anda yakin?',
'text' => 'Tindakan ini tidak dapat dibatalkan!'
])

<form action="{{ $action }}" method="POST" class="inline-block m-0 p-0">
    @csrf
    @if(strtoupper($method) !== 'POST')
    @method($method)
    @endif

    <button type="button" onclick="confirmSubmit(this.closest('form'), '{{ $title }}', '{{ $text }}')" {{
        $attributes->merge(['class' => 'inline-flex items-center justify-center font-bold transition shadow-sm']) }}>
        {{ $slot }}
    </button>
</form>