/**
 * Cliente API - Comunicación con el backend
 */
const API_BASE = '/api';

async function request(endpoint, options = {}) {
  const url = `${API_BASE}${endpoint}`;
  const config = {
    headers: { 'Content-Type': 'application/json' },
    ...options,
  };

  if (config.body && typeof config.body === 'object') {
    config.body = JSON.stringify(config.body);
  }

  const response = await fetch(url, config);
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.error || `Error ${response.status}`);
  }

  return data;
}

export const api = {
  // Productos
  getProductos: (params = '') => request(`/productos${params ? `?${params}` : ''}`),
  getProducto: (id) => request(`/productos/${id}`),
  createProducto: (data) => request('/productos', { method: 'POST', body: data }),
  updateProducto: (id, data) => request(`/productos/${id}`, { method: 'PUT', body: data }),
  deleteProducto: (id) => request(`/productos/${id}`, { method: 'DELETE' }),

  // Ventas
  getVentas: (params = '') => request(`/ventas${params ? `?${params}` : ''}`),
  getVenta: (id) => request(`/ventas/${id}`),
  createVenta: (data) => request('/ventas', { method: 'POST', body: data }),
  syncVentas: (ventas) => request('/ventas/sync', { method: 'POST', body: { ventas } }),
  getResumenVentas: () => request('/ventas/resumen'),

  // Alertas
  getAlertas: () => request('/alertas'),
  getAlertasPending: () => request('/alertas/pending'),
  runMotorPredictivo: () => request('/alertas/run', { method: 'POST' }),

  // Dashboard
  getDashboard: () => request('/dashboard'),
};
