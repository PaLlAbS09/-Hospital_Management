<?php

namespace App\Support\Sms;

interface SmsGateway
{
    /**
     * Deliver the given message.
     *
     * @throws \RuntimeException when the message cannot be handed to the provider.
     */
    public function send(SmsMessage $message): void;
}
