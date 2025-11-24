<?php
session_start();
require_once '../config/database.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cambiar_contrasena.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Obtener datos del formulario
$password_actual = trim($_POST['password_actual'] ?? '');
$password_nueva = trim($_POST['password_nueva'] ?? '');
$password_confirmar = trim($_POST['password_confirmar'] ?? '');

// Validación básica
if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
    $_SESSION['password_error'] = 'Todos los campos son obligatorios';
    header('Location: ../cambiar_contrasena.php');
    exit();
}

// Validar que las contraseñas nuevas coincidan
if ($password_nueva !== $password_confirmar) {
    $_SESSION['password_error'] = 'Las contraseñas nuevas no coinciden';
    header('Location: ../cambiar_contrasena.php');
    exit();
}

// Validar longitud mínima
if (strlen($password_nueva) < 6) {
    $_SESSION['password_error'] = 'La nueva contraseña debe tener al menos 6 caracteres';
    header('Location: ../cambiar_contrasena.php');
    exit();
}

// Cambiar contraseña
$result = updatePassword($userId, $password_actual, $password_nueva);

if (!$result['success']) {
    $_SESSION['password_error'] = $result['message'];
    header('Location: ../cambiar_contrasena.php');
    exit();
}

$_SESSION['password_success'] = 'Contraseña actualizada exitosamente';
header('Location: ../cambiar_contrasena.php');
exit();
?>
