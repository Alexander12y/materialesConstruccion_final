<?php
session_start();
require_once 'config/database.php';

$current_page = 'index';

// Obtener todas las categorías
$categorias = getAllCategories();

// Obtener todos los productos
$productos = getAllProducts();

// Iconos para cada categoría
$categoria_icons = [
    1 => 'bi-bricks',
    2 => 'bi-tools',
    3 => 'bi-paint-bucket',
    4 => 'bi-droplet',
    5 => 'bi-lightning-charge',
    6 => 'bi-hammer'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Materiales - WigerConstruction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <?php include 'components/mini_cart.php'; ?>

    <!-- Hero Section -->
    <div class="bg-light py-4">
        <div class="container">
            <h1 class="display-5 fw-bold">Catálogo de Materiales de Construcción</h1>
            <p class="lead">Encuentra todos los materiales que necesitas para tu proyecto</p>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-6 col-lg-5">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar materiales...">
                    <button class="btn btn-success" type="button" onclick="filterProducts()">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>
            <div class="col-md-4 col-lg-4">
                <select class="form-select" id="categoryFilter" onchange="filterProducts()">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['ID_Categoria']; ?>">
                            <?php echo htmlspecialchars($categoria['Nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-lg-3">
                <button class="btn btn-outline-secondary w-100" type="button" onclick="clearFilters()">
                    <i class="bi bi-x-circle"></i> Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <i class="bi bi-box-seam display-4 text-success"></i>
                        <h3 class="mt-2"><?php echo count($productos); ?></h3>
                        <p class="text-muted mb-0">Productos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <i class="bi bi-grid-3x3-gap display-4 text-success"></i>
                        <h3 class="mt-2"><?php echo count($categorias); ?></h3>
                        <p class="text-muted mb-0">Categorías</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <i class="bi bi-truck display-4 text-success"></i>
                        <h3 class="mt-2">24/7</h3>
                        <p class="text-muted mb-0">Disponibilidad</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <i class="bi bi-shield-check display-4 text-success"></i>
                        <h3 class="mt-2">100%</h3>
                        <p class="text-muted mb-0">Garantía</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catálogo de Productos -->
        <div class="row" id="productContainer">
            <?php foreach ($productos as $producto): 
                $categoria = null;
                foreach ($categorias as $cat) {
                    if ($cat['ID_Categoria'] == $producto['ID_Categoria_FK']) {
                        $categoria = $cat;
                        break;
                    }
                }
                $icon = isset($categoria_icons[$producto['ID_Categoria_FK']]) ? $categoria_icons[$producto['ID_Categoria_FK']] : 'bi-box';
            ?>
                <div class="col-md-4 col-lg-3 mb-4 product-item" 
                     data-category="<?php echo $producto['ID_Categoria_FK']; ?>"
                     data-name="<?php echo strtolower(htmlspecialchars($producto['Nombre'])); ?>">
                    <div class="card h-100">
                        <a href="producto.php?id=<?php echo $producto['ID_Producto']; ?>" class="text-decoration-none">
                            <div class="product-image-real">
                                <img src="public/products/<?php echo htmlspecialchars($producto['imagen'] ?? 'default-product.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($producto['Nombre']); ?>" 
                                     onerror="this.src='public/products/default-product.jpg'">
                            </div>
                        </a>
                        <div class="card-body">
                            <?php if ($categoria): ?>
                            <span class="badge bg-success mb-2">
                                <?php echo htmlspecialchars($categoria['Nombre']); ?>
                            </span>
                            <?php endif; ?>
                            <h5 class="card-title">
                                <a href="producto.php?id=<?php echo $producto['ID_Producto']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($producto['Nombre']); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(substr($producto['Descripcion'], 0, 60)); ?>...
                            </p>
                            <?php if ($producto['Fabricante']): ?>
                            <p class="small mb-2">
                                <i class="bi bi-building"></i> <?php echo htmlspecialchars($producto['Fabricante']); ?>
                            </p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="text-success mb-0">$<?php echo number_format($producto['Precio'], 2); ?></h4>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-box"></i> <?php echo $producto['Cantidad_Almacen']; ?>
                                </span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="producto.php?id=<?php echo $producto['ID_Producto']; ?>" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-eye"></i> Ver Detalle
                                </a>
                                <form action="cart_actions.php" method="POST" class="m-0">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $producto['ID_Producto']; ?>">
                                    <input type="hidden" name="cantidad" value="1">
                                    <input type="hidden" name="return_url" value="index.php">
                                    <button type="submit" class="btn btn-success w-100 btn-sm">
                                        <i class="bi bi-cart-plus"></i> Agregar al Carrito
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($productos)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h3 class="mt-3">No hay productos disponibles</h3>
                    <p class="text-muted">Vuelve más tarde para ver nuestro catálogo</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función de filtrado de productos
        function filterProducts() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const products = document.querySelectorAll('.product-item');
            
            let visibleCount = 0;
            
            products.forEach(product => {
                const productName = product.getAttribute('data-name');
                const productCategory = product.getAttribute('data-category');
                
                const matchesSearch = productName.includes(searchInput);
                const matchesCategory = !categoryFilter || productCategory === categoryFilter;
                
                if (matchesSearch && matchesCategory) {
                    product.style.display = 'block';
                    visibleCount++;
                } else {
                    product.style.display = 'none';
                }
            });
            
            // Mostrar mensaje si no hay resultados
            const container = document.getElementById('productContainer');
            let noResultsMsg = document.getElementById('noResultsMsg');
            
            if (visibleCount === 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'noResultsMsg';
                    noResultsMsg.className = 'col-12 text-center py-5';
                    noResultsMsg.innerHTML = `
                        <i class="bi bi-search display-1 text-muted"></i>
                        <h3 class="mt-3">No se encontraron productos</h3>
                        <p class="text-muted">Intenta con otros términos de búsqueda</p>
                    `;
                    container.appendChild(noResultsMsg);
                }
            } else {
                if (noResultsMsg) {
                    noResultsMsg.remove();
                }
            }
        }
        
        // Función para limpiar filtros
        function clearFilters() {
            // Limpiar input de búsqueda
            document.getElementById('searchInput').value = '';
            
            // Resetear select de categoría
            document.getElementById('categoryFilter').value = '';
            
            // Mostrar todos los productos
            const products = document.querySelectorAll('.product-item');
            products.forEach(product => {
                product.style.display = 'block';
            });
            
            // Eliminar mensaje de no resultados si existe
            const noResultsMsg = document.getElementById('noResultsMsg');
            if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
        
        // Permitir buscar con Enter
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterProducts();
            }
        });
    </script>
</body>
</html>
