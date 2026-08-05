{{-- Pipeline status pill for applications. Usage:
    <x-status-pill :status="$app->status" />
    Maps: received / interview / accepted / refused → readable label + color.
--}}
@props(['status' => 'received', 'class' => ''])

@php
    $map = [
        'received'  => ['label' => 'Received',  'classes' => 'bg-surface text-body'],
        'interview' => ['label' => 'Interview', 'classes' => 'bg-accent/20 text-textaccent'],
        'accepted'  => ['label' => 'Accepted',  'classes' => 'bg-ok-bg text-ok'],
        'refused'   => ['label' => 'Refused',   'classes' => 'bg-danger-bg text-danger'],
    ];
    $pill = $map[$status] ?? $map['received'];
@endphp

<span class="inline-flex items-center gap-1.5 rounded-pill px-3 py-1 text-xs font-semibold whitespace-nowrap {{ $pill['classes'] }} {{ $class }}">
    <span class="size-1.5 rounded-full bg-current opacity-70"></span>
    {{ $pill['label'] }}
</span>