<?php
namespace App\Http\Controllers;

use App\Http\Enums\PostReactionEnum;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\PostReaction;
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

  public function postReaction(Request $request, Post $post)
  {
    // obtenir les data après leur validation
    $data = $request->validate([
      'reaction' => [Rule::enum(PostReactionEnum::class)]
    ]);

    // si l'utilisateur actuel a déja créé une réaction sur ce post, on la supprime sinon on l'a créé en BDD
    $userId = Auth::id();
    $reaction = PostReaction::where('user_id', $userId)->where('post_id', $post->id)->first();

    if ($reaction) {
      $hasReaction = false;
      // supprimer totalement la reaction existante en bdd
      $reaction->delete();
    } else {
      $hasReaction = true;
      // créer la reaction en bdd
      PostReaction::create([
        'post_id' => $post->id,
        'user_id' => $userId,
        'type' => $data['reaction']
      ]);
    }

    // nombre total de réactions sur un post
    $reactions = PostReaction::where('post_id', $post->id)->count();

    // envoyer la réponse
    return response([
      'num_of_reactions' => $reactions,
      'current_user_has_reaction' => $hasReaction
    ]);
  }
}
