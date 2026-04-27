# Sazón Digital 🍳

## Sistema de Gestión de Restaurantes

Proyecto universitario desarrollado con PHP, MySQL, JavaScript y Apache (XAMPP).

---

## Tabla de Contenidos

1. [Tecnologías](#tecnologías)
2. [Estructura del Proyecto](#estructura-del-proyecto)
3. [Instalación Paso a Paso](#instalación-paso-a-paso)
4. [Usuarios de Prueba](#usuarios-de-prueba)
5. [Módulos del Sistema](#módulos-del-sistema)
6. [Flujo del Sistema](#flujo-del-sistema)
7. [Documentación de API](#documentación-de-api)
8. [Base de Datos](#base-de-datos)
9. [Arquitectura](#arquitectura)
10. [Reglas de Negocio](#reglas-de-negocio)

---

## Tecnologías

| Tecnología | Uso |
|------------|-----|
| **PHP 8+** | Backend, lógica del servidor, API REST |
| **MySQL 8+** | Base de datos relacional |
| **JavaScript (Vanilla)** | Frontend dinámico, llamadas AJAX |
| **HTML5 + CSS3** | Estructura y estilos de la interfaz |
| **Apache + .htaccess** | Servidor web, seguridad, reescritura de URLs |
| **XAMPP** | Entorno de desarrollo local |
| **PDO** | Conexión segura a base de datos (prepared statements) |

---

## Estructura del Proyecto

```
sazon-digital/
│
├── index.php                  # Punto de entrada (Router principal)
├── install.php                # Script de instalación automática
├── .htaccess                  # Configuración Apache + seguridad
│
├── config/
│   └── database.php           # Conexión a MySQL con PDO
│
├── models/                    # CAPA MODELO - Acceso a base de datos
│   ├── Usuario.php            #   Consultas SQL de usuarios
│   ├── Producto.php           #   Consultas SQL de productos
│   ├── Pedido.php             #   Consultas SQL de pedidos
│   ├── Venta.php              #   Consultas SQL de ventas
│   ├── Reservacion.php        #   Consultas SQL de reservaciones
│   └── Configuracion.php      #   Consultas SQL de horarios
│
├── services/                  # CAPA SERVICIO - Lógica de negocio
│   ├── AuthService.php        #   Login, logout, remember me
│   ├── ProductoService.php    #   Lógica de productos + imágenes
│   ├── PedidoService.php      #   Lógica de pedidos + auto-venta
│   ├── VentaService.php       #   Lógica de ventas y reportes
│   ├── ReservacionService.php #   Lógica de reservaciones
│   └── ConfiguracionService.php #  Lógica de horarios
│
├── controllers/               # CAPA CONTROLADOR - Procesa peticiones
│   ├── AuthController.php     #   Maneja login/logout
│   ├── ProductoController.php #   Maneja CRUD de productos
│   ├── PedidoController.php   #   Maneja CRUD de pedidos
│   ├── VentaController.php    #   Maneja ventas
│   ├── ReservacionController.php # Maneja reservaciones
│   └── ConfiguracionController.php # Maneja horarios
│
├── api/                       # ENDPOINTS REST (responden JSON)
│   ├── auth.php               #   POST login, POST logout
│   ├── productos.php          #   GET/POST/DELETE productos
│   ├── pedidos.php            #   GET/POST/PUT/DELETE pedidos
│   ├── usuarios.php           #   GET/POST/DELETE usuarios
│   ├── ventas.php             #   GET/POST ventas
│   ├── reservaciones.php      #   GET/POST/PUT/DELETE reservaciones
│   └── configuracion.php      #   GET/POST configuración
│
├── views/                     # VISTAS - Páginas HTML con PHP
│   ├── layout/
│   │   ├── header.php         #   Cabecera HTML + barra superior
│   │   ├── sidebar.php        #   Menú lateral de navegación
│   │   └── footer.php         #   Cierre HTML + scripts JS
│   ├── auth/
│   │   └── login.php          #   Formulario de inicio de sesión
│   ├── dashboard.php          #   Panel principal con resumen
│   ├── productos/
│   │   ├── index.php          #   Lista de productos (tabla)
│   │   └── form.php           #   Formulario crear/editar producto
│   ├── pedidos/
│   │   ├── index.php          #   Lista de pedidos
│   │   ├── crear.php          #   Crear nuevo pedido
│   │   └── detalle.php        #   Ver detalle de un pedido
│   ├── usuarios/
│   │   ├── index.php          #   Lista de usuarios
│   │   └── form.php           #   Formulario crear/editar usuario
│   ├── ventas/
│   │   └── index.php          #   Reporte de ventas
│   ├── reservaciones/
│   │   └── index.php          #   Gestión de reservaciones
│   ├── estados/
│   │   └── index.php          #   Estados de pedidos
│   ├── clientes/
│   │   └── index.php          #   Gestión de clientes
│   └── configuracion/
│       └── horarios.php       #   Configuración de horarios
│
├── assets/                    # ARCHIVOS PÚBLICOS (CSS, JS, imágenes)
│   ├── css/
│   │   └── styles.css         #   Estilos de toda la aplicación
│   ├── js/
│   │   ├── main.js            #   Funciones JS compartidas
│   │   ├── dashboard.js       #   JS del dashboard
│   │   ├── productos.js       #   JS de productos
│   │   ├── pedidos.js         #   JS de pedidos
│   │   ├── usuarios.js        #   JS de usuarios
│   │   ├── ventas.js          #   JS de ventas
│   │   ├── reservaciones.js   #   JS de reservaciones
│   │   └── configuracion.js   #   JS de configuración
│   └── img/
│       └── logo.png           #   Logo de la aplicación
│
├── uploads/                   # ARCHIVOS SUBIDOS por usuarios
│   └── productos/             #   Imágenes de productos
│
├── includes/                  # UTILIDADES compartidas
│   ├── helpers.php            #   Funciones auxiliares reutilizables
│   └── middleware.php         #   Verificación de autenticación y roles
│
└── database/
    └── schema.sql             #   Script completo de la base de datos
```

---

## Instalación Paso a Paso

### Requisitos Previos

- [XAMPP](https://www.apachefriends.org/es/index.html) instalado (incluye Apache, MySQL y PHP)
- Un navegador web moderno (Chrome, Firefox, Edge)

### Paso 1: Descargar e instalar XAMPP

1. Descargar XAMPP desde https://www.apachefriends.org/es/index.html
2. Instalar con las opciones por defecto
3. Abrir el Panel de Control de XAMPP

### Paso 2: Copiar el proyecto

Copiar toda la carpeta `sazon-digital` a la carpeta `htdocs` de XAMPP:

```
Windows:  C:\xampp\htdocs\sazon-digital\
macOS:    /Applications/XAMPP/htdocs/sazon-digital/
Linux:    /opt/lampp/htdocs/sazon-digital/
```

### Paso 3: Iniciar los servicios

En el Panel de Control de XAMPP:
1. Click en **Start** junto a **Apache**
2. Click en **Start** junto a **MySQL**
3. Ambos deben aparecer en verde

### Paso 4: Crear la base de datos

#### Opción A: Instalador automático (Recomendado)

1. Abrir el navegador
2. Ir a: `http://localhost/sazon-digital/install.php`
3. El script creará la base de datos, tablas y usuarios automáticamente
4. **Eliminar `install.php` después de la instalación** (por seguridad)

#### Opción B: Importar manualmente con phpMyAdmin

1. Abrir: `http://localhost/phpmyadmin`
2. Click en la pestaña **"Importar"** (en la barra superior)
3. Click en **"Seleccionar archivo"**
4. Seleccionar el archivo `database/schema.sql`
5. Click en **"Continuar"** (botón inferior)
6. Debería aparecer: "La importación se ejecutó exitosamente"

#### Opción C: Línea de comandos

```bash
# Windows (CMD desde carpeta XAMPP)
C:\xampp\mysql\bin\mysql -u root < C:\xampp\htdocs\sazon-digital\database\schema.sql

# macOS / Linux
mysql -u root < /opt/lampp/htdocs/sazon-digital/database/schema.sql
```

### Paso 5: Verificar la configuración

Abrir `config/database.php` y verificar estos valores:

```php
define('DB_HOST', 'localhost');    // No cambiar en XAMPP
define('DB_NAME', 'sazon_digital');
define('DB_USER', 'root');        // Usuario por defecto de XAMPP
define('DB_PASS', '');            // Sin contraseña por defecto
```

> **Nota:** Si cambiaste la contraseña de MySQL en phpMyAdmin, actualiza `DB_PASS`.

### Paso 6: Acceder al sistema

1. Abrir: `http://localhost/sazon-digital/`
2. Iniciar sesión con las credenciales de prueba (ver abajo)

---

## Usuarios de Prueba

> **Contraseña para todos:** `password123`

| # | Nombre | Email | Rol | Estado |
|---|--------|-------|-----|--------|
| 1 | Juan Pérez | admin@sazondigital.com | Administrador | Activo |
| 2 | María García | maria.garcia@sazondigital.com | Mesero | Activo |
| 3 | Carlos López | carlos.lopez@sazondigital.com | Mesero | Activo |
| 4 | Ana Torres | ana.torres@sazondigital.com | Cocina | Activo |
| 5 | Pedro Ruiz | pedro.ruiz@sazondigital.com | Mesero | **Inactivo** |

### Permisos por Rol

| Funcionalidad | Administrador | Mesero | Cocina |
|---------------|:---:|:---:|:---:|
| Dashboard | Si | Si | Si |
| Ver productos | Si | Si | Si |
| Crear/editar productos | Si | No | No |
| Ver pedidos | Si | Si | Si |
| Crear pedidos | Si | Si | No |
| Cambiar estado pedidos | Si | Si | Si |
| Ver usuarios | Si | No | No |
| Crear/editar usuarios | Si | No | No |
| Ver ventas | Si | No | No |
| Ver reservaciones | Si | Si | Si |
| Crear reservaciones | Si | Si | No |
| Configurar horarios | Si | No | No |

---

## Módulos del Sistema

### 1. Autenticación (Auth)
- Login con email y contraseña
- Logout (destruye sesión)
- Remember Me (sesión persistente con cookies/tokens)
- Protección por roles (Admin, Mesero, Cocina)

### 2. Productos
- CRUD completo (Crear, Leer, Actualizar, Eliminar)
- Subida de imágenes
- Filtro por categoría
- Búsqueda por nombre
- Estados: activo / inactivo

### 3. Pedidos (Órdenes)
- Crear pedido seleccionando productos del menú
- Asignar mesa (o "para llevar")
- Agregar notas personalizadas
- Cambiar estado del pedido (flujo de cocina)
- Vista detalle con resumen y productos
- 6 estados de cocina:
  - **Registrado** - Pedido recibido
  - **Preparación** - Se está cocinando
  - **Listo** - Listo para servir al cliente
  - **Entregado** - Entregado en la mesa del cliente
  - **Finalizado** - Pagado y cerrado (genera venta automática)
  - **Cancelado** - Pedido cancelado

### 4. Ventas
- Reporte diario de ventas
- Filtro por rango de fechas
- Tarjetas de resumen: ingresos totales, ticket promedio, cantidad
- Historial detallado con método de pago
- Venta automática al marcar pedido como "Finalizado" (pagado)

### 5. Reservaciones
- Crear reservaciones de mesas
- Verificación de disponibilidad (evita doble reservación)
- Estados: pendiente, confirmada, cancelada, completada
- Datos del cliente: nombre, teléfono, email

### 6. Configuración de Horarios
- Configurar días de apertura/cierre
- Establecer hora de apertura y cierre por día
- Toggle abierto/cerrado por día
- **Regla de negocio:** cuando el restaurante está cerrado:
  - Se bloquean pedidos
  - Se bloquean ventas
  - Se bloquean reservaciones

### 7. Usuarios
- CRUD de usuarios (solo Administrador)
- Asignar roles
- Activar/desactivar usuarios
- Búsqueda de usuarios

### 8. Estados de Pedido
- Vista visual del ciclo de vida de un pedido
- Cada estado tiene un color y descripción

---

## Flujo del Sistema

```
1. Cliente llega al restaurante
        │
2. Mesero crea un PEDIDO (asigna mesa + productos)
        │
3. Cocina recibe el pedido (estado: "Registrado")
        │
4. Cocina cambia estado a "Preparación"
        │
5. Cocina cambia estado a "Listo"
        │
6. Mesero entrega al cliente → estado "Entregado"
        │
7. Cajero cobra → estado "Finalizado" → SE CREA VENTA AUTOMÁTICAMENTE
        │
8. La venta aparece en el reporte diario
        │
(Opcional) Cliente puede RESERVAR mesa antes de llegar
```

---

## Documentación de API

Todos los endpoints están en la carpeta `api/` y responden en formato JSON.

### Autenticación

| Método | URL | Descripción | Requiere Login |
|--------|-----|-------------|:-:|
| `POST` | `/api/auth.php?action=login` | Iniciar sesión | No |
| `POST` | `/api/auth.php?action=logout` | Cerrar sesión | No |
| `GET`  | `/api/auth.php?action=status` | Ver estado de sesión | No |

**Ejemplo de login:**
```javascript
// Enviar formulario con email y password
fetch('api/auth.php?action=login', {
    method: 'POST',
    body: new FormData(document.getElementById('login-form'))
})
.then(r => r.json())
.then(data => {
    if (data.success) {
        window.location = 'index.php?page=dashboard';
    }
});
```

### Productos

| Método | URL | Descripción | Rol Requerido |
|--------|-----|-------------|:--:|
| `GET` | `/api/productos.php` | Listar todos los productos | Cualquiera |
| `GET` | `/api/productos.php?id=1` | Ver producto por ID | Cualquiera |
| `GET` | `/api/productos.php?categoria=2` | Filtrar por categoría | Cualquiera |
| `GET` | `/api/productos.php?action=categorias` | Listar categorías | Cualquiera |
| `POST` | `/api/productos.php` | Crear producto (FormData) | Admin |
| `POST` | `/api/productos.php?id=1` | Actualizar producto | Admin |
| `DELETE` | `/api/productos.php?id=1` | Eliminar producto | Admin |

### Pedidos

| Método | URL | Descripción | Rol Requerido |
|--------|-----|-------------|:--:|
| `GET` | `/api/pedidos.php` | Listar todos los pedidos | Cualquiera |
| `GET` | `/api/pedidos.php?id=1` | Ver pedido con detalle | Cualquiera |
| `GET` | `/api/pedidos.php?action=estados` | Listar estados de pedido | Cualquiera |
| `GET` | `/api/pedidos.php?action=mesas` | Mesas disponibles | Cualquiera |
| `GET` | `/api/pedidos.php?action=kitchen` | Pedidos para cocina | Cualquiera |
| `POST` | `/api/pedidos.php` | Crear pedido (JSON) | Admin, Mesero |
| `PUT` | `/api/pedidos.php?id=1` | Cambiar estado del pedido | Cualquiera |
| `DELETE` | `/api/pedidos.php?id=1` | Eliminar pedido | Admin |

**Ejemplo crear pedido:**
```javascript
fetch('api/pedidos.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        mesa_id: 1,
        notas: 'Sin cebolla',
        productos: [
            { producto_id: 1, cantidad: 2, precio_unitario: 12.50 },
            { producto_id: 6, cantidad: 1, precio_unitario: 3.00 }
        ]
    })
});
```

### Usuarios

| Método | URL | Descripción | Rol Requerido |
|--------|-----|-------------|:--:|
| `GET` | `/api/usuarios.php` | Listar usuarios | Admin |
| `GET` | `/api/usuarios.php?id=1` | Ver usuario | Admin |
| `GET` | `/api/usuarios.php?action=roles` | Listar roles | Admin |
| `POST` | `/api/usuarios.php` | Crear usuario (FormData) | Admin |
| `POST` | `/api/usuarios.php?id=1` | Actualizar usuario | Admin |
| `DELETE` | `/api/usuarios.php?id=1` | Eliminar usuario | Admin |

### Ventas

| Método | URL | Descripción | Rol Requerido |
|--------|-----|-------------|:--:|
| `GET` | `/api/ventas.php` | Listar ventas | Cualquiera |
| `GET` | `/api/ventas.php?fecha_inicio=2024-01-01&fecha_fin=2024-12-31` | Filtrar por fecha | Cualquiera |
| `GET` | `/api/ventas.php?action=summary` | Resumen de ventas | Cualquiera |
| `GET` | `/api/ventas.php?action=daily` | Resumen del día | Cualquiera |
| `POST` | `/api/ventas.php` | Registrar venta manual | Admin |

### Reservaciones

| Método | URL | Descripción | Rol Requerido |
|--------|-----|-------------|:--:|
| `GET` | `/api/reservaciones.php` | Listar reservaciones | Cualquiera |
| `GET` | `/api/reservaciones.php?id=1` | Ver reservación | Cualquiera |
| `GET` | `/api/reservaciones.php?fecha=2024-07-20` | Filtrar por fecha | Cualquiera |
| `POST` | `/api/reservaciones.php` | Crear reservación (JSON) | Admin, Mesero |
| `PUT` | `/api/reservaciones.php?id=1` | Actualizar reservación | Cualquiera |
| `DELETE` | `/api/reservaciones.php?id=1` | Eliminar reservación | Admin |

### Configuración

| Método | URL | Descripción | Rol Requerido |
|--------|-----|-------------|:--:|
| `GET` | `/api/configuracion.php` | Obtener horarios | Cualquiera |
| `GET` | `/api/configuracion.php?action=status` | Estado del restaurante | Cualquiera |
| `POST` | `/api/configuracion.php` | Actualizar horarios (JSON) | Admin |

---

## Base de Datos

### Diagrama de Relaciones (Simplificado)

```
roles ──────┐
            │ 1:N
         usuarios ──────┐
            │           │ 1:N
            │        tokens_login
            │
            │ 1:N (mesero)
categorias  │
   │ 1:N    │
productos   pedidos ────── estados_pedido
   │           │ 1:N
   └───── detalle_pedido
               │
            pedidos ────── ventas
               │
            mesas ──────── reservaciones
```

### Tablas (12 en total)

| # | Tabla | Descripción | Registros iniciales |
|---|-------|-------------|:--:|
| 1 | `roles` | Tipos de usuario | 3 |
| 2 | `usuarios` | Usuarios del sistema | 5 |
| 3 | `tokens_login` | Tokens Remember Me | 0 |
| 4 | `categorias` | Categorías de productos | 4 |
| 5 | `productos` | Menú del restaurante | 12 |
| 6 | `mesas` | Mesas del restaurante | 10 |
| 7 | `estados_pedido` | Estados del ciclo de pedidos | 6 |
| 8 | `pedidos` | Órdenes de clientes | 3 |
| 9 | `detalle_pedido` | Productos en cada pedido | 8 |
| 10 | `ventas` | Ventas finalizadas | 1 |
| 11 | `reservaciones` | Reservaciones de mesas | 2 |
| 12 | `configuracion_horarios` | Horarios de operación | 7 |

### Diagrama Entidad-Relación (ER)

```
┌──────────────┐       ┌──────────────────┐       ┌──────────────┐
│   roles      │       │    usuarios      │       │ tokens_login │
├──────────────┤       ├──────────────────┤       ├──────────────┤
│ PK id        │──1:N──│ PK id            │──1:N──│ PK id        │
│    nombre    │       │ FK rol_id        │       │ FK usuario_id│
└──────────────┘       │    nombre        │       │    token     │
                       │    email         │       │    expira_en │
                       │    password      │       └──────────────┘
                       │    estado        │
                       └──────┬───────────┘
                              │
                    ┌─────────┼──────────┐
                    │ 1:N     │ 1:N      │ 1:N
                    ▼         ▼          ▼
          ┌──────────────┐ ┌────────┐ ┌─────────────────┐
          │   pedidos    │ │ ventas │ │  reservaciones  │
          ├──────────────┤ ├────────┤ ├─────────────────┤
          │ PK id        │ │ PK id  │ │ PK id           │
          │ FK mesa_id   │ │FK ped. │ │ FK mesa_id      │
          │ FK mesero_id │ │FK caj. │ │    cliente_nom.  │
          │ FK estado_id │ │  total │ │    fecha         │
          │    notas     │ │  pago  │ │    hora_inicio   │
          │    total     │ └───┬────┘ │    hora_fin      │
          └──────┬───────┘     │      └────────┬────────┘
                 │             │               │
                 │ 1:N         │               │
                 ▼             │               │
       ┌─────────────────┐    │               │
       │ detalle_pedido  │    │               │
       ├─────────────────┤    │               │
       │ PK id           │    │               │
       │ FK pedido_id    │    │               │
       │ FK producto_id  │    │               │
       │    cantidad     │    │               │
       │    subtotal     │    │               │
       └────────┬────────┘    │               │
                │             │               │
                │ N:1         │               │
                ▼             │               │
       ┌──────────────┐      │               │
       │  productos   │      │               │
       ├──────────────┤      │               │
       │ PK id        │      │               │
       │ FK categ_id  │      │               │
       │    nombre    │      │               │
       │    precio    │      │               │
       │    imagen    │      │               │
       └──────┬───────┘      │               │
              │              │               │
              │ N:1          │               │
              ▼              │               │
       ┌──────────────┐      │          ┌────┴───────────────────┐
       │ categorias   │      │          │        mesas           │
       ├──────────────┤      │          ├────────────────────────┤
       │ PK id        │      │          │ PK id                  │
       │    nombre    │      │          │    numero               │
       └──────────────┘      │          │    capacidad            │
                             │          │    estado               │
       ┌──────────────────┐  │          └────────────────────────┘
       │ estados_pedido   │  │
       ├──────────────────┤  │     ┌─────────────────────────────┐
       │ PK id            │  │     │ configuracion_horarios      │
       │    nombre        │  │     ├─────────────────────────────┤
       │    color         │  │     │ PK id                       │
       │    orden         │  │     │    dia_semana                │
       └──────────────────┘  │     │    abierto                  │
                             │     │    hora_apertura             │
   Relaciones principales:   │     │    hora_cierre               │
   ─────────────────────     │     └─────────────────────────────┘
   roles ──1:N── usuarios
   usuarios ──1:N── pedidos (mesero)
   usuarios ──1:N── ventas (cajero)
   usuarios ──1:N── tokens_login
   mesas ──1:N── pedidos
   mesas ──1:N── reservaciones
   estados_pedido ──1:N── pedidos
   categorias ──1:N── productos
   pedidos ──1:N── detalle_pedido
   productos ──1:N── detalle_pedido
   pedidos ──1:1── ventas
```

---

## Arquitectura

### Patrón: Capas Simples (Layered Architecture)

```
┌─────────────────────────────────────────────────┐
│                   NAVEGADOR                     │
│          (HTML + CSS + JavaScript)              │
├────────────┬────────────────────────────────────┤
│   VISTAS   │           API (JSON)               │
│  (views/)  │           (api/)                   │
├────────────┴────────────────────────────────────┤
│              CONTROLADORES                      │
│              (controllers/)                     │
├─────────────────────────────────────────────────┤
│               SERVICIOS                         │
│              (services/)                        │
│        Lógica de negocio                        │
├─────────────────────────────────────────────────┤
│                MODELOS                          │
│               (models/)                         │
│         Acceso a base de datos                  │
├─────────────────────────────────────────────────┤
│              BASE DE DATOS                      │
│               (MySQL)                           │
└─────────────────────────────────────────────────┘
```

**¿Por qué esta estructura?**

- **Modelos:** Solo SQL. No saben de HTTP ni de HTML.
- **Servicios:** Reglas de negocio. Ejemplo: "al finalizar pedido (pagado), crear venta automática".
- **Controladores:** Procesan datos de entrada y llaman a los servicios.
- **API:** Reciben peticiones HTTP y responden JSON.
- **Vistas:** Muestran HTML al usuario.

Esta separación hace que el código sea más fácil de entender, mantener y modificar.

---

## Reglas de Negocio

1. **Horarios:** Si el restaurante está cerrado, no se pueden crear pedidos, ventas ni reservaciones.
2. **Pedidos:** Al marcar un pedido como "Finalizado" (pagado), se genera una venta automáticamente.
3. **Mesas:** Al crear un pedido con mesa, la mesa se marca como "ocupada". Al finalizar o cancelar, vuelve a "disponible".
4. **Reservaciones:** No se puede reservar una mesa si ya tiene otra reservación en el mismo horario.
5. **Usuarios:** Un usuario "inactivo" no puede iniciar sesión.
6. **Contraseñas:** Se almacenan encriptadas con `password_hash()` (bcrypt).
7. **Roles:** Cada usuario tiene un solo rol que determina sus permisos.

---

## Solución de Problemas

### Error: "Error de conexión a la base de datos"
- Verificar que MySQL está corriendo en XAMPP
- Verificar usuario/contraseña en `config/database.php`

### Error: "403 Forbidden"
- Verificar que `mod_rewrite` está habilitado
- En XAMPP: Abrir `C:\xampp\apache\conf\httpd.conf` y buscar `mod_rewrite.so` - debe estar sin `#`

### Error al subir imágenes
- Verificar que la carpeta `uploads/productos/` tiene permisos de escritura
- En Windows: clic derecho -> Propiedades -> Seguridad -> permisos de escritura
- En Linux: `chmod 755 uploads/productos/`

### La página se ve sin estilos
- Verificar que la URL es correcta: `http://localhost/sazon-digital/`
- NO usar doble barra o rutas incorrectas

---

## Licencia

Proyecto universitario - Uso educativo.
