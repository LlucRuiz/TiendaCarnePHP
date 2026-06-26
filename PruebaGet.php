<?php

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carne Manolo | Consulta enviada</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Georgia, "Times New Roman", serif;
            background: linear-gradient(180deg, #201714 0%, #120f0d 100%);
            color: #f8f1ea;
        }
        .card {
            width: min(760px, calc(100% - 32px));
            padding: 28px;
            border-radius: 28px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            box-shadow: 0 18px 60px rgba(0,0,0,.35);
        }
        h1 { margin-top: 0; }
        p { line-height: 1.7; color: #c7b8ad; }
        .meta {
            display: grid;
            gap: 12px;
            margin: 22px 0;
            padding: 18px;
            border-radius: 20px;
            background: rgba(255,255,255,.04);
        }
        a {
            display: inline-block;
            padding: 14px 20px;
            border-radius: 16px;
            text-decoration: none;
            color: #170d0a;
            background: linear-gradient(135deg, #f39a35, #ffbe6a);
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Consulta enviada</h1>
        <p>Recibimos tus datos y ya los dejamos listos para atención de la tienda.</p>
        <div class="meta">
            <div><strong>Nombre:</strong> <?php echo h($nombre ?: 'Sin nombre'); ?></div>
            <div><strong>Teléfono:</strong> <?php echo h($telefono ?: 'Sin teléfono'); ?></div>
            <div><strong>Mensaje:</strong> <?php echo nl2br(h($mensaje ?: 'Sin mensaje')); ?></div>
        </div>
        <a href="contacto.php">Volver a contacto</a>
    </main>
</body>
</html>