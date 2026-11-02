<x-app-layout>
@section('title',$poll->title) @section('heading',$poll->title) @section('subheading',$poll->description)
<div class="daruso-panel p-4"><div class="d-flex justify-content-between"><span class="badge text-bg-light">{{ $poll->status->label() }}</span><span class="small text-muted">{{ $poll->totalVotes() }} votes</span></div>
@if($poll->isOpen() && !$hasVoted && $canVote)
<form method="POST" action="{{ route('student.polls.vote',$poll) }}" class="mt-4">@csrf
@foreach($poll->options as $option)<label class="d-flex align-items-center gap-2 border rounded-3 p-3 mb-2"><input type="radio" name="option_id" value="{{ $option->id }}" required><span>{{ $option->label }}</span></label>@endforeach
<button class="btn btn-primary mt-2">Submit vote</button></form>
@elseif($hasVoted)<div class="alert alert-success mt-3">Your vote has been recorded.</div>@elseif($poll->isOpen() && !$canVote)<p class="text-muted mt-3">Voting is open to verified students. Results will be available when the poll closes.</p>@elseif(!$poll->isOpen())<p class="text-muted mt-3">Voting is not currently open.</p>@endif
@if(count($results))<hr><h2 class="h6 fw-bold mt-3">Poll results</h2>@foreach($results as $result)<div class="mb-3"><div class="d-flex justify-content-between"><strong>{{ $result['option']->label }}</strong><span>{{ $result['votes'] }} · {{ $result['percentage'] }}%</span></div><div class="progress" role="progressbar" aria-valuenow="{{ $result['percentage'] }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: {{ $result['percentage'] }}%"></div></div></div>@endforeach@endif
<a href="{{ auth()->user()->isLeader() ? route('leader.polls.index') : route('student.polls.index') }}" class="btn btn-light mt-3">Back to polls</a></div>
</x-app-layout>