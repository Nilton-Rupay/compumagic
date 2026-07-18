-- ============================================================
-- Sistema de Ventas - Tienda de Computadoras
-- Base de datos: sistema_ventas
-- ============================================================

CREATE DATABASE IF NOT EXISTS sistema_ventas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE sistema_ventas;

-- ------------------------------------------------------------
-- 1. Tabla: admin
-- ------------------------------------------------------------
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Usuario administrador por defecto (usuario: admin / contraseña: password)
INSERT INTO admin (usuario, password, nombre, email) VALUES
('admin', '$2b$12$Qbt1OE5WhseQDWHVWncuT.A5Q92ZYq286VJODQ3cQgzxZe9f.lRMW', 'Administrador', 'admin@sistemaventas.com');

-- ------------------------------------------------------------
-- 2. Tabla: categorias
-- ------------------------------------------------------------
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categorias (nombre, descripcion) VALUES
('Computadoras', 'Computadoras de escritorio, torres y equipos completos'),
('Laptops', 'Laptops portátiles de diversas marcas y modelos'),
('Accesorios', 'Accesorios de cómputo: teclados, mouse, audífonos, etc.'),
('Monitores', 'Monitores y pantallas para computadora'),
('Almacenamiento', 'Discos duros, SSD, memorias USB y tarjetas SD');

-- ------------------------------------------------------------
-- 3. Tabla: productos
-- ------------------------------------------------------------
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria_id INT NOT NULL,
    precio_compra DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    imagen VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- CAMBIO SEGURO: Bloquea el borrado de una categoría si tiene productos asociados
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Datos de ejemplo
INSERT INTO productos (codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo) VALUES
('PC-001', 'PC Escritorio Intel Core i5 8GB RAM 480GB SSD', 'Computadora de escritorio con Intel Core i5, 8GB RAM, 480GB SSD, incluye monitor', 1, 1200.00, 1599.00, 10, 3),
('LAP-001', 'Laptop HP Pavilion Ryzen 5 8GB 512GB SSD', 'Laptop HP Pavilion 15", AMD Ryzen 5, 8GB RAM, 512GB SSD', 2, 1800.00, 2399.00, 8, 3),
('ACC-001', 'Kit Teclado y Mouse Inalámbrico Logitech', 'Kit combo inalámbrico de teclado y mouse Logitech MK220', 3, 45.00, 79.90, 25, 5),
('MON-001', 'Monitor LED 24" Full HD Samsung', 'Monitor LED de 24 pulgadas, resolución Full HD 1920x1080', 4, 400.00, 599.00, 15, 5),
('ALM-001', 'SSD Kingston 480GB SATA III', 'Unidad de estado sólido Kingston A400 480GB SATA III', 5, 120.00, 189.90, 20, 5);

-- ------------------------------------------------------------
-- 4. Tabla: ventas
-- ------------------------------------------------------------
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_venta VARCHAR(50) NOT NULL UNIQUE,
    admin_id INT NOT NULL,
    cliente_nombre VARCHAR(200) DEFAULT 'Cliente General',
    cliente_documento VARCHAR(20) DEFAULT NULL,
    total DECIMAL(10,2) NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'yape') DEFAULT 'efectivo',
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- CAMBIO SEGURO: Bloquea el borrado de un administrador si ya registró ventas
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. Tabla: detalle_venta
-- ------------------------------------------------------------
CREATE TABLE detalle_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    -- Aquí SÍ se justifica el CASCADE: Si eliminas la factura completa, se borra su detalle
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    -- CAMBIO SEGURO: Bloquea borrar un producto si ya fue vendido en algún ticket
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
