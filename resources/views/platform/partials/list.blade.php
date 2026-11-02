<x-app-layout>
    @section('title', $heading)
    @section('eyebrow', 'DARUSO WORKSPACE')
    @section('heading', $heading)
    @section('subheading', $description ?? '')
    @section('actions')
        @if (!empty($createRoute))
            <a class="btn btn-primary" href="{{ route($createRoute) }}"><i class="bi bi-plus-lg me-1"></i>{{ $createLabel ?? 'Create new' }}</a>
        @endif
    @endsection
    <section class="daruso-panel">
        @forelse($items as $item)
            @php
                $label = $item->title ?? $item->subject ?? $item->email ?? ('Record #'.$item->getKey());
                $meta = $item->description ?? $item->message ?? $item->role_name ?? $item->summary ?? $item->email ?? '';
                $state = $item->status ?? null;
                $stateLabel = is_object($state) && method_exists($state, 'label') ? $state->label() : (is_scalar($state) ? ucfirst((string) $state) : null);
                $url = !empty($detailRoute) ? route($detailRoute, $item) : null;
            @endphp
            <div class="daruso-list-item">
                <span class="daruso-list-icon"><i class="bi bi-card-text"></i></span>
                <span class="flex-grow-1 min-w-0">
                    @if($url)<a href="{{ $url }}" class="fw-semibold d-block">{{ $label }}</a>@else<strong class="d-block">{{ $label }}</strong>@endif
                    <small>{{ \Illuminate\Support\Str::limit(strip_tags((string) $meta), 150) }}</small>
                    @if($item->created_at)<small class="d-block">{{ $item->created_at->diffForHumans() }}</small>@endif
                </span>
                @if($stateLabel)<span class="badge text-bg-light">{{ $stateLabel }}</span>@endif
            </div>
        @empty
            <div class="daruso-empty py-5 text-center"><i class="bi bi-inbox fs-2"></i><h3 class="h6 mt-2">Nothing here yet</h3><p class="text-muted small mb-0">New items will appear here when they are created.</p></div>
        @endforelse
        @if(method_exists($items, 'links'))<div class="p-3">{{ $items->links() }}</div>@endif
    </section>
</x-app-layout>