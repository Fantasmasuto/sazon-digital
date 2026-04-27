/**
 * Modelo Alerta - Acceso a datos de alertas predictivas
 */
const { query } = require('../config/database');

const Alerta = {
  /**
   * Crear una nueva alerta predictiva
   */
  async create({ producto_id, dias_restantes, velocidad_venta_dia, dias_entrega, mensaje }) {
    const result = await query(
      `INSERT INTO alertas_predictivas (producto_id, dias_restantes, velocidad_venta_dia, dias_entrega, mensaje)
       VALUES ($1, $2, $3, $4, $5)
       RETURNING *`,
      [producto_id, dias_restantes, velocidad_venta_dia, dias_entrega, mensaje]
    );
    return result.rows[0];
  },

  /**
   * Obtener alertas recientes
   */
  async findRecent({ limit = 50, offset = 0 } = {}) {
    const result = await query(
      `SELECT ap.*, p.nombre as producto_nombre, p.sku as producto_sku, p.stock_actual
       FROM alertas_predictivas ap
       JOIN productos p ON p.id = ap.producto_id
       ORDER BY ap.created_at DESC
       LIMIT $1 OFFSET $2`,
      [limit, offset]
    );
    return result.rows;
  },

  /**
   * Marcar alerta como enviada
   */
  async markSent(id) {
    const result = await query(
      'UPDATE alertas_predictivas SET enviada = TRUE WHERE id = $1 RETURNING *',
      [id]
    );
    return result.rows[0] || null;
  },

  /**
   * Obtener alertas no enviadas
   */
  async findPending() {
    const result = await query(
      `SELECT ap.*, p.nombre as producto_nombre, p.sku as producto_sku
       FROM alertas_predictivas ap
       JOIN productos p ON p.id = ap.producto_id
       WHERE ap.enviada = FALSE
       ORDER BY ap.dias_restantes ASC`
    );
    return result.rows;
  },

  /**
   * Contar alertas activas (últimas 24h)
   */
  async countActive() {
    const result = await query(
      `SELECT COUNT(*) as total
       FROM alertas_predictivas
       WHERE created_at >= NOW() - INTERVAL '24 hours'`
    );
    return parseInt(result.rows[0].total, 10);
  },
};

module.exports = Alerta;
