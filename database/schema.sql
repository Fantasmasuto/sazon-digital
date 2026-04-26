-- ============================================
-- SAZÓN DIGITAL - ESQUEMA DE BASE DE DATOS
-- Sistema de Gestión de Restaurantes
-- Proyecto Universitario
-- ============================================
--
-- INSTRUCCIONES DE INSTALACIÓN:
--
-- Opción 1 - phpMyAdmin:
--   1. Abrir http://localhost/phpmyadmin
--   2. Ir a la pestaña "Importar"
--   3. Seleccionar este archivo (schema.sql)
--   4. Click en "Continuar"
--
-- Opción 2 - Terminal/Consola:
--   mysql -u root < schema.sql
--
-- Opción 3 - MySQL Workbench:
--   Abrir este archivo y ejecutar todo (Ctrl+Shift+Enter)
--
-- ============================================

-- Crear la base de datos (si no existe)
CREATE DATABASE IF NOT EXISTS sazon_digital
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

-- Seleccionar la base de datos
USE sazon_digital;

-- ============================================
-- TABLA 1: ROLES
-- Define los tipos de usuario del sistema.
-- Cada rol tiene diferentes permisos.
-- ============================================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,         -- Identificador único
    nombre VARCHAR(50) NOT NULL UNIQUE,        -- Nombre del rol (único)
    descripcion VARCHAR(200),                  -- Descripción del rol
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- Fecha de creación automática
) ENGINE=InnoDB;

-- ============================================
-- TABLA 2: USUARIOS
-- Almacena los usuarios que acceden al sistema.
-- La contraseña se guarda encriptada (hash).
-- Cada usuario tiene un rol asignado (FK a roles).
-- ============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,              -- Nombre completo
    email VARCHAR(150) NOT NULL UNIQUE,        -- Email único (se usa como login)
    password VARCHAR(255) NOT NULL,            -- Contraseña encriptada con password_hash()
    rol_id INT NOT NULL,                       -- FK: Rol del usuario
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',  -- ENUM limita los valores posibles
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)  -- Relación con tabla roles
) ENGINE=InnoDB;

-- ============================================
-- TABLA 3: TOKENS DE LOGIN (Remember Me)
-- Almacena tokens para la función "Recordarme".
-- Cuando el usuario marca "Recordarme", se genera
-- un token aleatorio que se guarda en una cookie
-- y en esta tabla. Si la sesión expira, el token
-- permite re-autenticar automáticamente.
-- ============================================
CREATE TABLE tokens_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,                   -- FK: Usuario dueño del token
    token VARCHAR(255) NOT NULL UNIQUE,        -- Token aleatorio único
    expira_en DATETIME NOT NULL,               -- Fecha de expiración
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    -- ON DELETE CASCADE: Si se borra el usuario, se borran sus tokens
) ENGINE=InnoDB;

-- ============================================
-- TABLA 4: CATEGORÍAS DE PRODUCTOS
-- Agrupa los productos del menú.
-- Ejemplo: Comidas, Bebidas, Postres, Entradas
-- ============================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(200),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLA 5: PRODUCTOS
-- Los platillos, bebidas y postres del menú.
-- Cada producto pertenece a una categoría.
-- Puede tener una imagen asociada.
-- ============================================
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,                          -- TEXT permite descripciones largas
    precio DECIMAL(10,2) NOT NULL,             -- DECIMAL para precios exactos (no usar FLOAT)
    categoria_id INT NOT NULL,                 -- FK: Categoría del producto
    imagen VARCHAR(255),                       -- Nombre del archivo de imagen
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;

-- ============================================
-- TABLA 6: MESAS
-- Representa las mesas físicas del restaurante.
-- El estado cambia según la ocupación.
-- ============================================
CREATE TABLE mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,                -- Número visible de la mesa
    capacidad INT NOT NULL DEFAULT 4,          -- Cuántas personas caben
    estado ENUM('disponible', 'ocupada', 'reservada') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLA 7: ESTADOS DE PEDIDO
-- Define los posibles estados de un pedido.
-- El campo "orden" indica la secuencia lógica.
-- El campo "color" se usa en la interfaz web.
--
-- Flujo: Registrado -> Preparación -> Listo -> Entregado
--        (puede ser Cancelado en cualquier momento)
-- ============================================
CREATE TABLE estados_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200),
    color VARCHAR(7) DEFAULT '#000000',        -- Color hexadecimal para badges en la UI
    orden INT NOT NULL DEFAULT 0               -- Orden de aparición
) ENGINE=InnoDB;

