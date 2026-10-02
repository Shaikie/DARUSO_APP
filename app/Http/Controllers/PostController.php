<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->getKey();

        $posts = Post::query()
            ->published()
            ->with('author')
            ->withCount(['likes', 'comments'])
            ->withExists([
                'likes as liked_by_user' => fn ($query) => $query->where('users.id', $userId),
            ])
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('posts.index', ['posts' => $posts, 'user' => $request->user()]);
    }

    public function show(Request $request, Post $post): View
    {
        $this->authorize('view', $post);
        abort_unless($post->isPublished() || $post->author_id === $request->user()->getKey(), 404);

        $post->load(['author', 'comments' => fn ($query) => $query->with('user')->latest()])
            ->loadCount('likes');

        return view('posts.show', [
            'post' => $post,
            'liked' => $post->likedBy($request->user()),
        ]);
    }

    public function like(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('like', $post);
        $userId = $request->user()->getKey();

        if ($post->likes()->whereKey($userId)->exists()) {
            $post->likes()->detach($userId);
            return back()->with('success', 'Post unliked.');
        }

        $post->likes()->syncWithoutDetaching([$userId]);

        return back()->with('success', 'Post liked.');
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