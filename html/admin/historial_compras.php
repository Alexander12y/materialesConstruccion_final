<?php
session_start();
require_once 'check_admin.php';
require_once '../config/database.php';

$current_page = 'admin';

// Obtener todas las compras
$compras = getAllPurchases();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <div class="bg-success text-white py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-5 fw-bold mb-0">
                        <i class="bi bi-clock-history"></i> Historial de Compras
                    </h1>
                    <p class="lead mb-0">Registro completo de transacciones</p>
                </div>
                <a href="index.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-4 mb-5">
        <!-- Estadísticas Rápidas -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-cart-check display-4 text-success"></i>
                        <h3 class="mt-2"><?php echo count($compras); ?></h3>
                        <p class="text-muted mb-0">Total de Compras</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-dollar display-4 text-info"></i>
                        <h3 class="mt-2">$<?php echo number_format(array_sum(array_column($compras, 'Total')), 2); ?></h3>
                        <p class="text-muted mb-0">Ingresos Totales</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up display-4 text-warning"></i>
                        <h3 class="mt-2">$<?php echo count($compras) > 0 ? number_format(array_sum(array_column($compras, 'Total')) / count($compras), 2) : '0.00'; ?></h3>
                        <p class="text-muted mb-0">Ticket Promedio</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Compras -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Todas las Compras (<?php echo count($compras); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Compra</th>
                                <th>Cliente</th>
                                <th>Correo</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compras as $compra): ?>
                            <tr>
                                <td><strong>#<?php echo $compra['ID_Compra']; ?></strong></td>
                                <td><?php echo htmlspecialchars($compra['Nombre_Usuario']); ?></td>
                                <td><?php echo htmlspecialchars($compra['Correo_Electronico']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($compra['Fecha_Compra'])); ?></td>
                                <td><?php echo date('H:i', strtotime($compra['Fecha_Compra'])); ?></td>
                                <td><strong class="text-success">$<?php echo number_format($compra['Total'], 2); ?></strong></td>
                                <td><span class="badge bg-success">Completada</span></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($compras)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox display-1"></i>
                                    <p class="mt-3 h5">No hay compras registradas</p>
                                    <p class="text-muted">Las transacciones aparecerán aquí cuando los clientes realicen compras</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if (!empty($compras)): ?>
        <!-- Resumen por Cliente -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Resumen por Cliente</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Total de Compras</th>
                                <th>Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Agrupar por cliente
                            $clienteStats = [];
                            foreach ($compras as $compra) {
                                $clienteKey = $compra['ID_Usuario'];
                                if (!isset($clienteStats[$clienteKey])) {
                                    $clienteStats[$clienteKey] = [
                                        'nombre' => $compra['Nombre_Usuario'],
                                        'email' => $compra['Correo_Electronico'],
                                        'count' => 0,
                                        'total' => 0
                                    ];
                                }
                                $clienteStats[$clienteKey]['count']++;
                                $clienteStats[$clienteKey]['total'] += $compra['Total'];
                            }
                            
                            // Ordenar por monto total descendente
                            usort($clienteStats, function($a, $b) {
                                return $b['total'] <=> $a['total'];
                            });
                            
                            foreach ($clienteStats as $stats):
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($stats['nombre']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($stats['email']); ?></small>
                                </td>
                                <td><?php echo $stats['count']; ?> compra<?php echo $stats['count'] > 1 ? 's' : ''; ?></td>
                                <td><strong class="text-success">$<?php echo number_format($stats['total'], 2); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
