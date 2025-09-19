<nav class="nav">
    <ul>
        <li><a href="<?php echo BASE_URL; ?>dashboard" class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
        <li><a href="<?php echo BASE_URL; ?>products" class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'products') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Productos
            </a></li>
        <li><a href="<?php echo BASE_URL; ?>customers" class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'customers') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Clientes
            </a></li>
        <li><a href="<?php echo BASE_URL; ?>orders" class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'orders') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Pedidos
            </a></li>
    </ul>
</nav>