<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlayersTableService;



class PlayersTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'playerstable:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls and updates the players table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        PlayersTableService::update();
    }
}
