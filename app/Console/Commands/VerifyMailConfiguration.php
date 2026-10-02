<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class VerifyMailConfiguration extends Command
{
    protected $signature = 'mail:verify {--to= : Recipient address (defaults to MAIL_USERNAME)}';

    protected $description = 'Report the active mail configuration and send a test message to prove delivery works.';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $settings = (array) config('mail.mailers.'.$mailer, []);
        $transport = (string) ($settings['transport'] ?? '');
        $host = (string) ($settings['host'] ?? '');
        $username = (string) ($settings['username'] ?? '');
        $password = (string) ($settings['password'] ?? '');

        $this->newLine();
        $this->components->info('Active mail configuration');
        $this->components->twoColumnDetail('Mailer', $mailer);
        $this->components->twoColumnDetail('Transport', $transport !== '' ? $transport : '<unknown>');
        $this->components->twoColumnDetail('Host', $host !== '' ? $host.':'.($settings['port'] ?? '') : '<not set>');
        $this->components->twoColumnDetail('Username', $username !== '' ? $username : '<not set>');
        $this->components->twoColumnDetail('Password', $this->describePassword($password));
        $this->newLine();

        // These transports record messages instead of delivering them, so a
        // "success" here would be misleading.
        if (in_array($transport, ['log', 'array'], true)) {
            $this->components->warn('The "'.$transport.'" transport never delivers real email; it only records messages.');
            $this->components->info('Set MAIL_MAILER=smtp in .env to deliver real email.');

            return self::SUCCESS;
        }

        if (blank($password) && Str::contains($host, ['smtp.gmail.com', 'smtp.office365.com'])) {
            $this->components->error('MAIL_PASSWORD is empty, so the SMTP server rejects every login (error 535).');
            $this->newLine();
            $this->line('  Gmail:  turn on 2-Step Verification, then create an App Password at');
            $this->line('          https://myaccount.google.com/apppasswords');
            $this->line('          and paste the 16-character password into MAIL_PASSWORD in .env.');
            $this->newLine();
            $this->line('  Your normal Google account password will NOT work - Gmail refuses it.');
            $this->newLine();
        }

        $recipient = (string) ($this->option('to') ?: $username);

        if (blank($recipient)) {
            $this->components->error('No recipient available. Pass --to=you@example.com or set MAIL_USERNAME.');

            return self::FAILURE;
        }

        try {
            Mail::raw('This is a test message from '.config('app.name').'.', function ($message) use ($recipient): void {
                $message->to($recipient)->subject('['.config('app.name').'] Mail configuration test');
            });
        } catch (Throwable $exception) {
            $this->components->error('The test message could not be sent.');
            $this->newLine();
            $this->line($exception->getMessage());
            $this->newLine();

            return self::FAILURE;
        }

        $this->components->info('Test message sent to '.$recipient.'.');

        if ($transport === 'smtp') {
            $this->line('If it does not arrive, check the spam folder and confirm MAIL_FROM_ADDRESS is an authorised sender.');
        }

        return self::SUCCESS;
    }

    /**
     * Never echo the password itself, only whether one is present.
     */
    private function describePassword(string $password): string
    {
        if (blank($password)) {
            return '<empty - this is why delivery fails>';
        }

        return str_repeat('*', min(16, strlen($password))).' ('.strlen($password).' characters)';
    }
}
