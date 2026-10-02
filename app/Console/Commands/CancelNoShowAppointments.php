<?php

namespace App\Console\Commands;

use App\Services\AppointmentBookingService;
use Illuminate\Console\Command;

class CancelNoShowAppointments extends Command
{
    protected $signature = 'appointments:cancel-no-shows {--grace= : Grace minutes after slot start before auto-cancelling}';

    protected $description = 'Auto-cancel active appointments whose slot time passed without patient arrival, and email the patient.';

    public function handle(AppointmentBookingService $bookings): int
    {
        $grace = $this->option('grace');

        $count = $bookings->cancelExpiredNoShows(
            filled($grace) ? max(0, (int) $grace) : null
        );

        $this->info("Cancelled {$count} no-show appointment(s).");

        return self::SUCCESS;
    }
}
