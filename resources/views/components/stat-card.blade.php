{{--
    Dashboard statistic tile.

    Usage:
    <x-stat-card icon="people" label="Students" :value="$stats['students']" tone="primary" />
--}}
@props([
    'icon' => 'graph-up',
    'label',
    'value',
    'tone' => 'primary',
    'href' => null,
])

@php($tag = $href ? 'a' : 'div')

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'card stat-card h-100 text-decoration-none'.($href ? ' stretched-link' : '')]) }}>
    <div class="card-body d-flex align-items-center gap-3">
        <span class="stat-icon text-{{ $tone }}-bg bg-{{ $tone }}-subtle">
            <i class="bi bi-{{ $icon }}"></i>
        </span>
        <div>
            <div class="text-muted small">{{ $label }}</div>
            <div class="fs-4 fw-bold lh-1">{{ $value }}</div>
        </div>
    </div>
</{{ $tag }}>