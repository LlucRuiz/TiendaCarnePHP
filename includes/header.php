<?php

$activePage = $activePage ?? 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? $store['name']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alegreya+Sans:wght@400;500;700;800&family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="page-shell">
    <header class="topbar">
        <a class="brand" href="index.php">
            <span class="brand-mark">CM</span>
            <span>
                <strong><?php echo htmlspecialchars($store['name']); ?></strong>
                <small>Surtido castizo, carne de confianza</small>
            </span>
        </a>
        <nav class="nav">
            <a class="<?php echo $activePage === 'inicio' ? 'is-active' : ''; ?>" href="index.php">Inicio</a>
            <a class="<?php echo $activePage === 'productos' ? 'is-active' : ''; ?>" href="productos.php">Productos</a>
            <a class="<?php echo $activePage === 'carrito' ? 'is-active' : ''; ?>" href="carrito.php">Carrito</a>
            <a class="<?php echo $activePage === 'contacto' ? 'is-active' : ''; ?>" href="contacto.php">Contacto</a>
        </nav>
    </header>
    <main class="main-content">
