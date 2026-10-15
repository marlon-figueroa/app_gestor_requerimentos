@props([
    'label' => null,
])

<div {{ $attributes->class(['page-action-group'])->merge(['role' => 'group']) }}
     @if ($label) aria-label="{{ $label }}" @endif>
    {{ $slot }}
</div>
