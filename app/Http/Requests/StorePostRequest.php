<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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

    protected function prepareForValidation()
    {
      $this->merge([
        'user_id' => auth()->user()->id
      ]);
    }
}
