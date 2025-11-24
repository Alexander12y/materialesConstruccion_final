<?php
session_start();
require_once 'config/database.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_page = 'perfil';

// Mensajes de éxito o error
$success = '';
$error = '';

if (isset($_SESSION['password_success'])) {
    $success = $_SESSION['password_success'];
    unset($_SESSION['password_success']);
}

if (isset($_SESSION['password_error'])) {
    $error = $_SESSION['password_error'];
    unset($_SESSION['password_error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - WigerConstruction</title>
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
                        <i class="bi bi-shield-lock"></i> Cambiar Contraseña
                    </h1>
                    <p class="lead mb-0">Actualiza tu contraseña de seguridad</p>
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
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-key"></i> Actualizar Contraseña</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle"></i> Por seguridad, necesitas ingresar tu contraseña actual para cambiarla.
                        </div>

                        <form action="auth/change_password.php" method="POST" id="cambiarPasswordForm">
                            <!-- Contraseña Actual -->
                            <div class="mb-4">
                                <label for="password_actual" class="form-label fw-bold">
                                    <i class="bi bi-lock"></i> Contraseña Actual *
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password_actual" 
                                       name="password_actual" placeholder="Ingresa tu contraseña actual" required>
                            </div>

                            <hr class="my-4">

                            <!-- Nueva Contraseña -->
                            <div class="mb-3">
                                <label for="password_nueva" class="form-label fw-bold">
                                    <i class="bi bi-shield-check"></i> Nueva Contraseña *
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password_nueva" 
                                       name="password_nueva" placeholder="Mínimo 6 caracteres" required minlength="6">
                                <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres</small>
                            </div>

                            <!-- Confirmar Nueva Contraseña -->
                            <div class="mb-4">
                                <label for="password_confirmar" class="form-label fw-bold">
                                    <i class="bi bi-shield-check"></i> Confirmar Nueva Contraseña *
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password_confirmar" 
                                       name="password_confirmar" placeholder="Repite la nueva contraseña" required>
                            </div>

                            <!-- Botones -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg text-dark">
                                    <i class="bi bi-check-circle"></i> Cambiar Contraseña
                                </button>
                                <a href="perfil_usuario.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recomendaciones de seguridad -->
                <div class="card mt-4 border-info">
                    <div class="card-body">
                        <h6 class="card-title text-info">
                            <i class="bi bi-shield-fill-check"></i> Recomendaciones de Seguridad
                        </h6>
                        <ul class="mb-0 small">
                            <li>Usa una contraseña única que no utilices en otros sitios</li>
                            <li>Combina letras mayúsculas, minúsculas, números y símbolos</li>
                            <li>Evita información personal fácil de adivinar</li>
                            <li>Cambia tu contraseña regularmente</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Validación del formulario -->
    <script>
        document.getElementById('cambiarPasswordForm').addEventListener('submit', function(e) {
            const passwordNueva = document.getElementById('password_nueva').value;
            const passwordConfirmar = document.getElementById('password_confirmar').value;

            // Validar que las contraseñas nuevas coincidan
            if (passwordNueva !== passwordConfirmar) {
                e.preventDefault();
                alert('Las contraseñas nuevas no coinciden');
                return false;
            }

            // Validar longitud mínima
            if (passwordNueva.length < 6) {
                e.preventDefault();
                alert('La nueva contraseña debe tener al menos 6 caracteres');
                return false;
            }
        });
    </script>
</body>
</html>
