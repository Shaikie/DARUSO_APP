<x-app-layout>
@section('title','Poll management') @section('heading','Poll management') @section('subheading','Create polls, open voting and close polls when the voting period ends.')
@section('actions')<a href="{{ route('leader.polls.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create poll</a>@endsection
<div class="daruso-panel">
@forelse($items as $poll)
<div class="daruso-list-item"><span class="daruso-list-icon"><i class="bi bi-bar-chart"></i></span><span class="flex-grow-1"><a href="{{ route('leader.polls.show',$poll) }}" class="fw-semibold">{{ $poll->title }}</a><small>{{ $poll->totalVotes() }} votes · {{ $poll->options->count() }} options · {{ $poll->status->label() }}</small></span>
@if(!$poll->votes()->exists())<a href="{{ route('leader.polls.edit',$poll) }}" class="btn btn-sm btn-outline-secondary">Edit</a>@endif
@if($poll->status->value === 'draft')<form method="POST" action="{{ route('leader.polls.open',$poll) }}">@csrf<button class="btn btn-sm btn-success">Open</button></form>@elseif($poll->status->value === 'open')<form method="POST" action="{{ route('leader.polls.close',$poll) }}">@csrf<button class="btn btn-sm btn-outline-danger">Close</button></form>@endif
</div>
@empty<p class="text-muted p-3">No polls have been created.</p>@endforelse
{{ $items->links() }}
</div>
</x-app-layout>