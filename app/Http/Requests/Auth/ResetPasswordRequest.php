<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the "choose a new password" step for every role.
 */
class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'password' => ['required', 'string', 'confirmed', 'min:6', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'token.required' => 'This reset link is invalid. Please request a new one.',
            'email.required' => 'Please enter the email address on your account.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please choose a new password.',
            'password.confirmed' => 'The two passwords do not match.',
            'password.min' => 'Your password must be at least :min characters long.',
        ];
    }

    /**
     * Credentials handed to the password broker.
     *
     * @return array<string, string>
     */
    public function credentials(): array
    {
        return [
            'token' => $this->string('token')->toString(),
            'email' => $this->string('email')->trim()->toString(),
            'password' => $this->string('password')->toString(),
            'password_confirmation' => $this->string('password_confirmation')->toString(),
        ];
    }
}
