<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Presupuesto</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 11px;
        }

        .header {
            margin-bottom: 20px;
        }

        .logo-container {
            width: 50%;
            float: left;
        }

        .info-container {
            width: 45%;
            float: right;
            text-align: right;
        }

        .clear {
            clear: both;
        }

        .logo {
            max-width: 150px;
        }

        .presupuesto-title {
            color: #2563eb;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .presupuesto-number {
            color: #ef4444;
            font-size: 18px;
            font-weight: bold;
        }

        .client-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 20px;
        }

        .client-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .client-card td {
            padding: 3px 0;
        }

        .label {
            font-weight: bold;
            width: 100px;
        }

        .dates-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .dates-table th,
        .dates-table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: center;
        }

        .dates-table th {
            background-color: #f1f5f9;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            border-bottom: 2px solid #ef4444;
            padding: 8px 4px;
            text-align: center;
            background-color: #fff;
            color: #000;
            font-size: 10px;
        }

        .items-table td {
            padding: 8px 4px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
            font-size: 10px;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer-section {
            margin-top: 30px;
            width: 100%;
            border: 1px solid #ef4444;
            border-radius: 4px;
        }

        .totals {
            width: 50%;
            float: left;
            padding: 10px;
            box-sizing: border-box;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 4px 0;
            font-size: 11px;
            text-align: left;
        }

        .totals .total-label {
            font-weight: bold;
            width: 150px;
        }

        .totals .total-value {
            font-weight: bold;
            text-align: left;
        }

        .totals .grand-total {
            border-top: 1px solid #ef4444;
            color: #ef4444;
        }

        .totals .grand-total td {
            font-size: 14px;
            padding-top: 10px;
        }

        .company-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo-container">
            @if($empresa->imagePath)
                <img src="{{ public_path('storage/' . $empresa->imagePath) }}" class="logo">
            @else
                <h1 style="margin:0">{{ $empresa->nombre }}</h1>
            @endif
            <div style="margin-top: 5px; font-weight: bold;">RIF: {{ $empresa->rif }}</div>
        </div>
        <div class="info-container">
            <div class="presupuesto-title">Presupuesto</div>
            <div class="presupuesto-number">#{{ $presupuesto->id_presupuesto }}</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="client-card">
        <table>
            <tr>
                <td class="label">Razón Social:</td>
                <td>{{ $cliente->nombre }}</td>
            </tr>
            <tr>
                <td class="label">R.I.F / Cédula:</td>
                <td>{{ $cliente->rif ? $cliente->rif : $cliente->cedula }}</td>
            </tr>
            <tr>
                <td class="label">Vendedor:</td>
                <td>{{ $vendedor }}</td>
            </tr>
            <tr>
                <td class="label">Dirección:</td>
                <td>{{ $orden->direccion }}</td>
            </tr>
        </table>
    </div>

    <table class="dates-table">
        <thead>
            <tr>
                <th>Fecha Emisión</th>
                <th>Vencimiento</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ date('d-m-Y', strtotime($presupuesto->fecha_emision)) }}</td>
                <td>{{ date('d-m-Y', strtotime($presupuesto->fecha_vencimiento)) }}</td>
                <td>{{ $presupuesto->estado }}</td>
            </tr>
        </tbody>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="8%">Código</th>
                <th width="22%">Descripción</th>
                <th width="12%" class="text-right">Precio Gral. Unit.</th>
                <th width="8%" class="text-center">% Desc.</th>
                <th width="12%" class="text-right">Desc. Unit.</th>
                <th width="12%" class="text-right">Precio Neto Unit.</th>
                <th width="8%" class="text-center">Cant.</th>
                <th width="18%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $s)
                <tr>
                    <td>S-{{ str_pad($s->id_servicio, 3, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <strong>{{ $s->nombre }}</strong>
                        @if($s->descripcion)
                            <br><span style="color: #666; font-size: 9px;">{{ $s->descripcion }}</span>
                        @endif
                    </td>
                    <td class="text-right">${{ number_format($s->precio_general_unitario, 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($s->porcentaje_descuento, 0) }}%</td>
                    <td class="text-right">${{ number_format($s->descuento_unitario, 2, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($s->precio_neto_unitario, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $s->cantidad }}</td>
                    <td class="text-right">${{ number_format($s->precio_a_pagar, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-section">
        <div class="totals">
            <table>
                <tr>
                    <td>TOTAL GENERAL:</td>
                    <td class="text-right">${{ number_format($presupuesto->total_general, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>TOTAL DESCUENTO (-):</td>
                    <td class="text-right">${{ number_format($presupuesto->total_descuento, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="total-label">SUB-TOTAL:</td>
                    <td class="total-value">${{ number_format($presupuesto->sub_total, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>I.V.A ({{ $presupuesto->porcentaje_iva }}%):</td>
                    <td class="text-right">${{ number_format($presupuesto->iva, 2, ',', '.') }}</td>
                </tr>
                <tr class="grand-total">
                    <td class="total-label">TOTAL A PAGAR:</td>
                    <td class="total-value">
                        ${{ number_format($presupuesto->total_a_pagar, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>

    <div class="company-footer">
        Dirección: {{ $empresa->direccion }}<br>
        Correo Electrónico: {{ $empresa->correo }} | Teléfono: {{ $empresa->telefono }}
    </div>
</body>

</html>