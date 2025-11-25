<?php
session_start();
require_once 'config/database.php';

$current_page = 'producto';

// Obtener ID del producto
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    header('Location: index.php');
    exit();
}

// Obtener producto
$producto = getProductById($productId);

if (!$producto) {
    header('Location: index.php');
    exit();
}

// Obtener categoría
$categorias = getAllCategories();
$categoria = null;
foreach ($categorias as $cat) {
    if ($cat['ID_Categoria'] == $producto['ID_Categoria_FK']) {
        $categoria = $cat;
        break;
    }
}

// Obtener productos relacionados (misma categoría)
$productosRelacionados = [];
if ($producto['ID_Categoria_FK']) {
    $conn = getDBConnection();
    if ($conn) {
        try {
            $sql = "SELECT * FROM Productos 
                    WHERE ID_Categoria_FK = :cat_id AND ID_Producto != :prod_id 
                    ORDER BY RAND() LIMIT 3";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['cat_id' => $producto['ID_Categoria_FK'], 'prod_id' => $productId]);
            $productosRelacionados = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
        }
    }
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

$icon = isset($categoria_icons[$producto['ID_Categoria_FK']]) ? $categoria_icons[$producto['ID_Categoria_FK']] : 'bi-box';

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
    <title><?php echo htmlspecialchars($producto['Nombre']); ?> - WigerConstruction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <?php include 'components/mini_cart.php'; ?>

    <div class="container mt-4 mb-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="index.php">Catálogo</a></li>
                <?php if ($categoria): ?>
                <li class="breadcrumb-item"><?php echo htmlspecialchars($categoria['Nombre']); ?></li>
                <?php endif; ?>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($producto['Nombre']); ?></li>
            </ol>
        </nav>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="bi <?php echo $messageType == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?>"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Producto -->
        <div class="row">
            <!-- Imagen -->
            <div class="col-md-5 mb-4">
                <div class="card">
                    <div class="product-image-large-real">
                        <img src="public/products/<?php echo htmlspecialchars($producto['imagen'] ?? 'default-product.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($producto['Nombre']); ?>" 
                             onerror="this.src='public/products/default-product.jpg'">
                    </div>
                </div>
            </div>

            <!-- Información -->
            <div class="col-md-7">
                <div class="mb-3">
                    <?php if ($categoria): ?>
                    <span class="badge bg-success mb-2">
                        <i class="bi <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($categoria['Nombre']); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($producto['Nombre']); ?></h1>
                
                <div class="mb-4">
                    <h2 class="text-success mb-0">$<?php echo number_format($producto['Precio'], 2); ?></h2>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-info-circle"></i> Descripción</h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($producto['Descripcion'])); ?></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-box-seam display-6 text-success"></i>
                                <h5 class="mt-2"><?php echo $producto['Cantidad_Almacen']; ?> disponibles</h5>
                                <p class="text-muted mb-0">En Stock</p>
                            </div>
                        </div>
                    </div>
                    <?php if ($producto['Fabricante']): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-building display-6 text-success"></i>
                                <h5 class="mt-2"><?php echo htmlspecialchars($producto['Fabricante']); ?></h5>
                                <p class="text-muted mb-0">Fabricante</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($producto['Origen']): ?>
                <div class="mb-4">
                    <p><strong><i class="bi bi-geo-alt"></i> Origen:</strong> <?php echo htmlspecialchars($producto['Origen']); ?></p>
                </div>
                <?php endif; ?>

                <!-- Agregar al carrito -->
                <?php if ($producto['Cantidad_Almacen'] > 0): ?>
                <form action="cart_actions.php" method="POST" class="mb-3">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $producto['ID_Producto']; ?>">
                    <input type="hidden" name="return_url" value="producto.php?id=<?php echo $producto['ID_Producto']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" name="cantidad" value="1" min="1" 
                                   max="<?php echo $producto['Cantidad_Almacen']; ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-cart-plus"></i> Agregar al Carrito
                            </button>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                            <small class="text-muted d-block mt-2 text-center">
                                <i class="bi bi-info-circle"></i> <a href="registro.php">Crea una cuenta</a> para finalizar tu compra
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> Producto agotado
                </div>
                <?php endif; ?>

                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Catálogo
                    </a>
                </div>
            </div>
        </div>

        <!-- Productos Relacionados -->
        <?php if (!empty($productosRelacionados)): ?>
        <section class="mt-5">
            <h3 class="mb-4">Productos Relacionados</h3>
            <div class="row g-4">
                <?php foreach ($productosRelacionados as $related): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="product-image-real">
                            <img src="public/products/<?php echo htmlspecialchars($related['imagen'] ?? 'default-product.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($related['Nombre']); ?>" 
                                 onerror="this.src='public/products/default-product.jpg'">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($related['Nombre']); ?></h5>
                            <p class="text-muted small mb-3">
                                <?php echo htmlspecialchars(substr($related['Descripcion'], 0, 60)); ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="text-success mb-0">$<?php echo number_format($related['Precio'], 2); ?></h4>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-box"></i> <?php echo $related['Cantidad_Almacen']; ?>
                                </span>
                            </div>
                            <a href="producto.php?id=<?php echo $related['ID_Producto']; ?>" class="btn btn-outline-success w-100">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
