<?php
namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
}
