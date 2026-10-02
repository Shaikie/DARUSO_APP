<x-app-layout>
    @section('title','Manage posts')
    @section('eyebrow','CONTENT')
    @section('heading','Community posts')
    @section('subheading','Publish, edit and moderate the stories students see in the community feed.')
    @section('actions')
        @can('create',App\Models\Post::class)<a href="{{ route('leader.posts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Write post</a>@endcan
    @endsection

    <section class="daruso-panel">
        <div class="daruso-panel-head">
            <h3><i class="bi bi-newspaper"></i> Your content library</h3>
            <span class="small text-muted">{{ $posts->total() }} posts</span>
        </div>
        <div class="table-responsive">
            <table class="table daruso-table align-middle">
                <thead><tr><th>Post</th><th>Author</th><th>Status</th><th>Engagement</th><th></th></tr></thead>
                <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($post->cover_image_url)<img class="daruso-table-thumb" src="{{ $post->cover_image_url }}" alt="" loading="lazy">@else<span class="daruso-table-thumb daruso-table-thumb-empty"><i class="bi bi-newspaper"></i></span>@endif
                                <div><a class="fw-semibold text-dark" href="{{ route('student.posts.show',$post) }}">{{ $post->title }}</a><div class="small text-muted">{{ $post->created_at->format('d M Y · H:i') }}</div></div>
                            </div>
                        </td>
                        <td>{{ $post->author?->name }}</td>
                        <td>@if($post->isPublished())<span class="badge text-bg-success">Published</span>@else<span class="badge text-bg-secondary">Draft</span>@endif</td>
                        <td class="small text-muted">{{ $post->likes_count }} likes · {{ $post->comments_count }} comments · {{ $post->share_count }} shares</td>
                        <td class="text-end text-nowrap">
                            @can('update',$post)<a href="{{ route('leader.posts.edit',$post) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>@endcan
                            @can('publish',$post) @unless($post->isPublished())<form class="d-inline" method="POST" action="{{ route('leader.posts.publish',$post) }}">@csrf<button class="btn btn-sm btn-success"><i class="bi bi-send me-1"></i>Publish</button></form>@endunless @endcan
                            @can('delete',$post)<form class="d-inline" method="POST" action="{{ route('leader.posts.destroy',$post) }}" onsubmit="return confirm('Delete this post?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>@endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="daruso-empty py-5"><i class="bi bi-newspaper"></i><h3>No posts yet</h3><p>Create the first community story.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $posts->links() }}
    </section>
</x-app-layout>