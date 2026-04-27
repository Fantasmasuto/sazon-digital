/**
 * IndexedDB Service - Almacenamiento offline para ventas
 * Usa la librería 'idb' para una API más limpia con Promises
 */
import { openDB } from 'idb';

const DB_NAME = 'micro-erp-pos';
const DB_VERSION = 1;
const STORE_VENTAS = 'ventas_offline';
const STORE_PRODUCTOS = 'productos_cache';

let dbPromise = null;

function getDB() {
  if (!dbPromise) {
    dbPromise = openDB(DB_NAME, DB_VERSION, {
      upgrade(db) {
        // Store para ventas pendientes de sincronización
        if (!db.objectStoreNames.contains(STORE_VENTAS)) {
          const ventasStore = db.createObjectStore(STORE_VENTAS, { keyPath: 'offline_id' });
          ventasStore.createIndex('created_at', 'created_at');
        }
        // Store para cache de productos (para búsqueda offline)
        if (!db.objectStoreNames.contains(STORE_PRODUCTOS)) {
          const productosStore = db.createObjectStore(STORE_PRODUCTOS, { keyPath: 'id' });
          productosStore.createIndex('nombre', 'nombre');
          productosStore.createIndex('sku', 'sku');
        }
      },
    });
  }
  return dbPromise;
}

// ─── Ventas Offline ──────────────────────────────────────────

/**
 * Guardar una venta offline en IndexedDB
 */
export async function saveOfflineVenta(venta) {
  const db = await getDB();
  await db.put(STORE_VENTAS, {
    ...venta,
    offline_id: venta.offline_id || `offline-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
    created_at: new Date().toISOString(),
  });
}

/**
 * Obtener todas las ventas pendientes de sincronización
 */
export async function getPendingVentas() {
  const db = await getDB();
  return db.getAll(STORE_VENTAS);
}

/**
 * Eliminar una venta offline (tras sincronizar exitosamente)
 */
export async function removeOfflineVenta(offlineId) {
  const db = await getDB();
  await db.delete(STORE_VENTAS, offlineId);
}

/**
 * Eliminar todas las ventas sincronizadas
 */
export async function clearSyncedVentas(offlineIds) {
  const db = await getDB();
  const tx = db.transaction(STORE_VENTAS, 'readwrite');
  for (const id of offlineIds) {
    await tx.store.delete(id);
  }
  await tx.done;
}

/**
 * Contar ventas pendientes
 */
export async function countPendingVentas() {
  const db = await getDB();
  return db.count(STORE_VENTAS);
}

// ─── Cache de Productos ─────────────────────────────────────

/**
 * Actualizar cache local de productos
 */
export async function cacheProductos(productos) {
  const db = await getDB();
  const tx = db.transaction(STORE_PRODUCTOS, 'readwrite');
  // Limpiar y reescribir
  await tx.store.clear();
  for (const producto of productos) {
    await tx.store.put(producto);
  }
  await tx.done;
}

/**
 * Obtener productos desde cache local
 */
export async function getCachedProductos() {
  const db = await getDB();
  return db.getAll(STORE_PRODUCTOS);
}
