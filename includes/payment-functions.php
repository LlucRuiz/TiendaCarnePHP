<?php

// Instala con: composer require stripe/stripe-php

// ============ CONFIGURACIÓN STRIPE ============

function get_stripe_keys(): array
{
    return [
        'public' => getenv('STRIPE_PUBLIC_KEY') ?: 'pk_test_123456789',
        'secret' => getenv('STRIPE_SECRET_KEY') ?: 'sk_test_123456789',
    ];
}

// ============ CREAR SESIÓN DE PAGO ============

function create_stripe_payment_session(
    PDO $db,
    int $orderId,
    string $customerEmail,
    array $orderItems,
    float $totalAmount
): ?array
{
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $keys = get_stripe_keys();
        \Stripe\Stripe::setApiKey($keys['secret']);

        // Preparar items para Stripe
        $lineItems = [];
        foreach ($orderItems as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['product_name'],
                        'description' => $item['product_name'],
                    ],
                    'unit_amount' => (int)($item['unit_price'] * 100), // en centavos
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Crear sesión de checkout
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => getenv('APP_URL') . '/carrito.php?payment_success={CHECKOUT_SESSION_ID}',
            'cancel_url' => getenv('APP_URL') . '/carrito.php?payment_cancelled=1',
            'customer_email' => $customerEmail,
            'client_reference_id' => (string)$orderId,
        ]);

        // Guardar sesión en BD
        save_payment_session($db, $orderId, $session->id, (int)($totalAmount * 100));

        return [
            'session_id' => $session->id,
            'payment_url' => $session->url,
        ];
    } catch (Throwable $exception) {
        error_log('Stripe error: ' . $exception->getMessage());
        return null;
    }
}

// ============ GUARDAR SESIÓN DE PAGO ============

function save_payment_session(PDO $db, int $orderId, string $sessionId, int $amountCents): bool
{
    try {
        $statement = $db->prepare(
            'INSERT INTO payments (order_id, stripe_session_id, amount_cents, status)
             VALUES (:order_id, :session_id, :amount_cents, :status)
             ON DUPLICATE KEY UPDATE stripe_session_id = :session_id'
        );
        return $statement->execute([
            'order_id' => $orderId,
            'session_id' => $sessionId,
            'amount_cents' => $amountCents,
            'status' => 'pending',
        ]);
    } catch (Throwable $exception) {
        return false;
    }
}

// ============ VERIFICAR ESTADO DE PAGO ============

function verify_payment_session(PDO $db, string $sessionId): bool
{
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $keys = get_stripe_keys();
        \Stripe\Stripe::setApiKey($keys['secret']);

        $session = \Stripe\Checkout\Session::retrieve($sessionId);

        if ($session->payment_status === 'paid') {
            // Actualizar pago en BD
            $statement = $db->prepare(
                'UPDATE payments SET status = :status, paid_at = NOW() WHERE stripe_session_id = :session_id'
            );
            $statement->execute(['status' => 'paid', 'session_id' => $sessionId]);

            // Actualizar estado de pedido
            $paymentStatement = $db->prepare('SELECT order_id FROM payments WHERE stripe_session_id = :session_id');
            $paymentStatement->execute(['session_id' => $sessionId]);
            $payment = $paymentStatement->fetch(PDO::FETCH_ASSOC);

            if ($payment) {
                $orderStatement = $db->prepare('UPDATE orders SET status = :status WHERE id = :id');
                $orderStatement->execute(['status' => 'pagado', 'id' => $payment['order_id']]);
            }

            return true;
        }

        return false;
    } catch (Throwable $exception) {
        error_log('Payment verification error: ' . $exception->getMessage());
        return false;
    }
}

// ============ WEBHOOK (para procesamiento asincrónico) ============

function handle_stripe_webhook(PDO $db): bool
{
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $keys = get_stripe_keys();
        \Stripe\Stripe::setApiKey($keys['secret']);

        $input = file_get_contents('php://input');
        $event = json_decode($input, true);

        if ($event['type'] === 'checkout.session.completed') {
            $sessionId = $event['data']['object']['id'];
            return verify_payment_session($db, $sessionId);
        }

        return true;
    } catch (Throwable $exception) {
        error_log('Webhook error: ' . $exception->getMessage());
        return false;
    }
}

// ============ HISTORIAL DE PAGOS ============

function get_payment_history(PDO $db, int $limit = 50): array
{
    try {
        $statement = $db->prepare(
            'SELECT p.*, o.total as order_total, c.name as customer_name
             FROM payments p
             JOIN orders o ON p.order_id = o.id
             JOIN customers c ON o.customer_id = c.id
             ORDER BY p.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $exception) {
        return [];
    }
}

function get_payment_stats(PDO $db): array
{
    try {
        $totalPaid = $db->query('SELECT COALESCE(SUM(amount_cents), 0) / 100.0 FROM payments WHERE status = "paid"')->fetchColumn();
        $totalPending = $db->query('SELECT COALESCE(SUM(amount_cents), 0) / 100.0 FROM payments WHERE status = "pending"')->fetchColumn();
        $countPaid = $db->query('SELECT COUNT(*) FROM payments WHERE status = "paid"')->fetchColumn();
        $countPending = $db->query('SELECT COUNT(*) FROM payments WHERE status = "pending"')->fetchColumn();

        return [
            'total_paid' => (float)$totalPaid,
            'total_pending' => (float)$totalPending,
            'count_paid' => (int)$countPaid,
            'count_pending' => (int)$countPending,
        ];
    } catch (Throwable $exception) {
        return [];
    }
}
