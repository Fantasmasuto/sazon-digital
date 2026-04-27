/**
 * Rutas API - Alertas Predictivas
 */
const express = require('express');
const router = express.Router();
const Alerta = require('../models/Alerta');
const { runPredictiveEngine } = require('../scripts/predictiveEngine');

/**
 * GET /api/alertas
 * Obtener alertas recientes
 */
router.get('/', async (req, res, next) => {
  try {
    const { limit = 50, offset = 0 } = req.query;
    const alertas = await Alerta.findRecent({
      limit: parseInt(limit, 10),
      offset: parseInt(offset, 10),
    });

    res.json({ success: true, data: alertas });
  } catch (error) {
    next(error);
  }
});

/**
 * GET /api/alertas/pending
 * Obtener alertas pendientes de enviar
 */
router.get('/pending', async (req, res, next) => {
  try {
    const alertas = await Alerta.findPending();
    res.json({ success: true, data: alertas });
  } catch (error) {
    next(error);
  }
});

/**
 * POST /api/alertas/run
 * Ejecutar el motor predictivo manualmente
 */
router.post('/run', async (req, res, next) => {
  try {
    const result = await runPredictiveEngine();
    res.json({
      success: true,
      message: 'Motor predictivo ejecutado exitosamente',
      data: result,
    });
  } catch (error) {
    next(error);
  }
});

module.exports = router;
