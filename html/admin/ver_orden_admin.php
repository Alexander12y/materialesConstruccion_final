<?php
session_start();
require_once 'check_admin.php';
require_once '../config/database.php';

$current_page = 'admin';

// Obtener ID de la orden
$ordenId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$ordenId) {
    header('Location: historial_compras.php');
    exit();
}

// Obtener información de la orden
$orden = getOrderById($ordenId);
$detallesOrden = getOrderDetails($ordenId);

if (!$orden) {
    header('Location: historial_compras.php');
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

// Procesar cambio de estado si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_estado'])) {
    $nuevoEstado = $_POST['nuevo_estado'];
    if (updateOrderStatus($ordenId, $nuevoEstado)) {
        $_SESSION['admin_message'] = 'Estado actualizado correctamente';
        $_SESSION['admin_message_type'] = 'success';
        header('Location: ver_orden_admin.php?id=' . $ordenId);
        exit();
    }
}

// Mensajes
$message = isset($_SESSION['admin_message']) ? $_SESSION['admin_message'] : '';
$messageType = isset($_SESSION['admin_message_type']) ? $_SESSION['admin_message_type'] : '';
unset($_SESSION['admin_message'], $_SESSION['admin_message_type']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden #<?php echo str_pad($orden['ID_Orden'], 6, '0', STR_PAD_LEFT); ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
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
    <?php include '../components/navbar.php'; ?>

    <div class="bg-success text-white py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 fw-bold mb-0">
                        <i class="bi bi-receipt"></i> Orden #<?php echo str_pad($orden['ID_Orden'], 6, '0', STR_PAD_LEFT); ?>
                    </h1>
                    <p class="lead mb-0">Detalles completos de la orden</p>
                </div>
                <div>
                    <a href="historial_compras.php" class="btn btn-light me-2">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <a href="index.php" class="btn btn-outline-light">
                        <i class="bi bi-grid-3x3-gap"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4 mb-5">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $messageType == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <!-- Información del cliente -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-circle"></i> Información del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Nombre</small>
                                <strong><?php echo htmlspecialchars($orden['Nombre_Usuario']); ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Correo Electrónico</small>
                                <strong><?php echo htmlspecialchars($orden['Correo_Electronico']); ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">ID Usuario</small>
                                <strong>#<?php echo $orden['ID_Usuario_FK']; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de la orden -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
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
                                <small class="text-muted d-block">Total</small>
                                <strong class="h5 text-success">$<?php echo number_format($orden['Total_Orden'], 2); ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Estado Actual</small>
                                <span class="badge bg-<?php echo getEstadoBadgeClass($orden['Estado_Orden']); ?> fs-6">
                                    <?php echo htmlspecialchars($orden['Estado_Orden']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dirección de envío -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-truck"></i> Dirección de Envío</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($orden['Direccion_Envio_Snapshot'])); ?></p>
                    </div>
                </div>

                <!-- Productos ordenados -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-bag-check"></i> Productos Ordenados (<?php echo count($detallesOrden); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($detallesOrden as $detalle): ?>
                        <div class="order-detail-item">
                            <img src="../public/products/<?php echo htmlspecialchars($detalle['imagen'] ?? 'default-product.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($detalle['Nombre']); ?>"
                                 onerror="this.src='../public/products/default-product.jpg'">
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
            </div>

            <!-- Panel de control lateral -->
            <div class="col-lg-4">
                <!-- Actualizar estado -->
                <div class="card shadow-sm mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Control de Estado</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" onsubmit="return confirm('¿Confirmas el cambio de estado?');">
                            <div class="mb-3">
                                <label for="nuevo_estado" class="form-label">Estado de la Orden</label>
                                <select class="form-select" id="nuevo_estado" name="nuevo_estado" required>
                                    <option value="Procesando" <?php echo $orden['Estado_Orden'] == 'Procesando' ? 'selected' : ''; ?>>Procesando</option>
                                    <option value="En camino" <?php echo $orden['Estado_Orden'] == 'En camino' ? 'selected' : ''; ?>>En camino</option>
                                    <option value="Entregado" <?php echo $orden['Estado_Orden'] == 'Entregado' ? 'selected' : ''; ?>>Entregado</option>
                                    <option value="Completado" <?php echo $orden['Estado_Orden'] == 'Completado' ? 'selected' : ''; ?>>Completado</option>
                                    <option value="Cancelado" <?php echo $orden['Estado_Orden'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Actualizar Estado
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="bi bi-lightning"></i> Acciones Rápidas</h6>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Imprimir Orden
                        </button>
                        <a href="historial_compras.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al Historial
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
