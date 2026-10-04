<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\SmsChannel;
use App\Support\Sms\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DoctorRatingRequest extends Notification implements ShouldQueue
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
            ->subject('Rate your visit with Dr. '.$appointment->doctor->full_name)
            ->greeting('Hello '.$appointment->patient->full_name.',')
            ->line('Your session with Dr. '.$appointment->doctor->full_name.' ('.$appointment->doctor->specialization.') at '.$appointment->clinic->clinic_name.' is now marked as completed.')
            ->line('Visit details: '.$appointment->appointment_date->format('d M Y').' at '.$appointment->formatted_time.'.')
            ->action('Rate the doctor & share your experience', route('patient.appointments.review.create', $appointment))
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

    /**
     * SMS asking the patient to leave a rating once the checkup is done.
     */
    public function toSms(object $notifiable): ?SmsMessage
    {
        $appointment = $this->appointment;

        return new SmsMessage(
            $appointment->notification_phone ?? '',
            sprintf(
                '%s: thank you for visiting Dr. %s (%s). Please rate your doctor and share your experience: %s',
                config('app.name'),
                $appointment->doctor->full_name,
                $appointment->doctor->specialization,
                route('patient.appointments.review.create', $appointment)
            )
        );
    }
}
