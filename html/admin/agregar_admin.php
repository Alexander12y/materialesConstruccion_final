<?php
session_start();
require_once 'check_admin.php';

$current_page = 'admin';

// Mensajes de éxito/error
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Administrador - Admin</title>
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
                        <i class="bi bi-person-plus-fill"></i> Agregar Administrador
                    </h1>
                    <p class="lead mb-0">Crear nuevo usuario con permisos administrativos</p>
                </div>
                <a href="index.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-shield-fill-check"></i> Datos del Nuevo Administrador</h5>
                    </div>
                    <div class="card-body">
                        <form action="procesar_admin.php" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="bi bi-person"></i> Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                       placeholder="Ej: Juan Pérez García">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       placeholder="admin@wigerconstruction.com">
                                <small class="form-text text-muted">Este correo se usará para iniciar sesión</small>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required
                                       minlength="6" placeholder="Mínimo 6 caracteres">
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill"></i> Confirmar Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                       minlength="6" placeholder="Repite la contraseña">
                            </div>

                            <div class="mb-3">
                                <label for="fecha_nacimiento" class="form-label">
                                    <i class="bi bi-calendar"></i> Fecha de Nacimiento
                                </label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">
                                    <i class="bi bi-geo-alt"></i> Dirección
                                </label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2"
                                          placeholder="Calle, número, colonia, ciudad"></textarea>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> El usuario será creado con permisos de administrador y tendrá acceso completo al panel de control.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-person-plus-fill"></i> Crear Administrador
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validar que las contraseñas coincidan
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                document.getElementById('confirm_password').focus();
            }
        });
    </script>
</body>
</html>
