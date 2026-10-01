<x-app-layout>
    @section('title', 'Edit meeting')
    @section('heading', 'Edit meeting')
    @section('subheading', $meeting->title)

    @section('actions')
        <a href="{{ route('leader.meetings.show', $meeting) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    @php
        // Pre-select the current audience so editing does not silently retarget.
        $selectedAudience = $meeting->audienceRules->groupBy('audience_type')->map(
            fn ($rules) => $rules->pluck('audience_value')->filter()->values()->all()
        )->all();
    @endphp

    <form method="POST" action="{{ route('leader.meetings.update', $meeting) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <x-page-card icon="calendar-event" title="Meeting details">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" id="title" name="title" required minlength="5" maxlength="255"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $meeting->title) }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="4" required minlength="10"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $meeting->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <label for="meeting_date" class="form-label fw-semibold">Date</label>
                            <input type="date" id="meeting_date" name="meeting_date" required
                                   class="form-control @error('meeting_date') is-invalid @enderror"
                                   value="{{ old('meeting_date', $meeting->meeting_date->toDateString()) }}">
                            @error('meeting_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="meeting_time" class="form-label fw-semibold">Time</label>
                            <input type="time" id="meeting_time" name="meeting_time" required
                                   class="form-control @error('meeting_time') is-invalid @enderror"
                                   value="{{ old('meeting_time', $meeting->meeting_time->format('H:i')) }}">
                            @error('meeting_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select id="status" name="status" required
                                    class="form-select @error('status') is-invalid @enderror">
                                @foreach (\App\Enums\MeetingStatus::cases() as $status)
                                    <option value="{{ $status->value }}"
                                        @selected(old('status', $meeting->status?->value) === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="venue" class="form-label fw-semibold">Venue</label>
                        <input type="text" id="venue" name="venue" required maxlength="255"
                               class="form-control @error('venue') is-invalid @enderror"
                               value="{{ old('venue', $meeting->venue) }}">
                        @error('venue') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="agenda" class="form-label fw-semibold">Agenda</label>
                        <textarea id="agenda" name="agenda" rows="4"
                                  class="form-control @error('agenda') is-invalid @enderror">{{ old('agenda', $meeting->agenda) }}</textarea>
                        @error('agenda') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="attachment" class="form-label fw-semibold">Replace attachment</label>
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