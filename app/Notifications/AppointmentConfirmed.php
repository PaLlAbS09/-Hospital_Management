<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $doctorName = $this->appointment->doctor?->full_name ?? 'your doctor';

        return (new MailMessage)
            ->subject('Appointment Confirmation - ' . config('app.name'))
            ->greeting('Hello ' . ($notifiable->name ?? 'Patient') . ',')
            ->line('Your doctor appointment has been successfully scheduled.')
            ->line('Doctor: Dr. ' . $doctorName)
            ->line('Date: ' . $this->appointment->appointment_date)
            ->line('Time Slot: ' . $this->appointment->appointment_time)
            ->action('View Appointments', route('patient.dashboard'))
            ->line('Thank you for booking with us!');
    }
}
