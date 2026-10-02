<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\SmsChannel;
use App\Support\Sms\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public string $cancelledBy = 'clinic',
        public bool $automatic = false,
    ) {}

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
        $actor = $this->cancelledBy === 'patient' ? 'you' : 'the clinic/doctor';

        $message = (new MailMessage)
            ->subject('Appointment cancelled - '.$appointment->appointment_date->format('d M Y'))
            ->greeting('Hello '.$appointment->patient->full_name.',')
            ->line('Your appointment with Dr. '.$appointment->doctor->full_name.' at '.$appointment->clinic->clinic_name.' scheduled for '.$appointment->appointment_date->format('d M Y').' at '.$appointment->formatted_time.' has been cancelled by '.$actor.'.')
            ->line('Contact phone on file for this booking: '.($appointment->notification_phone ?? '—').'.');

        if (! $this->automatic) {
            $message->action('Book a new appointment', route('patient.clinics.index'));
        }

        return $message->line('Thank you for using '.config('app.name').'.');
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
            'cancelled_by' => $this->cancelledBy,
            'automatic' => $this->automatic,
        ];
    }

    /**
     * SMS warning that the slot is no longer booked.
     */
    public function toSms(object $notifiable): ?SmsMessage
    {
        $appointment = $this->appointment;
        $actor = $this->cancelledBy === 'patient' ? 'by you' : 'by the clinic';

        return new SmsMessage(
            $appointment->notification_phone ?? '',
            sprintf(
                '%s: your appointment with Dr. %s on %s at %s was cancelled %s. Please rebook if you still need care.',
                config('app.name'),
                $appointment->doctor->full_name,
                $appointment->appointment_date->format('d M Y'),
                $appointment->formatted_time,
                $actor
            )
        );
    }
}
