<x-app-layout>
@section('title',$poll ? 'Edit poll' : 'Create poll') @section('heading',$poll ? 'Edit poll' : 'Create a poll') @section('subheading','Set a clear question and at least two answer options.')
<div class="daruso-panel p-4"><form method="POST" action="{{ $poll ? route('leader.polls.update',$poll) : route('leader.polls.store') }}">@csrf @if($poll) @method('PUT') @endif
<label class="form-label">Question / title</label><input class="form-control mb-3" name="title" value="{{ old('title',$poll?->title) }}" required maxlength="180">
<label class="form-label">Description</label><textarea class="form-control mb-3" name="description" rows="3">{{ old('description',$poll?->description) }}</textarea>
<label class="form-label">Options</label>@php($existingOptions = $poll?->options?->pluck('label')->all() ?? [])
@for($i=0;$i<5;$i++)<input class="form-control mb-2" name="options[]" value="{{ old('options.'.$i,$existingOptions[$i] ?? '') }}" placeholder="Option {{ $i+1 }}" @required($i<2)>@endfor
<div class="row g-3 mt-1"><div class="col-md-6"><label class="form-label">Starts at</label><input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at',$poll?->starts_at?->format('Y-m-d\\TH:i')) }}"></div><div class="col-md-6"><label class="form-label">Ends at</label><input class="form-control" type="datetime-local" name="ends_at" value="{{ old('ends_at',$poll?->ends_at?->format('Y-m-d\\TH:i')) }}"></div></div>
<div class="form-check my-3"><input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="anonymous" @checked(old('is_anonymous',$poll?->is_anonymous))><label for="anonymous" class="form-check-label">Anonymous voting</label></div>
<button class="btn btn-primary">{{ $poll ? 'Update poll' : 'Save poll' }}</button> <a href="{{ route('leader.polls.index') }}" class="btn btn-light">Cancel</a>
</form></div></x-app-layout>