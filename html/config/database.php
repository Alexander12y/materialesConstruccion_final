<?php
// Configuración de conexión a la base de datos
define('DB_HOST', 'db');
define('DB_NAME', 'materiales_db');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root_password');
define('DB_CHARSET', 'utf8mb4');

/**
 * Obtener conexión a la base de datos
 * @return PDO|null Retorna objeto PDO o null si falla
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a BD: " . $e->getMessage());
        return null;
    }
}

/**
 * Verificar si un correo ya existe en la base de datos
 * @param string $email Correo electrónico a verificar
 * @return bool True si existe, False si no
 */
function emailExists($email) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Usuarios WHERE Correo_Electronico = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error al verificar email: " . $e->getMessage());
        return false;
    }
}

/**
 * Registrar un nuevo usuario en la base de datos
 * @param array $data Datos del usuario
 * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
 */
function registerUser($data) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
    }
    
    // Validar que el email no exista
    if (emailExists($data['email'])) {
        return ['success' => false, 'message' => 'Este correo electrónico ya está registrado'];
    }
    
    try {
        // Hash de la contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO Usuarios (Nombre_Usuario, Correo_Electronico, Contrasena, Fecha_Nacimiento, Direccion_Postal) 
                VALUES (:nombre, :email, :password, :fecha_nacimiento, :direccion)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'direccion' => $data['direccion'] ?? null
        ]);
        
        if ($result) {
            return [
                'success' => true, 
                'message' => 'Usuario registrado exitosamente',
                'user_id' => $conn->lastInsertId()
            ];
        }
        
        return ['success' => false, 'message' => 'Error al registrar usuario'];
    } catch (PDOException $e) {
        error_log("Error al registrar usuario: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al registrar usuario: ' . $e->getMessage()];
    }
}

/**
 * Autenticar usuario (login)
 * @param string $email Correo electrónico
 * @param string $password Contraseña
 * @return array ['success' => bool, 'message' => string, 'user' => array|null]
 */
function loginUser($email, $password) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM Usuarios WHERE Correo_Electronico = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // Verificar contraseña
        if (password_verify($password, $user['Contrasena'])) {
            // No devolver la contraseña
            unset($user['Contrasena']);
            return [
                'success' => true, 
                'message' => 'Login exitoso',
                'user' => $user
            ];
        }
        
        return ['success' => false, 'message' => 'Contraseña incorrecta'];
    } catch (PDOException $e) {
        error_log("Error al autenticar usuario: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al autenticar usuario'];
    }
}

/**
 * Obtener información de un usuario por ID
 * @param int $userId ID del usuario
 * @return array|null Datos del usuario o null
 */
function getUserById($userId) {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    try {
        $stmt = $conn->prepare("SELECT ID_Usuario, Nombre_Usuario, Correo_Electronico, Fecha_Nacimiento, Numero_Tarjeta_Bancaria, Direccion_Postal, id_rol FROM Usuarios WHERE ID_Usuario = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error al obtener usuario: " . $e->getMessage());
        return null;
    }
}

/**
 * Actualizar información de un usuario
 * @param int $userId ID del usuario
 * @param array $data Datos a actualizar
 * @return array ['success' => bool, 'message' => string]
 */
function updateUser($userId, $data) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
    }
    
    try {
        $sql = "UPDATE Usuarios SET 
                Nombre_Usuario = :nombre,
                Fecha_Nacimiento = :fecha_nacimiento,
                Direccion_Postal = :direccion
                WHERE ID_Usuario = :id";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'id' => $userId
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Perfil actualizado exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al actualizar perfil'];
    } catch (PDOException $e) {
        error_log("Error al actualizar usuario: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al actualizar perfil'];
    }
}

/**
 * Cambiar contraseña de un usuario
 * @param int $userId ID del usuario
 * @param string $currentPassword Contraseña actual
 * @param string $newPassword Nueva contraseña
 * @return array ['success' => bool, 'message' => string]
 */
