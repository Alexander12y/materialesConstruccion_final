<?php
session_start();
require_once 'check_admin.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: agregar_admin.php');
    exit();
}

// Obtener datos del formulario
$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirm_password = trim($_POST['confirm_password']);
$fecha_nacimiento = isset($_POST['fecha_nacimiento']) && !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
$direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;

// Validaciones
if (empty($nombre) || empty($email) || empty($password)) {
    $_SESSION['error'] = 'Por favor, completa todos los campos obligatorios.';
    header('Location: agregar_admin.php');
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Las contraseñas no coinciden.';
    header('Location: agregar_admin.php');
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
    header('Location: agregar_admin.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'El formato del correo electrónico no es válido.';
    header('Location: agregar_admin.php');
    exit();
}

// Verificar si el correo ya está registrado
if (emailExists($email)) {
    $_SESSION['error'] = 'El correo electrónico ya está registrado.';
    header('Location: agregar_admin.php');
    exit();
}

// Crear el administrador
$result = createAdmin($nombre, $email, $password, $fecha_nacimiento, $direccion);

if ($result) {
    $_SESSION['success'] = 'Administrador creado exitosamente.';
    header('Location: agregar_admin.php');
} else {
    $_SESSION['error'] = 'Hubo un error al crear el administrador. Intenta nuevamente.';
    header('Location: agregar_admin.php');
}
exit();
?>
