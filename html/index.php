<?php
session_start();
$current_page = 'index';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Materiales - WigerConstruction</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

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
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar materiales...">
                    <button class="btn btn-primary" type="button">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select">
                    <option selected>Todas las categorías</option>
                    <option>Cemento y Concreto</option>
                    <option>Varilla y Acero</option>
                    <option>Tabique y Block</option>
                    <option>Herramientas</option>
                    <option>Eléctrico</option>
                    <option>Plomería</option>
                </select>
            </div>
        </div>

        <!-- Catálogo de Productos -->
        <div class="row">
            <?php
            // Productos de ejemplo
            $productos = [
                [
                    'id' => 1,
                    'nombre' => 'Cemento Gris 50kg',
                    'precio' => 185.00,
                    'imagen' => 'https://via.placeholder.com/300x200?text=Cemento+Gris',
                    'stock' => 150
                ],
                [
                    'id' => 2,
                    'nombre' => 'Varilla 3/8" x 6m',
                    'precio' => 95.50,
                    'imagen' => 'https://via.placeholder.com/300x200?text=Varilla',
                    'stock' => 80
                ],
                [
                    'id' => 3,
                    'nombre' => 'Block Hueco 15x20x40cm',
                    'precio' => 18.50,
                    'imagen' => 'https://via.placeholder.com/300x200?text=Block',
                    'stock' => 500
                ],
                [
                    'id' => 4,
                    'nombre' => 'Arena de Río m³',
                    'precio' => 450.00,
                    'imagen' => 'https://via.placeholder.com/300x200?text=Arena',
                    'stock' => 25
                ],
                [
                    'id' => 5,
                    'nombre' => 'Grava m³',
                    'precio' => 380.00,
                    'imagen' => 'https://via.placeholder.com/300x200?text=Grava',
                    'stock' => 30
                ],
                [
                    'id' => 6,
                    'nombre' => 'Tabique Rojo',
                    'precio' => 8.50,
                    'imagen' => 'https://via.placeholder.com/300x200?text=Tabique',
                    'stock' => 1000
                ]
            ];

            foreach ($productos as $producto) {
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $producto['imagen']; ?>" class="card-img-top" alt="<?php echo $producto['nombre']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $producto['nombre']; ?></h5>
                            <p class="card-text">
                                <span class="badge bg-success mb-2">En stock: <?php echo $producto['stock']; ?></span>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="text-primary mb-0">$<?php echo number_format($producto['precio'], 2); ?></h4>
                                <button class="btn btn-primary btn-sm">
                                    <i class="bi bi-cart-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