-- ============================================
-- TABLA 8: PEDIDOS (ÓRDENES)
-- Cada vez que un mesero toma una orden, se crea un pedido.
-- El pedido se asocia a un mesero, una mesa y un estado.
-- El total se calcula sumando los productos del detalle.
-- ============================================
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa_id INT,                               -- FK: Mesa (puede ser NULL = para llevar)
    mesero_id INT NOT NULL,                    -- FK: Mesero que tomó la orden
    estado_id INT NOT NULL DEFAULT 1,          -- FK: Estado actual (1 = Registrado)
    notas TEXT,                                -- Notas especiales del pedido
    total DECIMAL(10,2) DEFAULT 0.00,          -- Total calculado
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id),
    FOREIGN KEY (mesero_id) REFERENCES usuarios(id),
    FOREIGN KEY (estado_id) REFERENCES estados_pedido(id)
) ENGINE=InnoDB;

-- ============================================
-- TABLA 9: DETALLE DE PEDIDO
-- Cada renglón es un producto dentro de un pedido.
-- Un pedido puede tener muchos productos (relación 1:N).
--
-- Ejemplo: Pedido #1 tiene:
--   - 2x Hamburguesa ($12.50 c/u) = $25.00
--   - 1x Coca-Cola ($3.00 c/u) = $3.00
--   Total del pedido: $28.00
-- ============================================
CREATE TABLE detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,                    -- FK: Pedido al que pertenece
    producto_id INT NOT NULL,                  -- FK: Producto ordenado
    cantidad INT NOT NULL DEFAULT 1,           -- Cantidad solicitada
    precio_unitario DECIMAL(10,2) NOT NULL,    -- Precio al momento de la orden
    subtotal DECIMAL(10,2) NOT NULL,           -- cantidad * precio_unitario
    notas VARCHAR(255),                        -- Notas específicas del producto
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

-- ============================================
-- TABLA 10: VENTAS
-- Registra las ventas finalizadas.
-- Se crea automáticamente cuando un pedido se marca como "Entregado".
-- Cada venta está ligada a un pedido y a un cajero.
-- ============================================
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,                    -- FK: Pedido que generó la venta
    total DECIMAL(10,2) NOT NULL,              -- Monto total cobrado
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
    cajero_id INT NOT NULL,                    -- FK: Usuario que registró la venta
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (cajero_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ============================================
-- TABLA 11: RESERVACIONES
-- Permite a los clientes reservar mesas.
-- Incluye verificación de disponibilidad por horario.
-- ============================================
CREATE TABLE reservaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_nombre VARCHAR(100) NOT NULL,      -- Nombre del cliente
    cliente_telefono VARCHAR(20),              -- Teléfono de contacto
    cliente_email VARCHAR(150),                -- Email de contacto
    mesa_id INT NOT NULL,                      -- FK: Mesa reservada
    fecha DATE NOT NULL,                       -- Fecha de la reservación
    hora_inicio TIME NOT NULL,                 -- Hora de llegada
    hora_fin TIME NOT NULL,                    -- Hora estimada de salida
    num_personas INT NOT NULL DEFAULT 1,       -- Cantidad de comensales
    estado ENUM('pendiente', 'confirmada', 'cancelada', 'completada') DEFAULT 'pendiente',
    notas TEXT,                                -- Notas o requerimientos especiales
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id)
) ENGINE=InnoDB;

