<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule)
    {
        // Define scheduled commands here.
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
