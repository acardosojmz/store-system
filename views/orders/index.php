<?php
$pageTitle = "Pedidos - " . SITE_NAME;
include 'views/layouts/header.php';

// Obtener pedidos
try {
    loadClass('Database');
    loadClass('Order');

    $database = new Database();
    $conn = $database->getConnection();
    $order = new Order($conn);
    $orders = $order->getAll();
} catch (Exception $e) {
    showAlert('Error al cargar pedidos: ' . $e->getMessage(), 'danger');
    $orders = [];
}
?>

    <div class="flex flex-between mb-3">
        <h1>Gestión de Pedidos</h1>
        <a href="<?php echo BASE_URL; ?>orders/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Pedido
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Pedidos</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($orders)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Orden #</th>
                            <th>Cliente</th>
                            <th>Fecha Pedido</th>
                            <th>Fecha Requerida</th>
                            <th>Fecha Envío</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary">#<?php echo $o['order_number']; ?></strong>
                                </td>
                                <td>
                                    <i class="fas fa-user text-secondary"></i>
                                    <?php echo htmlspecialchars($o['customer_name'] ?? 'Cliente no encontrado'); ?>
                                </td>
                                <td><?php echo formatDate($o['order_date']); ?></td>
                                <td><?php echo $o['required'] ? formatDate($o['required']) : '-'; ?></td>
                                <td><?php echo $o['shipped'] ? formatDate($o['shipped']) : '<span class="text-warning">Pendiente</span>'; ?></td>
                                <td>
                                    <?php if ($o['shipped']): ?>
                                        <span class="text-success">
                                    <i class="fas fa-check-circle"></i> Enviado
                                </span>
                                    <?php else: ?>
                                        <span class="text-warning">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>orders/details/<?php echo $o['order_number']; ?>"
                                       class="btn btn-sm btn-info" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <i class="fas fa-shopping-cart fa-4x text-secondary mb-2"></i>
                    <h3 class="text-secondary">No hay pedidos registrados</h3>
                    <p class="text-secondary mb-3">Comienza creando tu primer pedido</p>
                    <a href="<?php echo BASE_URL; ?>orders/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primer Pedido
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include 'views/layouts/footer.php'; ?>