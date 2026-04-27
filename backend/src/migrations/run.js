/**
 * Migración de la base de datos - Crea todas las tablas necesarias
 * Diseño ER: productos, ventas, detalle_ventas, alertas_predictivas
 */
require('dotenv').config({ path: require('path').join(__dirname, '../../.env') });
const { pool } = require('../config/database');

const migration = `
-- ============================================================
-- TABLA: productos
-- Almacena el catálogo de productos de la ferretería/abarrotes
-- ============================================================
CREATE TABLE IF NOT EXISTS productos (
  id            SERIAL PRIMARY KEY,
  nombre        VARCHAR(255) NOT NULL,
  sku           VARCHAR(100) NOT NULL UNIQUE,
  precio_compra DECIMAL(12, 2) NOT NULL CHECK (precio_compra >= 0),
  precio_venta  DECIMAL(12, 2) NOT NULL CHECK (precio_venta >= 0),
  stock_actual  INTEGER NOT NULL DEFAULT 0 CHECK (stock_actual >= 0),
  stock_minimo  INTEGER NOT NULL DEFAULT 5,
  dias_entrega  INTEGER NOT NULL DEFAULT 3 CHECK (dias_entrega >= 0),
  activo        BOOLEAN NOT NULL DEFAULT TRUE,
  created_at    TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at    TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================
-- TABLA: ventas
-- Registro de cada transacción de venta
-- ============================================================
CREATE TABLE IF NOT EXISTS ventas (
  id              SERIAL PRIMARY KEY,
  total           DECIMAL(12, 2) NOT NULL CHECK (total >= 0),
  metodo_pago     VARCHAR(50) NOT NULL DEFAULT 'efectivo',
  offline_id      VARCHAR(100) UNIQUE,
  sincronizado    BOOLEAN NOT NULL DEFAULT TRUE,
  created_at      TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================
-- TABLA: detalle_ventas
-- Líneas de cada venta (productos vendidos)
-- ============================================================
CREATE TABLE IF NOT EXISTS detalle_ventas (
  id            SERIAL PRIMARY KEY,
  venta_id      INTEGER NOT NULL REFERENCES ventas(id) ON DELETE CASCADE,
  producto_id   INTEGER NOT NULL REFERENCES productos(id) ON DELETE RESTRICT,
  cantidad      INTEGER NOT NULL CHECK (cantidad > 0),
  precio_unit   DECIMAL(12, 2) NOT NULL CHECK (precio_unit >= 0),
  subtotal      DECIMAL(12, 2) NOT NULL CHECK (subtotal >= 0),
  created_at    TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================
-- TABLA: alertas_predictivas
-- Alertas generadas por el motor predictivo
-- ============================================================
CREATE TABLE IF NOT EXISTS alertas_predictivas (
  id                    SERIAL PRIMARY KEY,
  producto_id           INTEGER NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
  dias_restantes        DECIMAL(8, 2) NOT NULL,
  velocidad_venta_dia   DECIMAL(10, 4) NOT NULL,
  dias_entrega          INTEGER NOT NULL,
  mensaje               TEXT NOT NULL,
  enviada               BOOLEAN NOT NULL DEFAULT FALSE,
  canal                 VARCHAR(50) NOT NULL DEFAULT 'whatsapp_mock',
  created_at            TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================
-- ÍNDICES para mejorar rendimiento
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_productos_sku ON productos(sku);
CREATE INDEX IF NOT EXISTS idx_productos_nombre ON productos(nombre);
CREATE INDEX IF NOT EXISTS idx_productos_activo ON productos(activo);
CREATE INDEX IF NOT EXISTS idx_ventas_created_at ON ventas(created_at);
CREATE INDEX IF NOT EXISTS idx_ventas_offline_id ON ventas(offline_id);
CREATE INDEX IF NOT EXISTS idx_detalle_ventas_venta_id ON detalle_ventas(venta_id);
CREATE INDEX IF NOT EXISTS idx_detalle_ventas_producto_id ON detalle_ventas(producto_id);
CREATE INDEX IF NOT EXISTS idx_alertas_producto_id ON alertas_predictivas(producto_id);
CREATE INDEX IF NOT EXISTS idx_alertas_created_at ON alertas_predictivas(created_at);

-- ============================================================
-- FUNCIÓN: Actualizar updated_at automáticamente
-- ============================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ language 'plpgsql';

-- Trigger para productos
DROP TRIGGER IF EXISTS update_productos_updated_at ON productos;
CREATE TRIGGER update_productos_updated_at
  BEFORE UPDATE ON productos
  FOR EACH ROW
  EXECUTE FUNCTION update_updated_at_column();
`;

async function runMigration() {
  console.log('🔄 Ejecutando migración de la base de datos...');
  try {
    await pool.query(migration);
    console.log('✅ Migración completada exitosamente.');
    console.log('   Tablas creadas: productos, ventas, detalle_ventas, alertas_predictivas');
  } catch (error) {
    console.error('❌ Error en la migración:', error.message);
    process.exit(1);
  } finally {
    await pool.end();
  }
}

runMigration();
