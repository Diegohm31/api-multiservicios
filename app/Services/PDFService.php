<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Presupuesto;
use App\Models\Orden;
use App\Models\Empresa;
use App\Models\Cliente;
use App\Services\OrdenServicioService;
use App\Models\Admin;

class PDFService
{
    public static function generarPresupuestoPDF($presupuesto, $orden, $cliente, $empresa, $servicios)
    {
        $admin = Admin::find($presupuesto->id_admin);
        $vendedor = $admin ? $admin->nombre : 'Sistema';

        $data = [
            'presupuesto' => $presupuesto,
            'orden' => $orden,
            'cliente' => $cliente,
            'empresa' => $empresa,
            'servicios' => $servicios,
            'vendedor' => $vendedor
        ];

        $pdf = Pdf::loadView('pdf.presupuesto', $data);

        $filename = 'factura_' . $presupuesto->id_presupuesto . '_' . time() . '.pdf';
        $path = 'facturas/' . $filename;

        try {
            Storage::disk('public')->put($path, $pdf->output());
            return $path;
        } catch (\Exception $e) {
            \Log::error('Error generating PDF: ' . $e->getMessage());
            return null;
        }
    }
    public static function generarPeritajePDF($orden, $servicios)
    {
        $data = [
            'orden' => $orden,
            'servicios' => $servicios
        ];

        $pdf = Pdf::loadView('pdf.peritaje', $data);

        $filename = 'peritaje_' . $orden->id_orden . '_' . time() . '.pdf';
        $path = 'peritajes/' . $filename;

        try {
            Storage::disk('public')->put($path, $pdf->output());
            return $path;
        } catch (\Exception $e) {
            \Log::error('Error generating Peritaje PDF: ' . $e->getMessage());
            return null;
        }
    }
}
