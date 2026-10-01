{{--
    Session flash feedback.

    Renders every documented channel in one place so pages stay consistent and no
    page has to hand-roll alerts.
--}}
@foreach (['success', 'error', 'warning', 'info', 'status'] as $type)
    @if (session()->has($type))
        <div class="alert alert-{{ $type === 'status' ? 'info' : $type }} alert-dismissible fade show d-flex align-items-start gap-2"
             role="alert">
            <i class="bi bi-{{ $type === 'success' ? 'check-circle-fill' : ($type === 'error' ? 'exclamation-octagon-fill' : 'info-circle-fill') }} mt-1"></i>
            <div class="flex-grow-1">{{ session($type) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-octagon-fill mt-1"></i>
            <div>
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif