<x-app-layout>
    @section('title', 'Send notification')
    @section('heading', 'Send notification')
    @section('subheading', 'Address specific people, or target an audience.')

    @section('actions')
        <a href="{{ route('leader.notifications.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    @unless (auth()->user()->can('notification.send'))
        <div class="alert alert-warning d-flex gap-2" role="alert">
            <i class="bi bi-shield-exclamation mt-1"></i>
            <div class="small">
                You can compose notifications but you do not hold
                <code>notification.send</code>, so sending will be refused.
            </div>
        </div>
    @endunless

    <form method="POST" action="{{ route('leader.notifications.store') }}">
        @csrf

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <x-page-card icon="send" title="Message">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" id="title" name="title" required minlength="5" maxlength="255"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" placeholder="e.g. Verification reminder">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label fw-semibold">Message</label>
                        <textarea id="message" name="message" rows="6" required minlength="5"
                                  class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                        @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="priority" class="form-label fw-semibold">Priority</label>
                        <select id="priority" name="priority" required
                                class="form-select @error('priority') is-invalid @enderror">
                            @foreach (\App\Enums\Priority::cases() as $priority)
                                <option value="{{ $priority->value }}"
                                    @selected(old('priority', 'normal') === $priority->value)>
                                    {{ $priority->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="recipients" class="form-label fw-semibold">Recipients</label>
                        <select id="recipients" name="recipients[]" multiple size="10"
                                class="form-select @error('recipients.*') is-invalid @enderror">
                            @foreach ($recipients as $recipient)
                                <option value="{{ $recipient['id'] }}"
                                    @selected(in_array($recipient['id'], old('recipients', [])))>
                                    {{ $recipient['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('recipients.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            Hold Ctrl/Cmd to select several people. Each recipient gets their own
                            notification with an independent read state.
                        </div>
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Send notification
                    </button>
                </x-page-card>
            </div>

            <div class="col-12 col-lg-4">
                <x-page-card icon="bullseye" title="Or target an audience">
                    <p class="small text-muted">
                        Choose an audience instead of listing recipients. Every reader gets their own
                        notification so read state stays personal.
                    </p>

                    {{-- Individuals are chosen from the recipient list above, so the audience
                         selector omits the `individual` type here. --}}
                    <x-audience-selector :options="$audienceOptions"
                                         :selected="old('audience', [])"
                                         :exclude-types="['individual']" />

                    <div class="alert alert-warning small mt-3 mb-0" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Broadcasts are capped at
                        {{ config('daruso.audience.notification_materialisation_limit') }} recipients.
                        For a wider audience, publish an announcement instead — it stays a single
                        targeted record.
                    </div>
                </x-page-card>
            </div>
        </div>
    </form>
</x-app-layout>