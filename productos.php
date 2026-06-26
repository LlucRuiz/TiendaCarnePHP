<?php

require_once __DIR__ . '/includes/app.php';

if (isset($_GET['add'])) {
    add_to_cart((int) $_GET['add']);
    header('Location: productos.php?added=1');
    exit;
}

if (isset($_GET['remove'])) {
    remove_from_cart((int) $_GET['remove']);
    header('Location: carrito.php?removed=1');
    exit;
}

$pageTitle = site_page_title('productos', $store);
$activePage = 'productos';

require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="section-heading split">
        <div>
            <span class="eyebrow">Catálogo</span>
            <h1>Nuestros cortes y preparados</h1>
        </div>
        <a class="button button-secondary" href="carrito.php">Ir al carrito</a>
    </div>

    <?php if (isset($_GET['added'])): ?>
        <div class="notice success">Producto añadido al carrito.</div>
    <?php endif; ?>

    <div class="cards-grid two-up">
        <?php foreach ($products as $product): ?>
            <article class="product-card product-card-large">
                <div class="product-image-wrap">
                    <img class="product-image" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="card-topline">
                    <span class="card-badge"><?php echo htmlspecialchars($product['badge']); ?></span>
                    <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                </div>
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <div class="product-meta">
                    <strong><?php echo format_currency($product['price']); ?></strong>
                    <span><?php echo htmlspecialchars($product['weight']); ?></span>
                </div>
                <a class="button button-primary full-width" href="productos.php?add=<?php echo (int) $product['id']; ?>">Agregar al carrito</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
