<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Patient;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'clinic_id' => ['required', 'integer', Rule::exists('clinics', 'clinic_id')],
            'doctor_id' => ['required', 'integer', Rule::exists('doctors', 'doctor_id')],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clinic_id.exists' => 'The selected clinic is no longer available.',
            'doctor_id.exists' => 'The selected doctor is no longer available.',
            'appointment_date.after_or_equal' => 'Appointments can only be booked for today or a future date.',
            'appointment_time.date_format' => 'Please select one of the offered time slots.',
        ];
    }
}
