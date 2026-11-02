<x-app-layout>
@section('title','Search DARUSO') @section('heading','Search DARUSO') @section('subheading','Search the content and records you are authorised to access.')
<div class="daruso-panel p-4">
<form method="GET" action="{{ route('search') }}" class="input-group mb-4"><input class="form-control" type="search" name="q" value="{{ $results['term'] }}" minlength="2" placeholder="Search announcements, posts, documents, events..."><button class="btn btn-primary">Search</button></form>
@if(mb_strlen($results['term']) >= 2)<p class="small text-muted">{{ $results['total'] }} result(s) found.</p>@foreach($results['groups'] as $group)<section class="mb-4"><h2 class="h6 fw-bold"><i class="bi bi-{{ $group['icon'] }} me-2"></i>{{ $group['label'] }}</h2>@foreach($group['results'] as $result)<a class="daruso-list-item" href="{{ $result['url'] }}"><span><strong>{{ $result['title'] }}</strong><small>{{ $result['meta'] }}</small></span><i class="bi bi-arrow-up-right ms-auto"></i></a>@endforeach</section>@endforeach
@if($results['total']===0)<p class="text-muted">No matching records were found.</p>@endif
@else<p class="text-muted">Enter at least two characters to search.</p>@endif
</div></x-app-layout>