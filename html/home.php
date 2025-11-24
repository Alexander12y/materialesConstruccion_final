<?php
session_start();
require_once 'config/database.php';

$current_page = 'home';

// Obtener categorías
$categorias = getAllCategories();

// Obtener productos recomendados
$productos_recomendados = getFeaturedProducts(6);

// Iconos para cada categoría
$categoria_icons = [
    1 => 'bi-bricks',           // Obra Gris
    2 => 'bi-tools',            // Aceros y Metales
    3 => 'bi-paint-bucket',     // Acabados y Pisos
    4 => 'bi-droplet',          // Plomería y Tubería
    5 => 'bi-lightning-charge', // Material Eléctrico
    6 => 'bi-hammer'            // Herramientas y Equipo
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WigerConstruction - Materiales de Construcción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <?php include 'components/mini_cart.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="hero-title">
                        Materiales para el futuro...
                    </h1>
                    <p class="hero-subtitle">
                        Calidad profesional, precios competitivos y servicio excepcional. 
                        Todo lo que necesitas para tu proyecto en un solo lugar.
                    </p>
                    <div class="mt-4">
                        <a href="#categorias" class="btn btn-light btn-lg hero-btn me-3">
                            <i class="bi bi-grid-3x3-gap"></i> Ver Categorías
                        </a>
                        <a href="#productos" class="btn btn-outline-light btn-lg hero-btn">
                            <i class="bi bi-box-seam"></i> Productos Destacados
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorías Section -->
    <section id="categorias" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Nuestras Categorías</h2>
                <p class="section-subtitle">Explora nuestra amplia gama de materiales de construcción</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($categorias as $categoria): 
                    $icon = $categoria_icons[$categoria['ID_Categoria']] ?? 'bi-box';
                    $productoCount = getProductCountByCategory($categoria['ID_Categoria']);
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card category-card">
                        <div class="card-body text-center p-4">
                            <i class="bi <?php echo $icon; ?> category-icon"></i>
                            <h5 class="card-title mb-2"><?php echo htmlspecialchars($categoria['Nombre']); ?></h5>
                            <p class="card-text mb-3"><?php echo htmlspecialchars($categoria['Descripcion']); ?></p>
                            <span class="category-badge">
                                <i class="bi bi-box-seam"></i> <?php echo $productoCount; ?> productos
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Productos Recomendados Section -->
    <section id="productos" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Productos Recomendados</h2>
                <p class="section-subtitle">Los materiales más populares y de mejor calidad</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($productos_recomendados as $producto): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card">
                        <a href="producto.php?id=<?php echo $producto['ID_Producto']; ?>" class="text-decoration-none">
                            <div class="product-image-real">
                                <img src="public/products/<?php echo htmlspecialchars($producto['imagen'] ?? 'default-product.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($producto['Nombre']); ?>" 
                                     onerror="this.src='public/products/default-product.jpg'">
                            </div>
                        </a>
                        <div class="card-body">
                            <span class="badge bg-success mb-2">
                                <?php echo htmlspecialchars($producto['Categoria_Nombre'] ?? 'General'); ?>
                            </span>
                            <h5 class="card-title">
                                <a href="producto.php?id=<?php echo $producto['ID_Producto']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($producto['Nombre']); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small mb-3">
                                <?php echo htmlspecialchars(substr($producto['Descripcion'], 0, 80)); ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="product-price">$<?php echo number_format($producto['Precio'], 2); ?></span>
                                <span class="product-stock">
                                    <i class="bi bi-box"></i> <?php echo $producto['Cantidad_Almacen']; ?> disponibles
                                </span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="producto.php?id=<?php echo $producto['ID_Producto']; ?>" class="btn btn-outline-success btn-add-cart">
                                    <i class="bi bi-eye"></i> Ver Detalle
                                </a>
                                <form action="cart_actions.php" method="POST" class="m-0">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $producto['ID_Producto']; ?>">
                                    <input type="hidden" name="cantidad" value="1">
                                    <input type="hidden" name="return_url" value="home.php">
                                    <button type="submit" class="btn btn-success w-100 btn-add-cart">
                                        <i class="bi bi-cart-plus"></i> Agregar al Carrito
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="index.php" class="btn btn-outline-success btn-lg">
                    <i class="bi bi-grid-3x3-gap"></i> Ver Catálogo Completo
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-success text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-3"><i class="bi bi-building"></i> ¿Listo para tu próximo proyecto?</h3>
                    <p class="mb-0">Regístrate hoy y obtén acceso a precios exclusivos y promociones especiales.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="registro.php" class="btn btn-light btn-lg">
                            <i class="bi bi-person-plus"></i> Registrarse Ahora
                        </a>
                    <?php else: ?>
                        <a href="perfil_usuario.php" class="btn btn-light btn-lg">
                            <i class="bi bi-person-circle"></i> Mi Perfil
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll para los enlaces ancla
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animación de entrada para las cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        entry.target.style.transition = 'all 0.6s ease';
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.category-card, .product-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>
