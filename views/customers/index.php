<?php
$pageTitle = "Clientes - " . SITE_NAME;
include 'views/layouts/header.php';

// Obtener clientes
try {
    loadClass('Database');
    loadClass('Customer');

    $database = new Database();
    $conn = $database->getConnection();
    $customer = new Customer($conn);
    $customers = $customer->getAll();
} catch (Exception $e) {
    showAlert('Error al cargar clientes: ' . $e->getMessage(), 'danger');
    $customers = [];
}
?>

    <div class="flex flex-between mb-3">
        <h1>Gestión de Clientes</h1>
        <a href="<?php echo BASE_URL; ?>customers/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Cliente
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Clientes</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($customers)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td><strong>#<?php echo $c['customer_number']; ?></strong></td>
                                <td>
                                    <i class="fas fa-user text-primary"></i>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </td>
                                <td>
                                    <i class="fas fa-envelope text-secondary"></i>
                                    <a href="mailto:<?php echo $c['email']; ?>" class="text-primary">
                                        <?php echo htmlspecialchars($c['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>orders/create?customer=<?php echo $c['customer_number']; ?>"
                                       class="btn btn-sm btn-success" title="Crear Pedido">
                                        <i class="fas fa-shopping-cart"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <i class="fas fa-users fa-4x text-secondary mb-2"></i>
                    <h3 class="text-secondary">No hay clientes registrados</h3>
                    <p class="text-secondary mb-3">Comienza agregando tu primer cliente</p>
                    <a href="<?php echo BASE_URL; ?>customers/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primer Cliente
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include 'views/layouts/footer.php'; ?>