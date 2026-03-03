<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MembresiaService;

class WarningMembresias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:warning-membresias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía correos de aviso de vencimiento de membresias 7 dias antes de vencer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //llamar a la funcion warningMembresias
        MembresiaService::warningMembresias();
    }
}
