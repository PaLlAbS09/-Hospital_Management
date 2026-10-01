<?php

namespace App\Http\Requests\Doctor;

use App\Models\Doctor;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Doctor;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'disease' => ['nullable', 'string', 'max:255'],
            'allergies' => ['nullable', 'string', 'max:255'],
            'prescription_details' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'disease.max' => 'The diagnosis may not be longer than 255 characters.',
            'allergies.max' => 'The allergies field may not be longer than 255 characters.',
            'prescription_details.max' => 'The prescription may not be longer than 5000 characters.',
        ];
    }
}
