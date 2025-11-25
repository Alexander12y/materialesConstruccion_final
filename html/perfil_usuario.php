<?php
session_start();
require_once 'config/database.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario desde la base de datos
$usuario = getUserById($_SESSION['user_id']);

if (!$usuario) {
    // Si no se encuentra el usuario, cerrar sesión y redirigir
    session_destroy();
    header('Location: login.php');
    exit();
}

// Formatear fecha de nacimiento para mejor presentación
$fecha_nacimiento_formateada = 'No especificada';
if ($usuario['Fecha_Nacimiento']) {
    $fecha_obj = new DateTime($usuario['Fecha_Nacimiento']);
    $fecha_nacimiento_formateada = $fecha_obj->format('d/m/Y');
}

// Obtener pedidos reales del usuario desde la base de datos
$pedidos = getUserOrders($_SESSION['user_id']);

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

// Calcular estadísticas
$totalPedidos = count($pedidos);
$totalGastado = array_sum(array_column($pedidos, 'Total_Orden'));
$estadoCuenta = $totalGastado > 10000 ? 'Premium' : ($totalGastado > 5000 ? 'Gold' : 'Silver');

$current_page = 'perfil';

// Mensajes de éxito
$success = '';
if (isset($_SESSION['registro_exitoso'])) {
    $success = $_SESSION['registro_exitoso'];
    unset($_SESSION['registro_exitoso']);
}
if (isset($_SESSION['update_success'])) {
    $success = $_SESSION['update_success'];
    unset($_SESSION['update_success']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - WigerConstruction</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <?php include 'components/mini_cart.php'; ?>

    <!-- Header -->
    <div class="bg-light py-4">
        <div class="container">
            <h1 class="display-5 fw-bold">
                <i class="bi bi-person-circle"></i> Mi Perfil
            </h1>
            <p class="lead">Consulta tu información personal e historial de pedidos!</p>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="container mt-4 mb-5">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 mb-4">
                <div class="list-group">
                    <a href="#datos-personales" class="list-group-item list-group-item-action active">
                        <i class="bi bi-person"></i> Datos Personales
                    </a>
                    <a href="#mis-pedidos" class="list-group-item list-group-item-action">
                        <i class="bi bi-box-seam"></i> Mis Pedidos
                    </a>
                    <a href="#direcciones" class="list-group-item list-group-item-action">
                        <i class="bi bi-geo-alt"></i> Direcciones
                    </a>
                    <a href="cambiar_contrasena.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield-lock"></i> Cambiar Contraseña
                    </a>
                    <a href="#configuracion" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear"></i> Configuración
                    </a>
                    <a href="auth/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </div>
            </div>

            <!-- Contenido -->
            <div class="col-md-9">
                <!-- Datos Personales -->
                <div id="datos-personales" class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Información Personal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombre Completo:</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['Nombre_Usuario']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Correo Electrónico:</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($usuario['Correo_Electronico']); ?>" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Fecha de Nacimiento:</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($fecha_nacimiento_formateada); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tarjeta Bancaria:</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['Numero_Tarjeta_Bancaria'] ?? 'No especificada'); ?>" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Dirección Postal:</label>
                                <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($usuario['Direccion_Postal'] ?? 'No especificada'); ?></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">ID de Usuario:</label>
                                <input type="text" class="form-control" value="#<?php echo htmlspecialchars($usuario['ID_Usuario']); ?>" readonly>
                            </div>
                        </div>
                        <a href="editar_perfil.php" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Editar Información
                        </a>
                    </div>
                </div>

                <!-- Mis Pedidos -->
                <div id="mis-pedidos" class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Pedidos Recientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pedido #</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pedidos)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox display-1"></i>
                                            <p class="mt-3">No tienes pedidos aún</p>
                                            <a href="index.php" class="btn btn-success">
                                                <i class="bi bi-shop"></i> Ir de Compras
                                            </a>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($pedidos as $pedido): 
                                        $fechaPedido = new DateTime($pedido['Fecha_Orden']);
                                        $fechaFormateada = $fechaPedido->format('d M Y');
                                    ?>
                                    <tr>
                                        <td><strong>#<?php echo str_pad($pedido['ID_Orden'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo $fechaFormateada; ?></td>
                                        <td>$<?php echo number_format($pedido['Total_Orden'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo getEstadoBadgeClass($pedido['Estado_Orden']); ?>">
                                                <?php echo htmlspecialchars($pedido['Estado_Orden']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="ver_orden.php?id=<?php echo $pedido['ID_Orden']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <i class="bi bi-cart-check display-4 text-primary"></i>
                                <h3 class="mt-2"><?php echo $totalPedidos; ?></h3>
                                <p class="text-muted">Pedidos Totales</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <i class="bi bi-currency-dollar display-4 text-success"></i>
                                <h3 class="mt-2">$<?php echo number_format($totalGastado, 2); ?></h3>
                                <p class="text-muted">Total Gastado</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <i class="bi bi-star-fill display-4 text-warning"></i>
                                <h3 class="mt-2"><?php echo $estadoCuenta; ?></h3>
                                <p class="text-muted">Estado de Cuenta</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
