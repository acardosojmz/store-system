<?php
$pageTitle = "Nuevo Pedido - " . SITE_NAME;
include 'views/layouts/header.php';

// Obtener datos necesarios
try {
    loadClass('Database');
    loadClass('Customer');
    loadClass('Product');

    $database = new Database();
    $conn = $database->getConnection();
    $customer = new Customer($conn);
    $product = new Product($conn);

    $customers = $customer->getAll();
    $products = $product->getAll();
    $selected_customer = $_GET['customer'] ?? '';
} catch (Exception $e) {
    showAlert('Error al cargar datos: ' . $e->getMessage(), 'danger');
    $customers = [];
    $products = [];
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        loadClass('Order');

        $order = new Order($conn);
        $customer_number = (int)$_POST['customer_number'];
        $required_date = $_POST['required_date'] ?: null;

        // Procesar items del pedido
        $order_items = [];
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $index => $product_code) {
                if (!empty($product_code) && !empty($_POST['quantities'][$index])) {
                    $product_info = $product->getById($product_code);
                    if ($product_info) {
                        $order_items[] = [
                                'product_code' => (int)$product_code,
                                'quantity' => (int)$_POST['quantities'][$index],
                                'unit_price' => (float)$product_info['unit_price']
                        ];
                    }
                }
            }
        }

        if (empty($order_items)) {
            throw new Exception('Debe agregar al menos un producto al pedido');
        }

        // <<< AQUI es donde se llama a create() SOLO en POST
        $order_id = $order->create($customer_number, $required_date, $order_items);

        showAlert('Pedido creado exitosamente. Orden #' . $order_id, 'success');
        redirect('orders/details/' . $order_id);

    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'danger');
    }
}

?>

    <div class="flex flex-between mb-3">
        <h1>Nuevo Pedido</h1>
        <a href="<?php echo BASE_URL; ?>orders" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <form method="POST" id="orderForm">
        <div class="grid grid-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Pedido</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="customer_number" class="form-label">Cliente *</label>
                        <select class="form-control form-select" id="customer_number" name="customer_number" required>
                            <option value="">Seleccionar cliente</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?php echo $c['customer_number']; ?>"
                                    <?php echo ($selected_customer == $c['customer_number']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?> - <?php echo htmlspecialchars($c['email']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="required_date" class="form-label">Fecha Requerida</label>
                        <input type="date" class="form-control" id="required_date" name="required_date"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> Al crear el pedido, el stock de los productos se descontará automáticamente.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header flex flex-between">
                    <h3 class="card-title">Resumen del Pedido</h3>
                    <span id="totalAmount" class="text-primary fw-bold">$0.00</span>
                </div>
                <div class="card-body">
                    <div id="orderSummary">
                        <p class="text-secondary text-center">Agrega productos para ver el resumen</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex flex-between">
                <h3 class="card-title">Productos del Pedido</h3>
                <button type="button" class="btn btn-sm btn-primary" onclick="addProductRow()">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            </div>
            <div class="card-body">
                <div id="productsContainer">
                    <div class="product-row">
                        <div class="form-row">
                            <div class="form-group" style="flex: 3;">
                                <label class="form-label">Producto</label>
                                <select class="form-control form-select product-select" name="products[]" onchange="updateProductInfo(this)">
                                    <option value="">Seleccionar producto</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['product_code']; ?>"
                                                data-price="<?php echo $p['unit_price']; ?>"
                                                data-stock="<?php echo $p['stock']; ?>">
                                            <?php echo htmlspecialchars($p['product']); ?> - Stock: <?php echo $p['stock']; ?> - <?php echo formatCurrency($p['unit_price']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control quantity-input" name="quantities[]"
                                       min="1" placeholder="1" onchange="updateTotal()">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label class="form-label">Precio</label>
                                <input type="text" class="form-control price-display" readonly placeholder="$0.00">
                            </div>
                            <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: end;">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeProductRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save"></i> Crear Pedido
            </button>
            <a href="<?php echo BASE_URL; ?>orders" class="btn btn-secondary btn-lg">
                Cancelar
            </a>
        </div>
    </form>

    <script src="<?php echo BASE_URL; ?>public/js/orders.js"></script>

<?php include 'views/layouts/footer.php'; ?>