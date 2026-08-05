{{-- SmartRecruit card container. Usage:
    <x-card pad="md" class="max-w-xl">...</x-card>
    Padding: none, sm (16px), md (24px, default), lg (32px).
--}}
@props([
    'pad' => 'md',
    'class' => '',
])

@php
    $pads = [
        'none' => '',
        'sm'   => 'p-4',
        'md'   => 'p-6',
        'lg'   => 'p-8',
    ];
    $base = 'bg-white rounded-card border border-line shadow-tint';
    $classes = trim($base . ' ' . $pads[$pad] . ' ' . $class);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>