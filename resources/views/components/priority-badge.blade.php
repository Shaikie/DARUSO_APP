{{--
    Priority badge for announcements and notifications.
--}}
@props(['priority'])

@if ($priority)
    <span {{ $attributes->merge(['class' => 'badge '.$priority->badgeClass()]) }}>
        <i class="bi bi-circle-fill me-1" style="font-size:.5rem;vertical-align:middle"></i>
        {{ $priority->label() }}
    </span>
@endif