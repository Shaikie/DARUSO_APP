{{--
    Card wrapper used for every panel so spacing and radius stay uniform.

    Slots: title, actions, default, footer
--}}
<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm']) }}>
    @if (isset($title) || isset($actions))
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="h6 mb-0 fw-semibold">
                @isset($icon)<i class="bi bi-{{ $icon }} me-2 text-primary"></i>@endisset
                {{ $title ?? '' }}
            </h2>
            @isset($actions)
                <div class="d-flex gap-2">{{ $actions }}</div>
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="card-footer bg-white">{{ $footer }}</div>
    @endif
</div>