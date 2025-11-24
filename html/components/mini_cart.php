<?php
// Mini carrito - Offcanvas
$isGuestCart = !isset($_SESSION['user_id']);

if ($isGuestCart) {
    $miniCartItems = getGuestCartItems();
    $miniCartTotal = getGuestCartTotal();
    $miniCartCount = getGuestCartCount();
} else {
    $miniCartItems = getCartItems($_SESSION['user_id']);
    $miniCartTotal = getCartTotal($_SESSION['user_id']);
    $miniCartCount = getCartCount($_SESSION['user_id']);
}

$categoria_icons = [
    1 => 'bi-bricks',
    2 => 'bi-tools',
    3 => 'bi-paint-bucket',
    4 => 'bi-droplet',
    5 => 'bi-lightning-charge',
    6 => 'bi-hammer'
];
?>

<!-- Offcanvas Mini Carrito -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="miniCartOffcanvas" aria-labelledby="miniCartLabel">
    <div class="offcanvas-header bg-success text-white">
        <h5 class="offcanvas-title" id="miniCartLabel">
            <i class="bi bi-cart3"></i> Mi Carrito
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?php if (empty($miniCartItems)): ?>
            <!-- Carrito vacío -->
            <div class="text-center p-5">
                <i class="bi bi-cart-x display-1 text-muted"></i>
                <p class="mt-3 text-muted">Tu carrito está vacío</p>
                <a href="<?php echo $base_path; ?>index.php" class="btn btn-success" data-bs-dismiss="offcanvas">
                    <i class="bi bi-grid-3x3-gap"></i> Ver Catálogo
                </a>
            </div>
        <?php else: ?>
            <?php if ($isGuestCart): ?>
            <!-- Mensaje para invitados -->
            <div class="alert alert-info m-3 mb-0">
                <i class="bi bi-info-circle"></i> <strong>Compra como invitado</strong><br>
                <small><a href="<?php echo $base_path; ?>registro.php" class="alert-link">Crea una cuenta</a> o <a href="<?php echo $base_path; ?>login.php" class="alert-link">inicia sesión</a> para finalizar tu compra.</small>
            </div>
            <?php endif; ?>
                <!-- Items del carrito -->
                <div class="mini-cart-items">
                    <?php foreach ($miniCartItems as $item): 
                        $icon = isset($categoria_icons[$item['ID_Categoria_FK'] ?? 0]) ? $categoria_icons[$item['ID_Categoria_FK']] : 'bi-box';
                        $subtotal = $item['Precio'] * $item['Cantidad'];
                    ?>
                    <div class="mini-cart-item p-3 border-bottom">
                        <div class="d-flex gap-3">
                            <div class="mini-cart-item-image flex-shrink-0">
                                <img src="<?php echo $base_path; ?>public/products/<?php echo htmlspecialchars($item['imagen'] ?? 'default-product.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['Nombre']); ?>" 
                                     onerror="this.src='<?php echo $base_path; ?>public/products/default-product.jpg'">
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <a href="<?php echo $base_path; ?>producto.php?id=<?php echo $isGuestCart ? $item['ID_Producto'] : $item['ID_Producto_FK']; ?>" 
                                       class="text-decoration-none text-dark"
                                       data-bs-dismiss="offcanvas">
                                        <?php echo htmlspecialchars($item['Nombre']); ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <?php echo $item['Cantidad']; ?> x $<?php echo number_format($item['Precio'], 2); ?>
                                </small>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <strong class="text-success">$<?php echo number_format($subtotal, 2); ?></strong>
                                    <form action="<?php echo $base_path; ?>cart_actions.php" method="POST" 
                                          style="display:inline;" 
                                          onsubmit="return confirm('¿Eliminar este producto?');">
                                        <input type="hidden" name="action" value="remove">
                                        <?php if ($isGuestCart): ?>
                                        <input type="hidden" name="product_id" value="<?php echo $item['ID_Producto']; ?>">
                                        <?php else: ?>
                                        <input type="hidden" name="cart_id" value="<?php echo $item['ID_Carrito']; ?>">
                                        <?php endif; ?>
                                        <input type="hidden" name="return_url" value="<?php echo $_SERVER['PHP_SELF']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Footer con total y acciones -->
                <div class="p-3 border-top bg-light mt-auto">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total:</span>
                        <span class="h5 text-success mb-0">$<?php echo number_format($miniCartTotal, 2); ?></span>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="<?php echo $base_path; ?>carrito.php" class="btn btn-success">
                            <i class="bi bi-cart-check"></i> Ver Carrito Completo
                        </a>
                        <a href="<?php echo $base_path; ?>index.php" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                            <i class="bi bi-arrow-left"></i> Continuar Comprando
                        </a>
                    </div>
                </div>
        <?php endif; ?>
    </div>
</div>
