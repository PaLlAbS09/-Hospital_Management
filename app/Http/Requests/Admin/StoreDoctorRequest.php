<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') instanceof \App\Models\Admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name' => ['required', 'string', 'min:1', 'max:50'],
            'specialization' => ['required', 'string', 'min:2', 'max:100'],
            'email' => [
                'required', 'string', 'email', 'max:100',
                Rule::unique('doctors', 'email'),
            ],
            'contact' => ['required', 'string', 'digits_between:7,15'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'A doctor already exists with this email address.',
            'contact.digits_between' => 'Enter a valid contact number (7-15 digits, no spaces or symbols).',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
