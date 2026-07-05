@props(['variant' => 'primary'])

@php
    $arquivo = match ($variant) {
        'stacked' => 'logo-stacked.svg',
        'dark' => 'logo-primary-dark-bg.svg',
        'icon' => 'icon.svg',
        default => 'logo-primary.svg',
    };
@endphp

<img src="{{ asset('images/logo/'.$arquivo) }}" alt="LocAtivo" {{ $attributes }}>
