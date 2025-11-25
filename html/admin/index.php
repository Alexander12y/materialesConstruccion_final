<?php
session_start();
require_once 'check_admin.php';
require_once '../config/database.php';

$current_page = 'admin';

// Obtener estadísticas
$productos = getAllProducts();
$ordenes = getAllOrders();

$totalProductos = count($productos);
$totalOrdenes = count($ordenes);
$inventarioTotal = array_sum(array_column($productos, 'Cantidad_Almacen'));

// Calcular ingresos totales
$ingresosTotal = 0;
foreach ($ordenes as $orden) {
    $ingresosTotal += $orden['Total_Orden'];
}

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

// Mensajes
$success = '';
$error = '';

if (isset($_SESSION['admin_success'])) {
    $success = $_SESSION['admin_success'];
    unset($_SESSION['admin_success']);
}

if (isset($_SESSION['admin_error'])) {
    $error = $_SESSION['admin_error'];
    unset($_SESSION['admin_error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - WigerConstruction</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <!-- Header -->
    <div class="bg-dark text-white py-4">
        <div class="container">
            <h1 class="display-5 fw-bold">
                <i class="bi bi-speedometer2"></i> Panel de Administración
            </h1>
            <p class="lead mb-0">Gestión de inventario, productos y usuarios</p>
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

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-box-seam display-4 text-primary"></i>
                        <h3 class="mt-2"><?php echo $totalProductos; ?></h3>
                        <p class="text-muted mb-0">Productos Totales</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-boxes display-4 text-success"></i>
                        <h3 class="mt-2"><?php echo $inventarioTotal; ?></h3>
                        <p class="text-muted mb-0">Unidades en Stock</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-cart-check display-4 text-warning"></i>
                        <h3 class="mt-2"><?php echo $totalOrdenes; ?></h3>
                        <p class="text-muted mb-0">Órdenes Realizadas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-dollar display-4 text-info"></i>
                        <h3 class="mt-2">$<?php echo number_format($ingresosTotal, 2); ?></h3>
                        <p class="text-muted mb-0">Ingresos Totales</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menú de Acciones -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-gear-fill"></i> Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="productos.php" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-box-seam"></i> Gestionar Productos
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="historial_compras.php" class="btn btn-success btn-lg w-100">
                                    <i class="bi bi-clock-history"></i> Historial de Compras
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="agregar_admin.php" class="btn btn-warning btn-lg w-100">
                                    <i class="bi bi-person-plus-fill"></i> Agregar Administrador
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos Recientes -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Productos Recientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fabricante</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $productosRecientes = array_slice($productos, 0, 5);
                            foreach ($productosRecientes as $producto): 
                                $stockClass = $producto['Cantidad_Almacen'] > 10 ? 'success' : ($producto['Cantidad_Almacen'] > 0 ? 'warning' : 'danger');
                            ?>
                            <tr>
                                <td>#<?php echo $producto['ID_Producto']; ?></td>
                                <td><strong><?php echo htmlspecialchars($producto['Nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($producto['Fabricante'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($producto['Precio'], 2); ?></td>
                                <td><?php echo $producto['Cantidad_Almacen']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $stockClass; ?>">
                                        <?php echo $producto['Cantidad_Almacen'] > 0 ? 'Disponible' : 'Agotado'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($productosRecientes)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="bi bi-inbox"></i> No hay productos registrados
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    <a href="productos.php" class="btn btn-outline-primary">Ver Todos los Productos</a>
                </div>
            </div>
        </div>

        <!-- Órdenes Recientes -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-cart-check"></i> Órdenes Recientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Orden</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $ordenesRecientes = array_slice($ordenes, 0, 5);
                            foreach ($ordenesRecientes as $orden): 
                            ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($orden['ID_Orden'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($orden['Nombre_Usuario']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($orden['Fecha_Orden'])); ?></td>
                                <td><strong class="text-success">$<?php echo number_format($orden['Total_Orden'], 2); ?></strong></td>
                                <td><span class="badge bg-<?php echo getEstadoBadgeClass($orden['Estado_Orden']); ?>"><?php echo htmlspecialchars($orden['Estado_Orden']); ?></span></td>
                                <td>
                                    <a href="ver_orden_admin.php?id=<?php echo $orden['ID_Orden']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($ordenesRecientes)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-1"></i>
                                    <p class="mt-2">No hay órdenes registradas</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    <a href="historial_compras.php" class="btn btn-outline-success">Ver Todas las Órdenes</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
