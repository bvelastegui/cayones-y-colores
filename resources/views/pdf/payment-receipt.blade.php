<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de pago {{ $reference }}</title>
    <style>
        body { color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 32px; }
        h1 { color: #173b69; font-size: 24px; margin: 0 0 6px; }
        .muted { color: #667085; }
        .header { border-bottom: 2px solid #173b69; margin-bottom: 28px; padding-bottom: 18px; }
        .summary { background: #f3f6fa; margin-bottom: 24px; padding: 16px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #173b69; color: #fff; padding: 10px 8px; text-align: left; }
        td { border-bottom: 1px solid #d8dee8; padding: 10px 8px; }
        .amount { text-align: right; }
        .total { font-size: 16px; font-weight: bold; margin-top: 18px; text-align: right; }
        .footer { color: #667085; font-size: 10px; margin-top: 36px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Comprobante de pago</h1>
        <div class="muted">Sistema de Gestión Académica</div>
    </div>

    <div class="summary">
        <strong>Referencia:</strong> {{ $reference }}<br>
        <strong>Fecha de pago:</strong> {{ $payments->first()->payment_date->format('d/m/Y') }}<br>
        <strong>Método:</strong> {{ $payments->first()->payment_method->label() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Período</th>
                <th class="amount">Valor pagado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->tuition->student->full_name }}</td>
                    <td>{{ $payment->tuition->billing_period->translatedFormat('F Y') }}</td>
                    <td class="amount">${{ number_format((float) $payment->amount_paid, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">Total pagado: ${{ $total }}</div>
    <div class="footer">Documento generado el {{ now()->format('d/m/Y H:i') }}. Los valores provienen de los pagos registrados en el sistema.</div>
</body>
</html>
