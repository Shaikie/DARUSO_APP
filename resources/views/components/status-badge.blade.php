{{--
    Status badge for a backed enum with label() and badgeClass() helpers.

    Usage: <x-status-badge :status="$announcement->status" />
--}}
@props(['status'])

@if ($status)
    <span {{ $attributes->merge(['class' => 'badge '.($status->badgeClass ?? 'text-bg-secondary')]) }}>
        {{ $status->label ?? $status->value }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'badge text-bg-secondary']) }}>—</span>
@endif