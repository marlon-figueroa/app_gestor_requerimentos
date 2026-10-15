@props([
    'action',
    'method' => 'POST',
    'files' => false,
    'cancel' => null,
    'cancelLabel' => null,
    'submitLabel' => 'Guardar',
    'submitIcon' => 'save',
    'card' => true,
])

@php
    $metodo = strtoupper($method);
    $metodoHtml = $metodo === 'GET' ? 'GET' : 'POST';
@endphp

@if ($card)
    <div class="card">
        <div class="card-body">
@endif
            <form method="{{ $metodoHtml }}" action="{{ $action }}"
                  {{ $attributes->merge(['autocomplete' => 'off']) }}
                  @if ($files) enctype="multipart/form-data" @endif>
                @if ($metodo !== 'GET')
                    @csrf
                @endif
                @unless (in_array($metodo, ['GET', 'POST'], true))
                    @method($metodo)
                @endunless

                <div class="row g-3">
                    {{ $slot }}
                </div>

                @isset($footer)
                    {{ $footer }}
                @else
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        @if ($cancel)
                            <a href="{{ $cancel }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> {{ $cancelLabel ?? __('Cancel') }}
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-{{ $submitIcon }}"></i> {{ $submitLabel }}
                        </button>
                    </div>
                @endisset
            </form>
@if ($card)
        </div>
    </div>
@endif
