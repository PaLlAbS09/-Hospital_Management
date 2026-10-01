<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClinicRegistrationRequest extends FormRequest
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
            'clinic_name' => ['required', 'string', 'min:3', 'max:100'],
            'area' => ['required', 'string', 'min:2', 'max:100'],
            'contact_number' => ['required', 'string', 'digits_between:7,15'],
            'email' => [
                'required', 'string', 'email', 'max:100',
                Rule::unique('clinics', 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_number.digits_between' => 'Enter a valid contact number (7-15 digits, no spaces or symbols).',
            'email.unique' => 'A clinic has already been registered with this email address.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
