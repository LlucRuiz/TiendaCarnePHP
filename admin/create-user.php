#!/usr/bin/env php
<?php
/**
 * Script para crear usuario administrador
 * Uso: php admin/create-user.php
 */

require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/admin-functions.php';

if (php_sapi_name() !== 'cli') {
    exit('Este script solo se puede ejecutar desde línea de comandos.');
}

echo "=== Crear Usuario Administrador ===\n\n";

// Solicitar datos
fwrite(STDOUT, "Usuario: ");
$username = trim(fgets(STDIN));

fwrite(STDOUT, "Contraseña: ");
$password = trim(fgets(STDIN));

fwrite(STDOUT, "Email: ");
$email = trim(fgets(STDIN));

// Validar
if (empty($username) || empty($password) || empty($email)) {
    echo "❌ Todos los campos son requeridos.\n";
    exit(1);
}

// Crear usuario
if (admin_create_user($db, $username, $password, $email)) {
    echo "\n✅ Usuario '$username' creado exitosamente.\n";
    echo "Puedes ingresar al panel en: http://localhost:8000/admin/login.php\n";
} else {
    echo "\n❌ Error al crear el usuario. Verifica que el usuario no exista.\n";
    exit(1);
}
