<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MaterialService;

class WarningStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:warning-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía correos de alerta de stock bajo de materiales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // llamar a MaterialService
        MaterialService::warningStock();
    }
}
