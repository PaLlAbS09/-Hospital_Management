<?php

namespace App\Http\Requests\Patient;

use App\Models\Appointment;
use App\Models\DoctorReview;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');

        return $this->user() instanceof Patient
            && $appointment instanceof Appointment
            && $appointment->patient_id === $this->user()->patient_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rating' => [
                'required', 'integer',
                Rule::in(DoctorReview::ratingScale()),
            ],
            'experience' => ['nullable', 'string', 'max:2000'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Please choose a rating from 1 to 5 stars.',
            'rating.in' => 'Please choose a rating from 1 to 5 stars.',
            'experience.max' => 'Your experience may not be longer than 2000 characters.',
        ];
    }
}
