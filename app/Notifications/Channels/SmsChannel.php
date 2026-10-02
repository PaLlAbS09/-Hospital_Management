<?php

namespace App\Notifications\Channels;

use App\Support\Sms\SmsGateway;
use App\Support\Sms\SmsMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Custom notification channel that hands an {@see SmsMessage} to the
 * configured {@see SmsGateway}.
 *
 * Notifications that support SMS return `SmsChannel::class` from their `via()`
 * method and expose a `toSms($notifiable)` method returning an SmsMessage
 * (or null when there is no number to send to).
 */
class SmsChannel
{
    public function __construct(protected SmsGateway $gateway) {}

    /**
     * Send the notification over SMS.
     *
     * Gateway errors are swallowed and logged on purpose: Laravel re-throws
     * channel exceptions, which would make the mail/database channels that
     * already succeeded look like a failure to the caller.
     */
    public function send(object $notifiable, object $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        try {
            $message = $notification->toSms($notifiable);

            if (! $message instanceof SmsMessage || blank($message->to)) {
                return;
            }

            $this->gateway->send($message);
        } catch (Throwable $exception) {
            Log::error('SMS delivery failed: '.$exception->getMessage(), [
                'notification' => $notification::class,
                'notifiable' => $notifiable::class,
            ]);
        }
    }
}