function updatePassword($userId, $currentPassword, $newPassword) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
    }
    
    try {
        // Obtener la contraseña actual del usuario
        $stmt = $conn->prepare("SELECT Contrasena FROM Usuarios WHERE ID_Usuario = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // Verificar que la contraseña actual sea correcta
        if (!password_verify($currentPassword, $user['Contrasena'])) {
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
        }
        
        // Hash de la nueva contraseña
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        // Actualizar la contraseña
        $stmt = $conn->prepare("UPDATE Usuarios SET Contrasena = :password WHERE ID_Usuario = :id");
        $result = $stmt->execute([
            'password' => $hashedPassword,
            'id' => $userId
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al actualizar contraseña'];
    } catch (PDOException $e) {
        error_log("Error al cambiar contraseña: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al cambiar contraseña'];
    }
}

/**
 * Verificar si un usuario es administrador
 * @param int $userId ID del usuario
 * @return bool True si es admin, False si no
 */
function isAdmin($userId) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("SELECT id_rol FROM Usuarios WHERE ID_Usuario = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        
        return $user && $user['id_rol'] == 2;
    } catch (PDOException $e) {
        error_log("Error al verificar admin: " . $e->getMessage());
        return false;
    }
}

/**
 * Crear un nuevo usuario administrador
 * @param array $data Datos del administrador
 * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
 */
function createAdmin($data) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
    }
    
    // Validar que el email no exista
    if (emailExists($data['email'])) {
        return ['success' => false, 'message' => 'Este correo electrónico ya está registrado'];
    }
    
    try {
        // Hash de la contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO Usuarios (Nombre_Usuario, Correo_Electronico, Contrasena, id_rol) 
                VALUES (:nombre, :email, :password, 2)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password' => $hashedPassword
        ]);
        
        if ($result) {
            return [
                'success' => true, 
                'message' => 'Administrador creado exitosamente',
                'user_id' => $conn->lastInsertId()
            ];
        }
        
        return ['success' => false, 'message' => 'Error al crear administrador'];
    } catch (PDOException $e) {
        error_log("Error al crear administrador: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al crear administrador'];
    }
}

/**
 * Obtener todos los productos del inventario
 * @return array Array de productos
 */
function getAllProducts() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $stmt = $conn->query("SELECT * FROM Productos ORDER BY ID_Producto DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener productos: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener un producto por ID
 * @param int $productId ID del producto
 * @return array|null Datos del producto o null
 */
function getProductById($productId) {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM Productos WHERE ID_Producto = :id");
        $stmt->execute(['id' => $productId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error al obtener producto: " . $e->getMessage());
        return null;
    }
}

/**
 * Crear un nuevo producto
 * @param array $data Datos del producto
 * @return array ['success' => bool, 'message' => string]
 */
function createProduct($data) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
    }
    
    try {
        $sql = "INSERT INTO Productos (Nombre, Descripcion, Precio, Cantidad_Almacen, Fabricante, Origen, ID_Categoria_FK) 
                VALUES (:nombre, :descripcion, :precio, :cantidad, :fabricante, :origen, :categoria)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'cantidad' => $data['cantidad'],
            'fabricante' => $data['fabricante'] ?? null,
            'origen' => $data['origen'] ?? null,
            'categoria' => !empty($data['categoria']) ? $data['categoria'] : null
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Producto creado exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al crear producto'];
    } catch (PDOException $e) {
        error_log("Error al crear producto: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al crear producto'];
    }
}

/**
 * Actualizar un producto existente
 * @param int $productId ID del producto
 * @param array $data Datos a actualizar
 * @return array ['success' => bool, 'message' => string]
 */
function updateProduct($productId, $data) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
    }
    
    try {
        $sql = "UPDATE Productos SET 
                Nombre = :nombre,
                Descripcion = :descripcion,
                Precio = :precio,
                Cantidad_Almacen = :cantidad,
                Fabricante = :fabricante,
                Origen = :origen,
                ID_Categoria_FK = :categoria
                WHERE ID_Producto = :id";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'cantidad' => $data['cantidad'],
            'fabricante' => $data['fabricante'] ?? null,
            'origen' => $data['origen'] ?? null,
            'categoria' => !empty($data['categoria']) ? $data['categoria'] : null,
            'id' => $productId
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Producto actualizado exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al actualizar producto'];
    } catch (PDOException $e) {
        error_log("Error al actualizar producto: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al actualizar producto'];
    }
}

/**
 * Eliminar un producto
 * @param int $productId ID del producto
 * @return array ['success' => bool, 'message' => string]
 */
function deleteProduct($productId) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
    }
    
    try {
        // Verificar si el producto existe
        $stmt = $conn->prepare("SELECT Nombre FROM Productos WHERE ID_Producto = :id");
        $stmt->execute(['id' => $productId]);
        $producto = $stmt->fetch();
        
        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }
        
        // Eliminar el producto
        $stmt = $conn->prepare("DELETE FROM Productos WHERE ID_Producto = :id");
        $result = $stmt->execute(['id' => $productId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Producto eliminado exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al eliminar producto'];
    } catch (PDOException $e) {
        error_log("Error al eliminar producto: " . $e->getMessage());
        // Si hay error de foreign key constraint (producto tiene órdenes asociadas)
        if ($e->getCode() == '23000') {
            return ['success' => false, 'message' => 'No se puede eliminar el producto porque tiene órdenes asociadas'];
        }
        return ['success' => false, 'message' => 'Error al eliminar producto'];
    }
}

