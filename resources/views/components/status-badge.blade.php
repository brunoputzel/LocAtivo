@props(['status'])

@php
    // classes por extenso (não interpolar o token) - o scanner do Tailwind
    // só encontra classes que aparecem literais no arquivo
    $classes = match ($status->corToken()) {
        'disponivel' => 'bg-status-disponivel/10 text-status-disponivel',
        'locacao' => 'bg-status-locacao/10 text-status-locacao',
        'manutencao' => 'bg-status-manutencao/10 text-status-manutencao',
        'inativo' => 'bg-status-inativo/10 text-status-inativo',
        'cancelado' => 'bg-status-cancelado/10 text-status-cancelado',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap {$classes}"]) }}>
    {{ $status->label() }}
</span>
