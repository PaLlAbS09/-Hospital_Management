<?php

namespace App\Http\Requests\Clinic;

use App\Models\Clinic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClinicProfileRequest extends FormRequest
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
            'clinic_name' => ['required', 'string', 'min:3', 'max:100'],
            'area' => ['required', 'string', 'min:2', 'max:100'],
            'contact_number' => ['required', 'string', 'digits_between:7,15'],
            // The email doubles as the clinic's login, so it may only move to an
            // address no other clinic already uses.
            'email' => [
                'nullable', 'string', 'email', 'max:100',
                Rule::unique('clinics', 'email')->ignore($this->user()->clinic_id, 'clinic_id'),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clinic_name.min' => 'The clinic name must be at least 3 characters long.',
            'area.min' => 'Enter the area your clinic serves.',
            'contact_number.digits_between' => 'Enter a valid contact number (7-15 digits, no spaces or symbols).',
            'email.unique' => 'Another clinic is already registered with this email address.',
            'address.max' => 'The address may not be longer than 255 characters.',
            'latitude.numeric' => 'Latitude must be a number, for example 23.232403.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a number, for example 87.861506.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ];
    }
}
