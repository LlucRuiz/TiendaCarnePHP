<?php
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/admin-functions.php';
require_once __DIR__ . '/../includes/invoice-functions.php';

admin_require_login();

$orderId = (int)($_GET['id'] ?? 0);

// Descargar PDF
if ($orderId > 0 && isset($_GET['download'])) {
    download_invoice_pdf($db, $orderId, $store);
    exit;
}

// Ver factura HTML
if ($orderId > 0) {
    $invoice = generate_invoice_html($db, $orderId, $store);
    if ($invoice) {
        echo $invoice;
        exit;
    }
}

// Listado de facturas
$orders = admin_get_all_orders($db, 50);

$pageTitle = 'Facturas - Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .navbar {
            background: white;
            border-bottom: 1px solid #e1e4e8;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .navbar h1 {
            font-size: 18px;
            color: #333;
        }

        .navbar a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .navbar a:hover {
            background: #f0f2f5;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            width: 200px;
            height: calc(100vh - 60px);
            background: white;
            border-right: 1px solid #e1e4e8;
            padding: 20px 0;
            overflow-y: auto;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .sidebar a.active {
            background: #f0f2f5;
            color: #333;
            border-left-color: #667eea;
        }

        .main-content {
            margin-left: 200px;
            padding: 20px;
        }

        .page-header {
            margin-bottom: 20px;
        }

        .page-header h2 {
            font-size: 24px;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table thead {
            background: #f5f7fa;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e1e4e8;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #e1e4e8;
        }

        table tbody tr:hover {
            background: #f9f9f9;
        }

        a {
            color: #667eea;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn-group {
            display: flex;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar a {
                padding: 12px 10px;
                text-align: center;
            }

            .main-content {
                margin-left: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🥩 <?php echo htmlspecialchars($store['name']); ?></h1>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="pedidos.php">Pedidos</a>
            <a href="productos.php">Productos</a>
            <a href="logout.php">Salir</a>
        </div>
    </div>

    <div class="sidebar">
        <a href="index.php">📊 Dashboard</a>
        <a href="pedidos.php">📦 Pedidos</a>
        <a href="productos.php">🛒 Productos</a>
        <a href="pagos.php">💳 Pagos</a>
        <a href="facturas.php" class="active">📄 Facturas</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Facturas</h2>
        </div>

        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>Factura #</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo number_format($order['total'], 2, ',', '.'); ?>€</td>
                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="facturas.php?id=<?php echo $order['id']; ?>">👁️ Ver</a>
                                <a href="facturas.php?id=<?php echo $order['id']; ?>&download=1">⬇️ PDF</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
