<?php
define('BASE_URL', 'http://store-system/'); // Cambiar por tu dominio
define('SITE_NAME', 'Sistema de Tienda');
define('TIMEZONE', 'America/Mexico_City');

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// Configuración de errores (desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>