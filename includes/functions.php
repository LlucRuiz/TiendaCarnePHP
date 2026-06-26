<?php

function initialize_database(PDO $connection, array $seedProducts): void
{
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS products (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            category VARCHAR(120) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            weight VARCHAR(50) NOT NULL,
            badge VARCHAR(80) NOT NULL,
            description TEXT NOT NULL,
            image VARCHAR(255) NOT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS customers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            email VARCHAR(160) NULL,
            address VARCHAR(255) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS orders (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_id INT UNSIGNED NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            shipping DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            notes TEXT NULL,
            status VARCHAR(30) NOT NULL DEFAULT "nuevo",
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers (id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS order_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED NOT NULL,
            product_id INT UNSIGNED NOT NULL,
            product_name VARCHAR(120) NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            quantity INT UNSIGNED NOT NULL,
            line_total DECIMAL(10,2) NOT NULL,
            CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS contact_messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            phone VARCHAR(40) NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    if (!empty($seedProducts)) {
        $count = (int) $connection->query('SELECT COUNT(*) FROM products')->fetchColumn();

        if ($count === 0) {
            $statement = $connection->prepare(
                'INSERT INTO products (id, name, category, price, weight, badge, description, image, active)
                 VALUES (:id, :name, :category, :price, :weight, :badge, :description, :image, 1)'
            );

            foreach ($seedProducts as $product) {
                $statement->execute([
                    'id' => (int) $product['id'],
                    'name' => $product['name'],
                    'category' => $product['category'],
                    'price' => $product['price'],
                    'weight' => $product['weight'],
                    'badge' => $product['badge'],
                    'description' => $product['description'],
                    'image' => $product['image'],
                ]);
            }
        }
    }
}

function load_products_catalog(?PDO $connection, array $fallbackProducts): array
{
    if (!$connection) {
        return $fallbackProducts;
    }

    try {
        $statement = $connection->query(
            'SELECT id, name, category, price, weight, badge, description, image
             FROM products
             WHERE active = 1
             ORDER BY id ASC'
        );

        $products = $statement->fetchAll();

        if (!$products) {
            return $fallbackProducts;
        }

        return array_map(static function (array $product): array {
            return [
                'id' => (int) $product['id'],
                'name' => $product['name'],
                'category' => $product['category'],
                'price' => (float) $product['price'],
                'weight' => $product['weight'],
                'badge' => $product['badge'],
                'description' => $product['description'],
                'image' => $product['image'],
            ];
        }, $products);
    } catch (Throwable $exception) {
        return $fallbackProducts;
    }
}

function save_contact_message(?PDO $connection, string $name, string $phone, string $message): bool
{
    if (!$connection) {
        return false;
    }

    try {
        $statement = $connection->prepare(
            'INSERT INTO contact_messages (name, phone, message)
             VALUES (:name, :phone, :message)'
        );

        return $statement->execute([
            'name' => $name,
            'phone' => $phone,
            'message' => $message,
        ]);
    } catch (Throwable $exception) {
        return false;
    }
}

function save_order(?PDO $connection, array $customer, array $summary): ?int
{
    if (!$connection || empty($summary['items'])) {
        return null;
    }

    try {
        $connection->beginTransaction();

        $customerStatement = $connection->prepare(
            'INSERT INTO customers (name, phone, email, address)
             VALUES (:name, :phone, :email, :address)'
        );

        $customerStatement->execute([
            'name' => $customer['name'],
            'phone' => $customer['phone'],
            'email' => $customer['email'] ?? null,
            'address' => $customer['address'] ?? null,
        ]);

        $customerId = (int) $connection->lastInsertId();

        $orderStatement = $connection->prepare(
            'INSERT INTO orders (customer_id, subtotal, shipping, total, notes, status)
             VALUES (:customer_id, :subtotal, :shipping, :total, :notes, :status)'
        );

        $orderStatement->execute([
            'customer_id' => $customerId,
            'subtotal' => $summary['subtotal'],
            'shipping' => $summary['shipping'],
            'total' => $summary['total'],
            'notes' => $customer['notes'] ?? null,
            'status' => 'nuevo',
        ]);

        $orderId = (int) $connection->lastInsertId();

        $itemStatement = $connection->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
             VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity, :line_total)'
        );

        foreach ($summary['items'] as $item) {
            $itemStatement->execute([
                'order_id' => $orderId,
                'product_id' => (int) $item['product']['id'],
                'product_name' => $item['product']['name'],
                'unit_price' => $item['product']['price'],
                'quantity' => (int) $item['count'],
                'line_total' => $item['line_total'],
            ]);
        }

        $connection->commit();

        return $orderId;
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        return null;
    }
}

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function format_currency(float $amount): string
{
    return number_format($amount, 2, ',', '.') . ' €';
}

function find_product(array $products, int $productId): ?array
{
    foreach ($products as $product) {
        if ((int) $product['id'] === $productId) {
            return $product;
        }
    }

    return null;
}

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function add_to_cart(int $productId): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
}

function remove_from_cart(int $productId): void
{
    if (!isset($_SESSION['cart'][$productId])) {
        return;
    }

    unset($_SESSION['cart'][$productId]);
}

function clear_cart(): void
{
    $_SESSION['cart'] = [];
}

function cart_summary(array $products): array
{
    $items = [];
    $quantity = 0;
    $subtotal = 0.0;

    foreach (cart_items() as $productId => $count) {
        $product = find_product($products, (int) $productId);

        if (!$product) {
            continue;
        }

        $lineTotal = $product['price'] * $count;
        $items[] = [
            'product' => $product,
            'count' => $count,
            'line_total' => $lineTotal,
        ];
        $quantity += $count;
        $subtotal += $lineTotal;
    }

    return [
        'items' => $items,
        'quantity' => $quantity,
        'subtotal' => $subtotal,
        'shipping' => $subtotal > 0 ? 4.50 : 0.0,
        'total' => $subtotal > 0 ? $subtotal + 4.50 : 0.0,
    ];
}

function site_page_title(string $section, array $store): string
{
    $titles = [
        'inicio' => 'Inicio',
        'productos' => 'Productos',
        'carrito' => 'Carrito',
        'contacto' => 'Contacto',
    ];

    $suffix = $titles[$section] ?? 'Tienda';

    return $store['name'] . ' | ' . $suffix;
}
