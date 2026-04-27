/**
 * Rutas API - Ventas (registro y sincronización offline)
 */
const express = require('express');
const router = express.Router();
const Venta = require('../models/Venta');
const { requireFields, validateId } = require('../middleware/validate');

/**
 * GET /api/ventas
 * Obtener ventas con filtro de fechas
 */
router.get('/', async (req, res, next) => {
  try {
    const { limit = 50, offset = 0, desde, hasta } = req.query;
    const ventas = await Venta.findAll({
      limit: parseInt(limit, 10),
      offset: parseInt(offset, 10),
      desde,
      hasta,
    });

    res.json({ success: true, data: ventas });
  } catch (error) {
    next(error);
  }
});

/**
 * GET /api/ventas/resumen
 * Obtener resumen de ventas del día
 */
router.get('/resumen', async (req, res, next) => {
  try {
    const resumen = await Venta.getDailySummary();
    res.json({ success: true, data: resumen });
  } catch (error) {
    next(error);
  }
});

/**
 * GET /api/ventas/:id
 * Obtener una venta con sus detalles
 */
router.get('/:id', validateId, async (req, res, next) => {
  try {
    const venta = await Venta.findById(req.params.id);
    if (!venta) {
      return res.status(404).json({ success: false, error: 'Venta no encontrada' });
    }
    res.json({ success: true, data: venta });
  } catch (error) {
    next(error);
  }
});

/**
 * POST /api/ventas
 * Registrar una nueva venta
 */
router.post(
  '/',
  requireFields(['total', 'items']),
  async (req, res, next) => {
    try {
      const { total, metodo_pago, offline_id, items } = req.body;

      if (!Array.isArray(items) || items.length === 0) {
        return res.status(400).json({
          success: false,
          error: 'La venta debe contener al menos un item',
        });
      }

      const result = await Venta.create({ total, metodo_pago, offline_id, items });

      if (result.duplicated) {
        return res.status(200).json({
          success: true,
          duplicated: true,
          message: 'Venta ya registrada (sincronización duplicada)',
          data: result.venta,
        });
      }

      res.status(201).json({ success: true, data: result.venta });
    } catch (error) {
      next(error);
    }
  }
);

/**
 * POST /api/ventas/sync
 * Sincronizar múltiples ventas offline
 */
router.post('/sync', async (req, res, next) => {
  try {
    const { ventas } = req.body;

    if (!Array.isArray(ventas) || ventas.length === 0) {
      return res.status(400).json({
        success: false,
        error: 'Debe enviar un array de ventas para sincronizar',
      });
    }

    const results = await Venta.syncOffline(ventas);

    const synced = results.filter((r) => r.success).length;
    const failed = results.filter((r) => !r.success).length;
    const duplicated = results.filter((r) => r.duplicated).length;

    res.json({
      success: true,
      summary: { synced, failed, duplicated, total: ventas.length },
      results,
    });
  } catch (error) {
    next(error);
  }
});

module.exports = router;
