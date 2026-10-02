<?php

namespace App\Support\Sms;

/**
 * A single outbound SMS.
 */
class SmsMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $content,
    ) {}

    /**
     * The recipient in E.164 format (+91XXXXXXXXXX by default).
     */
    public function toNumber(): string
    {
        $digits = (string) preg_replace('/\D+/', '', $this->to);
        $digits = ltrim($digits, '0');

        $country = (string) config('sms.country_code', '');
        $nationalLength = (int) config('sms.national_length', 10);

        if ($country !== '' && $nationalLength > 0 && strlen($digits) <= $nationalLength) {
            $digits = $country.$digits;
        }

        return '+'.$digits;
    }
}
