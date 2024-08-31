<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
  public function index()
  {
    $userId = Auth::id();
    $posts = Post::query() // SELECT * FROM posts
      ->withCount('reactions') // SELECT COUNT(*) FROM reactions
      ->with([
        'comments' => function ($query) use ($userId) {
          $query->withCount('reactions'); // SELECT * FROM comments WHERE post_id IN (1, 2, 3, ...)
          // SELECT COUNT(*) FROM reactions
        },
        'reactions' => function ($query) use ($userId) {
          $query->where('user_id', $userId); // SELECT * FROM reactions WHERE user_id = ?
        }])
      ->latest()
      ->paginate(20);

    return Inertia::render('Home', [
      'posts' => PostResource::collection($posts)
    ]);
  }
}
