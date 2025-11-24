<?php
session_start();
require_once '../config/database.php';

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registro.php');
    exit();
}

// Obtener datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

// Validación básica
if (empty($nombre) || empty($email) || empty($password)) {
    $_SESSION['error'] = 'Por favor complete todos los campos obligatorios';
    header('Location: ../registro.php');
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Por favor ingrese un correo electrónico válido';
    header('Location: ../registro.php');
    exit();
}

// Validar que las contraseñas coincidan
if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Las contraseñas no coinciden';
    header('Location: ../registro.php');
    exit();
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
    header('Location: ../registro.php');
    exit();
}


// Preparar datos para registro
$userData = [
    'nombre' => $nombre,
    'email' => $email,
    'password' => $password,
    'fecha_nacimiento' => !empty($fecha_nacimiento) ? $fecha_nacimiento : null,
    'direccion' => !empty($direccion) ? $direccion : null
];

// Intentar registrar usuario
$result = registerUser($userData);

if ($result['success']) {
    // Registro exitoso - iniciar sesión automáticamente
    $_SESSION['user_id'] = $result['user_id'];
    $_SESSION['user_name'] = $nombre;
    $_SESSION['user_email'] = $email;
    $_SESSION['registro_exitoso'] = 'Cuenta creada exitosamente. ¡Bienvenido!';
    
    // Redirigir al perfil
    header('Location: ../perfil_usuario.php');
    exit();
} else {
    $_SESSION['error'] = $result['message'];
    header('Location: ../registro.php');
    exit();
}
?>
