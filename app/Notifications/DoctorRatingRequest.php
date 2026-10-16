<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class DoctorRatingRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment;

        // Signed URL: works even when the patient clicks the email while logged out.
        // The signature proves the link came from our email, so review() can show
        // the login page (with redirect back) instead of a bare 404.
        $reviewUrl = URL::signedRoute(
            'patient.appointments.review.create',
            ['appointment' => $appointment->getKey()],
            now()->addDays(7)
        );

        return (new MailMessage)
            ->subject('Rate your visit with Dr. '.$appointment->doctor->full_name)
            ->greeting('Hello '.$appointment->patient->full_name.',')
            ->line('Your session with Dr. '.$appointment->doctor->full_name.' ('.$appointment->doctor->specialization.') at '.$appointment->clinic->clinic_name.' is now marked as completed.')
            ->line('Visit details: '.$appointment->appointment_date->format('d M Y').' at '.$appointment->formatted_time.'.')
            ->action('Rate the doctor & share your experience', $reviewUrl)
            ->line('Your rating helps other patients pick the right doctor, and it takes less than a minute.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->appointment_id,
            'clinic' => $this->appointment->clinic->clinic_name,
            'doctor' => $this->appointment->doctor->full_name,
            'department' => $this->appointment->doctor->specialization,
            'date' => $this->appointment->appointment_date->toDateString(),
            'time' => $this->appointment->formatted_time,
            'message' => 'Rate your doctor and share your experience.',
            'url' => route('patient.appointments.review.create', $this->appointment),
        ];
    }
}
