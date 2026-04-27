/**
 * Rutas API - Productos (CRUD completo)
 */
const express = require('express');
const router = express.Router();
const Producto = require('../models/Producto');
const { requireFields, validateId } = require('../middleware/validate');

/**
 * GET /api/productos
 * Obtener todos los productos (con búsqueda y paginación)
 */
router.get('/', async (req, res, next) => {
  try {
    const { search, limit = 50, offset = 0 } = req.query;
    const [productos, total] = await Promise.all([
      Producto.findAll({ search, limit: parseInt(limit, 10), offset: parseInt(offset, 10) }),
      Producto.count({ search }),
    ]);

    res.json({
      success: true,
      data: productos,
      pagination: { total, limit: parseInt(limit, 10), offset: parseInt(offset, 10) },
    });
  } catch (error) {
    next(error);
  }
});

/**
 * GET /api/productos/:id
 * Obtener un producto por ID
 */
router.get('/:id', validateId, async (req, res, next) => {
  try {
    const producto = await Producto.findById(req.params.id);
    if (!producto) {
      return res.status(404).json({ success: false, error: 'Producto no encontrado' });
    }
    res.json({ success: true, data: producto });
  } catch (error) {
    next(error);
  }
});

/**
 * POST /api/productos
 * Crear un nuevo producto
 */
router.post(
  '/',
  requireFields(['nombre', 'sku', 'precio_compra', 'precio_venta']),
  async (req, res, next) => {
    try {
      const producto = await Producto.create(req.body);
      res.status(201).json({ success: true, data: producto });
    } catch (error) {
      next(error);
    }
  }
);

/**
 * PUT /api/productos/:id
 * Actualizar un producto
 */
router.put(
  '/:id',
  validateId,
  requireFields(['nombre', 'sku', 'precio_compra', 'precio_venta']),
  async (req, res, next) => {
    try {
      const producto = await Producto.update(req.params.id, req.body);
      if (!producto) {
        return res.status(404).json({ success: false, error: 'Producto no encontrado' });
      }
      res.json({ success: true, data: producto });
    } catch (error) {
      next(error);
    }
  }
);

/**
 * DELETE /api/productos/:id
 * Eliminar (desactivar) un producto
 */
router.delete('/:id', validateId, async (req, res, next) => {
  try {
    const producto = await Producto.delete(req.params.id);
    if (!producto) {
      return res.status(404).json({ success: false, error: 'Producto no encontrado' });
    }
    res.json({ success: true, data: producto, message: 'Producto desactivado' });
  } catch (error) {
    next(error);
  }
});

module.exports = router;
