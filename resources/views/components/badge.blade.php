{{-- SmartRecruit tone badge (small pill label). Usage:
    <x-badge tone="pink">AI Powered</x-badge>
    Tones: neutral, pink, blue, green, amber, purple — light fills, tinted text.
--}}
@props([
    'tone' => 'neutral',
    'class' => '',
])

@php
    $tones = [
        'neutral' => 'bg-surface text-body',
        'pink'    => 'bg-secondary/10 text-secondary',
        'blue'    => 'bg-accent/20 text-textaccent',
        'green'   => 'bg-ok-bg text-ok',
        'amber'   => 'bg-warn-bg text-warn',
        'purple'  => 'bg-section-purple/30 text-[#6c34b8]',
    ];
    $classes = trim('inline-flex items-center gap-1.5 rounded-pill px-3 py-1 text-xs font-semibold whitespace-nowrap ' . $tones[$tone] . ' ' . $class);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>