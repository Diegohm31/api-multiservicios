<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MembresiaService;

class CheckMembresias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-membresias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check memberships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //llamar a MembresiaService
        MembresiaService::checkMembresias();
    }
}
