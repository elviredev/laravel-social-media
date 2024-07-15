<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
      // todo peut-être à changer plus tard
      $post = Post::where('id', $this->input('id'))->where('user_id', Auth::id())->first();

      return !!$post;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
      return [
        'body' => ['nullable', 'string'],
        'user_id' => ['numeric']
      ];
    }
}
