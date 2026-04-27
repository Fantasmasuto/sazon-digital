/**
 * Seed - Datos iniciales de prueba para desarrollo
 */
require('dotenv').config({ path: require('path').join(__dirname, '../../.env') });
const { pool } = require('../config/database');

const seedData = `
INSERT INTO productos (nombre, sku, precio_compra, precio_venta, stock_actual, stock_minimo, dias_entrega) VALUES
  ('Cemento Portland 50kg', 'CEM-001', 180.00, 220.00, 45, 10, 5),
  ('Varilla 3/8"', 'VAR-002', 85.00, 110.00, 120, 20, 3),
  ('Clavo 2.5" (1kg)', 'CLV-003', 25.00, 38.00, 200, 30, 2),
  ('Tubo PVC 4" (3m)', 'TUB-004', 95.00, 135.00, 30, 8, 7),
  ('Pintura Vinílica 19L', 'PIN-005', 450.00, 620.00, 15, 5, 4),
  ('Cable THW 12 (100m)', 'CAB-006', 680.00, 850.00, 25, 5, 6),
  ('Foco LED 10W', 'FOC-007', 18.00, 35.00, 300, 50, 2),
  ('Llave Stilson 14"', 'LLA-008', 220.00, 310.00, 8, 3, 10),
  ('Resistol 5000 (1L)', 'RES-009', 75.00, 105.00, 40, 10, 3),
  ('Azulejo 30x30 (m2)', 'AZU-010', 120.00, 175.00, 60, 15, 5),
  ('Arroz 1kg', 'ARR-011', 18.00, 28.00, 150, 40, 1),
  ('Aceite 1L', 'ACE-012', 32.00, 45.00, 80, 20, 2),
  ('Jabón en barra', 'JAB-013', 8.00, 15.00, 200, 50, 1),
  ('Escoba básica', 'ESC-014', 35.00, 55.00, 25, 8, 3),
  ('Cinta de aislar', 'CIN-015', 12.00, 22.00, 100, 25, 2)
ON CONFLICT (sku) DO NOTHING;

-- Insertar ventas de ejemplo (últimos 30 días) para que el motor predictivo funcione
INSERT INTO ventas (total, metodo_pago, created_at) VALUES
  (660.00, 'efectivo', NOW() - INTERVAL '1 day'),
  (1230.00, 'tarjeta', NOW() - INTERVAL '2 days'),
  (380.00, 'efectivo', NOW() - INTERVAL '3 days'),
  (850.00, 'efectivo', NOW() - INTERVAL '5 days'),
  (1540.00, 'tarjeta', NOW() - INTERVAL '7 days'),
  (220.00, 'efectivo', NOW() - INTERVAL '10 days'),
  (990.00, 'efectivo', NOW() - INTERVAL '12 days'),
  (1800.00, 'tarjeta', NOW() - INTERVAL '15 days'),
  (450.00, 'efectivo', NOW() - INTERVAL '18 days'),
  (670.00, 'efectivo', NOW() - INTERVAL '20 days'),
  (1100.00, 'tarjeta', NOW() - INTERVAL '22 days'),
  (300.00, 'efectivo', NOW() - INTERVAL '25 days'),
  (780.00, 'efectivo', NOW() - INTERVAL '28 days');

-- Detalles de ventas para simular movimiento de inventario
INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unit, subtotal) VALUES
  (1, 1, 3, 220.00, 660.00),
  (2, 2, 5, 110.00, 550.00),
  (2, 5, 1, 620.00, 620.00),
  (2, 7, 2, 35.00, 70.00),
  (3, 3, 10, 38.00, 380.00),
  (4, 6, 1, 850.00, 850.00),
  (5, 4, 3, 135.00, 405.00),
  (5, 10, 5, 175.00, 875.00),
  (5, 15, 12, 22.00, 264.00),
  (6, 1, 1, 220.00, 220.00),
  (7, 11, 15, 28.00, 420.00),
  (7, 12, 8, 45.00, 360.00),
  (7, 13, 20, 15.00, 300.00),
  (8, 2, 8, 110.00, 880.00),
  (8, 9, 5, 105.00, 525.00),
  (8, 14, 7, 55.00, 385.00),
  (9, 7, 10, 35.00, 350.00),
  (9, 15, 5, 22.00, 110.00),
  (10, 1, 2, 220.00, 440.00),
  (10, 3, 6, 38.00, 228.00),
  (11, 5, 1, 620.00, 620.00),
  (11, 8, 1, 310.00, 310.00),
  (11, 15, 8, 22.00, 176.00),
  (12, 11, 5, 28.00, 140.00),
  (12, 13, 10, 15.00, 150.00),
  (13, 4, 2, 135.00, 270.00),
  (13, 9, 3, 105.00, 315.00),
  (13, 12, 4, 45.00, 180.00);
`;

async function runSeed() {
  console.log('🌱 Insertando datos de prueba...');
  try {
    await pool.query(seedData);
    console.log('✅ Datos de prueba insertados exitosamente.');
  } catch (error) {
    console.error('❌ Error al insertar datos:', error.message);
    process.exit(1);
  } finally {
    await pool.end();
  }
}

runSeed();
