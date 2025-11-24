-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Nov 23, 2025 at 04:52 PM
-- Server version: 8.1.0
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `materiales_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `Carrito_Compras`
--

CREATE TABLE `Carrito_Compras` (
  `ID_Carrito` int NOT NULL,
  `ID_Usuario_FK` int NOT NULL,
  `ID_Producto_FK` int NOT NULL,
  `Cantidad` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Categorias`
--

CREATE TABLE `Categorias` (
  `ID_Categoria` int NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Categorias`
--

INSERT INTO `Categorias` (`ID_Categoria`, `Nombre`, `Descripcion`) VALUES
(1, 'Obra Gris', 'Materiales básicos para cimentación y muros (cemento, arena, cal).'),
(2, 'Aceros y Metales', 'Varillas, mallas, vigas y alambres para estructura.'),
(3, 'Acabados y Pisos', 'Pinturas, estucos, pisos cerámicos y recubrimientos.'),
(4, 'Plomería y Tubería', 'Tubos PVC, CPVC, cobre, conexiones y tinacos.'),
(5, 'Material Eléctrico', 'Cables, apagadores, centros de carga y conduit.'),
(6, 'Herramientas y Equipo', 'Herramienta manual y eléctrica para construcción.');

-- --------------------------------------------------------

--
-- Table structure for table `Ordenes`
--

CREATE TABLE `Ordenes` (
  `ID_Orden` int NOT NULL,
  `ID_Usuario_FK` int NOT NULL,
  `Fecha_Orden` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Total_Orden` decimal(10,2) NOT NULL,
  `Estado_Orden` varchar(50) NOT NULL DEFAULT 'Procesando',
  `Direccion_Envio_Snapshot` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Ordenes_Detalles`
--

CREATE TABLE `Ordenes_Detalles` (
  `ID_Detalle` int NOT NULL,
  `ID_Orden_FK` int NOT NULL,
  `ID_Producto_FK` int NOT NULL,
  `Cantidad` int NOT NULL,
  `Precio_Unitario_Snapshot` decimal(10,2) NOT NULL,
  `Subtotal_Linea` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Productos`
--

CREATE TABLE `Productos` (
  `ID_Producto` int NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Descripcion` text,
  `imagen` varchar(255) DEFAULT 'default-product.jpg',
  `Fotos` longblob,
  `Precio` decimal(10,2) NOT NULL,
  `Cantidad_Almacen` int NOT NULL DEFAULT '0',
  `Fabricante` varchar(150) DEFAULT NULL,
  `Origen` varchar(100) DEFAULT NULL,
  `ID_Categoria_FK` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Productos`
--

INSERT INTO `Productos` (`ID_Producto`, `Nombre`, `Descripcion`, `imagen`, `Fotos`, `Precio`, `Cantidad_Almacen`, `Fabricante`, `Origen`, `ID_Categoria_FK`) VALUES
(1, 'Cemento Gris Tolteca 50kg', 'Cemento Portland compuesto para uso general.', 'cemento_gris.jpeg', NULL, 230.00, 500, 'Cemex', 'Nacional', 1),
(2, 'Mortero Moctezuma 50kg', 'Ideal para pegado de block y ladrillo.', 'mortero.jpg', NULL, 185.50, 400, 'Moctezuma', 'Nacional', 1),
(3, 'Calidra 25kg', 'Cal hidratada para mezclas de albañilería.', 'calidra.jpg', NULL, 65.00, 300, 'Calidra', 'Nacional', 1),
(4, 'Yeso Supremo 40kg', 'Yeso de construcción para aplanados finos.', 'yeso_supremo.jpg', NULL, 95.00, 150, 'Yesera Monterrey', 'Nacional', 1),
(5, 'Adhesivo para Pisos (Pegaazulejo)', 'Saco de 20kg adhesivo estándar gris.', 'pegaazulejo.jpg', NULL, 110.00, 200, 'Niasa', 'Nacional', 1),
(6, 'Varilla Corrugada 3/8', 'Varilla grado 42 de 12 metros.', 'varilla_corrugada_28.jpg', NULL, 145.00, 1000, 'Sicartsa', 'Nacional', 2),
(7, 'Castillo Electrosoldado 15x15-4', 'Armex para castillos, tramo de 6m.', 'castillo_electrosoldado.jpg', NULL, 190.00, 300, 'Deacero', 'Nacional', 2),
(8, 'Alambre Recocido (Kg)', 'Alambre para amarrar varilla.', 'alambre_recocido.jpg', NULL, 28.00, 500, 'Deacero', 'Nacional', 2),
(9, 'Malla Electrosoldada 6-6/10-10', 'Rollo de malla para losas de concreto.', 'malla_electrosoldada_6x6_10x10.jpg', NULL, 2500.00, 50, 'Deacero', 'Nacional', 2),
(10, 'Clavo para Concreto 2.5\"', 'Caja de clavos de acero galvanizado 1kg.', 'clavo_para_concreto_2.5.jpg', NULL, 45.00, 100, 'Fiero', 'Importado', 2),
(11, 'Pintura Vinílica Blanca 19L', 'Cubeta de pintura lavable acabado mate.', 'pintura_vinilica_blanca.jpg', NULL, 1200.00, 60, 'Comex', 'Nacional', 3),
(12, 'Impermeabilizante Rojo 3 Años', 'Cubeta 19L acrílico fibratado.', 'impermeabilizante_rojo.jpg', NULL, 980.00, 40, 'Sika', 'Importado', 3),
(13, 'Piso Cerámico Beige 60x60', 'Caja con 1.44m2, modelo Sahara.', 'piso_ceramico_beige.jpg', NULL, 280.00, 120, 'Interceramic', 'Nacional', 3),
(14, 'Boquilla Sin Arena Chocolate', 'Caja 5kg para juntas menores a 3mm.', 'boquilla_sin_arena_chocolate.jpg', NULL, 85.00, 80, 'Crest', 'Nacional', 3),
(15, 'Pasta Texturizada', 'Cubeta de pasta para acabados decorativos.', 'pasta_texturizada.jpg', NULL, 550.00, 30, 'Corev', 'Nacional', 3),
(16, 'Tubo PVC Sanitario 4\"', 'Tramo de 6 metros para drenaje.', 'tubo_pvc_sanitario_4.jpg', NULL, 210.00, 150, 'Amanco', 'Nacional', 4),
(17, 'Tinaco Tricapa 1100L', 'Tinaco beige con accesorios incluidos.', 'tinaco_tricapa_1100.jpg', NULL, 2300.00, 20, 'Rotoplas', 'Nacional', 4),
(18, 'Codo Cobre 90 grados 1/2\"', 'Conexión soldable para agua.', 'codo_cobre_90_12.jpg', NULL, 15.00, 500, 'IUSA', 'Nacional', 4),
(19, 'Juego de Herrajes WC', 'Válvula de admisión y descarga universal.', 'juego_de_herrajes_wc.jpg', NULL, 180.00, 60, 'Coflex', 'Nacional', 4),
(20, 'Tubo CPVC 1/2\"', 'Tramo de 6m para agua caliente/fría.', 'tubo_cpvc_12.jpg', NULL, 85.00, 200, 'Flowguard', 'Importado', 4),
(21, 'Cable THW Calibre 12 Rojo', 'Caja con 100 metros de cable.', 'cable_thw_calibre_12.jpg', NULL, 1150.00, 50, 'Condumex', 'Nacional', 5),
(22, 'Contacto Doble Polarizado', 'Placa blanca con dos contactos.', 'contacto_doble_polarizado.jpg', NULL, 65.00, 200, 'Bticino', 'Nacional', 5),
(23, 'Apagador Sencillo', 'Placa blanca con un apagador.', 'apagador_sencillo.jpeg', NULL, 55.00, 200, 'Bticino', 'Nacional', 5),
(24, 'Centro de Carga 2 Polos', 'Caja metálica para pastillas termomagnéticas.', 'centro_de_carga_2_polos.jpg', NULL, 250.00, 40, 'Square D', 'Importado', 5),
(25, 'Manguera Corrugada 1/2\"', 'Rollo de 50 metros flexible.', 'manguera_corrugada_1 2.jpg', NULL, 220.00, 80, 'Poliflex', 'Nacional', 5),
(26, 'Pala Cuadrada Puño Y', 'Pala de acero templado mango de madera.', 'pala_cuadrada_puno_y.jpg', NULL, 180.00, 50, 'Truper', 'Nacional', 6),
(27, 'Carretilla 5.5ft3', 'Carretilla con llanta neumática reforzada.', 'carretilla.jpg', NULL, 1450.00, 25, 'Truper', 'Nacional', 6),
(28, 'Taladro Rotomartillo 1/2\"', 'Taladro 650W velocidad variable.', 'taladro_rotomartillo.jpg', NULL, 1200.00, 15, 'Bosch', 'Importado', 6),
(29, 'Nivel de Mano 24\"', 'Nivel de aluminio 3 gotas.', 'nivel_de_mano.jpg', NULL, 140.00, 40, 'Stanley', 'Importado', 6),
(30, 'Cuchara de Albañil 10\"', 'Cuchara forjada tipo Filadelfia.', 'cuchara_albanil.jpg', NULL, 110.00, 60, 'Bellota', 'Importado', 6);

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--

CREATE TABLE `Roles` (
  `id_rol` int NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Roles`
--

INSERT INTO `Roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Cliente'),
(2, 'Administrador');

-- --------------------------------------------------------

--
-- Table structure for table `Usuarios`
--

CREATE TABLE `Usuarios` (
  `ID_Usuario` int NOT NULL,
  `Nombre_Usuario` varchar(100) NOT NULL,
  `Correo_Electronico` varchar(255) NOT NULL,
  `Contrasena` varchar(255) NOT NULL,
  `Fecha_Nacimiento` date DEFAULT NULL,
  `Numero_Tarjeta_Bancaria` varchar(255) DEFAULT NULL,
  `Direccion_Postal` text,
  `id_rol` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Usuarios`
--

INSERT INTO `Usuarios` (`ID_Usuario`, `Nombre_Usuario`, `Correo_Electronico`, `Contrasena`, `Fecha_Nacimiento`, `Numero_Tarjeta_Bancaria`, `Direccion_Postal`, `id_rol`) VALUES
(1, 'Alexander Clempner', 'alexanderclempner@gmail.com', '$2y$10$TsiP1wLqemDYAlSYDBuYUOf/PlT21RJjK5cgpD2IjJocIOFtZMnCi', '2004-08-24', NULL, 'Bosques', 1),
(3, 'Admin Principal', 'admin@wigerconstruction.com', '$2y$10$MySD8qNSMcv7.4gdIqPa.eh7eFvk9x5RgRYbfCB/POPgDWWQW0L5y', '1990-01-01', NULL, 'Oficina Principal', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Carrito_Compras`
--
ALTER TABLE `Carrito_Compras`
  ADD PRIMARY KEY (`ID_Carrito`),
  ADD UNIQUE KEY `ID_Usuario_FK` (`ID_Usuario_FK`,`ID_Producto_FK`),
  ADD KEY `ID_Producto_FK` (`ID_Producto_FK`);

--
-- Indexes for table `Categorias`
--
ALTER TABLE `Categorias`
  ADD PRIMARY KEY (`ID_Categoria`);

--
-- Indexes for table `Ordenes`
--
ALTER TABLE `Ordenes`
  ADD PRIMARY KEY (`ID_Orden`),
  ADD KEY `ID_Usuario_FK` (`ID_Usuario_FK`);

--
-- Indexes for table `Ordenes_Detalles`
--
ALTER TABLE `Ordenes_Detalles`
  ADD PRIMARY KEY (`ID_Detalle`),
  ADD KEY `ID_Orden_FK` (`ID_Orden_FK`),
  ADD KEY `ID_Producto_FK` (`ID_Producto_FK`);

--
-- Indexes for table `Productos`
--
ALTER TABLE `Productos`
  ADD PRIMARY KEY (`ID_Producto`),
  ADD KEY `fk_producto_categoria` (`ID_Categoria_FK`);

--
-- Indexes for table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indexes for table `Usuarios`
--
ALTER TABLE `Usuarios`
  ADD PRIMARY KEY (`ID_Usuario`),
  ADD UNIQUE KEY `Correo_Electronico` (`Correo_Electronico`),
  ADD KEY `fk_id_rol` (`id_rol`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Carrito_Compras`
--
ALTER TABLE `Carrito_Compras`
  MODIFY `ID_Carrito` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Categorias`
--
ALTER TABLE `Categorias`
  MODIFY `ID_Categoria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Ordenes`
--
ALTER TABLE `Ordenes`
  MODIFY `ID_Orden` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Ordenes_Detalles`
--
ALTER TABLE `Ordenes_Detalles`
  MODIFY `ID_Detalle` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Productos`
--
ALTER TABLE `Productos`
  MODIFY `ID_Producto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `id_rol` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Usuarios`
--
ALTER TABLE `Usuarios`
  MODIFY `ID_Usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Carrito_Compras`
--
ALTER TABLE `Carrito_Compras`
  ADD CONSTRAINT `Carrito_Compras_ibfk_1` FOREIGN KEY (`ID_Usuario_FK`) REFERENCES `Usuarios` (`ID_Usuario`),
  ADD CONSTRAINT `Carrito_Compras_ibfk_2` FOREIGN KEY (`ID_Producto_FK`) REFERENCES `Productos` (`ID_Producto`);

--
-- Constraints for table `Ordenes`
--
ALTER TABLE `Ordenes`
  ADD CONSTRAINT `Ordenes_ibfk_1` FOREIGN KEY (`ID_Usuario_FK`) REFERENCES `Usuarios` (`ID_Usuario`);

--
-- Constraints for table `Ordenes_Detalles`
--
ALTER TABLE `Ordenes_Detalles`
  ADD CONSTRAINT `Ordenes_Detalles_ibfk_1` FOREIGN KEY (`ID_Orden_FK`) REFERENCES `Ordenes` (`ID_Orden`),
  ADD CONSTRAINT `Ordenes_Detalles_ibfk_2` FOREIGN KEY (`ID_Producto_FK`) REFERENCES `Productos` (`ID_Producto`);

--
-- Constraints for table `Productos`
--
ALTER TABLE `Productos`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`ID_Categoria_FK`) REFERENCES `Categorias` (`ID_Categoria`);

--
-- Constraints for table `Usuarios`
--
ALTER TABLE `Usuarios`
  ADD CONSTRAINT `fk_id_rol` FOREIGN KEY (`id_rol`) REFERENCES `Roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
