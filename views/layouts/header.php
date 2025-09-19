<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>" class="logo">
            <i class="fas fa-store"></i> <?php echo SITE_NAME; ?>
        </a>
        <?php include 'nav.php'; ?>
    </div>
</header>

<main class="main-container">
    <?php
    $alert = getAlert();
    if ($alert):
        ?>
        <div class="alert alert-<?php echo $alert['type']; ?>">
            <?php echo $alert['message']; ?>
        </div>
    <?php endif; ?>
