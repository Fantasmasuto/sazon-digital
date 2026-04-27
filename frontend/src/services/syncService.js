/**
 * Servicio de Sincronización - Detecta conexión y sincroniza ventas offline
 */
import { getPendingVentas, clearSyncedVentas } from './offlineDB';
import { api } from '../utils/api';

let syncInProgress = false;

/**
 * Sincronizar ventas pendientes con el backend
 */
export async function syncPendingVentas() {
  if (syncInProgress) return { skipped: true };
  syncInProgress = true;

  try {
    const pendingVentas = await getPendingVentas();
    if (pendingVentas.length === 0) {
      return { synced: 0 };
    }

    console.log(`[Sync] Sincronizando ${pendingVentas.length} ventas offline...`);

    const result = await api.syncVentas(pendingVentas);

    // Eliminar las ventas sincronizadas exitosamente
    const syncedIds = result.results
      .filter((r) => r.success)
      .map((r) => r.offline_id);

    if (syncedIds.length > 0) {
      await clearSyncedVentas(syncedIds);
    }

    console.log(`[Sync] Resultado: ${syncedIds.length} sincronizadas, ${result.summary.failed} fallidas`);
    return result.summary;
  } catch (error) {
    console.error('[Sync] Error al sincronizar:', error.message);
    return { error: error.message };
  } finally {
    syncInProgress = false;
  }
}

/**
 * Registrar listeners de conexión para sincronización automática
 */
export function registerSyncListeners(onSyncComplete) {
  const handleOnline = async () => {
    console.log('[Sync] Conexión restaurada. Iniciando sincronización...');
    const result = await syncPendingVentas();
    if (onSyncComplete) onSyncComplete(result);
  };

  window.addEventListener('online', handleOnline);

  // También intentar sincronizar al cargar la app
  if (navigator.onLine) {
    syncPendingVentas().then((result) => {
      if (onSyncComplete && result.synced > 0) onSyncComplete(result);
    });
  }

  return () => {
    window.removeEventListener('online', handleOnline);
  };
}