-- ============================================
-- TABLA 12: CONFIGURACIÓN DE HORARIOS
-- Define los días y horarios de operación del restaurante.
-- Regla de negocio: si está cerrado, se bloquean
-- pedidos, ventas y reservaciones.
-- ============================================
CREATE TABLE configuracion_horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana ENUM('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL UNIQUE,
    abierto TINYINT(1) DEFAULT 1,              -- 1 = abierto, 0 = cerrado
    hora_apertura TIME DEFAULT '08:00:00',
    hora_cierre TIME DEFAULT '22:00:00',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ============================================
-- ============================================
--           DATOS INICIALES (SEED)
-- ============================================
-- ============================================

-- ============================================
-- ROLES DEL SISTEMA
-- ============================================
INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Acceso total al sistema: usuarios, productos, ventas, configuración'),
('Mesero', 'Crear pedidos, ver productos, gestionar mesas'),
('Cocina', 'Ver pedidos pendientes, cambiar estados de preparación');

-- ============================================
-- USUARIOS DE PRUEBA
-- ============================================
-- NOTA: Todas las contraseñas son "password123"
-- El hash fue generado con: password_hash('password123', PASSWORD_DEFAULT)
-- NUNCA guardes contraseñas en texto plano en producción.

-- Admin principal
INSERT INTO usuarios (nombre, email, password, rol_id, estado) VALUES
('Juan Pérez', 'admin@sazondigital.com',
 '$2y$10$8K1p/a0dR1xqM8k3.1ZqYe6VJY6OYKQ1s2CmJEuIp4aJ1nLxHxeO', 1, 'activo');

-- Meseros de prueba
INSERT INTO usuarios (nombre, email, password, rol_id, estado) VALUES
('María García', 'maria.garcia@sazondigital.com',
 '$2y$10$8K1p/a0dR1xqM8k3.1ZqYe6VJY6OYKQ1s2CmJEuIp4aJ1nLxHxeO', 2, 'activo'),
('Carlos López', 'carlos.lopez@sazondigital.com',
 '$2y$10$8K1p/a0dR1xqM8k3.1ZqYe6VJY6OYKQ1s2CmJEuIp4aJ1nLxHxeO', 2, 'activo');

-- Usuario de cocina
INSERT INTO usuarios (nombre, email, password, rol_id, estado) VALUES
('Ana Torres', 'ana.torres@sazondigital.com',
 '$2y$10$8K1p/a0dR1xqM8k3.1ZqYe6VJY6OYKQ1s2CmJEuIp4aJ1nLxHxeO', 3, 'activo');

-- Usuario inactivo (para probar que no puede hacer login)
INSERT INTO usuarios (nombre, email, password, rol_id, estado) VALUES
('Pedro Ruiz', 'pedro.ruiz@sazondigital.com',
 '$2y$10$8K1p/a0dR1xqM8k3.1ZqYe6VJY6OYKQ1s2CmJEuIp4aJ1nLxHxeO', 2, 'inactivo');

-- ============================================
-- ESTADOS DE PEDIDO
-- Estos estados representan el ciclo de vida de una orden.
-- ============================================
INSERT INTO estados_pedido (nombre, descripcion, color, orden) VALUES
('Registrado',        'El pedido ha sido recibido y está esperando ser procesado.',           '#3498db', 1),
('En Preparación',    'El pedido está siendo preparado en la cocina.',                        '#f39c12', 2),
('Listo para Recoger','El pedido está listo para ser recogido por el cliente o repartidor.',  '#27ae60', 3),
('En Camino',         'El pedido está siendo entregado al cliente.',                          '#9b59b6', 4),
('Entregado',         'El pedido ha sido entregado exitosamente al cliente.',                 '#2ecc71', 5),
('Cancelado',         'El pedido ha sido cancelado.',                                         '#e74c3c', 6);

-- ============================================
-- CATEGORÍAS DE PRODUCTOS
-- ============================================
INSERT INTO categorias (nombre, descripcion) VALUES
('Comidas',  'Platos principales y guarniciones'),
('Bebidas',  'Refrescos, jugos y bebidas calientes'),
('Postres',  'Dulces, pasteles y helados'),
('Entradas', 'Aperitivos y entradas');

-- ============================================
-- MESAS DEL RESTAURANTE
-- 10 mesas con diferentes capacidades
-- ============================================
INSERT INTO mesas (numero, capacidad) VALUES
(1, 4), (2, 4), (3, 6), (4, 2), (5, 8),
(6, 4), (7, 6), (8, 2), (9, 4), (10, 10);

-- ============================================
-- HORARIOS DE OPERACIÓN
-- Lunes a Sábado abierto, Domingo cerrado por defecto
-- ============================================
INSERT INTO configuracion_horarios (dia_semana, abierto, hora_apertura, hora_cierre) VALUES
('lunes',    1, '08:00:00', '22:00:00'),
('martes',   1, '08:00:00', '22:00:00'),
('miercoles',1, '08:00:00', '22:00:00'),
('jueves',   1, '08:00:00', '22:00:00'),
('viernes',  1, '08:00:00', '23:00:00'),
('sabado',   1, '09:00:00', '23:00:00'),
('domingo',  0, '09:00:00', '20:00:00');

-- ============================================
-- PRODUCTOS DE EJEMPLO (MENÚ)
-- ============================================
INSERT INTO productos (nombre, descripcion, precio, categoria_id) VALUES
('Hamburguesa Clásica',  'Carne de res, lechuga, tomate, queso, cebolla',        12.50, 1),
('Tacos al Pastor',      'Tres tacos con piña, cebolla y cilantro',              8.00,  1),
('Ensalada César',       'Lechuga, pollo a la parrilla, crutones, aderezo César', 9.00, 4),
('Papas Fritas Grandes', 'Porción grande con sal y catsup',                       5.00,  1),
('Sopa del Día',         'Preparación especial del chef',                         6.50,  4),
('Coca-Cola',            'Lata de 355ml',                                         3.00,  2),
('Jugo de Naranja',      'Vaso de 300ml natural',                                 4.00,  2),
('Agua Mineral',         'Botella de 500ml',                                      2.50,  2),
('Café Americano',       'Taza de café recién preparado',                         3.50,  2),
('Torta de Queso',       'Porción individual con frutos rojos',                   6.50,  3),
('Helado de Vainilla',   'Tres bolas con jarabe de chocolate',                    5.50,  3),
('Refresco Cola',        'Botella 600ml',                                         3.50,  2);

-- ============================================
-- DATOS DE EJEMPLO: PEDIDOS, DETALLES Y VENTAS
-- (Para que la aplicación no se vea vacía al iniciar)
-- ============================================

-- Pedido #1: Juan Pérez (Mesero: María García, Mesa 1, Entregado)
INSERT INTO pedidos (mesa_id, mesero_id, estado_id, notas, total) VALUES
(1, 2, 5, 'Sin cebolla en la hamburguesa', 25.00);

INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal, notas) VALUES
(1, 1, 1, 12.50, 12.50, 'Sin cebolla'),
(1, 6, 2, 3.00, 6.00, ''),
(1, 4, 1, 5.00, 5.00, 'Extra sal'),
(1, 11, 1, 5.50, 5.50, '');

