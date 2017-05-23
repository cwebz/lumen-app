<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RegisteredUserService;



class CheckTradeProposal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checktradeproposal:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls and notifies individual users of a proposal';

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
        RegisteredUserService::update();
    }
}
