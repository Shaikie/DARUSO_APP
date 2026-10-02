<x-app-layout>
    @section('title','Community')
    @section('eyebrow','DARUSO COMMUNITY')
    @section('heading','Community')
    @section('subheading','Stories, updates and conversations from the DARUSO community.')
    @section('actions')
        @can('create',App\Models\Post::class)
            <a href="{{ route('leader.posts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Write a post</a>
        @endcan
    @endsection

    <div class="daruso-feed-layout">
        <div class="daruso-feed-column">
            @forelse($posts as $post)
                <article class="daruso-post-card">
                    <div class="daruso-post-head">
                        <div class="daruso-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($post->author?->name ?? 'D', 0, 1)) }}</div>
                        <div class="min-w-0">
                            <strong>{{ $post->author?->name ?? 'DARUSO' }}</strong>
                            <small>{{ $post->published_at?->diffForHumans() }} · DARUSO Community</small>
                        </div>
                        <button class="daruso-more-button ms-auto" type="button" aria-label="Post options"><i class="bi bi-three-dots"></i></button>
                    </div>

                    @if($post->cover_image_url)
                        <a href="{{ route('student.posts.show',$post) }}" class="daruso-post-cover">
                            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" loading="lazy">
                        </a>
                    @endif

                    <div class="daruso-post-body">
                        <h2><a href="{{ route('student.posts.show',$post) }}">{{ $post->title }}</a></h2>
                        <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 220) }}</p>
                    </div>

                    <div class="daruso-post-engagement">
                        <span><i class="bi bi-heart-fill"></i> {{ $post->likes_count }}</span>
                        <span>{{ $post->comments_count }} comments · {{ $post->share_count }} shares</span>
                    </div>

                    <div class="daruso-post-actions">
                        <form method="POST" action="{{ route('student.posts.like',$post) }}">
                            @csrf
                            <button type="submit" class="{{ $post->liked_by_user ? 'is-liked' : '' }}">
                                <i class="bi bi-heart{{ $post->liked_by_user ? '-fill' : '' }}"></i>
                                {{ $post->liked_by_user ? 'Liked' : 'Like' }}
                            </button>
                        </form>
                        <a href="{{ route('student.posts.show',$post) }}"><i class="bi bi-chat"></i> Comment</a>
                        <form method="POST" action="{{ route('student.posts.share',$post) }}" onsubmit="return sharePost(event, '{{ route('student.posts.show',$post) }}');">
                            @csrf
                            <button type="submit"><i class="bi bi-share"></i> Share</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="daruso-empty"><i class="bi bi-newspaper"></i><h3>Your community feed is quiet</h3><p>New posts from DARUSO leadership will appear here.</p></div>
            @endforelse

            <div class="mt-4">{{ $posts->links() }}</div>
        </div>

        <aside class="daruso-feed-sidebar">
            <section class="daruso-panel">
                <div class="daruso-panel-head"><h3>About the feed</h3></div>
                <p class="small text-muted mb-0">A shared space for leadership stories, useful student information and community conversations. Open a post to join the discussion.</p>
            </section>
            <section class="daruso-panel">
                <div class="daruso-panel-head"><h3>Quick links</h3></div>
                <a class="daruso-quick-link" href="{{ route('student.announcements.index') }}"><i class="bi bi-megaphone"></i>Announcements <i class="bi bi-chevron-right ms-auto"></i></a>
                <a class="daruso-quick-link" href="{{ route('student.events.index') }}"><i class="bi bi-calendar-check"></i>Events <i class="bi bi-chevron-right ms-auto"></i></a>
                <a class="daruso-quick-link" href="{{ route('student.documents.index') }}"><i class="bi bi-folder2-open"></i>Documents <i class="bi bi-chevron-right ms-auto"></i></a>
            </section>
        </aside>
    </div>

    @push('scripts')
    <script>
        async function sharePost(event, url) {
            if (!navigator.share) return true;
            event.preventDefault();
            try {
                await navigator.share({ title: document.title, url });
                event.target.submit();
            } catch (error) {
                if (error?.name !== 'AbortError') event.target.submit();
            }
            return false;
        }
    </script>
    @endpush
</x-app-layout>