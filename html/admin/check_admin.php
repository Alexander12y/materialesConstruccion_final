<?php
// Middleware para verificar que el usuario sea administrador
// Incluir este archivo al inicio de cualquier página que requiera permisos de admin

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Verificar que el usuario sea administrador
if (!isAdmin($_SESSION['user_id'])) {
    // Redirigir al inicio con mensaje de error
    $_SESSION['error'] = 'No tienes permisos para acceder a esta página';
    header('Location: ../index.php');
    exit();
}

// Si llegamos aquí, el usuario es admin y puede continuar
?>
