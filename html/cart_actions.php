<?php
session_start();
require_once 'config/database.php';

// Inicializar carrito de invitado si no existe
if (!isset($_SESSION['guest_cart'])) {
    $_SESSION['guest_cart'] = [];
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$returnUrl = isset($_POST['return_url']) ? $_POST['return_url'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php');
$isGuest = !isset($_SESSION['user_id']);

switch ($action) {
    case 'add':
        // Agregar producto al carrito
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
        
        if ($productId && $cantidad > 0) {
            if ($isGuest) {
                // Carrito de invitado (sesión)
                if (!isset($_SESSION['guest_cart'][$productId])) {
                    $_SESSION['guest_cart'][$productId] = 0;
                }
                $_SESSION['guest_cart'][$productId] += $cantidad;
                $_SESSION['cart_message'] = 'Producto agregado al carrito. <a href="registro.php" class="alert-link">Crea una cuenta</a> para finalizar tu compra.';
                $_SESSION['cart_message_type'] = 'success';
            } else {
                // Usuario autenticado (base de datos)
                $result = addToCart($_SESSION['user_id'], $productId, $cantidad);
                $_SESSION['cart_message'] = $result['message'];
                $_SESSION['cart_message_type'] = $result['success'] ? 'success' : 'danger';
            }
        } else {
            $_SESSION['cart_message'] = 'Datos inválidos';
            $_SESSION['cart_message_type'] = 'danger';
        }
        break;
        
    case 'update':
        // Actualizar cantidad
        if ($isGuest) {
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
            
            if ($productId && isset($_SESSION['guest_cart'][$productId])) {
                if ($cantidad <= 0) {
                    unset($_SESSION['guest_cart'][$productId]);
                    $_SESSION['cart_message'] = 'Producto eliminado';
                } else {
                    $_SESSION['guest_cart'][$productId] = $cantidad;
                    $_SESSION['cart_message'] = 'Cantidad actualizada';
                }
                $_SESSION['cart_message_type'] = 'success';
            }
        } else {
            $cartId = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
            $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
            
            if ($cartId) {
                $success = updateCartItemQuantity($cartId, $cantidad);
                $_SESSION['cart_message'] = $success ? 'Cantidad actualizada' : 'Error al actualizar';
                $_SESSION['cart_message_type'] = $success ? 'success' : 'danger';
            }
        }
        break;
        
    case 'remove':
        // Eliminar producto del carrito
        if ($isGuest) {
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            
            if ($productId && isset($_SESSION['guest_cart'][$productId])) {
                unset($_SESSION['guest_cart'][$productId]);
                $_SESSION['cart_message'] = 'Producto eliminado del carrito';
                $_SESSION['cart_message_type'] = 'success';
            }
        } else {
            $cartId = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
            
            if ($cartId) {
                $success = removeFromCart($cartId);
                $_SESSION['cart_message'] = $success ? 'Producto eliminado del carrito' : 'Error al eliminar';
                $_SESSION['cart_message_type'] = $success ? 'success' : 'danger';
            }
        }
        break;
        
    case 'clear':
        // Vaciar carrito
        if ($isGuest) {
            $_SESSION['guest_cart'] = [];
            $_SESSION['cart_message'] = 'Carrito vaciado';
            $_SESSION['cart_message_type'] = 'success';
        } else {
            $success = clearCart($_SESSION['user_id']);
            $_SESSION['cart_message'] = $success ? 'Carrito vaciado' : 'Error al vaciar carrito';
            $_SESSION['cart_message_type'] = $success ? 'success' : 'danger';
        }
        break;
        
    default:
        $_SESSION['cart_message'] = 'Acción no válida';
        $_SESSION['cart_message_type'] = 'danger';
}

header('Location: ' . $returnUrl);
exit();
?>
