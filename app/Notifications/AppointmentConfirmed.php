<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\SmsChannel;
use App\Support\Sms\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    // SMS is attempted before mail because it can never throw (the channel
    // swallows gateway errors). Laravel aborts the channel loop on the first
    // exception, so putting mail first would mean a broken SMTP server also
    // suppresses the SMS.
    public function via(object $notifiable): array
    {
        return [SmsChannel::class, 'mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment;

        return (new MailMessage)
            ->subject('Appointment confirmed - '.$appointment->appointment_date->format('d M Y'))
            ->greeting('Hello '.$appointment->patient->full_name.',')
            ->line('Your appointment with Dr. '.$appointment->doctor->full_name.' at '.$appointment->clinic->clinic_name.' is confirmed.')
            ->line('Date: '.$appointment->appointment_date->format('d M Y').' at '.$appointment->formatted_time.'.')
            ->line('Contact phone for this booking: '.($appointment->notification_phone ?? '—').'.')
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
            'contact_phone' => $this->appointment->notification_phone,
        ];
    }

    /**
     * SMS confirmation of the booked slot.
     */
    public function toSms(object $notifiable): ?SmsMessage
    {
        $appointment = $this->appointment;

        return new SmsMessage(
            $appointment->notification_phone ?? '',
            sprintf(
                config('app.name').': your appointment with Dr. %s at %s is confirmed for %s at %s. Contact phone on file: %s.',
                $appointment->doctor->full_name,
                $appointment->clinic->clinic_name,
                $appointment->appointment_date->format('d M Y'),
                $appointment->formatted_time,
                $appointment->notification_phone ?? '—'
            )
        );
    }
}
