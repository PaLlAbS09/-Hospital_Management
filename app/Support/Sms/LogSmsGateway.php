<?php

namespace App\Support\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Default gateway. Writes the SMS to the application log so the booking,
 * cancellation and no-show flows can be verified without a real provider.
 */
class LogSmsGateway implements SmsGateway
{
    public function send(SmsMessage $message): void
    {
        Log::info('SMS dispatched', [
            'to' => $message->toNumber(),
            'from' => config('sms.from'),
            'content' => $message->content,
        ]);
    }
}
