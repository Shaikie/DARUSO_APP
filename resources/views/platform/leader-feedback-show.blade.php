<x-app-layout>
@section('title',$item->subject) @section('heading',$item->subject) @section('subheading','Student feedback record')
<div class="daruso-panel p-4"><span class="badge text-bg-light">{{ $item->status->label() }}</span><p class="small text-muted mt-2">{{ $item->category }} · {{ $item->displayAuthor(auth()->user()) }} · {{ $item->created_at->format('d M Y H:i') }}</p><p>{{ $item->message }}</p>
@if($item->response)<hr><h2 class="h6">Previous response</h2><p>{{ $item->response }}</p>@endif
@if(!in_array($item->status->value,['resolved','closed'],true))<form method="POST" action="{{ route('leader.feedback.respond',$item) }}">@csrf<label class="form-label">Response</label><textarea class="form-control mb-3" name="response" rows="4" required></textarea><label class="form-label">Status</label><select class="form-select mb-3" name="status"><option value="in_review">In review</option><option value="resolved">Resolved</option><option value="closed">Closed</option></select><button class="btn btn-primary">Send response</button></form>@endif
<a href="{{ route('leader.feedback.index') }}" class="btn btn-light mt-3">Back to feedback</a></div>
</x-app-layout>