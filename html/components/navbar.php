<!-- Navbar -->
<?php
// Detectar si estamos en la carpeta admin para ajustar las rutas
$in_admin = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$base_path = $in_admin ? '../' : '';
$admin_path = $in_admin ? '' : 'admin/';
$auth_path = $in_admin ? '../auth/' : 'auth/';

// Incluir funciones de base de datos para todos los usuarios
require_once __DIR__ . '/../config/database.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $base_path; ?>home.php">
            <i class="bi bi-building"></i> WigerConstruction
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>home.php">
                        <i class="bi bi-house-door"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>index.php">
                        <i class="bi bi-grid-3x3-gap"></i> Catálogo
                    </a>
                </li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Usuario autenticado -->
                    <?php
                    // Verificar si es administrador
                    $is_admin = isAdmin($_SESSION['user_id']);
                    ?>
                    
                    <?php if ($is_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'admin') ? 'active' : ''; ?>" href="<?php echo $admin_path; ?>index.php">
                            <i class="bi bi-speedometer2"></i> Panel Admin
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'perfil') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>perfil_usuario.php">
                            <i class="bi bi-person-circle"></i> Mi Perfil
                        </a>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link border-0 bg-transparent <?php echo ($current_page == 'carrito') ? 'active' : ''; ?>" 
                                type="button" 
                                data-bs-toggle="offcanvas" 
                                data-bs-target="#miniCartOffcanvas" 
                                aria-controls="miniCartOffcanvas">
                            <i class="bi bi-cart3"></i> Carrito 
                            <?php 
                            $cart_count = isset($_SESSION['user_id']) ? getCartCount($_SESSION['user_id']) : getGuestCartCount();
                            if ($cart_count > 0): 
                            ?>
                            <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-check"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($is_admin): ?>
                            <li><a class="dropdown-item" href="<?php echo $admin_path; ?>index.php"><i class="bi bi-speedometer2"></i> Panel Admin</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo $base_path; ?>perfil_usuario.php"><i class="bi bi-person"></i> Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $auth_path; ?>logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Usuario no autenticado -->
                    <li class="nav-item">
                        <button class="nav-link border-0 bg-transparent" 
                                type="button" 
                                data-bs-toggle="offcanvas" 
                                data-bs-target="#miniCartOffcanvas" 
                                aria-controls="miniCartOffcanvas"
                                title="Ver carrito">
                            <i class="bi bi-cart3"></i> Carrito
                            <?php 
                            $cart_count = getGuestCartCount();
                            if ($cart_count > 0): 
                            ?>
                            <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'login') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
