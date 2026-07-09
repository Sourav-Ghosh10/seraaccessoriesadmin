<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppPopupRequest extends FormRequest
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
            'banner_image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'status' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'banner_image.required' => 'An announcement popup image is required.',
            'banner_image.image' => 'The uploaded file must be a valid image (jpeg, png, jpg, webp, gif).',
            'banner_image.max' => 'The image size must not exceed 10 MB.',
        ];
    }
}
