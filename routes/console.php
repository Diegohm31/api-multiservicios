<?php

// use Illuminate\Foundation\Inspiring;
// use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// verificar membresias por inactivar
Schedule::command('app:check-membresias')->dailyAt('00:00');

// enviar correo de aviso de vencimiento de membresia 7 dias antes de vencer
Schedule::command('app:warning-membresias')->dailyAt('00:00');

// enviar correo de aviso de alerta de stock de materiales solo los lunes
Schedule::command('app:warning-stock')->weeklyOn(1, '00:00');