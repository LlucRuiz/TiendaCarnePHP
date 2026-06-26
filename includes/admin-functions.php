<?php

// ============ AUTENTICACIÓN ============

function admin_require_login(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function admin_login(PDO $db, string $username, string $password): bool
{
    try {
        $statement = $db->prepare('SELECT id, password_hash FROM admins WHERE username = :username AND active = 1');
        $statement->execute(['username' => $username]);
        $admin = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return false;
        }

        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $username;
        return true;
    } catch (Throwable $exception) {
        return false;
    }
}

function admin_logout(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
}

function admin_create_user(PDO $db, string $username, string $password, string $email): bool
{
    try {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $statement = $db->prepare(
            'INSERT INTO admins (username, password_hash, email) VALUES (:username, :password_hash, :email)'
        );
        return $statement->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
            'email' => $email,
        ]);
    } catch (Throwable $exception) {
        return false;
    }
}

// ============ GESTIÓN DE PRODUCTOS ============

function admin_get_all_products(PDO $db): array
{
    try {
        $statement = $db->query('SELECT * FROM products ORDER BY id DESC');
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $exception) {
        return [];
    }
}

function admin_get_product(PDO $db, int $productId): ?array
{
    try {
        $statement = $db->prepare('SELECT * FROM products WHERE id = :id');
        $statement->execute(['id' => $productId]);
        $product = $statement->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    } catch (Throwable $exception) {
        return null;
    }
}

function admin_create_product(PDO $db, array $data): bool
{
    try {
        $statement = $db->prepare(
            'INSERT INTO products (name, category, price, weight, badge, description, image)
             VALUES (:name, :category, :price, :weight, :badge, :description, :image)'
        );
        return $statement->execute([
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => (float)$data['price'],
            'weight' => $data['weight'],
            'badge' => $data['badge'],
            'description' => $data['description'],
            'image' => $data['image'],
        ]);
    } catch (Throwable $exception) {
        return false;
    }
}

function admin_update_product(PDO $db, int $productId, array $data): bool
{
    try {
        $statement = $db->prepare(
            'UPDATE products SET name = :name, category = :category, price = :price, 
             weight = :weight, badge = :badge, description = :description, image = :image
             WHERE id = :id'
        );
        return $statement->execute([
            'id' => $productId,
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => (float)$data['price'],
            'weight' => $data['weight'],
            'badge' => $data['badge'],
            'description' => $data['description'],
            'image' => $data['image'],
        ]);
    } catch (Throwable $exception) {
        return false;
    }
}

function admin_delete_product(PDO $db, int $productId): bool
{
    try {
        $statement = $db->prepare('UPDATE products SET active = 0 WHERE id = :id');
        return $statement->execute(['id' => $productId]);
    } catch (Throwable $exception) {
        return false;
    }
}

// ============ GESTIÓN DE PEDIDOS ============

function admin_get_all_orders(PDO $db, int $limit = 50, int $offset = 0): array
{
    try {
        $statement = $db->prepare(
            'SELECT o.*, c.name as customer_name, c.email, c.phone 
             FROM orders o
             JOIN customers c ON o.customer_id = c.id
             ORDER BY o.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $exception) {
        return [];
    }
}

function admin_get_order(PDO $db, int $orderId): ?array
{
    try {
        $statement = $db->prepare(
            'SELECT o.*, c.name as customer_name, c.email, c.phone, c.address
             FROM orders o
             JOIN customers c ON o.customer_id = c.id
             WHERE o.id = :id'
        );
        $statement->execute(['id' => $orderId]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $exception) {
        return null;
    }
}

function admin_get_order_items(PDO $db, int $orderId): array
{
    try {
        $statement = $db->prepare(
            'SELECT * FROM order_items WHERE order_id = :order_id'
        );
        $statement->execute(['order_id' => $orderId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $exception) {
        return [];
    }
}

function admin_update_order_status(PDO $db, int $orderId, string $status): bool
{
    try {
        $statement = $db->prepare('UPDATE orders SET status = :status WHERE id = :id');
        return $statement->execute(['status' => $status, 'id' => $orderId]);
    } catch (Throwable $exception) {
        return false;
    }
}

function admin_get_total_orders_count(PDO $db): int
{
    try {
        return (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    } catch (Throwable $exception) {
        return 0;
    }
}

// ============ ESTADÍSTICAS ============

function admin_get_dashboard_stats(PDO $db): array
{
    try {
        $totalOrders = (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $totalProducts = (int)$db->query('SELECT COUNT(*) FROM products WHERE active = 1')->fetchColumn();
        $totalCustomers = (int)$db->query('SELECT COUNT(*) FROM customers')->fetchColumn();
        
        $totalRevenue = $db->query('SELECT COALESCE(SUM(total), 0) FROM orders')->fetchColumn();
        
        // Últimos 7 días
        $recentOrders = $db->query(
            'SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();

        return [
            'total_orders' => $totalOrders,
            'total_products' => $totalProducts,
            'total_customers' => $totalCustomers,
            'total_revenue' => (float)$totalRevenue,
            'recent_orders_7days' => (int)$recentOrders,
        ];
    } catch (Throwable $exception) {
        return [];
    }
}

function admin_get_revenue_by_date(PDO $db, int $days = 30): array
{
    try {
        $statement = $db->prepare(
            'SELECT DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue
             FROM orders
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC'
        );
        $statement->bindValue('days', $days, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $exception) {
        return [];
    }
}

function admin_get_top_products(PDO $db, int $limit = 10): array
{
    try {
        $statement = $db->prepare(
            'SELECT p.id, p.name, p.price, COUNT(oi.id) as total_sold, SUM(oi.quantity) as total_quantity
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             GROUP BY p.id
             ORDER BY total_quantity DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $exception) {
        return [];
    }
}

// ============ PAGOS ============

function admin_get_payment(PDO $db, int $orderId): ?array
{
    try {
        $statement = $db->prepare('SELECT * FROM payments WHERE order_id = :order_id');
        $statement->execute(['order_id' => $orderId]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $exception) {
        return null;
    }
}

function admin_update_payment_status(PDO $db, int $orderId, string $status): bool
{
    try {
        $statement = $db->prepare(
            'UPDATE payments SET status = :status, paid_at = CASE WHEN :status = "paid" THEN NOW() ELSE paid_at END 
             WHERE order_id = :order_id'
        );
        return $statement->execute(['status' => $status, 'order_id' => $orderId]);
    } catch (Throwable $exception) {
        return false;
    }
}
