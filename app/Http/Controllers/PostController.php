<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PostController extends Controller
{
    private const REACTIONS = [
        'love' => ['label' => 'Love', 'icon' => '❤️'],
        'celebrate' => ['label' => 'Celebrate', 'icon' => '🎉'],
        'support' => ['label' => 'Support', 'icon' => '🙌'],
        'insightful' => ['label' => 'Insightful', 'icon' => '💡'],
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Post::class);
        $userId = $request->user()->getKey();

        $posts = Post::query()
            ->published()
            ->with([
                'author',
                'reactions' => fn ($query) => $query->where('user_id', $userId),
            ])
            ->withCount([
                'likes',
                'comments' => fn ($query) => $query->visible(),
                'reactions',
                'reactions as love_reactions_count' => fn ($query) => $query->where('type', 'love'),
                'reactions as celebrate_reactions_count' => fn ($query) => $query->where('type', 'celebrate'),
                'reactions as support_reactions_count' => fn ($query) => $query->where('type', 'support'),
                'reactions as insightful_reactions_count' => fn ($query) => $query->where('type', 'insightful'),
            ])
            ->withExists([
                'likes as liked_by_user' => fn ($query) => $query->where('users.id', $userId),
            ])
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('posts.index', ['posts' => $posts, 'user' => $request->user(), 'reactionTypes' => self::REACTIONS]);
    }

    public function show(Request $request, Post $post): View
    {
        $this->authorize('view', $post);
        abort_unless($post->isPublished() || $post->author_id === $request->user()->getKey(), 404);

        $userId = $request->user()->getKey();

        $post->load([
            'author',
            'reactions' => fn ($query) => $query->where('user_id', $userId),
            'comments' => fn ($query) => $query->visible()->with('user')->latest(),
        ])->loadCount([
            'likes',
            'reactions',
            'reactions as love_reactions_count' => fn ($query) => $query->where('type', 'love'),
            'reactions as celebrate_reactions_count' => fn ($query) => $query->where('type', 'celebrate'),
            'reactions as support_reactions_count' => fn ($query) => $query->where('type', 'support'),
            'reactions as insightful_reactions_count' => fn ($query) => $query->where('type', 'insightful'),
        ]);

        return view('posts.show', [
            'post' => $post,
            'liked' => $post->likedBy($request->user()),
            'reactionTypes' => self::REACTIONS,
        ]);
    }

    public function like(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('like', $post);
        $userId = $request->user()->getKey();

        $liked = DB::transaction(function () use ($post, $userId): bool {
            $post->newQuery()->whereKey($post->getKey())->lockForUpdate()->firstOrFail();

            if ($post->likes()->whereKey($userId)->exists()) {
                $post->likes()->detach($userId);

                return false;
            }

            $post->likes()->syncWithoutDetaching([$userId]);

            return true;
        });

        return back()->with('success', $liked ? 'Post liked.' : 'Post unliked.');
    }

    public function react(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('react', $post);

        $data = $request->validate([
            'type' => ['required', 'string', 'in:love,celebrate,support,insightful'],
        ]);

        $reactionAdded = DB::transaction(function () use ($post, $request, $data): bool {
            $post->newQuery()->whereKey($post->getKey())->lockForUpdate()->firstOrFail();
            $reaction = $post->reactions()->where('user_id', $request->user()->getKey())->first();

            if ($reaction?->type === $data['type']) {
                $reaction->delete();

                return false;
            }

            $post->reactions()->updateOrCreate(
                ['user_id' => $request->user()->getKey()],
                ['type' => $data['type']],
            );

            return true;
        });

        return back()->with(
            'success',
            $reactionAdded ? self::REACTIONS[$data['type']]['label'].' reaction added.' : 'Reaction removed.',
        );
    }

    public function comment(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('comment', $post);

        $data = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->getKey(),
            'content' => $data['content'],
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function deleteComment(Request $request, Post $post, PostComment $comment): RedirectResponse
    {
        $this->authorize('deleteComment', [$post, $comment]);
        $comment->delete();

        return back()->with('success', 'Comment removed.');
    }

    public function share(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('share', $post);
        $post->increment('share_count');

        return back()->with('success', 'Share recorded.');
    }
}
