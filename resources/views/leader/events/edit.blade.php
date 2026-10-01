<x-app-layout>
    @section('title', 'Edit event')
    @section('heading', 'Edit event')
    @section('subheading', $event->title)

    @section('actions')
        <a href="{{ route('leader.events.show', $event) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    @php
        // Pre-select the current audience so editing does not silently retarget.
        $selectedAudience = $event->audienceRules->groupBy('audience_type')->map(
            fn ($rules) => $rules->pluck('audience_value')->filter()->values()->all()
        )->all();
    @endphp

    <form method="POST" action="{{ route('leader.events.update', $event) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <x-page-card icon="calendar-check" title="Event details">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" id="title" name="title" required minlength="5" maxlength="255"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $event->title) }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="5" required minlength="10"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $event->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-6">
                            <label for="event_date" class="form-label fw-semibold">Date</label>
                            <input type="date" id="event_date" name="event_date" required
                                   class="form-control @error('event_date') is-invalid @enderror"
                                   value="{{ old('event_date', $event->event_date->toDateString()) }}">
                            @error('event_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select id="status" name="status" required
                                    class="form-select @error('status') is-invalid @enderror">
                                @foreach (\App\Enums\EventStatus::cases() as $status)
                                    <option value="{{ $status->value }}"
                                        @selected(old('status', $event->status?->value) === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="venue" class="form-label fw-semibold">Venue</label>
                        <input type="text" id="venue" name="venue" required maxlength="255"
                               class="form-control @error('venue') is-invalid @enderror"
                               value="{{ old('venue', $event->venue) }}">
                        @error('venue') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="attachment" class="form-label fw-semibold">Replace flyer</label>
                        <input type="file" id="attachment" name="attachment"
                               class="form-control @error('attachment') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.jpg,.jpeg,.png,.gif,.webp,.zip">
                        @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                </x-page-card>

                <div class="mt-4">
                    <x-audience-selector :options="$audienceOptions" :selected="old('audience', $selectedAudience)" />
                </div>
            </div>
        </div>
    </form>
</x-app-layout>