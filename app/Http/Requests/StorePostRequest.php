<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;

class StorePostRequest extends FormRequest
{
    public static array $extensions = [
      'jpg', 'jpeg', 'png', 'bmp', 'gif', 'svg', 'webp',
      'mp3', 'mp4', 'wav',
      'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'pdf',
      'zip', 'rar', '7z', 'tar', 'gz', 'tar.gz',
    ];

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
        'attachments' => [
          'array','max:50',
          function ($attribute, $value, $fail) {
            // Custom rule to check the total size of all files
            $totalSize = collect($value)->sum(fn (UploadedFile $file) => $file->getSize());
            // dd($totalSize, $value);

            if ($totalSize > 1 * 1024 * 1024 * 1024 ) {
              $fail('The total size of all files must not exceed 1GB.');
            }
          }
        ],
        'attachments.*' => [
          'file',
          File::types(self::$extensions),
        ],
        'user_id' => ['numeric']
      ];
    }

    protected function prepareForValidation()
    {
      $this->merge([
        'user_id' => auth()->user()->id,
        'body' => $this->input('body') ?: ''
      ]);
    }

    public function messages()
    {
      return [
        'attachments.*.file' => 'Each attachment must be a file',
        'attachments.*.mimes' => 'Invalid file type for attachments'
      ];
    }
}
