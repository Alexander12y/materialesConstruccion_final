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
    header('Location: ../editar_perfil.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Obtener datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

// Validación básica
if (empty($nombre)) {
    $_SESSION['update_error'] = 'El nombre es obligatorio';
    header('Location: ../editar_perfil.php');
    exit();
}

// Preparar datos para actualización
$userData = [
    'nombre' => $nombre,
    'fecha_nacimiento' => !empty($fecha_nacimiento) ? $fecha_nacimiento : null,
    'direccion' => !empty($direccion) ? $direccion : null
];

// Actualizar datos básicos
$result = updateUser($userId, $userData);

if (!$result['success']) {
    $_SESSION['update_error'] = $result['message'];
    header('Location: ../editar_perfil.php');
    exit();
}

$_SESSION['update_success'] = 'Perfil actualizado exitosamente';

// Actualizar el nombre en la sesión si cambió
$_SESSION['user_name'] = $nombre;

// Redirigir de vuelta al perfil
header('Location: ../perfil_usuario.php');
exit();
?>
