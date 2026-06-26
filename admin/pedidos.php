<?php
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/admin-functions.php';

admin_require_login();

$orderId = (int)($_GET['id'] ?? 0);
$orderDetail = $orderId > 0 ? admin_get_order($db, $orderId) : null;
$orderItems = $orderId > 0 ? admin_get_order_items($db, $orderId) : [];

// Actualizar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'] ?? '';
    if (!empty($newStatus) && $orderId > 0) {
        admin_update_order_status($db, $orderId, $newStatus);
        header('Location: pedidos.php?id=' . $orderId . '&updated=1');
        exit;
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 20;
$offset = ($page - 1) * $pageSize;
$orders = admin_get_all_orders($db, $pageSize, $offset);
$totalOrders = admin_get_total_orders_count($db);
$totalPages = ceil($totalOrders / $pageSize);

$pageTitle = $orderId > 0 ? 'Pedido #' . str_pad($orderId, 6, '0', STR_PAD_LEFT) : 'Pedidos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            font-size: 24px;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .order-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-block {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
        }

        .detail-block h4 {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .detail-block p {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .detail-block strong {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-bottom: 20px;
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
            padding: 6px 12px;
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
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 14px;
        }

        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .pagination {
            display: flex;
            gap: 5px;
            margin-top: 20px;
            justify-content: center;
        }

        .pagination a {
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #667eea;
        }

        .pagination a.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .back-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        a {
            color: #667eea;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
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

            .order-detail {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🥩 <?php echo htmlspecialchars($store['name']); ?></h1>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="productos.php">Productos</a>
            <a href="pagos.php">Pagos</a>
            <a href="logout.php">Salir</a>
        </div>
    </div>

    <div class="sidebar">
        <a href="index.php">📊 Dashboard</a>
        <a href="pedidos.php" class="active">📦 Pedidos</a>
        <a href="productos.php">🛒 Productos</a>
        <a href="pagos.php">💳 Pagos</a>
        <a href="facturas.php">📄 Facturas</a>
    </div>

    <div class="main-content">
        <?php if ($orderId > 0 && $orderDetail): ?>
            <!-- DETALLE DE PEDIDO -->
            <div class="page-header">
                <div>
                    <a href="pedidos.php" class="back-link">← Volver a pedidos</a>
                    <h2>Pedido #<?php echo str_pad($orderId, 6, '0', STR_PAD_LEFT); ?></h2>
                </div>
            </div>

            <?php if (isset($_GET['updated'])): ?>
                <div class="success">✓ Estado del pedido actualizado correctamente.</div>
            <?php endif; ?>

            <div class="section">
                <div class="order-detail">
                    <div class="detail-block">
                        <h4>Información del Pedido</h4>
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($orderDetail['created_at'])); ?></p>
                        <p><strong>Total:</strong> <?php echo number_format($orderDetail['total'], 2, ',', '.'); ?>€</p>
                        <p><strong>Envío:</strong> <?php echo number_format($orderDetail['shipping'], 2, ',', '.'); ?>€</p>
                    </div>

                    <div class="detail-block">
                        <h4>Datos del Cliente</h4>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($orderDetail['customer_name']); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($orderDetail['phone']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($orderDetail['email'] ?? '-'); ?></p>
                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($orderDetail['address'] ?? '-'); ?></p>
                    </div>
                </div>

                <h3 style="margin-top: 20px; margin-bottom: 10px;">Artículos del Pedido</h3>
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
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['unit_price'], 2, ',', '.'); ?>€</td>
                            <td><?php echo number_format($item['line_total'], 2, ',', '.'); ?>€</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form method="POST" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>Cambiar Estado</label>
                        <select name="status">
                            <option value="nuevo" <?php echo $orderDetail['status'] === 'nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                            <option value="pagado" <?php echo $orderDetail['status'] === 'pagado' ? 'selected' : ''; ?>>Pagado</option>
                            <option value="preparando" <?php echo $orderDetail['status'] === 'preparando' ? 'selected' : ''; ?>>Preparando</option>
                            <option value="entregado" <?php echo $orderDetail['status'] === 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn">Actualizar Estado</button>
                    <a href="facturas.php?id=<?php echo $orderId; ?>" class="btn btn-secondary">📄 Ver Factura</a>
                </form>
            </div>
        <?php else: ?>
            <!-- LISTADO DE PEDIDOS -->
            <div class="page-header">
                <h2>Pedidos</h2>
            </div>

            <div class="section">
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
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['phone']); ?></td>
                            <td><?php echo number_format($order['total'], 2, ',', '.'); ?>€</td>
                            <td><span class="badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="pedidos.php?id=<?php echo $order['id']; ?>">Ver</a> |
                                <a href="facturas.php?id=<?php echo $order['id']; ?>">Factura</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
