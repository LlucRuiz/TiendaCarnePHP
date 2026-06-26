<?php
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/admin-functions.php';

admin_require_login();

$stats = admin_get_dashboard_stats($db);
$revenueData = admin_get_revenue_by_date($db, 7);
$topProducts = admin_get_top_products($db, 5);
$recentOrders = admin_get_all_orders($db, 5);

$pageTitle = 'Dashboard - Admin';
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

        .navbar .logout {
            color: #e74c3c;
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

        .sidebar a:hover,
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
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-size: 28px;
            margin-bottom: 10px;
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

        .stat-card.orders {
            border-left-color: #3498db;
        }

        .stat-card.products {
            border-left-color: #2ecc71;
        }

        .stat-card.customers {
            border-left-color: #f39c12;
        }

        .stat-card.revenue {
            border-left-color: #e74c3c;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
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

        .badge.nuevo {
            background: #e8f4f8;
            color: #0c5460;
        }

        .badge.pagado {
            background: #d4edda;
            color: #155724;
        }

        .badge.preparando {
            background: #fff3cd;
            color: #856404;
        }

        .badge.entregado {
            background: #d1ecf1;
            color: #0c5460;
        }

        a {
            color: #667eea;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #5568d3;
            text-decoration: none;
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
            <a href="pedidos.php">📦 Pedidos</a>
            <a href="productos.php">🛒 Productos</a>
            <a href="pagos.php">💳 Pagos</a>
            <a href="facturas.php">📄 Facturas</a>
            <a href="logout.php" class="logout">🚪 Salir</a>
        </div>
    </div>

    <div class="sidebar">
        <a href="index.php" class="active">📊 Dashboard</a>
        <a href="pedidos.php">📦 Pedidos</a>
        <a href="productos.php">🛒 Productos</a>
        <a href="pagos.php">💳 Pagos</a>
        <a href="facturas.php">📄 Facturas</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Dashboard</h2>
            <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card orders">
                <h3>Pedidos Totales</h3>
                <div class="value"><?php echo $stats['total_orders'] ?? 0; ?></div>
            </div>

            <div class="stat-card products">
                <h3>Productos Activos</h3>
                <div class="value"><?php echo $stats['total_products'] ?? 0; ?></div>
            </div>

            <div class="stat-card customers">
                <h3>Clientes</h3>
                <div class="value"><?php echo $stats['total_customers'] ?? 0; ?></div>
            </div>

            <div class="stat-card revenue">
                <h3>Ingresos Totales</h3>
                <div class="value"><?php echo number_format($stats['total_revenue'] ?? 0, 0); ?>€</div>
            </div>
        </div>

        <div class="section">
            <h3>Últimos Pedidos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Pedido #</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><strong><?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                        <td><?php echo number_format($order['total'], 2, ',', '.'); ?>€</td>
                        <td><span class="badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><a href="pedidos.php?id=<?php echo $order['id']; ?>">Ver</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h3>Top 5 Productos Más Vendidos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Vendidos</th>
                        <th>Cantidad Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topProducts as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo number_format($product['price'], 2, ',', '.'); ?>€</td>
                        <td><?php echo $product['total_sold']; ?></td>
                        <td><?php echo $product['total_quantity']; ?> unidades</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
