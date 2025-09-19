<?php
function processRoute($route) {
    // Rutas del sistema
    $routes = [
        '' => 'views/dashboard/index.php',
        'dashboard' => 'views/dashboard/index.php',
        'products' => 'views/products/index.php',
        'products/create' => 'views/products/create.php',
        'products/stock' => 'views/products/stock.php',
        'customers' => 'views/customers/index.php',
        'customers/create' => 'views/customers/create.php',
        'orders' => 'views/orders/index.php',
        'orders/create' => 'views/orders/create.php',
        'api/products' => 'api/products.php',
        'api/customers' => 'api/customers.php',
        'api/orders' => 'api/orders.php',
    ];

    // Verificar si la ruta existe
    if (array_key_exists($route, $routes)) {
        $file = $routes[$route];
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Ruta con parámetros (ej: orders/details/123)
    $parts = explode('/', $route);
    if (count($parts) >= 2) {
        $controller = $parts[0];
        $action = $parts[1];
        $id = $parts[2] ?? null;

        if ($controller === 'orders' && $action === 'details' && $id) {
            $_GET['id'] = $id;
            require_once 'views/orders/details.php';
            return;
        }

        if ($controller === 'api') {
            handleApiRoute($action, $id);
            return;
        }
    }

    // Página 404
    http_response_code(404);
    require_once 'views/errors/404.php';
}

function handleApiRoute($endpoint, $id = null) {
    header('Content-Type: application/json');

    switch ($endpoint) {
        case 'products':
            require_once 'api/products.php';
            break;
        case 'customers':
            require_once 'api/customers.php';
            break;
        case 'orders':
            require_once 'api/orders.php';
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint no encontrado']);
    }
}
?>
