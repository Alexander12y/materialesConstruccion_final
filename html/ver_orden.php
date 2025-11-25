<?php
session_start();
require_once 'config/database.php';

$current_page = 'perfil';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener ID de la orden
$ordenId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$ordenId) {
    header('Location: perfil_usuario.php');
    exit();
}

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

// Función para determinar la clase del badge según el estado
function getEstadoBadgeClass($estado) {
    $clases = [
        'Procesando' => 'info',
        'En camino' => 'warning',
        'Entregado' => 'success',
        'Completado' => 'success',
        'Cancelado' => 'danger'
    ];
    return $clases[$estado] ?? 'secondary';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Orden #<?php echo str_pad($orden['ID_Orden'], 6, '0', STR_PAD_LEFT); ?> - WigerConstruction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
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
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <?php include 'components/mini_cart.php'; ?>

    <div class="bg-light py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 fw-bold">
                        <i class="bi bi-receipt"></i> Orden #<?php echo str_pad($orden['ID_Orden'], 6, '0', STR_PAD_LEFT); ?>
                    </h1>
                    <p class="lead mb-0">Estado: <span class="badge bg-<?php echo getEstadoBadgeClass($orden['Estado_Orden']); ?> fs-6"><?php echo htmlspecialchars($orden['Estado_Orden']); ?></span></p>
                </div>
                <a href="perfil_usuario.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-lg-8">
                <!-- Información de la orden -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información de la Orden</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Número de Orden</small>
                                <strong>#<?php echo str_pad($orden['ID_Orden'], 6, '0', STR_PAD_LEFT); ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Fecha de Orden</small>
                                <strong><?php echo $fechaFormateada; ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Estado</small>
                                <span class="badge bg-<?php echo getEstadoBadgeClass($orden['Estado_Orden']); ?> fs-6"><?php echo htmlspecialchars($orden['Estado_Orden']); ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Total</small>
                                <strong class="h5 text-success">$<?php echo number_format($orden['Total_Orden'], 2); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dirección de envío -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-truck"></i> Dirección de Envío</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($orden['Direccion_Envio_Snapshot'])); ?></p>
                    </div>
                </div>

                <!-- Productos ordenados -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-bag-check"></i> Productos (<?php echo count($detallesOrden); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($detallesOrden as $detalle): ?>
                        <div class="order-detail-item">
                            <img src="public/products/<?php echo htmlspecialchars($detalle['imagen'] ?? 'default-product.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($detalle['Nombre']); ?>"
                                 onerror="this.src='public/products/default-product.jpg'">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($detalle['Nombre']); ?></h6>
                                <small class="text-muted">Cantidad: <?php echo $detalle['Cantidad']; ?></small><br>
                                <small class="text-muted">Precio unitario: $<?php echo number_format($detalle['Precio_Unitario_Snapshot'], 2); ?></small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success">$<?php echo number_format($detalle['Subtotal_Linea'], 2); ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Resumen lateral -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Resumen</h5>
                    </div>
                    <div class="card-body">
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
                    <div class="card-footer">
                        <button class="btn btn-outline-primary w-100 mb-2" onclick="window.print()">
                            <i class="bi bi-printer"></i> Imprimir Orden
                        </button>
                        <a href="perfil_usuario.php" class="btn btn-success w-100">
                            <i class="bi bi-arrow-left"></i> Volver a Mis Pedidos
                        </a>
                    </div>
                </div>

                <!-- Timeline de estado -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Estado de la Orden</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Orden Recibida</span>
                            </div>
                            <div class="timeline-item <?php echo in_array($orden['Estado_Orden'], ['En camino', 'Entregado', 'Completado']) ? 'active' : ''; ?>">
                                <i class="bi bi-<?php echo in_array($orden['Estado_Orden'], ['En camino', 'Entregado', 'Completado']) ? 'check-circle-fill text-success' : 'circle'; ?>"></i>
                                <span>Procesando</span>
                            </div>
                            <div class="timeline-item <?php echo in_array($orden['Estado_Orden'], ['En camino', 'Entregado', 'Completado']) ? 'active' : ''; ?>">
                                <i class="bi bi-<?php echo in_array($orden['Estado_Orden'], ['En camino', 'Entregado', 'Completado']) ? 'check-circle-fill text-success' : 'circle'; ?>"></i>
                                <span>En Camino</span>
                            </div>
                            <div class="timeline-item <?php echo in_array($orden['Estado_Orden'], ['Entregado', 'Completado']) ? 'active' : ''; ?>">
                                <i class="bi bi-<?php echo in_array($orden['Estado_Orden'], ['Entregado', 'Completado']) ? 'check-circle-fill text-success' : 'circle'; ?>"></i>
                                <span>Entregado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 20px;
            width: 2px;
            height: calc(100% - 10px);
            background: #dee2e6;
        }
        .timeline-item i {
            position: absolute;
            left: -30px;
            top: 0;
            font-size: 1.2rem;
        }
        .timeline-item.active::before {
            background: #198754;
        }
    </style>
</body>
</html>
