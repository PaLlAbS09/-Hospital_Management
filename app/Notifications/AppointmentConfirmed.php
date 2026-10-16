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
        $clinic = $this->appointment->clinic;
        $clinicName = $clinic?->clinic_name ?? 'the clinic';
        $clinicAddress = $clinic?->full_address ?: null;
        $clinicPhone = $clinic?->contact_number ?: null;
        $directionsUrl = $clinic?->directions_url;

        $message = (new MailMessage)
            ->subject('Appointment Confirmation - ' . config('app.name'))
            ->greeting('Hello ' . ($notifiable->name ?? 'Patient') . ',')
            ->line('Your doctor appointment has been successfully scheduled.')
            ->line('Doctor: Dr. ' . $doctorName)
            ->line('Date: ' . $this->appointment->appointment_date)
            ->line('Time Slot: ' . $this->appointment->appointment_time)
            ->line('Clinic: ' . $clinicName);

        if ($clinicAddress) {
            $message->line('Clinic Address: ' . $clinicAddress);
        }

        if ($clinicPhone) {
            $message->line('Clinic Contact: ' . $clinicPhone);
        }

        if ($directionsUrl) {
            // Map link: render as a markdown link line (MailMessage supports only one ->action() button,
            // so directions goes here as a clickable link alongside the dashboard button below).
            $message->line('Location Map: [Get Directions / View on Google Maps](' . $directionsUrl . ')');
        }

        return $message
            ->action('View Appointments', route('patient.dashboard'))
            ->line('Thank you for booking with us!');
    }
}
