<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') instanceof Admin;
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
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:70'],
            'experience_note' => ['nullable', 'string', 'max:1000'],
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
            'experience_years.max' => 'Experience may not be greater than 70 years.',
            'experience_note.max' => 'The experience note may not be longer than 1000 characters.',
        ];
    }
}
