<x-app-layout>
@section('title',$post->title) @section('heading',$post->title) @section('subheading','Posted by '.$post->author?->name.' · '.$post->published_at?->format('d M Y · H:i'))
<article class="card border-0 overflow-hidden mb-4">@if($post->cover_image_url)<img src="{{ $post->cover_image_url }}" alt="" class="w-100" style="max-height:460px;object-fit:cover;">@endif
<div class="card-body p-4 p-lg-5"><div class="fs-5 lh-lg post-content">{!! nl2br(e($post->content)) !!}</div>
<div class="d-flex flex-wrap gap-2 align-items-center border-top pt-3 mt-4">
<form method="POST" action="{{ route('student.posts.like',$post) }}">@csrf<button class="btn btn-sm {{ $liked?'btn-primary':'btn-outline-primary' }}"><i class="bi bi-heart{{ $liked?'-fill':'' }} me-1"></i>{{ $liked?'Liked':'Like' }}</button></form>
<form method="POST" action="{{ route('student.posts.share',$post) }}" class="d-inline" onsubmit="return sharePost(event,'{{ route('student.posts.show',$post) }}');">@csrf<button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-share me-1"></i>Share ({{ $post->share_count }})</button></form>
</div></div></article>
<x-page-card icon="chat-dots" title="Comments">
<form method="POST" action="{{ route('student.posts.comments.store',$post) }}" class="mb-4">@csrf<label for="content" class="form-label">Join the conversation</label><textarea id="content" name="content" rows="3" class="form-control" maxlength="2000" required placeholder="Write a respectful comment..."></textarea>@error('content')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror<button class="btn btn-primary mt-2"><i class="bi bi-chat-dots me-1"></i>Comment</button></form>
@forelse($post->comments as $comment)<div class="border-bottom py-3"><div class="d-flex justify-content-between gap-2"><strong>{{ $comment->user?->name }}</strong><small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small></div><div class="mt-1">{{ $comment->content }}</div>@can('deleteComment',[$post,$comment])<form method="POST" action="{{ route('student.posts.comments.destroy',[$post,$comment]) }}" class="mt-2">@csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger p-0">Remove</button></form>@endcan</div>
@empty<p class="text-muted mb-0">No comments yet. Start the conversation.</p>@endforelse
</x-page-card>
@push('scripts')<script>
async function sharePost(event,url){if(navigator.share){event.preventDefault();try{await navigator.share({title:@json($post->title),url});event.target.submit();}catch(error){if(error?.name!=='AbortError')event.target.submit();}return false;}return true;}
</script>@endpush
</x-app-layout>