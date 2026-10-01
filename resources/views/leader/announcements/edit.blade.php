<x-app-layout>
    @section('title', 'Edit announcement')
    @section('heading', 'Edit announcement')
    @section('subheading', $announcement->title)

    @section('actions')
        <a href="{{ route('leader.announcements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    @php
        // Pre-select the current audience so editing does not silently retarget.
        $selectedAudience = $announcement->audienceRules->groupBy('audience_type')->map(
            fn ($rules) => $rules->pluck('audience_value')->filter()->values()->all()
        )->all();
    @endphp

    <form method="POST" action="{{ route('leader.announcements.update', $announcement) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <x-page-card icon="pencil-square" title="Content">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" id="title" name="title" required maxlength="255"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $announcement->title) }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label fw-semibold">Content</label>
                        <textarea id="content" name="content" rows="10" required
                                  class="form-control @error('content') is-invalid @enderror">{{ old('content', $announcement->content) }}</textarea>
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="attachment" class="form-label fw-semibold">Replace attachment</label>
                        <input type="file" id="attachment" name="attachment"
                               class="form-control @error('attachment') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.jpg,.jpeg,.png,.gif,.webp,.zip">
                        @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if ($announcement->attachments->isNotEmpty())
                            <div class="form-text">
                                Current: {{ $announcement->attachments->first()->file_name }}
                            </div>
                        @endif
                    </div>
                </x-page-card>

                <div class="mt-4">
                    <x-audience-selector :options="$audienceOptions" :selected="old('audience', $selectedAudience)" />
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <x-page-card icon="sliders" title="Settings">
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Current status</label>
                        <x-status-badge :status="$announcement->status" />
                    </div>

                    {{--
                        Status is not edited here: lifecycle changes (review, publish,
                        archive) are separate authorized actions, not a form field.
                    --}}
                    <input type="hidden" name="status" value="{{ $announcement->status->value }}">

                    <div class="mb-3">
                        <label for="priority" class="form-label fw-semibold">Priority</label>
                        <select id="priority" name="priority"
                                class="form-select @error('priority') is-invalid @enderror">
                            @foreach (\App\Enums\Priority::cases() as $priority)
                                <option value="{{ $priority->value }}"
                                    @selected(old('priority', $announcement->priority?->value) === $priority->value)>
                                    {{ $priority->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="expires_at" class="form-label fw-semibold">Expires at</label>
                        <input type="datetime-local" id="expires_at" name="expires_at"
                               class="form-control @error('expires_at') is-invalid @enderror"
                               value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}">
                        @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="requires_approval" value="0">
                        <input class="form-check-input" type="checkbox" name="requires_approval" value="1"
                               id="requires_approval"
                               @checked(old('requires_approval', $announcement->requires_approval))>
                        <label class="form-check-label" for="requires_approval">
                            Requires approval before publishing
                        </label>
                    </div>

                    <button class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                </x-page-card>
            </div>
        </div>
    </form>
</x-app-layout>