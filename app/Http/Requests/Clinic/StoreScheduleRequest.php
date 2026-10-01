<?php

namespace App\Http\Requests\Clinic;

use App\Models\Clinic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
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
            'doctor_id' => [
                'required', 'integer',
                Rule::exists('doctors', 'doctor_id'),
            ],
            'schedule_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'patient_capacity' => ['required', 'integer', 'min:1', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doctor_id.exists' => 'Please choose a doctor that exists in the system.',
            'schedule_date.after_or_equal' => 'Schedules can only be created for today or a future date.',
            'end_time.after' => 'The end time must be later than the start time.',
            'patient_capacity.max' => 'Patient capacity may not be greater than 200.',
        ];
    }
}
