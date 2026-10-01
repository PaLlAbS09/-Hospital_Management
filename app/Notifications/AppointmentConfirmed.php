<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmed extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('Appointment confirmed - '.$appointment->appointment_date->format('d M Y'))
            ->greeting('Hello '.$appointment->patient->full_name.',')
            ->line('Your appointment with Dr. '.$appointment->doctor->full_name.' at '.$appointment->clinic->clinic_name.' is confirmed.')
            ->line('Date: '.$appointment->appointment_date->format('d M Y').' at '.$appointment->formatted_time.'.')
            ->action('View my appointments', route('patient.dashboard'))
            ->line('Please arrive 15 minutes early.');
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
            'date' => $this->appointment->appointment_date->toDateString(),
            'time' => $this->appointment->formatted_time,
        ];
    }
}
