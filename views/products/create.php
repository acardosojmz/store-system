<?php
$pageTitle = "Nuevo Producto - " . SITE_NAME;
include 'views/layouts/header.php';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        loadClass('Database');
        loadClass('Product');

        $database = new Database();
        $conn = $database->getConnection();
        $product = new Product($conn);

        $data = [
            'product' => sanitize($_POST['product']),
            'description' => sanitize($_POST['description']),
            'stock' => (int)$_POST['stock'],
            'unit_price' => (float)$_POST['unit_price']
        ];

        if ($product->create($data)) {
            showAlert('Producto creado exitosamente', 'success');
            redirect('products');
        } else {
            throw new Exception('Error al crear el producto');
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'danger');
    }
}
?>

    <div class="flex flex-between mb-3">
        <h1>Nuevo Producto</h1>
        <a href="<?php echo BASE_URL; ?>products" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información del Producto</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="product" class="form-label">Nombre del Producto *</label>
                        <input type="text" class="form-control" id="product" name="product"
                               value="<?php echo $_POST['product'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="unit_price" class="form-label">Precio Unitario *</label>
                        <input type="number" class="form-control" id="unit_price" name="unit_price"
                               step="0.01" min="0" value="<?php echo $_POST['unit_price'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="stock" class="form-label">Stock Inicial *</label>
                        <input type="number" class="form-control" id="stock" name="stock"
                               min="0" value="<?php echo $_POST['stock'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control" id="description" name="description"
                              rows="3" placeholder="Descripción opcional del producto"><?php echo $_POST['description'] ?? ''; ?></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Producto
                    </button>
                    <a href="<?php echo BASE_URL; ?>products" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validación básica del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const product = document.getElementById('product').value.trim();
            const price = parseFloat(document.getElementById('unit_price').value);
            const stock = parseInt(document.getElementById('stock').value);

            if (!product) {
                e.preventDefault();
                alert('El nombre del producto es obligatorio');
                return;
            }

            if (price < 0) {
                e.preventDefault();
                alert('El precio debe ser mayor o igual a 0');
                return;
            }

            if (stock < 0) {
                e.preventDefault();
                alert('El stock debe ser mayor o igual a 0');
                return;
            }
        });
    </script>

<?php include 'views/layouts/footer.php'; ?>