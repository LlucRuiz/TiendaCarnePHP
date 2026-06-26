<?php

require_once __DIR__ . '/includes/app.php';

$name = '';
$phone = '';
$message = '';
$sent = false;
$saved = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name !== '' && $phone !== '' && $message !== '') {
        $saved = save_contact_message($db, $name, $phone, $message);
        $sent = $saved;

        if (!$saved) {
            $error = 'No se pudo guardar el mensaje en MySQL. Revisa la conexión y las tablas.';
        }
    } else {
        $error = 'Completa nombre, teléfono y mensaje.';
    }
}

$pageTitle = site_page_title('contacto', $store);
$activePage = 'contacto';

require __DIR__ . '/includes/header.php';
?>
<section class="section contact-grid">
    <div>
        <span class="eyebrow">Contacto</span>
        <h1>Hablemos de tu pedido</h1>
        <p>Cuéntanos qué necesitas y te devolvemos una recomendación simple: corte, peso y forma de preparación.</p>
        <div class="contact-box">
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($store['phone']); ?></p>
            <p><strong>WhatsApp:</strong> <?php echo htmlspecialchars($store['whatsapp']); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($store['email']); ?></p>
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($store['address']); ?></p>
        </div>
    </div>

    <form class="contact-form" method="post" action="contacto.php">
        <label>
            Nombre
            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Tu nombre" required>
        </label>
        <label>
            Teléfono
            <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Tu teléfono" required>
        </label>
        <label>
            Mensaje
            <textarea name="message" rows="6" placeholder="Cuéntanos qué carne buscas" required><?php echo htmlspecialchars($message); ?></textarea>
        </label>
        <button class="button button-primary" type="submit">Enviar consulta</button>

        <?php if ($sent): ?>
            <div class="notice success">Gracias, <?php echo htmlspecialchars($name); ?>. Hemos recibido tu consulta.</div>
        <?php elseif ($error !== ''): ?>
            <div class="notice"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
