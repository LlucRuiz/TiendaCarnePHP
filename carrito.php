<?php

require_once __DIR__ . '/includes/app.php';

$checkoutName = '';
$checkoutPhone = '';
$checkoutEmail = '';
$checkoutAddress = '';
$checkoutNotes = '';
$checkoutError = '';

if (isset($_GET['clear'])) {
    clear_cart();
    header('Location: carrito.php?emptied=1');
    exit;
}

if (isset($_GET['remove'])) {
    remove_from_cart((int) $_GET['remove']);
    header('Location: carrito.php?removed=1');
    exit;
}

$pageTitle = site_page_title('carrito', $store);
$activePage = 'carrito';
$summary = cart_summary($products);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
    $checkoutName = trim($_POST['customer_name'] ?? '');
    $checkoutPhone = trim($_POST['customer_phone'] ?? '');
    $checkoutEmail = trim($_POST['customer_email'] ?? '');
    $checkoutAddress = trim($_POST['customer_address'] ?? '');
    $checkoutNotes = trim($_POST['customer_notes'] ?? '');

    if ($checkoutName !== '' && $checkoutPhone !== '' && !empty($summary['items'])) {
        $orderId = save_order($db, [
            'name' => $checkoutName,
            'phone' => $checkoutPhone,
            'email' => $checkoutEmail,
            'address' => $checkoutAddress,
            'notes' => $checkoutNotes,
        ], $summary);

        if ($orderId !== null) {
            clear_cart();
            header('Location: carrito.php?ordered=' . $orderId);
            exit;
        }

        $checkoutError = 'No se pudo guardar el pedido en MySQL. Revisa la base de datos.';
    } else {
        $checkoutError = 'Completa nombre, teléfono y deja productos en el carrito.';
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="section-heading split">
        <div>
            <span class="eyebrow">Carrito</span>
            <h1>Tu pedido</h1>
        </div>
        <a class="button button-secondary" href="productos.php">Seguir comprando</a>
    </div>

    <?php if (isset($_GET['removed'])): ?>
        <div class="notice">Producto eliminado del carrito.</div>
    <?php endif; ?>

    <?php if (isset($_GET['ordered'])): ?>
        <div class="notice success">Pedido guardado en MySQL con el código #<?php echo (int) $_GET['ordered']; ?>.</div>
    <?php endif; ?>

    <?php if ($checkoutError !== ''): ?>
        <div class="notice"><?php echo htmlspecialchars($checkoutError); ?></div>
    <?php endif; ?>

    <?php if (empty($summary['items'])): ?>
        <div class="empty-state">
            <h2>No hay productos todavía.</h2>
            <p>Explora el catálogo y agrega los cortes que quieras para armar tu pedido.</p>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-list">
                <?php foreach ($summary['items'] as $item): ?>
                    <article class="cart-item">
                        <div>
                            <span class="product-category"><?php echo htmlspecialchars($item['product']['category']); ?></span>
                            <h2><?php echo htmlspecialchars($item['product']['name']); ?></h2>
                            <p><?php echo htmlspecialchars($item['product']['weight']); ?> · <?php echo $item['count']; ?> unidad<?php echo $item['count'] > 1 ? 'es' : ''; ?></p>
                        </div>
                        <div class="cart-item-actions">
                            <strong><?php echo format_currency($item['line_total']); ?></strong>
                            <a class="text-link" href="carrito.php?remove=<?php echo (int) $item['product']['id']; ?>">Quitar</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <aside class="cart-summary">
                <h2>Resumen</h2>
                <div class="summary-line"><span>Productos</span><strong><?php echo (int) $summary['quantity']; ?></strong></div>
                <div class="summary-line"><span>Subtotal</span><strong><?php echo format_currency($summary['subtotal']); ?></strong></div>
                <div class="summary-line"><span>Envío</span><strong><?php echo format_currency($summary['shipping']); ?></strong></div>
                <div class="summary-total"><span>Total</span><strong><?php echo format_currency($summary['total']); ?></strong></div>
                <form method="post" action="carrito.php">
                    <input type="hidden" name="action" value="checkout">
                    <label>
                        Nombre
                        <input type="text" name="customer_name" value="<?php echo htmlspecialchars($checkoutName); ?>" placeholder="Nombre del comprador">
                    </label>
                    <label>
                        Teléfono
                        <input type="text" name="customer_phone" value="<?php echo htmlspecialchars($checkoutPhone); ?>" placeholder="Teléfono de contacto">
                    </label>
                    <label>
                        Correo
                        <input type="email" name="customer_email" value="<?php echo htmlspecialchars($checkoutEmail); ?>" placeholder="Correo opcional">
                    </label>
                    <label>
                        Dirección
                        <input type="text" name="customer_address" value="<?php echo htmlspecialchars($checkoutAddress); ?>" placeholder="Dirección de entrega">
                    </label>
                    <label>
                        Notas
                        <textarea name="customer_notes" rows="4" placeholder="Indicaciones para el pedido"><?php echo htmlspecialchars($checkoutNotes); ?></textarea>
                    </label>
                    <button class="button button-primary full-width" type="submit">Guardar pedido en MySQL</button>
                </form>
                <a class="button button-secondary full-width" href="carrito.php?clear=1">Vaciar carrito</a>
            </aside>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
