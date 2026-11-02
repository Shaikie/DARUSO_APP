<x-app-layout>
@section('title','Student feedback') @section('heading','Student feedback') @section('subheading','Review suggestions and respond to students. Anonymous submissions conceal the author.')
<div class="daruso-panel">
@forelse($items as $item)
<article class="p-3 border-bottom"><div class="d-flex justify-content-between gap-2"><strong>{{ $item->subject }}</strong><span class="badge text-bg-light">{{ $item->status->label() }}</span></div><small class="text-muted">{{ $item->category }} · {{ $item->displayAuthor(auth()->user()) }} · {{ $item->created_at->diffForHumans() }}</small><p class="mt-2 mb-2">{{ $item->message }}</p>
@if($item->status->value !== 'resolved' && $item->status->value !== 'closed')
<form method="POST" action="{{ route('leader.feedback.respond',$item) }}" class="row g-2">@csrf<div class="col-12"><textarea class="form-control" name="response" required rows="2" placeholder="Write a response"></textarea></div><div class="col-md-4"><select class="form-select" name="status"><option value="in_review">In review</option><option value="resolved">Resolved</option><option value="closed">Closed</option></select></div><div class="col-md-3"><button class="btn btn-primary">Send response</button></div></form>
@elseif($item->response)<div class="alert alert-light mt-2 mb-0">{{ $item->response }}</div>@endif</article>
@empty<p class="p-4 text-muted">No feedback has been submitted.</p>@endforelse
{{ $items->links() }}
</div>
</x-app-layout>