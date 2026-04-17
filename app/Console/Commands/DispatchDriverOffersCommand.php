<?php

namespace App\Console\Commands;

use App\Services\DriverAutoDispatchService;
use Illuminate\Console\Command;

class DispatchDriverOffersCommand extends Command
{
    protected $signature = 'orders:dispatch-driver-offers';
    protected $description = 'Offer newly received branch orders to drivers one by one until accepted or manually assigned.';

    public function handle(DriverAutoDispatchService $driverAutoDispatchService): int
    {
        $offeredCount = $driverAutoDispatchService->dispatchPendingOffers();

        $this->info('Driver offers dispatched: ' . $offeredCount);

        return self::SUCCESS;
    }
}
