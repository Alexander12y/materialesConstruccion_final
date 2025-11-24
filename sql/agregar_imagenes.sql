-- ==================================================
-- Script para agregar columna de imagenes y asignar
-- las imagenes correspondientes a cada producto
-- ==================================================

USE materiales_db;

-- 1. Agregar columna 'imagen' a la tabla Productos
ALTER TABLE Productos 
ADD COLUMN imagen VARCHAR(255) DEFAULT 'default-product.jpg' AFTER Descripcion;

-- 2. Actualizar cada producto con su imagen correspondiente

-- Obra Gris (Categoría 1)
UPDATE Productos SET imagen = 'cemento_gris.jpeg' WHERE ID_Producto = 1;  -- Cemento Gris Tolteca
UPDATE Productos SET imagen = 'mortero.jpg' WHERE ID_Producto = 2;        -- Mortero Moctezuma
UPDATE Productos SET imagen = 'calidra.jpg' WHERE ID_Producto = 3;        -- Calidra
UPDATE Productos SET imagen = 'yeso_supremo.jpg' WHERE ID_Producto = 4;   -- Yeso Supremo
UPDATE Productos SET imagen = 'pegaazulejo.jpg' WHERE ID_Producto = 5;    -- Adhesivo para Pisos

-- Aceros y Metales (Categoría 2)
UPDATE Productos SET imagen = 'varilla_corrugada_28.jpg' WHERE ID_Producto = 6;       -- Varilla Corrugada 3/8
UPDATE Productos SET imagen = 'castillo_electrosoldado.jpg' WHERE ID_Producto = 7;    -- Castillo Electrosoldado
UPDATE Productos SET imagen = 'alambre_recocido.jpg' WHERE ID_Producto = 8;           -- Alambre Recocido
UPDATE Productos SET imagen = 'malla_electrosoldada_6x6_10x10.jpg' WHERE ID_Producto = 9;  -- Malla Electrosoldada
UPDATE Productos SET imagen = 'clavo_para_concreto_2.5.jpg' WHERE ID_Producto = 10;   -- Clavo para Concreto

-- Acabados y Pisos (Categoría 3)
UPDATE Productos SET imagen = 'pintura_vinilica_blanca.jpg' WHERE ID_Producto = 11;   -- Pintura Vinílica
UPDATE Productos SET imagen = 'impermeabilizante_rojo.jpg' WHERE ID_Producto = 12;    -- Impermeabilizante
UPDATE Productos SET imagen = 'piso_ceramico_beige.jpg' WHERE ID_Producto = 13;       -- Piso Cerámico
UPDATE Productos SET imagen = 'boquilla_sin_arena_chocolate.jpg' WHERE ID_Producto = 14;  -- Boquilla Sin Arena
UPDATE Productos SET imagen = 'pasta_texturizada.jpg' WHERE ID_Producto = 15;         -- Pasta Texturizada

-- Plomería y Tubería (Categoría 4)
UPDATE Productos SET imagen = 'tubo_pvc_sanitario_4.jpg' WHERE ID_Producto = 16;      -- Tubo PVC Sanitario
UPDATE Productos SET imagen = 'tinaco_tricapa_1100.jpg' WHERE ID_Producto = 17;       -- Tinaco Tricapa
UPDATE Productos SET imagen = 'codo_cobre_90_12.jpg' WHERE ID_Producto = 18;          -- Codo Cobre
UPDATE Productos SET imagen = 'juego_de_herrajes_wc.jpg' WHERE ID_Producto = 19;      -- Juego de Herrajes WC
UPDATE Productos SET imagen = 'tubo_cpvc_12.jpg' WHERE ID_Producto = 20;              -- Tubo CPVC

-- Material Eléctrico (Categoría 5)
UPDATE Productos SET imagen = 'cable_thw_calibre_12.jpg' WHERE ID_Producto = 21;      -- Cable THW
UPDATE Productos SET imagen = 'contacto_doble_polarizado.jpg' WHERE ID_Producto = 22; -- Contacto Doble
UPDATE Productos SET imagen = 'apagador_sencillo.jpeg' WHERE ID_Producto = 23;        -- Apagador Sencillo
UPDATE Productos SET imagen = 'centro_de_carga_2_polos.jpg' WHERE ID_Producto = 24;   -- Centro de Carga
UPDATE Productos SET imagen = 'manguera_corrugada_1 2.jpg' WHERE ID_Producto = 25;    -- Manguera Corrugada

-- Herramientas y Equipo (Categoría 6)
UPDATE Productos SET imagen = 'pala_cuadrada_puno_y.jpg' WHERE ID_Producto = 26;      -- Pala Cuadrada
UPDATE Productos SET imagen = 'carretilla.jpg' WHERE ID_Producto = 27;                -- Carretilla
UPDATE Productos SET imagen = 'taladro_rotomartillo.jpg' WHERE ID_Producto = 28;      -- Taladro Rotomartillo
UPDATE Productos SET imagen = 'nivel_de_mano.jpg' WHERE ID_Producto = 29;             -- Nivel de Mano
UPDATE Productos SET imagen = 'cuchara_albanil.jpg' WHERE ID_Producto = 30;           -- Cuchara de Albañil

-- 3. Verificar los resultados
SELECT ID_Producto, Nombre, imagen, ID_Categoria_FK 
FROM Productos 
ORDER BY ID_Categoria_FK, ID_Producto;

-- ==================================================
-- Fin del script
-- ==================================================
