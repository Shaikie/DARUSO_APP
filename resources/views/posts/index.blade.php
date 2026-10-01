<x-app-layout>
@section('title','Daily Posts') @section('heading','Daily Posts') @section('subheading','Updates, stories and everyday DARUSO content.')
@section('actions') @can('create',App\Models\Post::class)<a href="{{ route('leader.posts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Write a post</a>@endcan @endsection
<div class="row g-4"><div class="col-12 col-lg-8">
@forelse($posts as $post)
<article class="card border-0 overflow-hidden mb-4">@if($post->cover_image_url)<img src="{{ $post->cover_image_url }}" alt="" class="w-100" style="height:260px;object-fit:cover;">@endif
<div class="card-body"><div class="small text-muted mb-2">{{ $post->published_at?->format('d M Y · H:i') }} · {{ $post->author?->name }}</div>
<h2 class="h4 fw-bold"><a href="{{ route('student.posts.show',$post) }}">{{ $post->title }}</a></h2>
<p class="text-muted mb-3">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content),180) }}</p>
<div class="d-flex flex-wrap gap-2 align-items-center"><span class="badge text-bg-light border"><i class="bi bi-heart me-1"></i>{{ $post->likes_count }}</span><span class="badge text-bg-light border"><i class="bi bi-chat me-1"></i>{{ $post->comments_count }}</span><span class="badge text-bg-light border"><i class="bi bi-share me-1"></i>{{ $post->share_count }}</span><a class="btn btn-sm btn-outline-primary ms-auto" href="{{ route('student.posts.show',$post) }}">Read post</a></div>
</div></article>
@empty <x-empty-state icon="newspaper" title="No posts yet" description="Daily posts from DARUSO leadership will appear here." /> @endforelse
{{ $posts->links() }}</div>
<div class="col-12 col-lg-4"><x-page-card icon="info-circle" title="Community feed"><p class="small text-muted mb-0">Read updates, like posts, join conversations and share useful posts.</p></x-page-card></div></div>
</x-app-layout>