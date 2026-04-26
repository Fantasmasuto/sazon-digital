-- ============================================
-- Sazón Digital - Restaurant Management System
-- Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS sazon_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sazon_digital;

-- ============================================
-- 1. Roles
-- ============================================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 2. Usuarios
-- ============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ============================================
-- 3. Tokens de Login (Remember Me)
-- ============================================
CREATE TABLE tokens_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expira_en DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- 4. Categorías de Productos
-- ============================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(200),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 5. Productos
-- ============================================
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria_id INT NOT NULL,
    imagen VARCHAR(255),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;

-- ============================================
-- 6. Mesas
-- ============================================
CREATE TABLE mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    capacidad INT NOT NULL DEFAULT 4,
    estado ENUM('disponible', 'ocupada', 'reservada') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 7. Estados de Pedido
-- ============================================
CREATE TABLE estados_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200),
    color VARCHAR(7) DEFAULT '#000000',
    orden INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ============================================
-- 8. Pedidos
-- ============================================
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa_id INT,
    mesero_id INT NOT NULL,
    estado_id INT NOT NULL DEFAULT 1,
    notas TEXT,
    total DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id),
    FOREIGN KEY (mesero_id) REFERENCES usuarios(id),
    FOREIGN KEY (estado_id) REFERENCES estados_pedido(id)
) ENGINE=InnoDB;

-- ============================================
-- 9. Detalle de Pedido
-- ============================================
CREATE TABLE detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    notas VARCHAR(255),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

-- ============================================
-- 10. Ventas
-- ============================================
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
    cajero_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (cajero_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ============================================
-- 11. Reservaciones
-- ============================================
CREATE TABLE reservaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_nombre VARCHAR(100) NOT NULL,
    cliente_telefono VARCHAR(20),
    cliente_email VARCHAR(150),
    mesa_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    num_personas INT NOT NULL DEFAULT 1,
    estado ENUM('pendiente', 'confirmada', 'cancelada', 'completada') DEFAULT 'pendiente',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id)
) ENGINE=InnoDB;

-- ============================================
-- 12. Configuración de Horarios
-- ============================================
CREATE TABLE configuracion_horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana ENUM('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL UNIQUE,
    abierto TINYINT(1) DEFAULT 1,
    hora_apertura TIME DEFAULT '08:00:00',
    hora_cierre TIME DEFAULT '22:00:00',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Datos iniciales
-- ============================================

-- Roles
INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Acceso total al sistema'),
('Mesero', 'Gestión de pedidos y mesas'),
('Cocina', 'Visualización y actualización de estados de pedidos');

-- Usuario admin por defecto (password: admin123)
INSERT INTO usuarios (nombre, email, password, rol_id) VALUES
('Administrador', 'admin@sazondigital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Estados de pedido
INSERT INTO estados_pedido (nombre, descripcion, color, orden) VALUES
('Registrado', 'El pedido ha sido recibido y está esperando ser procesado.', '#3498db', 1),
('En Preparación', 'El pedido está siendo preparado en la cocina.', '#f39c12', 2),
('Listo para Recoger', 'El pedido está listo para ser recogido por el cliente o repartidor.', '#27ae60', 3),
('En Camino', 'El pedido está siendo entregado al cliente.', '#9b59b6', 4),
('Entregado', 'El pedido ha sido entregado exitosamente al cliente.', '#2ecc71', 5),
('Cancelado', 'El pedido ha sido cancelado.', '#e74c3c', 6);

-- Categorías iniciales
INSERT INTO categorias (nombre, descripcion) VALUES
('Comidas', 'Platos principales y guarniciones'),
('Bebidas', 'Refrescos, jugos y bebidas calientes'),
('Postres', 'Dulces, pasteles y helados'),
('Entradas', 'Aperitivos y entradas');

-- Mesas iniciales
INSERT INTO mesas (numero, capacidad) VALUES
(1, 4), (2, 4), (3, 6), (4, 2), (5, 8), (6, 4), (7, 6), (8, 2), (9, 4), (10, 10);

-- Horarios iniciales (Lunes a Domingo)
INSERT INTO configuracion_horarios (dia_semana, abierto, hora_apertura, hora_cierre) VALUES
('lunes', 1, '08:00:00', '22:00:00'),
('martes', 1, '08:00:00', '22:00:00'),
('miercoles', 1, '08:00:00', '22:00:00'),
('jueves', 1, '08:00:00', '22:00:00'),
('viernes', 1, '08:00:00', '23:00:00'),
('sabado', 1, '09:00:00', '23:00:00'),
('domingo', 0, '09:00:00', '20:00:00');

-- Productos de ejemplo
INSERT INTO productos (nombre, descripcion, precio, categoria_id) VALUES
('Hamburguesa Clásica', 'Carne, lechuga, tomate, queso, cebolla', 12.50, 1),
('Coca-Cola', 'Lata de 355ml', 3.00, 2),
('Torta de Queso', 'Porción individual', 6.50, 3),
('Ensalada César', 'Lechuga, pollo a la parrilla, crutones, aderezo César', 9.00, 4),
('Jugo de Naranja', 'Vaso de 300ml natural', 4.00, 2),
('Papas Fritas Grandes', 'Porción grande con sal', 5.00, 1),
('Refresco Cola', 'Botella 600ml', 3.50, 2);