/**
 * Obtener todas las compras con información del usuario
 * @return array Array de compras
 */
function getAllPurchases() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $sql = "SELECT c.*, u.Nombre_Usuario, u.Correo_Electronico 
                FROM Compras c 
                INNER JOIN Usuarios u ON c.ID_Usuario = u.ID_Usuario 
                ORDER BY c.Fecha_Compra DESC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener compras: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener todas las categorías
 * @return array Array de categorías
 */
function getAllCategories() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $sql = "SELECT * FROM Categorias ORDER BY ID_Categoria ASC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener categorías: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener productos recomendados (primeros 6 productos)
 * @param int $limit Número de productos a obtener
 * @return array Array de productos
 */
function getFeaturedProducts($limit = 6) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $sql = "SELECT p.*, c.Nombre as Categoria_Nombre 
                FROM Productos p 
                LEFT JOIN Categorias c ON p.ID_Categoria_FK = c.ID_Categoria 
                WHERE p.Cantidad_Almacen > 0 
                ORDER BY p.ID_Producto ASC 
                LIMIT :limit";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener productos destacados: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener conteo de productos por categoría
 * @param int $categoryId ID de la categoría
 * @return int Número de productos
 */
function getProductCountByCategory($categoryId) {
    $conn = getDBConnection();
    if (!$conn) return 0;
    
    try {
        $sql = "SELECT COUNT(*) FROM Productos WHERE ID_Categoria_FK = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $categoryId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al contar productos: " . $e->getMessage());
        return 0;
    }
}

/* ========================================
   FUNCIONES DE CARRITO DE COMPRAS
   ======================================== */

/**
 * Agregar producto al carrito
 * @param int $userId ID del usuario
 * @param int $productId ID del producto
 * @param int $cantidad Cantidad a agregar
 * @return array ['success' => bool, 'message' => string]
 */
