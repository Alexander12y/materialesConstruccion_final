<?php
session_start();
require_once 'check_admin.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: productos.php');
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'crear') {
    $data = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'precio' => floatval($_POST['precio'] ?? 0),
        'cantidad' => intval($_POST['cantidad'] ?? 0),
        'categoria' => trim($_POST['categoria'] ?? '')
    ];
    
    if (empty($data['nombre']) || $data['precio'] <= 0) {
        $_SESSION['product_error'] = 'El nombre y precio son obligatorios';
        header('Location: productos.php');
        exit();
    }
    
    $result = createProduct($data);
    
    if ($result['success']) {
        $_SESSION['product_success'] = $result['message'];
    } else {
        $_SESSION['product_error'] = $result['message'];
    }
    
} elseif ($action === 'editar') {
    $productId = intval($_POST['id'] ?? 0);
    $data = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'precio' => floatval($_POST['precio'] ?? 0),
        'cantidad' => intval($_POST['cantidad'] ?? 0),
        'categoria' => trim($_POST['categoria'] ?? '')
    ];
    
    if (empty($data['nombre']) || $data['precio'] <= 0 || $productId <= 0) {
        $_SESSION['product_error'] = 'Datos inválidos para actualizar el producto';
        header('Location: productos.php');
        exit();
    }
    
    $result = updateProduct($productId, $data);
    
    if ($result['success']) {
        $_SESSION['product_success'] = $result['message'];
    } else {
        $_SESSION['product_error'] = $result['message'];
    }
}

header('Location: productos.php');
exit();
?>
