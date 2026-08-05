{{-- SmartRecruit pill button. Usage:
    <x-btn href="/jobs" variant="primary" size="lg" icon="arrow-right">Explore jobs</x-btn>
    <x-btn type="button" variant="ghost" wire:click="x">Cancel</x-btn>
    Variants: primary (#f43f85 pill), accent, ghost, subtle, danger.
    Sizes: sm, md (default), lg. Icon: any x-icon name.
--}}
@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'class' => '',
])

@php
    $variants = [
        'primary' => 'bg-secondary text-white hover:shadow-[0_12px_28px_rgb(244_63_133/0.28)] hover:-translate-y-0.5',
        'accent'  => 'bg-accent text-dark hover:-translate-y-0.5',
        'ghost'   => 'bg-transparent text-body border border-line hover:border-secondary hover:text-dark hover:-translate-y-0.5',
        'subtle'  => 'bg-surface text-dark hover:bg-white hover:-translate-y-0.5',
        'danger'  => 'bg-danger text-white hover:-translate-y-0.5',
    ];
    $sizes = [
        'sm' => 'px-4 py-2 text-sm gap-2',
        'md' => 'px-7 py-3 text-base gap-2.5',
        'lg' => 'px-9 py-4 text-lg gap-3',
    ];
    $base = 'inline-flex items-center justify-center rounded-pill font-semibold cursor-pointer border-0 transition-all duration-[400ms] ease-[cubic-bezier(0.32,0.72,0,1)] active:translate-y-0 select-none';
    $classes = trim($base . ' ' . $variants[$variant] . ' ' . $sizes[$size] . ' ' . $class);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon) <x-icon :name="$icon" /> @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon) <x-icon :name="$icon" /> @endif
        {{ $slot }}
    </button>
@endif