function addToCart($userId, $productId, $cantidad = 1) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión'];
    }
    
    try {
        // Verificar si el producto ya está en el carrito
        $stmt = $conn->prepare("SELECT ID_Carrito, Cantidad FROM Carrito_Compras 
                                WHERE ID_Usuario_FK = :user_id AND ID_Producto_FK = :product_id");
        $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Actualizar cantidad
            $newQuantity = $existing['Cantidad'] + $cantidad;
            $stmt = $conn->prepare("UPDATE Carrito_Compras SET Cantidad = :cantidad 
                                    WHERE ID_Carrito = :id");
            $stmt->execute(['cantidad' => $newQuantity, 'id' => $existing['ID_Carrito']]);
            return ['success' => true, 'message' => 'Cantidad actualizada en el carrito'];
        } else {
            // Insertar nuevo
            $stmt = $conn->prepare("INSERT INTO Carrito_Compras (ID_Usuario_FK, ID_Producto_FK, Cantidad) 
                                    VALUES (:user_id, :product_id, :cantidad)");
            $stmt->execute(['user_id' => $userId, 'product_id' => $productId, 'cantidad' => $cantidad]);
            return ['success' => true, 'message' => 'Producto agregado al carrito'];
        }
    } catch (PDOException $e) {
        error_log("Error al agregar al carrito: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al agregar al carrito'];
    }
}

/**
 * Obtener items del carrito del usuario
 * @param int $userId ID del usuario
 * @return array Array de productos en el carrito
 */
function getCartItems($userId) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $sql = "SELECT c.*, p.Nombre, p.Descripcion, p.Precio, p.Cantidad_Almacen, p.imagen,
                       cat.Nombre as Categoria_Nombre
                FROM Carrito_Compras c
                INNER JOIN Productos p ON c.ID_Producto_FK = p.ID_Producto
                LEFT JOIN Categorias cat ON p.ID_Categoria_FK = cat.ID_Categoria
                WHERE c.ID_Usuario_FK = :user_id
                ORDER BY c.ID_Carrito DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener carrito: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener cantidad de items en el carrito
 * @param int $userId ID del usuario
 * @return int Número de items
 */
function getCartCount($userId) {
    $conn = getDBConnection();
    if (!$conn) return 0;
    
    try {
        $stmt = $conn->prepare("SELECT SUM(Cantidad) FROM Carrito_Compras WHERE ID_Usuario_FK = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al contar items: " . $e->getMessage());
        return 0;
    }
}

/**
 * Actualizar cantidad de un item del carrito
 * @param int $cartId ID del carrito
 * @param int $cantidad Nueva cantidad
 * @return bool True si se actualizó correctamente
 */
function updateCartItemQuantity($cartId, $cantidad) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        if ($cantidad <= 0) {
            return removeFromCart($cartId);
        }
        
        $stmt = $conn->prepare("UPDATE Carrito_Compras SET Cantidad = :cantidad WHERE ID_Carrito = :id");
        $stmt->execute(['cantidad' => $cantidad, 'id' => $cartId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al actualizar cantidad: " . $e->getMessage());
        return false;
    }
}

/**
 * Eliminar item del carrito
 * @param int $cartId ID del carrito
 * @return bool True si se eliminó correctamente
 */
function removeFromCart($cartId) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("DELETE FROM Carrito_Compras WHERE ID_Carrito = :id");
        $stmt->execute(['id' => $cartId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al eliminar del carrito: " . $e->getMessage());
        return false;
    }
}

/**
 * Vaciar carrito del usuario
 * @param int $userId ID del usuario
 * @return bool True si se vació correctamente
 */
function clearCart($userId) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("DELETE FROM Carrito_Compras WHERE ID_Usuario_FK = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al vaciar carrito: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener total del carrito
 * @param int $userId ID del usuario
 * @return float Total del carrito
 */
function getCartTotal($userId) {
    $conn = getDBConnection();
    if (!$conn) return 0;
    
    try {
        $sql = "SELECT SUM(c.Cantidad * p.Precio) as total
                FROM Carrito_Compras c
                INNER JOIN Productos p ON c.ID_Producto_FK = p.ID_Producto
                WHERE c.ID_Usuario_FK = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return (float)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al calcular total: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtener items del carrito de invitado desde la sesión
 * @return array Items del carrito con información completa
 */
function getGuestCartItems() {
    if (!isset($_SESSION['guest_cart']) || empty($_SESSION['guest_cart'])) {
        return [];
    }
    
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $productIds = array_keys($_SESSION['guest_cart']);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    
    try {
        $sql = "SELECT p.*, cat.Nombre as Categoria_Nombre
                FROM Productos p
                LEFT JOIN Categorias cat ON p.ID_Categoria_FK = cat.ID_Categoria
                WHERE p.ID_Producto IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($productIds);
        $products = $stmt->fetchAll();
        
        // Combinar con cantidades de la sesión
        foreach ($products as &$product) {
            $product['Cantidad'] = $_SESSION['guest_cart'][$product['ID_Producto']];
            $product['ID_Carrito'] = 'guest_' . $product['ID_Producto']; // ID ficticio para el frontend
        }
        
        return $products;
    } catch (PDOException $e) {
        error_log("Error al obtener carrito de invitado: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener total del carrito de invitado
 * @return float Total del carrito
 */
function getGuestCartTotal() {
    $items = getGuestCartItems();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['Precio'] * $item['Cantidad'];
    }
    return $total;
}

/**
 * Obtener cantidad de items en el carrito de invitado
 * @return int Número de items
 */
function getGuestCartCount() {
    if (!isset($_SESSION['guest_cart'])) {
        return 0;
    }
    return array_sum($_SESSION['guest_cart']);
}

/* ========================================
   FUNCIONES DE ÓRDENES Y CHECKOUT
   ======================================== */

/**
 * Crear una nueva orden con sus detalles
 * @param int $userId ID del usuario
 * @param float $total Total de la orden
 * @param string $direccionEnvio Dirección de envío
 * @param array $cartItems Items del carrito
 * @return array ['success' => bool, 'message' => string, 'order_id' => int|null]
 */
function createOrder($userId, $total, $direccionEnvio, $cartItems) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
    }
    
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        // 1. Crear la orden
        $sql = "INSERT INTO Ordenes (ID_Usuario_FK, Total_Orden, Estado_Orden, Direccion_Envio_Snapshot) 
                VALUES (:user_id, :total, 'Procesando', :direccion)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'total' => $total,
            'direccion' => $direccionEnvio
        ]);
        
        $orderId = $conn->lastInsertId();
        
        // 2. Insertar detalles de la orden y actualizar inventario
        foreach ($cartItems as $item) {
            $productId = isset($item['ID_Producto_FK']) ? $item['ID_Producto_FK'] : $item['ID_Producto'];
            $cantidad = $item['Cantidad'];
            $precioUnitario = $item['Precio'];
            $subtotal = $cantidad * $precioUnitario;
            
            // Verificar stock disponible
            $checkStock = $conn->prepare("SELECT Cantidad_Almacen FROM Productos WHERE ID_Producto = :id");
            $checkStock->execute(['id' => $productId]);
            $stock = $checkStock->fetchColumn();
            
            if ($stock < $cantidad) {
                // Rollback si no hay suficiente stock
                $conn->rollBack();
                return ['success' => false, 'message' => 'Stock insuficiente para algunos productos'];
            }
            
            // Insertar detalle de la orden
            $sqlDetalle = "INSERT INTO Ordenes_Detalles (ID_Orden_FK, ID_Producto_FK, Cantidad, Precio_Unitario_Snapshot, Subtotal_Linea) 
                          VALUES (:orden_id, :producto_id, :cantidad, :precio, :subtotal)";
            $stmtDetalle = $conn->prepare($sqlDetalle);
            $stmtDetalle->execute([
                'orden_id' => $orderId,
                'producto_id' => $productId,
                'cantidad' => $cantidad,
                'precio' => $precioUnitario,
                'subtotal' => $subtotal
            ]);
            
            // Actualizar inventario
            $sqlUpdate = "UPDATE Productos SET Cantidad_Almacen = Cantidad_Almacen - :cantidad WHERE ID_Producto = :id";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'cantidad' => $cantidad,
                'id' => $productId
            ]);
        }
        
        // 3. Vaciar el carrito del usuario
        clearCart($userId);
        
        // Confirmar transacción
        $conn->commit();
        
        return [
            'success' => true, 
            'message' => 'Orden creada exitosamente',
            'order_id' => $orderId
        ];
    } catch (PDOException $e) {
        // Rollback en caso de error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error al crear orden: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al procesar la orden: ' . $e->getMessage()];
    }
}

