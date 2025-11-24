<?php
session_start();
require_once 'check_admin.php';
require_once '../config/database.php';

$current_page = 'admin';

// Obtener todos los productos
$productos = getAllProducts();

// Mensajes
$success = '';
$error = '';

if (isset($_SESSION['product_success'])) {
    $success = $_SESSION['product_success'];
    unset($_SESSION['product_success']);
}

if (isset($_SESSION['product_error'])) {
    $error = $_SESSION['product_error'];
    unset($_SESSION['product_error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <div class="bg-primary text-white py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-5 fw-bold mb-0">
                        <i class="bi bi-box-seam"></i> Gestión de Productos
                    </h1>
                    <p class="lead mb-0">Administra el inventario de materiales de construcción</p>
                </div>
                <a href="index.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-4 mb-5">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Botón Agregar Producto -->
        <div class="mb-4">
            <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                <i class="bi bi-plus-circle"></i> Agregar Nuevo Producto
            </button>
        </div>

        <!-- Tabla de Productos -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Lista de Productos (<?php echo count($productos); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): 
                                $stockClass = $producto['Cantidad_Disponible'] > 10 ? 'success' : ($producto['Cantidad_Disponible'] > 0 ? 'warning' : 'danger');
                            ?>
                            <tr>
                                <td>#<?php echo $producto['ID_Producto']; ?></td>
                                <td><strong><?php echo htmlspecialchars($producto['Nombre_Producto']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($producto['Descripcion'] ?? '', 0, 50)) . (strlen($producto['Descripcion'] ?? '') > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($producto['Categoria'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($producto['Precio'], 2); ?></td>
                                <td><?php echo $producto['Cantidad_Disponible']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $stockClass; ?>">
                                        <?php echo $producto['Cantidad_Disponible'] > 0 ? 'Disponible' : 'Agotado'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editarProducto(<?php echo htmlspecialchars(json_encode($producto)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($productos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-1"></i>
                                    <p class="mt-2">No hay productos registrados</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Producto -->
    <div class="modal fade" id="agregarProductoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Agregar Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesar_producto.php" method="POST">
                    <input type="hidden" name="action" value="crear">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría</label>
                                <select class="form-select" name="categoria">
                                    <option value="">Seleccionar...</option>
                                    <option value="Cemento y Concreto">Cemento y Concreto</option>
                                    <option value="Varilla y Acero">Varilla y Acero</option>
                                    <option value="Tabique y Block">Tabique y Block</option>
                                    <option value="Herramientas">Herramientas</option>
                                    <option value="Eléctrico">Eléctrico</option>
                                    <option value="Plomería">Plomería</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="precio" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cantidad Disponible *</label>
                                <input type="number" class="form-control" name="cantidad" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Producto -->
    <div class="modal fade" id="editarProductoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesar_producto.php" method="POST">
                    <input type="hidden" name="action" value="editar">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría</label>
                                <select class="form-select" name="categoria" id="edit_categoria">
                                    <option value="">Seleccionar...</option>
                                    <option value="Cemento y Concreto">Cemento y Concreto</option>
                                    <option value="Varilla y Acero">Varilla y Acero</option>
                                    <option value="Tabique y Block">Tabique y Block</option>
                                    <option value="Herramientas">Herramientas</option>
                                    <option value="Eléctrico">Eléctrico</option>
                                    <option value="Plomería">Plomería</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="precio" id="edit_precio" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cantidad Disponible *</label>
                                <input type="number" class="form-control" name="cantidad" id="edit_cantidad" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning"><i class="bi bi-check-circle"></i> Actualizar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarProducto(producto) {
            document.getElementById('edit_id').value = producto.ID_Producto;
            document.getElementById('edit_nombre').value = producto.Nombre_Producto;
            document.getElementById('edit_categoria').value = producto.Categoria || '';
            document.getElementById('edit_descripcion').value = producto.Descripcion || '';
            document.getElementById('edit_precio').value = producto.Precio;
            document.getElementById('edit_cantidad').value = producto.Cantidad_Disponible;
            
            new bootstrap.Modal(document.getElementById('editarProductoModal')).show();
        }
    </script>
</body>
</html>
