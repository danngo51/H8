<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Subpage;
use Illuminate\Support\Facades\Auth;
use App\Models\Like;
use Illuminate\Support\Str;


class PostController extends Controller
{
     // Show a form for creating a new post in a subpage
     public function create(Subpage $subpage)
     {
         return view('posts.create', compact('subpage'));
     }
 
    public function store(Request $request, $slug)
    {
        $subpage = Subpage::where('slug', $slug)->firstOrFail();

        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $postSlug = Str::slug($request->title);

        // Ensure uniqueness of the post slug within the subpage
        $postSlugCount = Post::where('subpage_id', $subpage->id)
                             ->where('slug', 'LIKE', $postSlug . '%')
                             ->count();


        $post = new Post();
        $post->title = $request->title;
        $post->content = $request->content;
        $post->user_id = Auth::id();
        $post->subpage_id = $subpage->id;
        $post->slug = $postSlugCount > 0 ? "{$postSlug}-{$postSlugCount}" : $postSlug;

        $post->save();

        return redirect()->route('subpages.show', $subpage->slug);
    }

    public function toggleLike($slug, Post $post)
    {
        $like = $post->likes()->where('user_id', auth()->id())->first();
    
        if ($like) {
            $like->delete();
        } else {
            $post->likes()->create(['user_id' => auth()->id()]);
        }
    
        return back();
    }
    
    public function destroy($slug, $postSlug)
    {
        $post = Post::where('slug', $postSlug)->firstOrFail();
    
        if ($post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    
        $post->delete();
    
        return redirect()->route('subpages.show', $slug)->with('status', 'Post deleted successfully.');
    }




}