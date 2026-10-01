<?php

namespace App\Notifications;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Clinic $clinic) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your clinic registration has been approved')
            ->greeting('Hello '.$this->clinic->clinic_name.',')
            ->line('Good news! Your clinic registration has been approved by the administrator.')
            ->line('You can now sign in and publish doctor schedules.')
            ->action('Sign in to your clinic', route('clinic.login'))
            ->line('Thank you for joining '.config('app.name').'.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'clinic_id' => $this->clinic->clinic_id,
            'clinic_name' => $this->clinic->clinic_name,
            'status' => $this->clinic->status,
        ];
    }
}
