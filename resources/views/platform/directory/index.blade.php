<x-app-layout>
@section('title','Student directory') @section('heading','Student directory') @section('subheading','Verified student records visible under your directory permission.')
<div class="daruso-panel">
<form method="GET" class="row g-2 p-3"><div class="col-md-6"><input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search programme or college"></div><div class="col-md-4"><input class="form-control" name="college" value="{{ request('college') }}" placeholder="College"></div><div class="col-md-2"><button class="btn btn-primary w-100">Search</button></div></form>
@forelse($profiles as $profile)<a class="daruso-list-item" href="{{ route('leader.directory.show',$profile) }}"><span class="daruso-avatar">{{ $profile->user?->initials() }}</span><span><strong>{{ $profile->user?->name }}</strong><small>{{ $profile->programme }} · {{ $profile->college }} · Year {{ $profile->year_of_study }}</small></span></a>@empty<p class="p-4 text-muted">No matching verified students.</p>@endforelse
<div class="p-3">{{ $profiles->links() }}</div>
</div>
</x-app-layout>