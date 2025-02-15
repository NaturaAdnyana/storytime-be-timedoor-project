<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'type' => 'required|string|in:profile,story',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'The image file is required.',
            'file.image' => 'The uploaded file must be an image.',
            'file.mimes' => 'The image must be in jpeg, png, jpg, gif, or webp format.',
            'file.max' => 'The image size must not exceed 2MB.',
            'type.in' => 'The type must be either "profile" or "story".',
        ];
    }
}
