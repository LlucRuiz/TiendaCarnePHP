<?php

// Instala con: composer require tecnickcom/tcpdf

// ============ GENERAR FACTURA PDF ============

function generate_invoice_pdf(
    PDO $db,
    int $orderId,
    array $store,
    string $outputPath = null
): ?string
{
    try {
        require_once __DIR__ . '/../vendor/autoload.php';

        // Obtener datos del pedido
        $order = admin_get_order($db, $orderId);
        if (!$order) {
            return null;
        }

        $items = admin_get_order_items($db, $orderId);

        // Crear PDF
        $pdf = new \TCPDF('P', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        
        // Fuente
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'FACTURA', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(5);

        // Información de tienda
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 5, 'Tienda:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, $store['name'], 0, 1);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(50, 4, '');
        $pdf->Cell(0, 4, $store['address'], 0, 1);
        $pdf->Cell(50, 4, '');
        $pdf->Cell(0, 4, $store['phone'], 0, 1);
        $pdf->Cell(50, 4, '');
        $pdf->Cell(0, 4, $store['email'], 0, 1);

        $pdf->Ln(5);

        // Información de pedido
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 5, 'Factura #' . str_pad($orderId, 6, '0', STR_PAD_LEFT) . ':', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, date('d/m/Y', strtotime($order['created_at'])), 0, 1);

        $pdf->Ln(5);

        // Información de cliente
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'Datos del Cliente:', 0, 1);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(50, 4, 'Nombre:', 0, 0);
        $pdf->Cell(0, 4, $order['customer_name'], 0, 1);

        $pdf->Cell(50, 4, 'Teléfono:', 0, 0);
        $pdf->Cell(0, 4, $order['phone'], 0, 1);

        $pdf->Cell(50, 4, 'Email:', 0, 0);
        $pdf->Cell(0, 4, $order['email'] ?? '-', 0, 1);

        $pdf->Cell(50, 4, 'Dirección:', 0, 0);
        $pdf->Cell(0, 4, $order['address'] ?? '-', 0, 1);

        $pdf->Ln(5);

        // Tabla de items
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(80, 7, 'Producto', 1, 0, 'L', true);
        $pdf->Cell(20, 7, 'Cantidad', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Precio', 1, 0, 'R', true);
        $pdf->Cell(30, 7, 'Total', 1, 1, 'R', true);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetFillColor(255, 255, 255);

        foreach ($items as $item) {
            $pdf->Cell(80, 6, substr($item['product_name'], 0, 35), 1, 0, 'L');
            $pdf->Cell(20, 6, $item['quantity'], 1, 0, 'C');
            $pdf->Cell(30, 6, number_format($item['unit_price'], 2, ',', '.') . ' €', 1, 0, 'R');
            $pdf->Cell(30, 6, number_format($item['line_total'], 2, ',', '.') . ' €', 1, 1, 'R');
        }

        $pdf->Ln(5);

        // Totales
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 6, 'Subtotal:', 0, 0, 'R');
        $pdf->Cell(30, 6, number_format($order['subtotal'], 2, ',', '.') . ' €', 0, 1, 'R');

        $pdf->Cell(130, 6, 'Envío:', 0, 0, 'R');
        $pdf->Cell(30, 6, number_format($order['shipping'], 2, ',', '.') . ' €', 0, 1, 'R');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(130, 7, 'Total:', 0, 0, 'R', true);
        $pdf->Cell(30, 7, number_format($order['total'], 2, ',', '.') . ' €', 0, 1, 'R', true);

        $pdf->Ln(10);

        // Estado del pago
        $pdf->SetFont('helvetica', 'B', 9);
        $paymentStatus = ucfirst($order['status']);
        $pdf->Cell(0, 5, 'Estado: ' . $paymentStatus, 0, 1);

        if ($order['notes']) {
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 5, 'Notas:', 0, 1);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell(0, 4, $order['notes']);
        }

        // Guardar o devolver
        if ($outputPath) {
            $pdf->Output($outputPath, 'F');
            return $outputPath;
        } else {
            return $pdf->Output('factura_' . $orderId . '.pdf', 'S');
        }
    } catch (Throwable $exception) {
        error_log('Invoice generation error: ' . $exception->getMessage());
        return null;
    }
}

