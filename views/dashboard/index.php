<?php
$pageTitle = "Dashboard - " . SITE_NAME;
include 'views/layouts/header.php';

// Obtener estadísticas
try {
    loadClass('Database');
    loadClass('Product');
    loadClass('Customer');
    loadClass('Order');

    $database = new Database();
    $conn = $database->getConnection();

    $product = new Product($conn);
    $customer = new Customer($conn);
    $order = new Order($conn);

    $products = $product->getAll();
    $customers = $customer->getAll();
    $orders = $order->getAll();

    $totalProducts = count($products);
    $totalCustomers = count($customers);
    $totalOrders = count($orders);

    // Productos con stock bajo
    $lowStockProducts = array_filter($products, function($p) {
        return $p['stock'] < 10;
    });

} catch (Exception $e) {
    showAlert('Error: ' . $e->getMessage(), 'danger');
    $totalProducts = $totalCustomers = $totalOrders = 0;
    $lowStockProducts = [];
}
?>

<div class="grid grid-4">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-box fa-3x text-primary"></i>
            <h3 class="text-primary"><?php echo $totalProducts; ?></h3>
            <p>Productos</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-users fa-3x text-success"></i>
            <h3 class="text-success"><?php echo $totalCustomers; ?></h3>
            <p>Clientes</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-shopping-cart fa-3x text-warning"></i>
            <h3 class="text-warning"><?php echo $totalOrders; ?></h3>
            <p>Pedidos</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
            <h3 class="text-danger"><?php echo count($lowStockProducts); ?></h3>
            <p>Stock Bajo</p>
        </div>
    </div>
</div>

<?php if (!empty($lowStockProducts)): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">⚠️ Productos con Stock Bajo</h2>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock Actual</th>
                        <th>Precio</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lowStockProducts as $p): ?>
                        <tr>
                            <td><?php echo $p['product_code']; ?></td>
                            <td><?php echo htmlspecialchars($p['product']); ?></td>
                            <td class="text-danger"><strong><?php echo $p['stock']; ?></strong></td>
                            <td><?php echo formatCurrency($p['unit_price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Acciones Rápidas</h3>
        </div>
        <div class="card-body">
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>orders/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Pedido
                </a>
                <a href="<?php echo BASE_URL; ?>products/create" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </a>
                <a href="<?php echo BASE_URL; ?>customers/create" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </a>
                <a href="<?php echo BASE_URL; ?>products/stock" class="btn btn-warning">
                    <i class="fas fa-search"></i> Consultar Stock
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pedidos Recientes</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($orders)): ?>
                <?php $recentOrders = array_slice($orders, 0, 5); ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Orden #</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>orders/details/<?php echo $ord['order_number']; ?>" class="text-primary">
                                        #<?php echo $ord['order_number']; ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($ord['customer_name']); ?></td>
                                <td><?php echo formatDate($ord['order_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-secondary">No hay pedidos registrados</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
