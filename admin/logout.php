<?php
session_start();
require_once __DIR__ . '/../includes/admin-functions.php';

admin_logout();
header('Location: login.php');
exit;
