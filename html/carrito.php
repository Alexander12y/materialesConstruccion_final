<?php
session_start();
require_once 'config/database.php';

$current_page = 'carrito';
$isGuest = !isset($_SESSION['user_id']);

// Obtener items del carrito según el tipo de usuario
if ($isGuest) {
    $cartItems = getGuestCartItems();
    $cartTotal = getGuestCartTotal();
} else {
    $cartItems = getCartItems($_SESSION['user_id']);
    $cartTotal = getCartTotal($_SESSION['user_id']);
}

// Iconos para categorías
$categoria_icons = [
    1 => 'bi-bricks',
    2 => 'bi-tools',
    3 => 'bi-paint-bucket',
    4 => 'bi-droplet',
    5 => 'bi-lightning-charge',
    6 => 'bi-hammer'
];

// Mensajes
$message = isset($_SESSION['cart_message']) ? $_SESSION['cart_message'] : '';
$messageType = isset($_SESSION['cart_message_type']) ? $_SESSION['cart_message_type'] : '';
unset($_SESSION['cart_message'], $_SESSION['cart_message_type']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - WigerConstruction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <?php include 'components/mini_cart.php'; ?>

    <div class="bg-success text-white py-4">
        <div class="container">
            <h1 class="display-5 fw-bold mb-0">
                <i class="bi bi-cart3"></i> Mi Carrito de Compras
            </h1>
            <p class="lead mb-0">Revisa los productos que deseas comprar</p>
        </div>
    </div>

    <div class="container mt-4 mb-5">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="bi <?php echo $messageType == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?>"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
        <!-- Carrito vacío -->
        <div class="row">
            <div class="col-md-8 mx-auto text-center py-5">
                <i class="bi bi-cart-x display-1 text-muted"></i>
                <h3 class="mt-4">Tu carrito está vacío</h3>
                <p class="text-muted">Agrega productos desde nuestro catálogo</p>
                <a href="index.php" class="btn btn-success btn-lg mt-3">
                    <i class="bi bi-grid-3x3-gap"></i> Ver Catálogo
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Carrito con productos -->
        <?php if ($isGuest): ?>
        <div class="alert alert-info">
            <h5><i class="bi bi-info-circle"></i> Comprando como invitado</h5>
            <p class="mb-0">Puedes continuar con tu compra sin registrarte. <a href="registro.php" class="alert-link">Crea una cuenta</a> o <a href="login.php" class="alert-link">inicia sesión</a> para guardar tu carrito y acceder a más beneficios.</p>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Lista de productos -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-bag-check"></i> Productos en tu Carrito (<?php echo count($cartItems); ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($cartItems as $item): 
                            $icon = isset($categoria_icons[$item['ID_Categoria_FK'] ?? 0]) ? $categoria_icons[$item['ID_Categoria_FK']] : 'bi-box';
                            $subtotal = $item['Precio'] * $item['Cantidad'];
                        ?>
                        <div class="cart-item p-3 border-bottom">
                            <div class="row align-items-center">
                                <!-- Imagen -->
                                <div class="col-md-2 col-3 mb-2 mb-md-0">
                                    <div class="cart-item-image-real">
                                        <img src="public/products/<?php echo htmlspecialchars($item['imagen'] ?? 'default-product.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['Nombre']); ?>" 
                                             onerror="this.src='public/products/default-product.jpg'">
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="col-md-4 col-9 mb-2 mb-md-0">
                                    <h6 class="mb-1">
                                        <a href="producto.php?id=<?php echo $isGuest ? $item['ID_Producto'] : $item['ID_Producto_FK']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($item['Nombre']); ?>
                                        </a>
                                    </h6>
                                    <?php if (isset($item['Categoria_Nombre'])): ?>
                                    <small class="text-muted">
                                        <i class="bi <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($item['Categoria_Nombre']); ?>
                                    </small>
                                    <?php endif; ?>
                                </div>

                                <!-- Precio unitario -->
                                <div class="col-4 col-md-2 mb-2 mb-md-0">
                                    <small class="text-muted d-block">Precio</small>
                                    <strong class="text-success">$<?php echo number_format($item['Precio'], 2); ?></strong>
                                </div>

                                <!-- Cantidad -->
                                <div class="col-4 col-md-2 mb-2 mb-md-0">
                                    <form action="cart_actions.php" method="POST" class="d-flex align-items-center" onsubmit="return confirm('¿Actualizar cantidad?');">
                                        <input type="hidden" name="action" value="update">
                                        <?php if ($isGuest): ?>
                                        <input type="hidden" name="product_id" value="<?php echo $item['ID_Producto']; ?>">
                                        <?php else: ?>
                                        <input type="hidden" name="cart_id" value="<?php echo $item['ID_Carrito']; ?>">
                                        <?php endif; ?>
                                        <input type="hidden" name="return_url" value="carrito.php">
                                        <input type="number" class="form-control form-control-sm" name="cantidad" 
                                               value="<?php echo $item['Cantidad']; ?>" min="1" 
                                               max="<?php echo $item['Cantidad_Almacen']; ?>"
                                               onchange="this.form.submit()">
                                    </form>
                                </div>

                                <!-- Subtotal y eliminar -->
                                <div class="col-4 col-md-2 text-end">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Subtotal</small>
                                        <strong class="text-success">$<?php echo number_format($subtotal, 2); ?></strong>
                                    </div>
                                    <form action="cart_actions.php" method="POST" style="display:inline;" 
                                          onsubmit="return confirm('¿Eliminar este producto?');">
                                        <input type="hidden" name="action" value="remove">
                                        <?php if ($isGuest): ?>
                                        <input type="hidden" name="product_id" value="<?php echo $item['ID_Producto']; ?>">
                                        <?php else: ?>
                                        <input type="hidden" name="cart_id" value="<?php echo $item['ID_Carrito']; ?>">
                                        <?php endif; ?>
                                        <input type="hidden" name="return_url" value="carrito.php">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Botón vaciar carrito -->
                <div class="mt-3">
                    <form action="cart_actions.php" method="POST" style="display:inline;" 
                          onsubmit="return confirm('¿Estás seguro de vaciar el carrito?');">
                        <input type="hidden" name="action" value="clear">
                        <input type="hidden" name="return_url" value="carrito.php">
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash"></i> Vaciar Carrito
                        </button>
                    </form>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Continuar Comprando
                    </a>
                </div>
            </div>

            <!-- Resumen -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Resumen del Pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Productos:</span>
                            <strong><?php echo count($cartItems); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items totales:</span>
                            <strong><?php echo array_sum(array_column($cartItems, 'Cantidad')); ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Total:</span>
                            <span class="h4 text-success mb-0">$<?php echo number_format($cartTotal, 2); ?></span>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg" disabled>
                                <i class="bi bi-credit-card"></i> Proceder al Pago
                            </button>
                            <small class="text-muted text-center">
                                <i class="bi bi-info-circle"></i> Funcionalidad de pago próximamente
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Compra 100% segura
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
