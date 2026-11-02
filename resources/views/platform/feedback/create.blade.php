<x-app-layout>
@section('title','Send feedback') @section('heading','Send feedback') @section('subheading','Share a suggestion or raise an issue with DARUSO leadership.')
<div class="daruso-panel p-4"><form method="POST" action="{{ route('student.feedback.store') }}">@csrf
<label class="form-label">Subject</label><input class="form-control mb-3" name="subject" value="{{ old('subject') }}" required maxlength="180">
<label class="form-label">Category</label><select class="form-select mb-3" name="category" required>@foreach(['general'=>'General','academic'=>'Academic','welfare'=>'Welfare','hostel'=>'Hostel','other'=>'Other'] as $v=>$label)<option value="{{ $v }}" @selected(old('category')===$v)>{{ $label }}</option>@endforeach</select>
<label class="form-label">Message</label><textarea class="form-control mb-3" rows="6" name="message" required>{{ old('message') }}</textarea>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="anonymous"><label class="form-check-label" for="anonymous">Submit anonymously to leadership</label></div>
<button class="btn btn-primary">Submit feedback</button> <a class="btn btn-light" href="{{ route('student.feedback.index') }}">Cancel</a>
</form></div></x-app-layout>