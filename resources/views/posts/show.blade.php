<x-app-layout>
    @section('title', $post->title)
    @section('eyebrow', 'COMMUNITY STORY')
    @section('heading', '')
    @section('subheading', '')

    <div class="daruso-article-shell">
        <article class="daruso-article">
            @if($post->cover_image_url)
                <div class="daruso-article-cover">
                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}">
                </div>
            @endif

            <div class="daruso-article-header">
                <div class="daruso-post-head">
                    <div class="daruso-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($post->author?->name ?? 'D', 0, 1)) }}</div>
                    <div>
                        <strong>{{ $post->author?->name ?? 'DARUSO' }}</strong>
                        <small>Published {{ $post->published_at?->diffForHumans() }}</small>
                    </div>
                </div>

                <h1>{{ $post->title }}</h1>

                @if($post->excerpt)
                    <p>{{ $post->excerpt }}</p>
                @endif
            </div>

            <div class="daruso-article-content">{!! nl2br(e($post->content)) !!}</div>

            <div class="daruso-article-actions">
                <form method="POST" action="{{ route('student.posts.like', $post) }}">
                    @csrf
                    <button class="btn {{ $liked ? 'btn-primary' : 'btn-light' }}">
                        <i class="bi bi-heart{{ $liked ? '-fill' : '' }} me-1"></i>
                        {{ $liked ? 'Liked' : 'Like' }}
                        <span class="ms-1">{{ $post->likes_count }}</span>
                    </button>
                </form>

                @include('posts._reactions', ['post' => $post])

                <form method="POST" action="{{ route('student.posts.share', $post) }}" onsubmit="return sharePost(event, '{{ route('student.posts.show', $post) }}');">
                    @csrf
                    <button class="btn btn-light">
                        <i class="bi bi-share me-1"></i>
                        Share{{ $post->share_count ? ' · '.$post->share_count : '' }}
                    </button>
                </form>
            </div>

            <div class="daruso-reaction-counts">
                @if($post->reactions_count)
                    <span>❤️ {{ $post->love_reactions_count }}</span>
                    <span>🎉 {{ $post->celebrate_reactions_count }}</span>
                    <span>🙌 {{ $post->support_reactions_count }}</span>
                    <span>💡 {{ $post->insightful_reactions_count }}</span>
                @endif
            </div>
        </article>

        <section class="daruso-comments daruso-panel">
            <div class="daruso-panel-head">
                <h2><i class="bi bi-chat-dots"></i> Conversation</h2>
                <span>{{ $post->comments->count() }} comments</span>
            </div>

            <form method="POST" action="{{ route('student.posts.comments.store', $post) }}" class="daruso-comment-form">
                @csrf
                <div class="daruso-avatar">ME</div>
                <div class="flex-grow-1">
                    <textarea name="content" rows="3" class="form-control" maxlength="2000" required placeholder="Share a thoughtful comment..."></textarea>
                    @error('content')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <button class="btn btn-primary mt-2">
                        <i class="bi bi-send me-1"></i>Post comment
                    </button>
                </div>
            </form>

            <div class="daruso-comment-list">
                @forelse($post->comments as $comment)
                    <div class="daruso-comment">
                        <div class="daruso-avatar daruso-avatar-sm">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($comment->user?->name ?? 'U', 0, 1)) }}</div>
                        <div class="flex-grow-1">
                            <div class="daruso-comment-bubble">
                                <strong>{{ $comment->user?->name ?? 'Community member' }}</strong>
                                <p>{{ $comment->content }}</p>
                            </div>
                            <div class="daruso-comment-meta">
                                <span>{{ $comment->created_at->diffForHumans() }}</span>
                                @can('deleteComment', [$post, $comment])
                                    <form method="POST" action="{{ route('student.posts.comments.destroy', [$post, $comment]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link btn-sm p-0 text-danger">Remove</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="daruso-empty py-4">
                        <i class="bi bi-chat-square-text"></i>
                        <h3>No comments yet</h3>
                        <p>Start the conversation respectfully.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
        async function sharePost(event, url) {
            if (!navigator.share) return true;

            event.preventDefault();

            try {
                await navigator.share({ title: @json($post->title), url });
                event.target.submit();
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    event.target.submit();
                }
            }

            return false;
        }
    </script>
    @endpush
</x-app-layout>