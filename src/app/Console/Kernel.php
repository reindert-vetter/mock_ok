<?php

namespace App\Console;

use App\Console\Commands\TwinsAction;
use App\Console\Commands\TwinsList;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        TwinsAction::class
    ];


    protected function getArtisan()
    {
        if (is_null($this->artisan)) {
            $this->artisan = parent::getArtisan();

            // Make our modifications here.

            // Rename the console application.
            $this->artisan->setName('Twins');

            // Change the version number.
            $this->artisan->setVersion('1.0.0');

            // Change the default command.
//            $this->artisan->setDefaultCommand('preserve:list');

        }

        return $this->artisan;
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
