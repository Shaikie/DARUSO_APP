{{--
    Audience targeting selector.

    Shared by announcements, meetings and events so the targeting vocabulary and
    validation contract stay identical across the communication engine. Values are
    submitted as `audience[<type>][]` (or a boolean for the "all" types).

    Props:
      - options: array<string, array<string>> keyed by audience type value
      - selected: currently selected rules, keyed by type
--}}
@props([
    'options' => [],
    'selected' => [],
    // Notifications pick individuals from the recipient list, so the
    // `individual` audience type is omitted there to avoid addressing twice.
    'excludeTypes' => [],
])

@php
    use App\Enums\AudienceType;

    $types = collect(AudienceType::cases())
        ->reject(fn (AudienceType $type): bool => in_array($type->value, $excludeTypes, true));

    $selectedFor = function (AudienceType $type) use ($selected) {
        $value = $selected[$type->value] ?? null;

        if ($type->requiresValue()) {
            return collect(is_array($value) ? $value : [$value])->filter()->values();
        }

        return (bool) ($value ?? false);
    };
@endphp

<div class="border rounded p-3 bg-light-subtle">
    <p class="fw-semibold mb-1">
        <i class="bi bi-bullseye me-1"></i>Target audience
    </p>
    <p class="text-muted small mb-3">
        Choose one or more groups. Broad targets such as "All students" are stored as a
        single targeting rule rather than one row per student.
    </p>

    <div class="row g-3">
        @foreach ($types as $type)
            @php($values = $options[$type->value] ?? [])

            <div class="col-12 col-md-6">
                <div class="border rounded p-2 h-100">
                    @if ($type->requiresValue())
                        <label class="form-label fw-semibold mb-1">{{ $type->label() }}</label>

                        @if (count($values))
                            <select name="audience[{{ $type->value }}][]"
                                    multiple
                                    size="4"
                                    class="form-select form-select-sm @error('audience.'.$type->value) is-invalid @enderror"
                                    aria-label="{{ $type->label() }}">
                                @foreach ($values as $value)
                                    <option value="{{ $value }}" @selected($selectedFor($type)->contains((string) $value))>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select more than one.</div>
                        @else
                            <p class="text-muted small mb-0">
                                No {{ strtolower($type->label()) }} values exist yet.
                            </p>
                        @endif
                    @else
                        <div class="form-check form-switch">
                            <input type="hidden" name="audience[{{ $type->value }}]" value="0">
                            <input class="form-check-input @error('audience.'.$type->value) is-invalid @enderror"
                                   type="checkbox"
                                   name="audience[{{ $type->value }}]"
                                   id="audience_{{ $type->value }}"
                                   value="1"
                                   @checked($selectedFor($type))>
                            <label class="form-check-label" for="audience_{{ $type->value }}">
                                {{ $type->label() }}
                            </label>
                        </div>
                    @endif

                    @error('audience.'.$type->value)
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endforeach
    </div>

    @error('audience')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>