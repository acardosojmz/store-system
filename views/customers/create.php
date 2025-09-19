<?php
$pageTitle = "Nuevo Cliente - " . SITE_NAME;
include 'views/layouts/header.php';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        loadClass('Database');
        loadClass('Customer');

        $database = new Database();
        $conn = $database->getConnection();
        $customer = new Customer($conn);

        $data = [
            'name' => sanitize($_POST['name']),
            'email' => sanitize($_POST['email'])
        ];

        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email no válido');
        }

        if ($customer->create($data)) {
            showAlert('Cliente creado exitosamente', 'success');
            redirect('customers');
        } else {
            throw new Exception('Error al crear el cliente');
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'danger');
    }
}
?>

    <div class="flex flex-between mb-3">
        <h1>Nuevo Cliente</h1>
        <a href="<?php echo BASE_URL; ?>customers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información del Cliente</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="form-group">
                    <label for="name" class="form-label">Nombre Completo *</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?php echo $_POST['name'] ?? ''; ?>"
                           placeholder="Nombre completo del cliente" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico *</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo $_POST['email'] ?? ''; ?>"
                           placeholder="cliente@ejemplo.com" required>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Cliente
                    </button>
                    <a href="<?php echo BASE_URL; ?>customers" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validación básica del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!name) {
                e.preventDefault();
                alert('El nombre es obligatorio');
                return;
            }

            if (!email || !email.includes('@')) {
                e.preventDefault();
                alert('Por favor ingresa un email válido');
                return;
            }
        });
    </script>

<?php include 'views/layouts/footer.php'; ?>