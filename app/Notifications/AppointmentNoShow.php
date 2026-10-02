<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\SmsChannel;
use App\Support\Sms\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentNoShow extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    /**
     * SMS is attempted before mail because it can never throw (the channel
     * swallows gateway errors). Laravel aborts the channel loop on the first
     * exception, so putting mail first would mean a broken SMTP server also
     * suppresses the SMS.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [SmsChannel::class, 'mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment;

        return (new MailMessage)
            ->subject('Appointment auto-cancelled - no show on '.$appointment->appointment_date->format('d M Y'))
            ->greeting('Hello '.$appointment->patient->full_name.',')
            ->line('Your appointment with Dr. '.$appointment->doctor->full_name.' at '.$appointment->clinic->clinic_name.' scheduled for '.$appointment->appointment_date->format('d M Y').' at '.$appointment->formatted_time.' was automatically cancelled because you did not arrive after the slot time passed.')
            ->line('Contact phone on file for this booking: '.($appointment->notification_phone ?? '—').'.')
            ->line('If you still need care, please book a new appointment.')
            ->action('Book a new appointment', route('patient.clinics.index'))
            ->line('Thank you for using '.config('app.name').'.');
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
            'reason' => 'no_show',
        ];
    }

    /**
     * SMS telling the patient the slot was released because they never showed up.
     */
    public function toSms(object $notifiable): ?SmsMessage
    {
        $appointment = $this->appointment;

        return new SmsMessage(
            $appointment->notification_phone ?? '',
            sprintf(
                '%s: you did not arrive for your appointment with Dr. %s on %s at %s, so it was auto-cancelled. Please rebook if you still need care.',
                config('app.name'),
                $appointment->doctor->full_name,
                $appointment->appointment_date->format('d M Y'),
                $appointment->formatted_time
            )
        );
    }
}
