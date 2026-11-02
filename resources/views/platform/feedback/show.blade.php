<x-app-layout>
@section('title',$item->subject) @section('heading',$item->subject) @section('subheading','Your submitted feedback and its response history.')
<div class="daruso-panel p-4"><span class="badge text-bg-light">{{ $item->status->label() }}</span><p class="mt-3">{{ $item->message }}</p><small class="text-muted">Submitted {{ $item->created_at->format('d M Y H:i') }} · {{ $item->is_anonymous ? 'Anonymous' : 'Identified' }}</small>
@if($item->response)<hr><h2 class="h6">Leadership response</h2><p>{{ $item->response }}</p><small class="text-muted">{{ $item->responder?->name }} · {{ $item->responded_at?->format('d M Y H:i') }}</small>@else<p class="text-muted mt-3 mb-0">No response has been recorded yet.</p>@endif
<a class="btn btn-light mt-3" href="{{ route('student.feedback.index') }}">Back to my feedback</a></div>
</x-app-layout>