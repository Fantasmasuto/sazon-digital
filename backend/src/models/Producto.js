/**
 * Modelo Producto - Acceso a datos de productos
 */
const { query } = require('../config/database');

const Producto = {
  /**
   * Obtener todos los productos activos
   */
  async findAll({ search, limit = 50, offset = 0 } = {}) {
    let sql = 'SELECT * FROM productos WHERE activo = TRUE';
    const params = [];

    if (search) {
      params.push(`%${search}%`, `%${search}%`);
      sql += ` AND (nombre ILIKE $1 OR sku ILIKE $2)`;
    }

    sql += ' ORDER BY nombre ASC';
    sql += ` LIMIT $${params.length + 1} OFFSET $${params.length + 2}`;
    params.push(limit, offset);

    const result = await query(sql, params);
    return result.rows;
  },

  /**
   * Obtener producto por ID
   */
  async findById(id) {
    const result = await query('SELECT * FROM productos WHERE id = $1', [id]);
    return result.rows[0] || null;
  },

  /**
   * Obtener producto por SKU
   */
  async findBySku(sku) {
    const result = await query('SELECT * FROM productos WHERE sku = $1', [sku]);
    return result.rows[0] || null;
  },

  /**
   * Crear un nuevo producto
   */
  async create({ nombre, sku, precio_compra, precio_venta, stock_actual, stock_minimo, dias_entrega }) {
    const result = await query(
      `INSERT INTO productos (nombre, sku, precio_compra, precio_venta, stock_actual, stock_minimo, dias_entrega)
       VALUES ($1, $2, $3, $4, $5, $6, $7)
       RETURNING *`,
      [nombre, sku, precio_compra, precio_venta, stock_actual || 0, stock_minimo || 5, dias_entrega || 3]
    );
    return result.rows[0];
  },

  /**
   * Actualizar un producto existente
   */
  async update(id, { nombre, sku, precio_compra, precio_venta, stock_actual, stock_minimo, dias_entrega }) {
    const result = await query(
      `UPDATE productos
       SET nombre = $1, sku = $2, precio_compra = $3, precio_venta = $4,
           stock_actual = $5, stock_minimo = $6, dias_entrega = $7
       WHERE id = $8
       RETURNING *`,
      [nombre, sku, precio_compra, precio_venta, stock_actual, stock_minimo, dias_entrega, id]
    );
    return result.rows[0] || null;
  },

  /**
   * Desactivar un producto (soft delete)
   */
  async delete(id) {
    const result = await query(
      'UPDATE productos SET activo = FALSE WHERE id = $1 RETURNING *',
      [id]
    );
    return result.rows[0] || null;
  },

  /**
   * Descontar stock tras una venta
   */
  async decrementStock(id, cantidad) {
    const result = await query(
      `UPDATE productos
       SET stock_actual = stock_actual - $1
       WHERE id = $2 AND stock_actual >= $1
       RETURNING *`,
      [cantidad, id]
    );
    return result.rows[0] || null;
  },

  /**
   * Contar total de productos activos (para paginación)
   */
  async count({ search } = {}) {
    let sql = 'SELECT COUNT(*) as total FROM productos WHERE activo = TRUE';
    const params = [];

    if (search) {
      params.push(`%${search}%`, `%${search}%`);
      sql += ` AND (nombre ILIKE $1 OR sku ILIKE $2)`;
    }

    const result = await query(sql, params);
    return parseInt(result.rows[0].total, 10);
  },
};

module.exports = Producto;
