/**
 * Modelo Venta - Acceso a datos de ventas y detalles
 */
const { query, getClient } = require('../config/database');

const Venta = {
  /**
   * Registrar una venta completa (con transacción)
   * @param {Object} ventaData - { total, metodo_pago, offline_id, items: [{ producto_id, cantidad, precio_unit }] }
   */
  async create({ total, metodo_pago, offline_id, items }) {
    const client = await getClient();
    try {
      await client.query('BEGIN');

      // Verificar si ya existe (prevenir duplicados de sincronización offline)
      if (offline_id) {
        const existing = await client.query(
          'SELECT id FROM ventas WHERE offline_id = $1',
          [offline_id]
        );
        if (existing.rows.length > 0) {
          await client.query('COMMIT');
          return { duplicated: true, venta: existing.rows[0] };
        }
      }

      // Insertar la venta
      const ventaResult = await client.query(
        `INSERT INTO ventas (total, metodo_pago, offline_id, sincronizado)
         VALUES ($1, $2, $3, $4)
         RETURNING *`,
        [total, metodo_pago || 'efectivo', offline_id || null, !offline_id]
      );
      const venta = ventaResult.rows[0];

      // Insertar cada línea de detalle y descontar stock
      for (const item of items) {
        const subtotal = item.cantidad * item.precio_unit;

        await client.query(
          `INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unit, subtotal)
           VALUES ($1, $2, $3, $4, $5)`,
          [venta.id, item.producto_id, item.cantidad, item.precio_unit, subtotal]
        );

        // Descontar stock
        const stockResult = await client.query(
          `UPDATE productos
           SET stock_actual = stock_actual - $1
           WHERE id = $2 AND stock_actual >= $1
           RETURNING *`,
          [item.cantidad, item.producto_id]
        );

        if (stockResult.rows.length === 0) {
          throw new Error(`Stock insuficiente para producto ID ${item.producto_id}`);
        }
      }

      await client.query('COMMIT');
      return { duplicated: false, venta };
    } catch (error) {
      await client.query('ROLLBACK');
      throw error;
    } finally {
      client.release();
    }
  },

  /**
   * Sincronizar múltiples ventas offline
   */
  async syncOffline(ventasArray) {
    const results = [];
    for (const ventaData of ventasArray) {
      try {
        const result = await Venta.create(ventaData);
        results.push({ success: true, offline_id: ventaData.offline_id, ...result });
      } catch (error) {
        results.push({ success: false, offline_id: ventaData.offline_id, error: error.message });
      }
    }
    return results;
  },

  /**
   * Obtener todas las ventas con paginación
   */
  async findAll({ limit = 50, offset = 0, desde, hasta } = {}) {
    let sql = 'SELECT * FROM ventas WHERE 1=1';
    const params = [];

    if (desde) {
      params.push(desde);
      sql += ` AND created_at >= $${params.length}`;
    }
    if (hasta) {
      params.push(hasta);
      sql += ` AND created_at <= $${params.length}`;
    }

    sql += ' ORDER BY created_at DESC';
    sql += ` LIMIT $${params.length + 1} OFFSET $${params.length + 2}`;
    params.push(limit, offset);

    const result = await query(sql, params);
    return result.rows;
  },

  /**
   * Obtener venta por ID con sus detalles
   */
  async findById(id) {
    const ventaResult = await query('SELECT * FROM ventas WHERE id = $1', [id]);
    if (ventaResult.rows.length === 0) return null;

    const detallesResult = await query(
      `SELECT dv.*, p.nombre as producto_nombre, p.sku as producto_sku
       FROM detalle_ventas dv
       JOIN productos p ON p.id = dv.producto_id
       WHERE dv.venta_id = $1`,
      [id]
    );

    return {
      ...ventaResult.rows[0],
      detalles: detallesResult.rows,
    };
  },

  /**
   * Obtener velocidad de venta por producto en los últimos N días
   */
  async getRunRate(productoId, dias = 30) {
    const result = await query(
      `SELECT COALESCE(SUM(dv.cantidad), 0) as total_vendido
       FROM detalle_ventas dv
       JOIN ventas v ON v.id = dv.venta_id
       WHERE dv.producto_id = $1
         AND v.created_at >= NOW() - INTERVAL '1 day' * $2`,
      [productoId, dias]
    );
    const totalVendido = parseInt(result.rows[0].total_vendido, 10);
    return totalVendido / dias;
  },

  /**
   * Obtener resumen de ventas del día
   */
  async getDailySummary() {
    const result = await query(
      `SELECT
         COUNT(*) as total_ventas,
         COALESCE(SUM(total), 0) as ingresos_totales
       FROM ventas
       WHERE created_at >= CURRENT_DATE`
    );
    return result.rows[0];
  },
};

module.exports = Venta;
