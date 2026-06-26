<?php
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/admin-functions.php';
require_once __DIR__ . '/../includes/payment-functions.php';

admin_require_login();

$paymentHistory = get_payment_history($db, 50);
$paymentStats = get_payment_stats($db);

$pageTitle = 'Pagos - Admin';
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #667eea;
        }

        .stat-card h3 {
            font-size: 12px;
            color: #999;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }

        .stat-card.paid {
            border-left-color: #2ecc71;
        }

        .stat-card.pending {
            border-left-color: #f39c12;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .section h3 {
            font-size: 16px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e1e4e8;
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

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.paid {
            background: #d4edda;
            color: #155724;
        }

        .badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        a {
            color: #667eea;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
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

            .stats-grid {
                grid-template-columns: 1fr 1fr;
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
        <a href="pagos.php" class="active">💳 Pagos</a>
        <a href="facturas.php">📄 Facturas</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Pagos</h2>
        </div>

        <div class="stats-grid">
            <div class="stat-card paid">
                <h3>Pagados</h3>
                <div class="value"><?php echo number_format($paymentStats['total_paid'] ?? 0, 0); ?>€</div>
                <p style="font-size: 12px; color: #999; margin-top: 5px;"><?php echo $paymentStats['count_paid'] ?? 0; ?> transacciones</p>
            </div>

            <div class="stat-card pending">
                <h3>Pendientes</h3>
                <div class="value"><?php echo number_format($paymentStats['total_pending'] ?? 0, 0); ?>€</div>
                <p style="font-size: 12px; color: #999; margin-top: 5px;"><?php echo $paymentStats['count_pending'] ?? 0; ?> transacciones</p>
            </div>
        </div>

        <div class="section">
            <h3>Historial de Pagos</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Pago</th>
                        <th>Cliente</th>
                        <th>Pedido #</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Pagado el</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paymentHistory as $payment): ?>
                    <tr>
                        <td><small><?php echo substr($payment['stripe_session_id'], 0, 12) . '...'; ?></small></td>
                        <td><?php echo htmlspecialchars($payment['customer_name'] ?? 'N/A'); ?></td>
                        <td><strong><?php echo str_pad($payment['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                        <td><?php echo number_format($payment['amount_cents'] / 100, 2, ',', '.'); ?>€</td>
                        <td><span class="badge <?php echo $payment['status']; ?>"><?php echo ucfirst($payment['status']); ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($payment['created_at'])); ?></td>
                        <td><?php echo $payment['paid_at'] ? date('d/m/Y H:i', strtotime($payment['paid_at'])) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section" style="margin-top: 20px;">
            <h3>Configuración de Pagos (Stripe)</h3>
            <p style="margin-bottom: 15px; font-size: 14px; color: #666;">
                Para habilitar pagos con Stripe, configura estas variables de entorno:
            </p>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; color: #333;">
                <p>STRIPE_PUBLIC_KEY=pk_test_xxxxx</p>
                <p>STRIPE_SECRET_KEY=sk_test_xxxxx</p>
                <p style="margin-top: 10px; color: #999;">O en un archivo .env en la raíz del proyecto</p>
            </div>
        </div>
    </div>
</body>
</html>
