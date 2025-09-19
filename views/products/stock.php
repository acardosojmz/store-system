<?php
$pageTitle = "Consultar Stock - " . SITE_NAME;
include 'views/layouts/header.php';

$product_info = null;
$product_code = $_GET['code'] ?? '';

// Consultar stock si se proporciona código
if ($product_code) {
    try {
        loadClass('Database');
        loadClass('Product');

        $database = new Database();
        $conn = $database->getConnection();
        $product = new Product($conn);
        $product_info = $product->getStock($product_code);

        if (!$product_info) {
            showAlert('Producto no encontrado', 'warning');
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'danger');
    }
}

// Procesar formulario de búsqueda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_code'])) {
    $search_code = sanitize($_POST['search_code']);
    redirect('products/stock?code=' . $search_code);
}
?>

    <div class="flex flex-between mb-3">
        <h1>Consultar Stock</h1>
        <a href="<?php echo BASE_URL; ?>products" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Productos
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Buscar Producto por Código</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="mb-4">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="search_code" class="form-label">Código del Producto</label>
                        <input type="number" class="form-control" id="search_code" name="search_code"
                               value="<?php echo htmlspecialchars($product_code); ?>"
                               placeholder="Ingresa el código del producto" required>
                    </div>
                    <div class="form-group" style="flex: 1; display: flex; align-items: end;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>

            <?php if ($product_info): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Producto encontrado:</strong> <?php echo htmlspecialchars($product_info['product']); ?>
                </div>

                <div class="grid grid-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-box fa-3x text-primary mb-2"></i>
                            <h3>Código: <?php echo $product_code; ?></h3>
                            <p class="text-secondary">Producto</p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-cubes fa-3x <?php echo ($product_info['stock'] < 10) ? 'text-danger' : 'text-success'; ?> mb-2"></i>
                            <h3 class="<?php echo ($product_info['stock'] < 10) ? 'text-danger' : 'text-success'; ?>">
                                <?php echo $product_info['stock']; ?>
                            </h3>
                            <p class="text-secondary">Stock Disponible</p>
                            <?php if ($product_info['stock'] < 10): ?>
                                <small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-dollar-sign fa-3x text-success mb-2"></i>
                            <h3 class="text-success"><?php echo formatCurrency($product_info['unit_price']); ?></h3>
                            <p class="text-secondary">Precio Unitario</p>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="mb-3">Detalles del Producto</h5>
                        <table class="table">
                            <tbody>
                            <tr>
                                <td><strong>Código:</strong></td>
                                <td>#<?php echo $product_code; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nombre:</strong></td>
                                <td><?php echo htmlspecialchars($product_info['product']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Stock Actual:</strong></td>
                                <td>
                                <span class="<?php echo ($product_info['stock'] < 10) ? 'text-danger fw-bold' : 'text-success'; ?>">
                                    <?php echo $product_info['stock']; ?> unidades
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Precio Unitario:</strong></td>
                                <td><?php echo formatCurrency($product_info['unit_price']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Valor Total en Stock:</strong></td>
                                <td class="fw-bold text-primary">
                                    <?php echo formatCurrency($product_info['stock'] * $product_info['unit_price']); ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <?php if ($product_info['stock'] > 10): ?>
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Stock Normal</span>
                                    <?php elseif ($product_info['stock'] > 0): ?>
                                        <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</span>
                                    <?php else: ?>
                                        <span class="text-danger"><i class="fas fa-times-circle"></i> Sin Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($product_code): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Producto no encontrado.</strong> Verifica que el código sea correcto.
                </div>
            <?php endif; ?>

            <?php if (!$product_code): ?>
                <div class="text-center">
                    <i class="fas fa-search fa-4x text-secondary mb-3"></i>
                    <h3 class="text-secondary">Ingresa un código de producto</h3>
                    <p class="text-secondary">Utiliza el formulario superior para buscar información de stock</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include 'views/layouts/footer.php'; ?>