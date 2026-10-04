<?php

namespace App\Http\Requests\Clinic;

use App\Models\Clinic;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClinicAboutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Clinic;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'about' => ['nullable', 'string', 'max:5000'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_banner' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'about.max' => 'The about text may not be longer than 5000 characters.',
            'banner_image.image' => 'The banner must be an image file (JPG, PNG or WEBP).',
            'banner_image.max' => 'The banner image may not be larger than 4 MB.',
        ];
    }
}
