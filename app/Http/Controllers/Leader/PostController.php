<?php
namespace App\Http\Controllers\Leader;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PostController extends Controller {
 public function index(Request $request): View { $this->authorize('viewAny',Post::class); $posts=Post::query()->with('author')->withCount(['likes','comments'])->latest()->paginate(15); return view('leader.posts.index',compact('posts')); }
 public function create(): View { $this->authorize('create',Post::class); return view('leader.posts.create'); }
 public function store(StorePostRequest $request): RedirectResponse {
  $this->authorize('create',Post::class); $data=$request->validated(); $published=(bool)($data['is_published']??false);
  Post::create([...$data,'author_id'=>$request->user()->getKey(),'is_published'=>$published,'published_at'=>$published?($data['published_at']??now()):null]);
  return redirect()->route('leader.posts.index')->with('success','Post created.');
 }
 public function edit(Post $post): View { $this->authorize('update',$post); return view('leader.posts.edit',compact('post')); }
 public function update(StorePostRequest $request, Post $post): RedirectResponse {
  $this->authorize('update',$post); $data=$request->validated(); $published=(bool)($data['is_published']??$post->is_published);
  $post->update([...$data,'is_published'=>$published,'published_at'=>$published?($data['published_at']??$post->published_at??now()):null]);
  return redirect()->route('leader.posts.index')->with('success','Post updated.');
 }
 public function destroy(Post $post): RedirectResponse { $this->authorize('delete',$post); $post->delete(); return back()->with('success','Post deleted.'); }
 public function publish(Post $post): RedirectResponse { $this->authorize('publish',$post); $post->update(['is_published'=>true,'published_at'=>$post->published_at??now()]); return back()->with('success','Post published.'); }
}