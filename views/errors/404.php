<?php
$pageTitle = "Página no encontrada - " . SITE_NAME;
include 'views/layouts/header.php';
?>

    <div class="text-center">
        <div class="card" style="max-width: 600px; margin: 4rem auto;">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-5x text-warning mb-3"></i>
                <h1 class="text-primary mb-3">404 - Página no encontrada</h1>
                <p class="text-secondary mb-4">
                    La página que estás buscando no existe o ha sido movida.
                </p>
                <div class="flex gap-2" style="justify-content: center;">
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                        <i class="fas fa-home"></i> Ir al Inicio
                    </a>
                    <button onclick="history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php include 'views/layouts/footer.php'; ?>