<?php
session_start();
require_once '../config/database.php';

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

// Obtener datos del formulario
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Validación básica
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Por favor complete todos los campos';
    header('Location: ../login.php');
    exit();
}

// Intentar login
$result = loginUser($email, $password);

if ($result['success']) {
    // Guardar información en sesión
    $_SESSION['user_id'] = $result['user']['ID_Usuario'];
    $_SESSION['user_name'] = $result['user']['Nombre_Usuario'];
    $_SESSION['user_email'] = $result['user']['Correo_Electronico'];
    
    // Redirigir al perfil
    header('Location: ../perfil_usuario.php');
    exit();
} else {
    $_SESSION['error'] = $result['message'];
    header('Location: ../login.php');
    exit();
}
?>
