/**
 * Rutas API - Dashboard (resumen general)
 */
const express = require('express');
const router = express.Router();
const Producto = require('../models/Producto');
const Venta = require('../models/Venta');
const Alerta = require('../models/Alerta');

/**
 * GET /api/dashboard
 * Obtener datos del dashboard
 */
router.get('/', async (req, res, next) => {
  try {
    const [totalProductos, ventasDiarias, alertasActivas] = await Promise.all([
      Producto.count(),
      Venta.getDailySummary(),
      Alerta.countActive(),
    ]);

    res.json({
      success: true,
      data: {
        productos: { total: totalProductos },
        ventas_hoy: ventasDiarias,
        alertas_activas: alertasActivas,
      },
    });
  } catch (error) {
    next(error);
  }
});

module.exports = router;
