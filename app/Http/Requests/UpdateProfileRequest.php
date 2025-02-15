<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'old_password' => ['nullable', 'string', 'min:8', 'max:255'],
            'new_password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'max:255',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "The name field is required.",
            'old_password.min' => 'The password must be at least 8 characters.',
            'new_password.min' => 'The password must be at least 8 characters.',
            'new_password.confirmed' => 'The password confirmation does not match.',
            'new_password.regex' => 'The password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.',
        ];
    }
}
