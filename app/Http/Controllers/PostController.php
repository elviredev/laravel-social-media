<?php
namespace App\Http\Controllers;

use App\Http\Enums\ReactionEnum;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
  /**
   * Store a newly created resource in storage.
   * @throws \Exception
   */
  public function store(StorePostRequest $request)
  {
    $data = $request->validated();
    $user = $request->user();

    DB::beginTransaction();
    $allFilePaths = [];
    try {
      $post = Post::create($data);

      /** @var \Illuminate\Http\UploadedFile[] $files */
      $files = $data['attachments'] ?? [];

      foreach ($files as $file) {
        // enregistrer le fichier dans le stockage et en bdd
        $path = $file->store('attachments/'.$post->id, 'public');
        $allFilePaths[] = $path;
        PostAttachment::create([
          'post_id' => $post->id,
          'name' => $file->getClientOriginalName(),
          'path' => $path,
          'mime' => $file->getMimeType(),
          'size' => $file->getSize(),
          'created_by' => $user->id,
        ]);
      }
      DB::commit();
    } catch (\Exception $e) {
      foreach ($allFilePaths as $path) {
        Storage::disk('public')->delete($path);
      }
      DB::rollBack();
      throw $e;
    }

    return back();
  }

  /**
   * Update the specified resource in storage.
   * @throws \Exception
   */
  public function update(UpdatePostRequest $request, Post $post)
  {
    $user = $request->user();

    DB::beginTransaction();
    $allFilePaths = [];
    try {
      $data = $request->validated();
      $post->update($data);

      $deleted_ids = $data['deleted_file_ids'] ?? [];

      // supprimer les pièce-jointes
      $attachments = PostAttachment::query()
        ->where('post_id', $post->id)
        ->whereIn('id', $deleted_ids)
        ->get();

      foreach ($attachments as $attachment) {
        $attachment->delete();
      }

      /** @var \Illuminate\Http\UploadedFile[] $files */
      $files = $data['attachments'] ?? [];

      foreach ($files as $file) {
        // enregistrer le fichier dans le stockage et en bdd
        $path = $file->store('attachments/'.$post->id, 'public');
        $allFilePaths[] = $path;
        PostAttachment::create([
          'post_id' => $post->id,
          'name' => $file->getClientOriginalName(),
          'path' => $path,
          'mime' => $file->getMimeType(),
          'size' => $file->getSize(),
          'created_by' => $user->id,
        ]);
      }
      DB::commit();
    } catch (\Exception $e) {
      foreach ($allFilePaths as $path) {
        Storage::disk('public')->delete($path);
      }
      DB::rollBack();
      throw $e;
    }

    return back();
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Post $post)
  {
    // TODO peut-être à changer plus tard
    $userId = Auth::id();

    if ($post->user_id !== $userId) {
      return response("You don't have permission to delete this post.", 403);
    }
    $post->delete();
    return back();
  }

  /**
   * Télécharger une pièce-jointe
   * @param PostAttachment $attachment
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   */
  public function downloadAttachment(PostAttachment $attachment)
  {
    // TODO check if user has permission to download that attachment

    return response()
      ->download(Storage::disk('public')->path($attachment->path), $attachment->name);
  }

  /**
   * Envoyer une réaction de type Like sur un article et la créer en BDD
   * @param Request $request
   * @param Post $post
   * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
   */
  public function postReaction(Request $request, Post $post)
  {
    // obtenir les data après leur validation
    $data = $request->validate([
      'reaction' => [Rule::enum(ReactionEnum::class)]
    ]);

    // si l'utilisateur actuel a déja créé une réaction sur ce post, on la supprime sinon on l'a créé en BDD
    $userId = Auth::id();
    $reaction = Reaction::where('user_id', $userId)
      ->where('object_id', $post->id)
      ->where('object_type', Post::class)
      ->first();

    if ($reaction) {
      $hasReaction = false;
      // supprimer totalement la reaction existante en bdd
      $reaction->delete();
    } else {
      $hasReaction = true;
      // créer la reaction en bdd
      Reaction::create([
        'object_id' => $post->id,
        'object_type' => Post::class,
        'user_id' => $userId,
        'type' => $data['reaction']
      ]);
    }

    // nombre total de réactions sur un post
    $reactions = Reaction::where('object_id', $post->id)->where('object_type', Post::class)->count();

    // envoyer la réponse
    return response([
      'num_of_reactions' => $reactions,
      'current_user_has_reaction' => $hasReaction
    ]);
  }

  /**
   * Créer un commentaire sur un article
   * @param Request $request
   * @param Post $post
   * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
   */
  public function createComment(Request $request, Post $post)
  {
    $data = $request->validate([
      'comment' => ['required']
    ]);

    $comment = Comment::create([
      'post_id' => $post->id,
      'comment' => nl2br($data['comment']),
      'user_id' => Auth::id()
    ]);

    return response(new CommentResource($comment), status: 201);
  }

  /**
   * Supprimer un commentaire
   * @param Comment $comment
   * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
   */
  public function deleteComment(Comment $comment)
  {
    if ($comment->user_id !== Auth::id()) {
      return response("You don't have permission to delete this comment.", 403);
    }
    $comment->delete();
    return response('', 204);
  }


  /**
   * Modifier un commentaire
   * @param UpdateCommentRequest $request
   * @param Comment $comment
   * @return CommentResource
   */
  public function updateComment(UpdateCommentRequest $request, Comment $comment)
  {
    $data = $request->validated();

    $comment->update([
      'comment' => nl2br($data['comment'])
    ]);

    return new CommentResource($comment);
  }


  /**
   * Envoyer une réaction de type Like sur un commentaire et la créer en BDD
   * @param Request $request
   * @param Comment $comment
   * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
   */
  public function commentReaction(Request $request, Comment $comment)
  {
    // obtenir les data après leur validation
    $data = $request->validate([
      'reaction' => [Rule::enum(ReactionEnum::class)]
    ]);

    // si l'utilisateur actuel a déja créé une réaction sur ce comment, on la supprime sinon on l'a créé en BDD
    $userId = Auth::id();
    $reaction = Reaction::where('user_id', $userId)
      ->where('object_id', $comment->id)
      ->where('object_type', Comment::class)
      ->first();

    if ($reaction) {
      $hasReaction = false;
      // supprimer totalement la reaction existante en bdd
      $reaction->delete();
    } else {
      $hasReaction = true;
      // créer la reaction en bdd
      Reaction::create([
        'object_id' => $comment->id,
        'object_type' => Comment::class,
        'user_id' => $userId,
        'type' => $data['reaction']
      ]);
    }

    // nombre total de réactions sur un commentaire
    $reactions = Reaction::where('object_id', $comment->id)->where('object_type', Comment::class)->count();

    // envoyer la réponse
    return response([
      'num_of_reactions' => $reactions,
      'current_user_has_reaction' => $hasReaction
    ]);
  }

}
