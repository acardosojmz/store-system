<?php
$pageTitle = "Detalles del Pedido - " . SITE_NAME;
include 'views/layouts/header.php';

$order_id = $_GET['id'] ?? 0;
$order_info = null;
$order_details = [];

if ($order_id) {
    try {
        loadClass('Database');
        loadClass('Order');
        loadClass('OrderDetail');

        $database = new Database();
        $conn = $database->getConnection();
        $order = new Order($conn);
        $orderDetail = new OrderDetail($conn);

        $order_info = $order->getById($order_id);
        if ($order_info) {
            $order_details = $orderDetail->getByOrderId($order_id);
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'danger');
    }
}

if (!$order_info) {
    showAlert('Pedido no encontrado', 'danger');
    redirect('orders');
    exit;
}

// Calcular totales
$subtotal = 0;
foreach ($order_details as $detail) {
    $subtotal += $detail['quantity'] * $detail['unit_price']; // Nota: 'quantity' tiene typo en la BD
}
?>

    <div class="flex flex-between mb-3">
        <h1>Pedido #<?php echo $order_info['order_number']; ?></h1>
        <a href="<?php echo BASE_URL; ?>orders" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Pedidos
        </a>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información del Pedido</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                    <tr>
                        <td><strong>Número de Orden:</strong></td>
                        <td>#<?php echo $order_info['order_number']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Cliente:</strong></td>
                        <td>
                            <i class="fas fa-user text-primary"></i>
                            <?php echo htmlspecialchars($order_info['customer_name']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>
                            <i class="fas fa-envelope text-secondary"></i>
                            <a href="mailto:<?php echo $order_info['customer_email']; ?>">
                                <?php echo htmlspecialchars($order_info['customer_email']); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fecha del Pedido:</strong></td>
                        <td><?php echo formatDate($order_info['order_date']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Fecha Requerida:</strong></td>
                        <td><?php echo $order_info['required'] ? formatDate($order_info['required']) : 'No especificada'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Envío:</strong></td>
                        <td><?php echo $order_info['shipped'] ? formatDate($order_info['shipped']) : '<span class="text-warning">Pendiente</span>'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            <?php if ($order_info['shipped']): ?>
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i> Enviado
                                </span>
                            <?php else: ?>
                                <span class="text-warning">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Resumen del Pedido</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-shopping-cart fa-4x text-primary mb-2"></i>
                    <h2 class="text-primary"><?php echo formatCurrency($subtotal); ?></h2>
                    <p class="text-secondary">Total del Pedido</p>
                </div>

                <div class="grid grid-2 gap-2">
                    <div class="text-center">
                        <h4 class="text-success"><?php echo count($order_details); ?></h4>
                        <small class="text-secondary">Productos</small>
                    </div>
                    <div class="text-center">
                        <h4 class="text-info">
                            <?php
                            $total_quantity = array_sum(array_column($order_details, 'quantity'));
                            echo $total_quantity;
                            ?>
                        </h4>
                        <small class="text-secondary">Unidades</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Productos del Pedido</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($order_details)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($order_details as $detail): ?>
                            <tr>
                                <td><strong>#<?php echo $detail['product_code']; ?></strong></td>
                                <td><?php echo htmlspecialchars($detail['product']); ?></td>
                                <td><?php echo htmlspecialchars($detail['description'] ?? 'Sin descripción'); ?></td>
                                <td class="text-center">
                                    <strong><?php echo $detail['quantity']; ?></strong>
                                </td>
                                <td><?php echo formatCurrency($detail['unit_price']); ?></td>
                                <td class="text-right">
                                    <strong class="text-primary">
                                        <?php echo formatCurrency($detail['quantity'] * $detail['unit_price']); ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Total del Pedido:</strong></td>
                            <td class="text-right">
                                <strong class="text-success" style="font-size: 1.2em;">
                                    <?php echo formatCurrency($subtotal); ?>
                                </strong>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <i class="fas fa-box-open fa-4x text-secondary mb-2"></i>
                    <h3 class="text-secondary">Sin productos</h3>
                    <p class="text-secondary">Este pedido no tiene productos asociados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include 'views/layouts/footer.php'; ?>