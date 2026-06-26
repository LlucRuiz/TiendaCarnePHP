<?php

require_once __DIR__ . '/includes/app.php';

$pageTitle = site_page_title('inicio', $store);
$activePage = 'inicio';
$featuredProducts = array_slice($products, 0, 3);

require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">Carnicería online artesanal</span>
        <h1>Carne Manolo Toda la carne del Mercado (No vendemos carne Humana)</h1>
        <p><?php echo htmlspecialchars($store['description']); ?></p>
        <div class="hero-actions">
            <a class="button button-primary" href="productos.php">Ver catálogo</a>
            <a class="button button-secondary" href="contacto.php">Pedir asesoría</a>
        </div>
        <div class="hero-metrics">
            <div><strong>24 h</strong><span>preparación rápida</span></div>
            <div><strong>100%</strong><span>cortes seleccionados</span></div>
            <div><strong>4.8/5</strong><span>valoración estimada</span></div>
        </div>
    </div>
    <aside class="hero-card">
        <h2>Hoy en mostrador</h2>
        <p>Entrecot madurado, solomillo y hamburguesas artesanas listos para salir.</p>
        <ul>
            <li>Recolecta en tienda</li>
            <li>Entrega local</li>
            <li>Recomendación por uso</li>
        </ul>
    </aside>
</section>

<section class="section">
    <div class="section-heading">
        <span class="eyebrow">Lo que nos define</span>
        <h2>Una tienda pensada como negocio real.</h2>
    </div>
    <div class="cards-grid three-up">
        <?php foreach ($highlights as $highlight): ?>
            <article class="info-card">
                <h3><?php echo htmlspecialchars($highlight['title']); ?></h3>
                <p><?php echo htmlspecialchars($highlight['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="section-heading split">
        <div>
            <span class="eyebrow">Selección destacada</span>
            <h2>Productos listos para vender.</h2>
        </div>
        <a class="text-link" href="productos.php">Ver todos los productos</a>
    </div>
    <div class="cards-grid three-up">
        <?php foreach ($featuredProducts as $product): ?>
            <article class="product-card">
                <div class="product-image-wrap product-image-wrap-small">
                    <img class="product-image" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="card-badge"><?php echo htmlspecialchars($product['badge']); ?></div>
                <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <div class="product-meta">
                    <strong><?php echo format_currency($product['price']); ?></strong>
                    <span><?php echo htmlspecialchars($product['weight']); ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
