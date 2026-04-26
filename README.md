# Sazón Digital - Sistema de Gestión de Restaurantes

Sistema de gestión de restaurantes desarrollado como proyecto universitario.

## Tecnologías

- **Backend:** PHP 8+
- **Base de datos:** MySQL 8+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Servidor:** Apache con .htaccess (XAMPP)

## Estructura del Proyecto

```
sazon-digital/
├── api/                  # Endpoints REST (JSON)
├── assets/
│   ├── css/              # Estilos CSS
│   ├── js/               # JavaScript del frontend
│   └── img/              # Imágenes y logo
├── config/               # Configuración de base de datos
├── controllers/          # Controladores (lógica de rutas)
├── database/             # Script SQL del esquema
├── includes/             # Helpers y middleware
├── models/               # Modelos (acceso a BD)
├── services/             # Servicios (lógica de negocio)
├── uploads/              # Archivos subidos (imágenes de productos)
├── views/                # Vistas PHP (HTML)
│   ├── layout/           # Header, sidebar, footer
│   ├── auth/             # Login
│   ├── productos/        # CRUD de productos
│   ├── pedidos/          # Gestión de pedidos
│   ├── usuarios/         # CRUD de usuarios
│   ├── ventas/           # Reporte de ventas
│   ├── reservaciones/    # Reservaciones de mesas
│   ├── estados/          # Estados de pedidos
│   ├── clientes/         # Gestión de clientes
│   └── configuracion/    # Horarios del restaurante
├── .htaccess             # Configuración Apache
└── index.php             # Punto de entrada principal (router)
```

## Instalación en XAMPP

### 1. Requisitos

- XAMPP con PHP 8+ y MySQL
- Navegador web moderno

### 2. Configuración

1. Copiar el proyecto a la carpeta `htdocs` de XAMPP:
   ```
   C:\xampp\htdocs\sazon-digital\
   ```

2. Iniciar Apache y MySQL desde el panel de control de XAMPP.

3. Crear la base de datos:
   - Abrir phpMyAdmin: `http://localhost/phpmyadmin`
   - Importar el archivo `database/schema.sql`
   - O ejecutar directamente en MySQL:
     ```bash
     mysql -u root < database/schema.sql
     ```

4. Verificar la configuración de conexión en `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sazon_digital');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. Acceder al sistema:
   ```
   http://localhost/sazon-digital/
   ```

### 3. Credenciales por defecto

| Email | Contraseña | Rol |
|-------|------------|-----|
| admin@sazondigital.com | admin123 | Administrador |

## Módulos

### Autenticación
- Login con email y contraseña
- Sesión persistente (Remember Me)
- 3 roles: Administrador, Mesero, Cocina

### Productos
- CRUD completo
- Subida de imágenes
- Filtro por categoría
- Estados (activo/inactivo)

### Pedidos
- Crear pedidos con productos
- Selección de mesa
- Notas personalizadas
- 6 estados: Registrado → En Preparación → Listo para Recoger → En Camino → Entregado → Cancelado

### Ventas
- Reporte de ventas diario
- Filtro por rango de fechas
- Resumen con totales, ticket promedio, y cantidad de ventas
- Venta automática al entregar pedido

### Reservaciones
- Crear y gestionar reservaciones
- Verificación de disponibilidad de mesa
- Estados: pendiente, confirmada, cancelada, completada

### Configuración de Horarios
- Configurar días abiertos/cerrados
- Establecer horarios de apertura y cierre
- Bloqueo automático de operaciones cuando el restaurante está cerrado

## Flujo del Sistema

```
Cliente llega → Mesero crea pedido → Cocina recibe pedido →
Cocina cambia estado → Cajero cierra pedido → Historial de ventas
```

## API Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/auth.php?action=login` | Iniciar sesión |
| GET | `/api/productos.php` | Listar productos |
| POST | `/api/productos.php` | Crear producto |
| GET | `/api/pedidos.php` | Listar pedidos |
| POST | `/api/pedidos.php` | Crear pedido |
| PUT | `/api/pedidos.php?id=X` | Cambiar estado |
| GET | `/api/ventas.php` | Listar ventas |
| GET | `/api/reservaciones.php` | Listar reservaciones |
| POST | `/api/reservaciones.php` | Crear reservación |
| GET | `/api/configuracion.php` | Obtener horarios |
| POST | `/api/configuracion.php` | Actualizar horarios |

## Licencia

Proyecto universitario - Uso educativo.
