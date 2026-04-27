# Micro-ERP & POS Predictivo - Backend API

API RESTful para el sistema de Punto de Venta y ERP predictivo.

## Stack

- **Node.js** + **Express.js**
- **PostgreSQL** 14+
- **node-cron** para el motor predictivo

## Setup

```bash
cd backend
cp .env.example .env   # Configurar variables de entorno
npm install
npm run migrate        # Crear tablas
npm run seed           # Datos de prueba
npm start              # Iniciar servidor (puerto 3001)
```

## Endpoints API

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/health` | Health check |
| GET | `/api/dashboard` | Datos del dashboard |
| GET | `/api/productos` | Listar productos |
| GET | `/api/productos/:id` | Obtener producto |
| POST | `/api/productos` | Crear producto |
| PUT | `/api/productos/:id` | Actualizar producto |
| DELETE | `/api/productos/:id` | Desactivar producto |
| GET | `/api/ventas` | Listar ventas |
| GET | `/api/ventas/:id` | Detalle de venta |
| POST | `/api/ventas` | Registrar venta |
| POST | `/api/ventas/sync` | Sincronizar ventas offline |
| GET | `/api/alertas` | Alertas predictivas |
| POST | `/api/alertas/run` | Ejecutar motor predictivo |

## Motor Predictivo

Ejecuta diariamente a las 6:00 AM (cron) o manualmente:

```bash
npm run predict
```

Algoritmo:
1. Calcula velocidad de venta diaria (últimos 30 días)
2. Divide stock actual / velocidad = días restantes
3. Si días restantes <= días_entrega + 2 → genera alerta
