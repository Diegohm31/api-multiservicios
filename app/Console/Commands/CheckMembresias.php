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
    protected $description = 'Verifica las membresias que estan por vencer y envia un correo de aviso';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //llamar a MembresiaService
        MembresiaService::checkMembresias();
    }
}
