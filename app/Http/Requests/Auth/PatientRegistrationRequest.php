<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRegistrationRequest extends FormRequest
{
    /**
     * @return array<int, string>
     */
    public static function genders(): array
    {
        return ['Male', 'Female', 'Other'];
    }

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
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name' => ['required', 'string', 'min:1', 'max:50'],
            'gender' => ['required', 'string', Rule::in(self::genders())],
            'email' => [
                'required', 'string', 'email', 'max:100',
                Rule::unique('patients', 'email'),
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
            'gender.in' => 'Please choose one of the listed genders.',
            'contact.digits_between' => 'Enter a valid contact number (7-15 digits, no spaces or symbols).',
            'email.unique' => 'An account already exists with this email address.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
