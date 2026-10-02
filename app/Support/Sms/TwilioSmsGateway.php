<?php

namespace App\Support\Sms;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Sends SMS through the Twilio Messages REST API using the built-in HTTP
 * client, so no additional Composer package is required.
 */
class TwilioSmsGateway implements SmsGateway
{
    public function send(SmsMessage $message): void
    {
        $sid = (string) config('sms.twilio.sid');
        $token = (string) config('sms.twilio.token');
        $from = (string) (config('sms.twilio.from') ?: config('sms.from'));

        if ($sid === '' || $token === '' || $from === '') {
            throw new RuntimeException('Twilio SMS is not configured. Set TWILIO_SID, TWILIO_AUTH_TOKEN and TWILIO_FROM.');
        }

        $response = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $message->toNumber(),
                'Body' => $message->content,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Twilio rejected the message: '.$response->body());
        }
    }
}
