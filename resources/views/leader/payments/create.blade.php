<x-app-layout>
@section('title','Create payment request') @section('heading','Create payment request') @section('subheading','Create a contribution request and notify only the selected verified student audience.')
<div class="daruso-panel p-4"><form method="POST" action="{{ route('leader.payments.store') }}">@csrf
<label class="form-label">Title</label><input class="form-control mb-3" name="title" required maxlength="180">
<label class="form-label">Description</label><textarea class="form-control mb-3" name="description" rows="3"></textarea>
<label class="form-label">Amount (TZS)</label><input class="form-control mb-3" type="number" name="amount" min="0.01" step="0.01" required>
<label class="form-label">Due date and time</label><input class="form-control mb-3" type="datetime-local" name="due_at">
<label class="form-label">Payment instructions</label><textarea class="form-control mb-3" name="instructions" rows="4" placeholder="Provide the approved payment channel and reference instructions."></textarea>
<label class="form-label">Target audience</label><select class="form-select mb-3" name="audience" required><option value="all">All verified active students</option><option value="college">College</option><option value="programme">Programme</option><option value="year">Year of study</option><option value="hostel">Hostel</option></select>
<div class="row g-3"><div class="col-md-6"><label class="form-label">College (when selected)</label><input class="form-control" name="college"></div><div class="col-md-6"><label class="form-label">Programme (when selected)</label><input class="form-control" name="programme"></div><div class="col-md-6"><label class="form-label">Year of study (when selected)</label><input class="form-control" type="number" min="1" max="8" name="year_of_study"></div><div class="col-md-6"><label class="form-label">Hostel (when selected)</label><input class="form-control" name="hostel"></div></div>
<div class="alert alert-info mt-3 mb-3 small">Only students matching this audience receive a notification and see this request. Students can view only their own submission. There is no public paid/unpaid list.</div>
<button class="btn btn-primary">Create and notify audience</button> <a href="{{ route('leader.payments.index') }}" class="btn btn-light">Cancel</a>
</form></div>
</x-app-layout>