/**
 * Servidor Principal - Micro-ERP & POS Predictivo
 * API RESTful con Express.js
 */
require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const rateLimit = require('express-rate-limit');
const cron = require('node-cron');

// Rutas
const productosRoutes = require('./routes/productos');
const ventasRoutes = require('./routes/ventas');
const alertasRoutes = require('./routes/alertas');
const dashboardRoutes = require('./routes/dashboard');

// Middleware
const errorHandler = require('./middleware/errorHandler');

// Motor predictivo
const { runPredictiveEngine } = require('./scripts/predictiveEngine');

const app = express();
const PORT = process.env.PORT || 3001;

// ─── Middleware Global ───────────────────────────────────────
app.use(helmet());
app.use(cors({
  origin: process.env.FRONTEND_URL || 'http://localhost:5173',
  methods: ['GET', 'POST', 'PUT', 'DELETE'],
  credentials: true,
}));
app.use(morgan('dev'));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutos
  max: 1000,
  message: { success: false, error: 'Demasiadas solicitudes, intente más tarde' },
});
app.use('/api/', limiter);

// ─── Rutas de la API ─────────────────────────────────────────
app.use('/api/productos', productosRoutes);
app.use('/api/ventas', ventasRoutes);
app.use('/api/alertas', alertasRoutes);
app.use('/api/dashboard', dashboardRoutes);

// Health check
app.get('/api/health', (req, res) => {
  res.json({ success: true, message: 'Micro-ERP POS API funcionando', timestamp: new Date().toISOString() });
});

// 404 para rutas no encontradas
app.use('/api/*', (req, res) => {
  res.status(404).json({ success: false, error: 'Endpoint no encontrado' });
});

// ─── Error Handler Global ────────────────────────────────────
app.use(errorHandler);

// ─── Cron Job: Motor Predictivo (diario a las 6:00 AM) ──────
cron.schedule('0 6 * * *', async () => {
  console.log('⏰ Ejecutando motor predictivo programado (6:00 AM)...');
  try {
    await runPredictiveEngine();
  } catch (error) {
    console.error('Error en cron del motor predictivo:', error.message);
  }
}, {
  timezone: 'America/Mexico_City',
});

// ─── Iniciar Servidor ────────────────────────────────────────
app.listen(PORT, () => {
  console.log('');
  console.log('══════════════════════════════════════════════════');
  console.log('  🏪 Micro-ERP & POS Predictivo - API Server');
  console.log(`  🌐 http://localhost:${PORT}`);
  console.log(`  📊 Entorno: ${process.env.NODE_ENV || 'development'}`);
  console.log('  ⏰ Cron: Motor predictivo a las 6:00 AM diario');
  console.log('══════════════════════════════════════════════════');
  console.log('');
});

module.exports = app;
