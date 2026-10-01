<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'contact_number' => ['required', 'string', 'digits_between:7,15'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_name.required' => 'Please tell us your name.',
            'contact_number.digits_between' => 'Enter a valid contact number (7-15 digits, no spaces or symbols).',
            'message.min' => 'Please describe your query in at least :min characters.',
        ];
    }
}