// ============ GENERAR FACTURA HTML (para navegador) ============

function generate_invoice_html(
    PDO $db,
    int $orderId,
    array $store
): ?string
{
    try {
        // Obtener datos del pedido
        $order = admin_get_order($db, $orderId);
        if (!$order) {
            return null;
        }

        $items = admin_get_order_items($db, $orderId);

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo str_pad($orderId, 6, '0', STR_PAD_LEFT); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background: #f9f9f9;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }
        
        .invoice-title h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        
        .store-info {
            text-align: right;
        }
        
        .store-info h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .store-info p {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }
        
        .invoice-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .section h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            color: #333;
        }
        
        .section p {
            font-size: 13px;
            margin-bottom: 8px;
            line-height: 1.6;
        }
        
        .section strong {
            display: inline-block;
            width: 80px;
            color: #555;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        table thead {
            background: #f0f0f0;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        table th:nth-child(2),
        table th:nth-child(3),
        table th:nth-child(4),
        table td:nth-child(2),
        table td:nth-child(3),
        table td:nth-child(4) {
            text-align: right;
        }
        
        .invoice-summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        
        .summary-box {
            width: 300px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 13px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 16px;
            background: #f0f0f0;
            padding: 15px;
            border: 2px solid #333;
            margin-top: 10px;
        }
        
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 11px;
            color: #999;
        }
        
        .print-btn {
            display: block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #333;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Imprimir Factura</button>
    
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="invoice-title">
                <h1>FACTURA</h1>
                <div class="invoice-number">#<?php echo str_pad($orderId, 6, '0', STR_PAD_LEFT); ?></div>
            </div>
            <div class="store-info">
                <h2><?php echo htmlspecialchars($store['name']); ?></h2>
                <p><?php echo htmlspecialchars($store['address']); ?><br>
                   <?php echo htmlspecialchars($store['phone']); ?><br>
                   <?php echo htmlspecialchars($store['email']); ?></p>
            </div>
        </div>
        
        <div class="invoice-content">
            <div class="section">
                <h3>Datos del Cliente</h3>
                <p><strong>Nombre:</strong><br><?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Teléfono:</strong><br><?php echo htmlspecialchars($order['phone']); ?></p>
                <p><strong>Email:</strong><br><?php echo htmlspecialchars($order['email'] ?? '-'); ?></p>
                <p><strong>Dirección:</strong><br><?php echo htmlspecialchars($order['address'] ?? '-'); ?></p>
            </div>
            
            <div class="section">
                <h3>Detalles del Pedido</h3>
                <p><strong>Fecha:</strong><br><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Estado:</strong><br><?php echo ucfirst($order['status']); ?></p>
                <p><strong>Método:</strong><br>En línea</p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['unit_price'], 2, ',', '.'); ?> €</td>
                    <td><?php echo number_format($item['line_total'], 2, ',', '.'); ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="invoice-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo number_format($order['subtotal'], 2, ',', '.'); ?> €</span>
                </div>
                <div class="summary-row">
                    <span>Envío</span>
                    <span><?php echo number_format($order['shipping'], 2, ',', '.'); ?> €</span>
                </div>
                <div class="summary-row total">
                    <span>TOTAL</span>
                    <span><?php echo number_format($order['total'], 2, ',', '.'); ?> €</span>
                </div>
            </div>
        </div>
        
        <?php if ($order['notes']): ?>
        <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 30px;">
            <strong>Notas del pedido:</strong><br>
            <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>Gracias por tu compra. Factura generada automáticamente por <?php echo htmlspecialchars($store['name']); ?></p>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    } catch (Throwable $exception) {
        error_log('Invoice HTML generation error: ' . $exception->getMessage());
        return null;
    }
}

// ============ DESCARGAR FACTURA ============

function download_invoice_pdf(PDO $db, int $orderId, array $store): void
{
    $pdf = generate_invoice_pdf($db, $orderId, $store);
    
    if ($pdf) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="factura_' . str_pad($orderId, 6, '0', STR_PAD_LEFT) . '.pdf"');
        echo $pdf;
        exit;
    }
}
