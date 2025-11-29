<?php
session_start();
require_once 'config/database.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrito.php');
    exit();
}

// Obtener datos del formulario
$direccionEnvio = trim($_POST['direccion_envio'] ?? '');
$numeroTarjeta = trim($_POST['numero_tarjeta'] ?? '');
$nombreTarjeta = trim($_POST['nombre_tarjeta'] ?? '');
$fechaExpiracion = trim($_POST['fecha_expiracion'] ?? '');
$cvv = trim($_POST['cvv'] ?? '');
$updateAddress = isset($_POST['update_address']);
$updateCard = isset($_POST['update_card']);
$usingSavedCard = isset($_POST['using_saved_card']) && $_POST['using_saved_card'] === '1';

// Validaciones básicas
if (empty($direccionEnvio) || empty($numeroTarjeta) || empty($nombreTarjeta) || empty($fechaExpiracion) || empty($cvv)) {
    $_SESSION['checkout_error'] = 'Todos los campos son obligatorios';
    header('Location: checkout.php');
    exit();
}

// Limpiar número de tarjeta (remover espacios)
$numeroTarjetaLimpio = str_replace(' ', '', $numeroTarjeta);

// Si se está usando una tarjeta guardada enmascarada (ej: **** **** **** 1234),
// no validar el número como dígitos completos. En caso contrario validar 15-16 dígitos.
if (!($usingSavedCard && preg_match('/\*/', $numeroTarjeta))) {
    if (!preg_match('/^\d{15,16}$/', $numeroTarjetaLimpio)) {
        $_SESSION['checkout_error'] = 'Número de tarjeta inválido';
        header('Location: checkout.php');
        exit();
    }
}

// Validar formato de fecha de expiración (MM/AA)
if (!preg_match('/^\d{2}\/\d{2}$/', $fechaExpiracion)) {
    $_SESSION['checkout_error'] = 'Fecha de expiración inválida';
    header('Location: checkout.php');
    exit();
}

// Validar que la tarjeta no esté vencida
list($mes, $anio) = explode('/', $fechaExpiracion);
$mesActual = date('m');
$anioActual = date('y');
if ($anio < $anioActual || ($anio == $anioActual && $mes < $mesActual)) {
    $_SESSION['checkout_error'] = 'La tarjeta está vencida';
    header('Location: checkout.php');
    exit();
}

// Validar CVV (3-4 dígitos)
if (!preg_match('/^\d{3,4}$/', $cvv)) {
    $_SESSION['checkout_error'] = 'CVV inválido';
    header('Location: checkout.php');
    exit();
}

// Obtener items del carrito
$userId = $_SESSION['user_id'];
$cartItems = getCartItems($userId);
$cartTotal = getCartTotal($userId);

// Verificar que el carrito no esté vacío
if (empty($cartItems)) {
    $_SESSION['cart_message'] = 'Tu carrito está vacío';
    $_SESSION['cart_message_type'] = 'warning';
    header('Location: carrito.php');
    exit();
}

// Calcular total con IVA
$totalConIVA = $cartTotal * 1.16;

// Enmascarar número de tarjeta para guardar (últimos 4 dígitos)
$tarjetaEnmascarada = '**** **** **** ' . substr($numeroTarjetaLimpio, -4);

// Actualizar información del usuario si se solicitó
$usuario = getUserById($userId);
// Si el cliente indicó que usará la tarjeta guardada, validar que coincida con la registrada
if ($usingSavedCard && isset($usuario['Numero_Tarjeta_Bancaria'])) {
    if ($numeroTarjeta !== $usuario['Numero_Tarjeta_Bancaria']) {
        // Si no coincide, considerar que el usuario está proveyendo una nueva tarjeta
        $usingSavedCard = false;
        // limpiar la bandera proveniente del formulario por seguridad
        $_POST['using_saved_card'] = '0';
    }
}

if (empty($usuario['Direccion_Postal']) || $updateAddress) {
    updateUserAddress($userId, $direccionEnvio);
}

if (empty($usuario['Numero_Tarjeta_Bancaria']) || $updateCard) {
    updateUserCard($userId, $tarjetaEnmascarada);
}

// SIMULACIÓN DE PROCESAMIENTO DE PAGO
// En un sistema real, aquí se haría la conexión con una pasarela de pago
// como Stripe, PayPal, Mercado Pago, etc.

// Simular tiempo de procesamiento
sleep(1);

// Simular que el pago fue exitoso (en producción verificar respuesta de pasarela)
$pagoExitoso = true;

if (!$pagoExitoso) {
    $_SESSION['checkout_error'] = 'Error al procesar el pago. Por favor, verifica tus datos e intenta nuevamente.';
    header('Location: checkout.php');
    exit();
}

// Crear la orden en la base de datos
$resultado = createOrder($userId, $totalConIVA, $direccionEnvio, $cartItems);

if (!$resultado['success']) {
    $_SESSION['checkout_error'] = $resultado['message'];
    header('Location: checkout.php');
    exit();
}

// Orden creada exitosamente
$orderId = $resultado['order_id'];

// Guardar información de la orden en la sesión para la página de confirmación
$_SESSION['last_order_id'] = $orderId;
$_SESSION['order_success'] = true;

// Redirigir a página de confirmación
header('Location: confirmacion_orden.php?orden=' . $orderId);
exit();
?>
