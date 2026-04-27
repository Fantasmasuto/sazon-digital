# Micro-ERP & POS Predictivo - Frontend PWA

Interfaz de usuario Progressive Web App con soporte offline-first.

## Stack

- **React 19** + **Vite**
- **React Router** v7
- **IndexedDB** (via `idb`) para almacenamiento offline
- **Service Workers** para PWA
- **Lucide React** para iconos

## Setup

```bash
cd frontend
npm install
npm run dev    # Servidor de desarrollo (puerto 5173)
npm run build  # Build de producción
```

## Módulos

- **Dashboard**: Resumen de productos, ventas e inventario
- **POS**: Punto de venta con soporte offline-first
- **Inventario**: CRUD completo de productos
- **Alertas**: Visualización de alertas predictivas

## Offline-First

- Las ventas se guardan en IndexedDB cuando no hay conexión
- Al recuperar conexión, se sincronizan automáticamente con el backend
- Los productos se cachean localmente para búsqueda offline
- Service Worker cachea assets y respuestas API para uso sin conexión
