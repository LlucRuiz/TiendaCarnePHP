<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$db = get_db_connection();
$seedProducts = $products;

if ($db) {
	initialize_database($db, $seedProducts);
}

$products = load_products_catalog($db, $seedProducts);

