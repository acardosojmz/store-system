<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'routes.php';

// Obtener la ruta
$route = $_GET['route'] ?? '';
$route = trim($route, '/');

// Procesar la ruta
processRoute($route);
?>