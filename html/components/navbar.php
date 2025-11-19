<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-building"></i> WigerConstruction
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index') ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-house-door"></i> Catálogo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'perfil') ? 'active' : ''; ?>" href="perfil_usuario.php">
                        <i class="bi bi-person-circle"></i> Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-cart3"></i> Carrito <span class="badge bg-danger">0</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
