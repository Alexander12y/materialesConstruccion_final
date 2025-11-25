<?php
session_start();
require_once 'config/database.php';

$current_page = 'perfil';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar que venga de una orden exitosa
$ordenId = isset($_GET['orden']) ? intval($_GET['orden']) : 0;
$orderSuccess = isset($_SESSION['order_success']) && $_SESSION['order_success'];

if (!$ordenId || !$orderSuccess) {
    header('Location: index.php');
    exit();
}

// Limpiar flag de éxito
unset($_SESSION['order_success']);

// Obtener información de la orden
$orden = getOrderById($ordenId);
$detallesOrden = getOrderDetails($ordenId);

// Verificar que la orden pertenece al usuario actual
if (!$orden || $orden['ID_Usuario_FK'] != $_SESSION['user_id']) {
    header('Location: perfil_usuario.php');
    exit();
}

// Formatear fecha
$fechaOrden = new DateTime($orden['Fecha_Orden']);
$fechaFormateada = $fechaOrden->format('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden Confirmada - WigerConstruction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        .success-header {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
        }
        .success-icon {
            font-size: 5rem;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .order-detail-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .order-detail-item:last-child {
            border-bottom: none;
        }
        .order-detail-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <?php include 'components/mini_cart.php'; ?>

    <div class="success-header text-white py-5">
        <div class="container text-center">
            <i class="bi bi-check-circle-fill success-icon"></i>
            <h1 class="display-4 fw-bold mt-3">¡Orden Confirmada!</h1>
            <p class="lead">Tu compra ha sido procesada exitosamente</p>
        </div>
    </div>

    <div class="container mt-5 mb-5">
        <!-- Información de la orden -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Resumen -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center py-4">
                        <h5 class="mb-3">Gracias por tu compra, <?php echo htmlspecialchars($orden['Nombre_Usuario']); ?>!</h5>
                        <p class="text-muted mb-4">Hemos recibido tu orden y la estamos procesando. Te enviaremos un correo de confirmación a <strong><?php echo htmlspecialchars($orden['Correo_Electronico']); ?></strong></p>
                        
                        <div class="row text-start">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Número de Orden</small>
                                <strong class="h5">#<?php echo str_pad($orden['ID_Orden'], 6, '0', STR_PAD_LEFT); ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Fecha</small>
                                <strong><?php echo $fechaFormateada; ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Total Pagado</small>
                                <strong class="h5 text-success">$<?php echo number_format($orden['Total_Orden'], 2); ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Estado</small>
                                <span class="badge bg-info fs-6"><?php echo htmlspecialchars($orden['Estado_Orden']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dirección de envío -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-truck"></i> Dirección de Envío</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($orden['Direccion_Envio_Snapshot'])); ?></p>
                    </div>
                </div>

                <!-- Detalles de productos -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-bag-check"></i> Productos Ordenados</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($detallesOrden as $detalle): ?>
                        <div class="order-detail-item">
                            <img src="public/products/<?php echo htmlspecialchars($detalle['imagen'] ?? 'default-product.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($detalle['Nombre']); ?>"
                                 onerror="this.src='public/products/default-product.jpg'">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($detalle['Nombre']); ?></h6>
                                <small class="text-muted">Cantidad: <?php echo $detalle['Cantidad']; ?> x $<?php echo number_format($detalle['Precio_Unitario_Snapshot'], 2); ?></small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success">$<?php echo number_format($detalle['Subtotal_Linea'], 2); ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="border-top mt-3 pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>$<?php echo number_format($orden['Total_Orden'] / 1.16, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>IVA (16%):</span>
                                <strong>$<?php echo number_format($orden['Total_Orden'] - ($orden['Total_Orden'] / 1.16), 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Envío:</span>
                                <strong class="text-success">GRATIS</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="h5">Total:</span>
                                <span class="h4 text-success">$<?php echo number_format($orden['Total_Orden'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> Información Importante</h6>
                    <ul class="mb-0">
                        <li>Recibirás un correo de confirmación en los próximos minutos</li>
                        <li>El tiempo estimado de entrega es de 3-5 días hábiles</li>
                        <li>Puedes revisar el estado de tu pedido en tu perfil</li>
                        <li>Si tienes alguna duda, contáctanos a través de nuestros canales de atención</li>
                    </ul>
                </div>

                <!-- Botones de acción -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-4">
                    <a href="perfil_usuario.php" class="btn btn-success btn-lg">
                        <i class="bi bi-person-circle"></i> Ver Mis Pedidos
                    </a>
                    <a href="index.php" class="btn btn-outline-success btn-lg">
                        <i class="bi bi-house"></i> Volver al Inicio
                    </a>
                </div>

                <!-- Compartir en redes -->
                <div class="text-center">
                    <p class="text-muted">¡Comparte tu compra!</p>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
