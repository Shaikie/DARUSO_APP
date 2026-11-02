<x-app-layout>
@section('title','New activity report') @section('heading','Ministry activity report') @section('subheading','Document the ministry work completed during a reporting period.')
<div class="daruso-panel p-4"><form method="POST" action="{{ route('leader.activity-reports.store') }}">@csrf
<label class="form-label">Ministry</label><select class="form-select mb-3" name="ministry_id" required>@foreach($ministries as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select>
<label class="form-label">Leadership term</label><select class="form-select mb-3" name="leadership_term_id"><option value="">Current / unspecified</option>@foreach($terms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach</select>
<div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Period start</label><input class="form-control" type="date" name="period_start" required></div><div class="col-md-6"><label class="form-label">Period end</label><input class="form-control" type="date" name="period_end" required></div></div>
@foreach(['summary'=>'Summary','achievements'=>'Achievements','challenges'=>'Challenges','recommendations'=>'Recommendations'] as $field=>$label)<label class="form-label">{{ $label }}</label><textarea class="form-control mb-3" rows="3" name="{{ $field }}" @required($field==='summary')></textarea>@endforeach
<button class="btn btn-primary">Save draft</button> <a href="{{ route('leader.activity-reports.index') }}" class="btn btn-light">Cancel</a>
</form></div></x-app-layout>