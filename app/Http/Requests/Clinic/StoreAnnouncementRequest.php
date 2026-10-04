<?php

namespace App\Http\Requests\Clinic;

use App\Models\Clinic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
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
                'nullable', 'integer',
                Rule::exists('doctors', 'doctor_id'),
            ],
            'department' => ['required', 'string', 'min:2', 'max:100'],
            'joining_date' => ['required', 'date'],
            'joining_time' => ['required', 'date_format:H:i'],
            'message' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doctor_id.exists' => 'Please choose a doctor that exists in the system.',
            'department.required' => 'Tell patients which department the new doctor is joining.',
            'joining_date.required' => 'A joining date is required.',
            'joining_time.required' => 'A joining time is required.',
            'joining_time.date_format' => 'The joining time must be a valid time.',
            'message.max' => 'The announcement message may not be longer than 1000 characters.',
        ];
    }
}
