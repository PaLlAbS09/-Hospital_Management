<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Sends notifications without letting a delivery failure (for example an
 * unreachable SMTP server) break the surrounding request or scheduled command.
 */
class Notifier
{
    /**
     * Deliver a notification, swallowing (and logging) transport errors.
     *
     * @return bool True when the notification was handed off successfully.
     */
    public static function send(object $notifiable, object $notification): bool
    {
        try {
            $notifiable->notify($notification);

            return true;
        } catch (\Throwable $exception) {
            Log::error('Notification delivery failed: '.$exception->getMessage(), [
                'notification' => $notification::class,
                'notifiable' => $notifiable::class,
            ]);

            return false;
        }
    }
}