-- Pedido #2: Carlos López (Mesa 3, En Preparación)
INSERT INTO pedidos (mesa_id, mesero_id, estado_id, notas, total) VALUES
(3, 3, 2, '', 28.50);

INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal, notas) VALUES
(2, 3, 2, 9.00, 18.00, ''),
(2, 7, 1, 4.00, 4.00, ''),
(2, 10, 1, 6.50, 6.50, '');

-- Pedido #3: María García (Mesa 5, Registrado)
INSERT INTO pedidos (mesa_id, mesero_id, estado_id, notas, total) VALUES
(5, 2, 1, 'Mesa de cumpleaños', 33.50);

INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal, notas) VALUES
(3, 1, 2, 12.50, 25.00, ''),
(3, 2, 1, 8.00, 8.00, 'Extra picante'),
(3, 8, 1, 2.50, 2.50, '');

-- Venta del Pedido #1 (ya fue entregado)
INSERT INTO ventas (pedido_id, total, metodo_pago, cajero_id) VALUES
(1, 25.00, 'efectivo', 1);

-- Actualizar mesa 1 a disponible (pedido ya entregado)
UPDATE mesas SET estado = 'disponible' WHERE id = 1;

-- Actualizar mesa 3 a ocupada (pedido en preparación)
UPDATE mesas SET estado = 'ocupada' WHERE id = 3;

-- Actualizar mesa 5 a ocupada (pedido registrado)
UPDATE mesas SET estado = 'ocupada' WHERE id = 5;

-- ============================================
-- RESERVACIONES DE EJEMPLO
-- ============================================
INSERT INTO reservaciones (cliente_nombre, cliente_telefono, cliente_email, mesa_id, fecha, hora_inicio, hora_fin, num_personas, estado, notas) VALUES
('Roberto Sánchez', '555-1234', 'roberto@email.com', 2, CURDATE(), '19:00:00', '21:00:00', 4, 'confirmada', 'Aniversario'),
('Laura Martínez',  '555-5678', 'laura@email.com',   7, CURDATE(), '20:00:00', '22:00:00', 6, 'pendiente', 'Cena de negocios');
