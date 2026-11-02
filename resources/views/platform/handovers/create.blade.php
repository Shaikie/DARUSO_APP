<x-app-layout>
@section('title','Create handover') @section('heading','Create a leadership handover') @section('subheading','Record context, unfinished work and recommendations for the incoming officer.')
<div class="daruso-panel p-4"><form method="POST" action="{{ route('leader.handovers.store') }}">@csrf
<label class="form-label">Title</label><input class="form-control mb-3" name="title" required>
<label class="form-label">Incoming officer</label><select class="form-select mb-3" name="to_user_id" required><option value="">Select leader</option>@foreach($leaders as $leader)<option value="{{ $leader->id }}">{{ $leader->name }}</option>@endforeach</select>
<label class="form-label">Summary and context</label><textarea class="form-control mb-3" rows="5" name="summary" required></textarea>
<label class="form-label">Outstanding items</label><textarea class="form-control mb-3" rows="3" name="outstanding_items"></textarea>
<label class="form-label">Recommendations</label><textarea class="form-control mb-3" rows="3" name="recommendations"></textarea>
<div class="row g-3"><div class="col-md-6"><label class="form-label">Ministry</label><select class="form-select" name="ministry_id"><option value="">None</option>@foreach($ministries as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label">Committee</label><select class="form-select" name="committee_id"><option value="">None</option>@foreach($committees as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div></div>
<label class="form-label mt-3">Leadership term</label><select class="form-select mb-3" name="leadership_term_id"><option value="">Select term</option>@foreach($terms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach</select>
<button class="btn btn-primary">Save handover draft</button> <a href="{{ route('leader.handovers.index') }}" class="btn btn-light">Cancel</a>
</form></div></x-app-layout>