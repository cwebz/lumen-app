<?php

namespace App\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Console\Commands\FranchiseMaps;
use App\Console\Commands\PlayersTable;
use App\Console\Commands\TradeBait;
use App\Console\Commands\CheckTrade;
use App\Console\Commands\CheckTradeProposal;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FranchiseMaps::class,
        PlayersTable::class,
        TradeBait::class,
        CheckTrade::class,
        CheckTradeProposal::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //Retrieves JSON and create mapping ID => Name
        $schedule->call(function () {
            Artisan::call('franchisemaps:update');
        })->daily();

        //Retrieves JSON and upsert players table
        $schedule->call(function () {
            Artisan::call('playerstable:update');
        })->daily();

        //Retrieves JSON and notify od Trade Bait Updates
        $schedule->call(function () {
            Artisan::call('tradebait:update');
        })->everyFiveMinutes();

        //Retrieves JSON and notify user of new proposal
        $schedule->call(function () {
            Artisan::call('checktradeproposal:update');
        })->everyFiveMinutes();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
