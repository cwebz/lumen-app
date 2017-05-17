<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TradeBaitService;



class TradeBait extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tradebait:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls and notifies slack channel of an update';

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
        TradeBaitService::update();
    }
}
