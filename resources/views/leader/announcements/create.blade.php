<x-app-layout>
    @section('title', 'New announcement')
    @section('heading', 'New announcement')
    @section('subheading', 'Write, target and submit an announcement.')

    @section('actions')
        <a href="{{ route('leader.announcements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <form method="POST" action="{{ route('leader.announcements.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <x-page-card icon="pencil-square" title="Content">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" id="title" name="title" required maxlength="255"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label fw-semibold">Content</label>
                        <textarea id="content" name="content" rows="10" required
                                  class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Plain text is preserved when displayed to readers.</div>
                    </div>

                    <div class="mb-3">
                        <label for="attachment" class="form-label fw-semibold">Attachment (optional)</label>
                        <input type="file" id="attachment" name="attachment"
                               class="form-control @error('attachment') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.jpg,.jpeg,.png,.gif,.webp,.zip">
                        @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            Max {{ config('daruso.uploads.max_size_kb') }} KB.
                            Allowed: {{ implode(', ', config('daruso.uploads.mimes')) }}.
                        </div>
                    </div>
                </x-page-card>

                <div class="mt-4">
                    <x-audience-selector :options="$audienceOptions" :selected="old('audience', [])" />
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <x-page-card icon="sliders" title="Publishing">
                    <div class="mb-3">
                        <label for="priority" class="form-label fw-semibold">Priority</label>
                        <select id="priority" name="priority"
                                class="form-select @error('priority') is-invalid @enderror">
                            @foreach (\App\Enums\Priority::cases() as $priority)
                                <option value="{{ $priority->value }}"
                                    @selected(old('priority', \App\Enums\Priority::Normal->value) === $priority->value)>
                                    {{ $priority->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Initial status</label>
                        <select id="status" name="status"
                                class="form-select @error('status') is-invalid @enderror">
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                            <option value="review" @selected(old('status') === 'review')>Submit for review</option>
                            @can('publish', App\Models\Announcement::class)
                                <option value="published" @selected(old('status') === 'published')>Publish immediately</option>
                            @endcan
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            @cannot('publish', App\Models\Announcement::class)
                                You do not hold the publish permission, so this will be saved as a draft.
                            @endcannot
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="expires_at" class="form-label fw-semibold">Expires at (optional)</label>
                        <input type="datetime-local" id="expires_at" name="expires_at"
                               class="form-control @error('expires_at') is-invalid @enderror"
                               value="{{ old('expires_at') }}">
                        @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">After this time the announcement stops appearing in listings.</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="requires_approval" value="0">
                        <input class="form-check-input" type="checkbox" name="requires_approval" value="1"
                               id="requires_approval" @checked(old('requires_approval'))>
                        <label class="form-check-label" for="requires_approval">
                            Requires approval before publishing
                        </label>
                    </div>

                    <button class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>Save announcement
                    </button>
                </x-page-card>
            </div>
        </div>
    </form>
</x-app-layout>