<?php
$pageTitle = "Productos - " . SITE_NAME;
include 'views/layouts/header.php';

// Obtener productos
try {
    loadClass('Database');
    loadClass('Product');

    $database = new Database();
    $conn = $database->getConnection();
    $product = new Product($conn);
    $products = $product->getAll();
} catch (Exception $e) {
    showAlert('Error al cargar productos: ' . $e->getMessage(), 'danger');
    $products = [];
}
?>

    <div class="flex flex-between mb-3">
        <h1>Gestión de Productos</h1>
        <a href="<?php echo BASE_URL; ?>products/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Producto
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Productos</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($products)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Stock</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><strong>#<?php echo $p['product_code']; ?></strong></td>
                                <td><?php echo htmlspecialchars($p['product']); ?></td>
                                <td><?php echo htmlspecialchars($p['description'] ?? 'Sin descripción'); ?></td>
                                <td>
                            <span class="<?php echo ($p['stock'] < 10) ? 'text-danger fw-bold' : 'text-success'; ?>">
                                <?php echo $p['stock']; ?>
                            </span>
                                </td>
                                <td><?php echo formatCurrency($p['unit_price']); ?></td>
                                <td>
                                    <?php if ($p['stock'] > 0): ?>
                                        <span class="text-success">
                                    <i class="fas fa-check-circle"></i> Disponible
                                </span>
                                    <?php else: ?>
                                        <span class="text-danger">
                                    <i class="fas fa-times-circle"></i> Sin Stock
                                </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex gap-1">
                                        <button class="btn btn-sm btn-info" onclick="viewStock(<?php echo $p['product_code']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <i class="fas fa-box fa-4x text-secondary mb-2"></i>
                    <h3 class="text-secondary">No hay productos registrados</h3>
                    <p class="text-secondary mb-3">Comienza agregando tu primer producto</p>
                    <a href="<?php echo BASE_URL; ?>products/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primer Producto
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewStock(productCode) {
            window.location.href = '<?php echo BASE_URL; ?>products/stock?code=' + productCode;
        }
    </script>

<?php include 'views/layouts/footer.php'; ?>