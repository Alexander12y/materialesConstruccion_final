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
        $sql = "INSERT INTO Productos (Nombre_Producto, Descripcion, Precio, Cantidad_Disponible, Categoria) 
                VALUES (:nombre, :descripcion, :precio, :cantidad, :categoria)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'cantidad' => $data['cantidad'],
            'categoria' => $data['categoria'] ?? null
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
                Nombre_Producto = :nombre,
                Descripcion = :descripcion,
                Precio = :precio,
                Cantidad_Disponible = :cantidad,
                Categoria = :categoria
                WHERE ID_Producto = :id";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'cantidad' => $data['cantidad'],
            'categoria' => $data['categoria'] ?? null,
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
?>
