<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Batalkan pengajuan yang lewat batas 1x24 jam';

    public function handle(BookingService $bookings): void
    {
        $this->info($bookings->expireStale().' pengajuan dibatalkan.');
    }
}