/**
 * Obtener todas las órdenes de un usuario
 * @param int $userId ID del usuario
 * @return array Array de órdenes
 */
function getUserOrders($userId) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $sql = "SELECT * FROM Ordenes WHERE ID_Usuario_FK = :user_id ORDER BY Fecha_Orden DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener órdenes del usuario: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener detalles de una orden específica
 * @param int $orderId ID de la orden
 * @return array|null Datos de la orden o null
 */
function getOrderById($orderId) {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    try {
        $sql = "SELECT o.*, u.Nombre_Usuario, u.Correo_Electronico 
                FROM Ordenes o
                INNER JOIN Usuarios u ON o.ID_Usuario_FK = u.ID_Usuario
                WHERE o.ID_Orden = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error al obtener orden: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtener detalles de productos de una orden
 * @param int $orderId ID de la orden
 * @return array Array de productos de la orden
 */
function getOrderDetails($orderId) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $sql = "SELECT od.*, p.Nombre, p.imagen 
                FROM Ordenes_Detalles od
                INNER JOIN Productos p ON od.ID_Producto_FK = p.ID_Producto
                WHERE od.ID_Orden_FK = :orden_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['orden_id' => $orderId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener detalles de orden: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener todas las órdenes (para admin)
 * @return array Array de todas las órdenes
 */
function getAllOrders() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $sql = "SELECT o.*, u.Nombre_Usuario, u.Correo_Electronico 
                FROM Ordenes o
                INNER JOIN Usuarios u ON o.ID_Usuario_FK = u.ID_Usuario
                ORDER BY o.Fecha_Orden DESC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error al obtener todas las órdenes: " . $e->getMessage());
        return [];
    }
}

/**
 * Actualizar estado de una orden
 * @param int $orderId ID de la orden
 * @param string $estado Nuevo estado
 * @return bool True si se actualizó correctamente
 */
function updateOrderStatus($orderId, $estado) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("UPDATE Ordenes SET Estado_Orden = :estado WHERE ID_Orden = :id");
        $stmt->execute(['estado' => $estado, 'id' => $orderId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al actualizar estado de orden: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar tarjeta bancaria del usuario
 * @param int $userId ID del usuario
 * @param string $numeroTarjeta Número de tarjeta (encriptado)
 * @return bool True si se actualizó correctamente
 */
function updateUserCard($userId, $numeroTarjeta) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("UPDATE Usuarios SET Numero_Tarjeta_Bancaria = :tarjeta WHERE ID_Usuario = :id");
        $stmt->execute(['tarjeta' => $numeroTarjeta, 'id' => $userId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al actualizar tarjeta: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar dirección postal del usuario
 * @param int $userId ID del usuario
 * @param string $direccion Nueva dirección
 * @return bool True si se actualizó correctamente
 */
function updateUserAddress($userId, $direccion) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("UPDATE Usuarios SET Direccion_Postal = :direccion WHERE ID_Usuario = :id");
        $stmt->execute(['direccion' => $direccion, 'id' => $userId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al actualizar dirección: " . $e->getMessage());
        return false;
    }
}
?>
