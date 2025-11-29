<?php
session_start();
require_once 'config/database.php';

$current_page = 'carrito';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_redirect'] = true;
    header('Location: login.php');
    exit();
}

// Obtener items del carrito
$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);

// Redirigir si el carrito está vacío
if (empty($cartItems)) {
    $_SESSION['cart_message'] = 'Tu carrito está vacío. Agrega productos antes de proceder al pago.';
    $_SESSION['cart_message_type'] = 'warning';
    header('Location: carrito.php');
    exit();
}

// Obtener datos del usuario
$usuario = getUserById($_SESSION['user_id']);

// Mensajes
$error = isset($_SESSION['checkout_error']) ? $_SESSION['checkout_error'] : '';
unset($_SESSION['checkout_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - WigerConstruction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        .checkout-header {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: -1;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .step-item.active .step-number {
            background: #198754;
            color: white;
        }
        .step-item.completed .step-number {
            background: #0d6efd;
            color: white;
        }
        .card-input {
            letter-spacing: 2px;
        }
        .product-mini {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .product-mini:last-child {
            border-bottom: none;
        }
        .product-mini img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <?php include 'components/mini_cart.php'; ?>

    <div class="checkout-header text-white py-4">
        <div class="container">
            <h1 class="display-5 fw-bold mb-0">
                <i class="bi bi-credit-card"></i> Proceso de Pago
            </h1>
            <p class="lead mb-0">Completa tu información para finalizar la compra</p>
        </div>
    </div>

    <div class="container mt-4 mb-5">
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Indicador de pasos -->
        <div class="step-indicator mb-4">
            <div class="step-item completed">
                <div class="step-number">
                    <i class="bi bi-check"></i>
                </div>
                <small>Carrito</small>
            </div>
            <div class="step-item active">
                <div class="step-number">2</div>
                <small>Información de Pago</small>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <small>Confirmación</small>
            </div>
        </div>

        <form action="procesar_orden.php" method="POST" id="checkoutForm">
            <div class="row">
                <!-- Formulario de pago -->
                <div class="col-lg-8 mb-4">
                    <!-- Información de Envío -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Dirección de Envío</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="direccion_envio" class="form-label">Dirección Completa *</label>
                                <textarea class="form-control" id="direccion_envio" name="direccion_envio" rows="3" required 
                                          placeholder="Calle, número, colonia, ciudad, código postal"><?php echo htmlspecialchars($usuario['Direccion_Postal'] ?? ''); ?></textarea>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> Asegúrate de que la dirección sea correcta para la entrega
                                </div>
                            </div>
                            
                            <?php if (empty($usuario['Direccion_Postal'])): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-lightbulb"></i> Esta dirección se guardará en tu perfil para futuras compras.
                            </div>
                            <?php else: ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="update_address" name="update_address" value="1">
                                <label class="form-check-label" for="update_address">
                                    Actualizar mi dirección guardada con esta información
                                </label>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Información de Pago -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Información de Tarjeta</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="numero_tarjeta" class="form-label">Número de Tarjeta *</label>
                                <?php
                                $storedCard = $usuario['Numero_Tarjeta_Bancaria'] ?? '';
                                $isMasked = $storedCard !== '' && preg_match('/[^0-9\s]/', $storedCard);
                                ?>
                                <input type="text" class="form-control card-input" id="numero_tarjeta" name="numero_tarjeta" 
                                       maxlength="19" required 
                                       placeholder="#### #### #### ####"
                                       value="<?php echo htmlspecialchars($storedCard); ?>"
                                       data-saved-card="<?php echo $isMasked ? '1' : '0'; ?>">
                                <input type="hidden" name="using_saved_card" id="using_saved_card" value="<?php echo $isMasked ? '1' : '0'; ?>">
                                <div class="form-text">
                                    <i class="bi bi-shield-check"></i> Tu información está protegida con encriptación SSL
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_tarjeta" class="form-label">Nombre en la Tarjeta *</label>
                                    <input type="text" class="form-control" id="nombre_tarjeta" name="nombre_tarjeta" 
                                           required placeholder="Como aparece en la tarjeta"
                                           value="<?php echo htmlspecialchars($usuario['Nombre_Usuario']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_expiracion" class="form-label">Fecha de Expiración *</label>
                                    <input type="text" class="form-control" id="fecha_expiracion" name="fecha_expiracion" 
                                           maxlength="5" required placeholder="MM/AA">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV *</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" 
                                           maxlength="4" required placeholder="123">
                                    <div class="form-text">
                                        <i class="bi bi-question-circle"></i> Código de 3 o 4 dígitos al reverso
                                    </div>
                                </div>
                            </div>

                            <?php if (empty($usuario['Numero_Tarjeta_Bancaria'])): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-lightbulb"></i> Esta tarjeta se guardará en tu perfil para futuras compras.
                            </div>
                            <?php else: ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="update_card" name="update_card" value="1">
                                <label class="form-check-label" for="update_card">
                                    Actualizar mi tarjeta guardada con esta información
                                </label>
                            </div>
                            <?php endif; ?>

                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <i class="bi bi-lock-fill text-success"></i>
                                    <strong>Pago 100% Seguro:</strong> Utilizamos los más altos estándares de seguridad para proteger tu información.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen del pedido -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-bag-check"></i> Resumen del Pedido</h5>
                        </div>
                        <div class="card-body">
                            <!-- Lista de productos -->
                            <div class="mb-3">
                                <h6 class="border-bottom pb-2">Productos (<?php echo count($cartItems); ?>)</h6>
                                <?php foreach ($cartItems as $item): ?>
                                <div class="product-mini">
                                    <img src="public/products/<?php echo htmlspecialchars($item['imagen'] ?? 'default-product.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['Nombre']); ?>"
                                         onerror="this.src='public/products/default-product.jpg'">
                                    <div class="flex-grow-1">
                                        <small class="d-block fw-bold"><?php echo htmlspecialchars($item['Nombre']); ?></small>
                                        <small class="text-muted">Cantidad: <?php echo $item['Cantidad']; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <small class="fw-bold text-success">$<?php echo number_format($item['Precio'] * $item['Cantidad'], 2); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Totales -->
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong>$<?php echo number_format($cartTotal, 2); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Envío:</span>
                                    <strong class="text-success">GRATIS</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>IVA (16%):</span>
                                    <strong>$<?php echo number_format($cartTotal * 0.16, 2); ?></strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="h5">Total:</span>
                                    <span class="h4 text-success mb-0">$<?php echo number_format($cartTotal * 1.16, 2); ?></span>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 mb-2">
                                    <i class="bi bi-check-circle"></i> Confirmar y Pagar
                                </button>
                                <a href="carrito.php" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-left"></i> Volver al Carrito
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <small class="text-muted">
                                <i class="bi bi-shield-check"></i> Compra protegida
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const numeroInput = document.getElementById('numero_tarjeta');
            const fechaInput = document.getElementById('fecha_expiracion');
            const cvvInput = document.getElementById('cvv');
            const checkoutForm = document.getElementById('checkoutForm');
            const usingSavedHidden = document.getElementById('using_saved_card');
            const updateCardCheckbox = document.getElementById('update_card');

            function formatCardValue(value) {
                const digits = value.replace(/\D/g, '');
                return digits.match(/.{1,4}/g)?.join(' ') || digits;
            }

            function formatExpiryValue(value) {
                let digits = value.replace(/\D/g, '');
                if (digits.length === 0) return '';
                if (digits.length >= 2) {
                    digits = digits.substring(0,2) + '/' + digits.substring(2,4);
                }
                return digits;
            }

            // Inicializar formateo en carga
            document.addEventListener('DOMContentLoaded', function() {
                // Si el valor viene enmascarado (ej: **** **** **** 1234), no intentar validarlo como número completo
                const isSavedMasked = numeroInput.dataset.savedCard === '1' || usingSavedHidden.value === '1';

                if (!isSavedMasked && numeroInput.value.trim() !== '') {
                    numeroInput.value = formatCardValue(numeroInput.value);
                }

                if (fechaInput.value.trim() !== '') {
                    fechaInput.value = formatExpiryValue(fechaInput.value);
                }
            });

            // Input handlers
            numeroInput.addEventListener('input', function(e) {
                const isSavedMasked = numeroInput.dataset.savedCard === '1' || usingSavedHidden.value === '1';
                // If it was masked and user starts typing digits, switch to editing mode
                if (isSavedMasked && /\d/.test(e.target.value)) {
                    numeroInput.dataset.savedCard = '0';
                    usingSavedHidden.value = '0';
                    if (updateCardCheckbox) updateCardCheckbox.checked = true;
                }

                // Only format when the value contains digits (avoid mangling masked stars)
                if (!/\*/.test(e.target.value)) {
                    const cursorPos = e.target.selectionStart;
                    const beforeLen = e.target.value.length;
                    e.target.value = formatCardValue(e.target.value);
                    // Try to restore cursor position approximately
                    const afterLen = e.target.value.length;
                    const diff = afterLen - beforeLen;
                    e.target.selectionStart = e.target.selectionEnd = Math.max(0, cursorPos + diff);
                }
            });

            fechaInput.addEventListener('input', function(e) {
                e.target.value = formatExpiryValue(e.target.value);
            });

            cvvInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '');
            });

            // Submit validation
            checkoutForm.addEventListener('submit', function(e) {
                const usingSaved = usingSavedHidden.value === '1';
                const numeroRaw = numeroInput.value.trim();
                const numeroDigits = numeroRaw.replace(/\s/g, '');
                const fechaExp = fechaInput.value.trim();
                const cvv = cvvInput.value.trim();

                // If not using a saved masked card, validate number length
                if (!(usingSaved && /\*/.test(numeroRaw))) {
                    if (numeroDigits.length < 15 || numeroDigits.length > 16) {
                        e.preventDefault();
                        alert('El número de tarjeta debe tener 15 o 16 dígitos');
                        return false;
                    }
                }

                // Validar fecha de expiración
                if (!/^\d{2}\/\d{2}$/.test(fechaExp)) {
                    e.preventDefault();
                    alert('La fecha de expiración debe tener el formato MM/AA');
                    return false;
                }

                // Validar que la fecha no esté vencida
                const [mes, anio] = fechaExp.split('/').map(Number);
                const hoy = new Date();
                const mesActual = hoy.getMonth() + 1;
                const anioActual = hoy.getFullYear() % 100;

                if (anio < anioActual || (anio === anioActual && mes < mesActual)) {
                    e.preventDefault();
                    alert('La tarjeta está vencida');
                    return false;
                }

                // Validar CVV
                if (cvv.length < 3 || cvv.length > 4) {
                    e.preventDefault();
                    alert('El CVV debe tener 3 o 4 dígitos');
                    return false;
                }

                return confirm('¿Confirmas que deseas proceder con el pago de $' + <?php echo $cartTotal * 1.16; ?>.toFixed(2) + '?');
            });
        })();
    </script>
</body>
</html>
