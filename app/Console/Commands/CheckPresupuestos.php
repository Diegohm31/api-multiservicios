<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PresupuestoService;

class CheckPresupuestos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-presupuestos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los presupuestos que estan por vencer y los cancela';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // llamar a la funcion estatica checkPresupuestos del PresupuestoService
        PresupuestoService::checkPresupuestos();
    }
}
