<?php

namespace App\Http\Requests\Clinic;

use App\Models\Clinic;
use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
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
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Give the offer a short headline.',
            'title.max' => 'The headline may not be longer than 150 characters.',
            'discount_percent.required' => 'Enter the discount percentage.',
            'discount_percent.min' => 'The discount must be at least 1%.',
            'discount_percent.max' => 'The discount may not exceed 100%.',
            'valid_until.after_or_equal' => 'The end date must be on or after the start date.',
            'description.max' => 'The description may not be longer than 1000 characters.',
        ];
    }
}
