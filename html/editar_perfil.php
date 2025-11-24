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
    session_destroy();
    header('Location: login.php');
    exit();
}

$current_page = 'perfil';

// Mensajes de éxito o error
$success = '';
$error = '';

if (isset($_SESSION['update_success'])) {
    $success = $_SESSION['update_success'];
    unset($_SESSION['update_success']);
}

if (isset($_SESSION['update_error'])) {
    $error = $_SESSION['update_error'];
    unset($_SESSION['update_error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - WigerConstruction</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <!-- Header -->
    <div class="bg-light py-4">
        <div class="container">
            <div class="d-flex align-items-center">
                <a href="perfil_usuario.php" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <div>
                    <h1 class="display-5 fw-bold mb-0">
                        <i class="bi bi-pencil-square"></i> Editar Perfil
                    </h1>
                    <p class="lead mb-0">Actualiza tu información personal</p>
                </div>
            </div>
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

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Información Personal</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="auth/update_profile.php" method="POST" id="editarPerfilForm">
                            <!-- Nombre -->
                            <div class="mb-3">
                                <label for="nombre" class="form-label fw-bold">
                                    <i class="bi bi-person"></i> Nombre Completo *
                                </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($usuario['Nombre_Usuario']); ?>" 
                                       required>
                            </div>

                            <!-- Email (solo lectura) -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">
                                    <i class="bi bi-envelope"></i> Correo Electrónico
                                </label>
                                <input type="email" class="form-control" id="email" 
                                       value="<?php echo htmlspecialchars($usuario['Correo_Electronico']); ?>" 
                                       readonly disabled>
                                <small class="form-text text-muted">El correo electrónico no puede ser modificado</small>
                            </div>

                            <!-- Fecha de Nacimiento -->
                            <div class="mb-3">
                                <label for="fecha_nacimiento" class="form-label fw-bold">
                                    <i class="bi bi-calendar"></i> Fecha de Nacimiento
                                </label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                       value="<?php echo htmlspecialchars($usuario['Fecha_Nacimiento'] ?? ''); ?>">
                            </div>

                            <!-- Dirección Postal -->
                            <div class="mb-3">
                                <label for="direccion" class="form-label fw-bold">
                                    <i class="bi bi-geo-alt"></i> Dirección Postal
                                </label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="3" 
                                          placeholder="Calle, número, colonia, ciudad, código postal"><?php echo htmlspecialchars($usuario['Direccion_Postal'] ?? ''); ?></textarea>
                            </div>

                            <!-- Botones -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="perfil_usuario.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="text-center mt-4 text-muted">
                    <small>
                        <i class="bi bi-shield-check"></i> Tu información está protegida y encriptada
                    </small>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    

</body>
</html>
