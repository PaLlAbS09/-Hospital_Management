<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS delivery driver
    |--------------------------------------------------------------------------
    |
    | The "log" driver records the message in the application log, so the SMS
    | flow can be exercised locally without a gateway account. Swap it for a
    | real driver (for example "twilio") once you have provider credentials.
    |
    */

    'driver' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Sender identity
    |--------------------------------------------------------------------------
    |
    | Short code, alphanumeric ID or purchased number shown to the recipient.
    | Twilio requires a number in E.164 format (for example +14155550123).
    |
    */

    'from' => env('SMS_FROM', 'AEGISH'),

    /*
    |--------------------------------------------------------------------------
    | Number normalisation
    |--------------------------------------------------------------------------
    |
    | Stored phone numbers may include a leading zero or omit the country code.
    | Any number whose length is at most "national_length" is prefixed with the
    | "country_code" to build an E.164 number (+91XXXXXXXXXX by default).
    |
    */

    'country_code' => env('SMS_COUNTRY_CODE', '91'),

    'national_length' => (int) env('SMS_NATIONAL_LENGTH', 10),

    /*
    |--------------------------------------------------------------------------
    | Twilio
    |--------------------------------------------------------------------------
    */

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

];
