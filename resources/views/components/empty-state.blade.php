{{--
    Empty-state placeholder for lists with no records.

    This is a genuine "nothing here yet" state, not a stand-in for missing
    implementation.
--}}
@props([
    'icon' => 'inbox',
    'title' => 'Nothing to show yet',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-5']) }}>
    <i class="bi bi-{{ $icon }} fs-1 text-muted"></i>
    <p class="mt-3 mb-1 fw-semibold">{{ $title }}</p>
    @if ($description)
        <p class="text-muted small mb-0">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-3">{{ $action }}</div>
    @endif
</div>