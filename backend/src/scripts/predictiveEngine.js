/**
 * Motor Predictivo - El Cerebro del Sistema
 *
 * Calcula el Run-Rate (velocidad de venta diaria) de cada producto
 * y genera alertas cuando el inventario está en riesgo de agotarse
 * antes de que el proveedor pueda resurtir.
 *
 * Fórmula:
 *   velocidad_venta_dia = total_vendido_30_dias / 30
 *   dias_restantes = stock_actual / velocidad_venta_dia
 *   Si dias_restantes <= dias_entrega + 2 (margen) → ALERTA
 */
require('dotenv').config({ path: require('path').join(__dirname, '../../.env') });
const Producto = require('../models/Producto');
const Venta = require('../models/Venta');
const Alerta = require('../models/Alerta');
const { sendWhatsAppAlert } = require('../services/alertService');

const MARGEN_SEGURIDAD_DIAS = 2;
const DIAS_ANALISIS = 30;

async function runPredictiveEngine() {
  console.log('🧠 Iniciando Motor Predictivo...');
  console.log(`   Período de análisis: ${DIAS_ANALISIS} días`);
  console.log(`   Margen de seguridad: ${MARGEN_SEGURIDAD_DIAS} días`);
  console.log('');

  try {
    const productos = await Producto.findAll({ limit: 10000 });
    let alertasGeneradas = 0;

    for (const producto of productos) {
      const velocidadVentaDia = await Venta.getRunRate(producto.id, DIAS_ANALISIS);

      // Si no hay ventas, no hay riesgo de agotarse
      if (velocidadVentaDia === 0) {
        console.log(`  ⚪ ${producto.nombre} (${producto.sku}): Sin ventas en ${DIAS_ANALISIS} días`);
        continue;
      }

      const diasRestantes = producto.stock_actual / velocidadVentaDia;
      const umbralAlerta = producto.dias_entrega + MARGEN_SEGURIDAD_DIAS;

      console.log(`  📊 ${producto.nombre} (${producto.sku}):`);
      console.log(`     Stock: ${producto.stock_actual} | Vel: ${velocidadVentaDia.toFixed(2)}/día | Días restantes: ${diasRestantes.toFixed(1)} | Umbral: ${umbralAlerta}`);

      if (diasRestantes <= umbralAlerta) {
        const mensaje = `Alerta: El producto ${producto.nombre} se agotará en ${Math.round(diasRestantes)} días. Tu proveedor tarda ${producto.dias_entrega} días en surtir. Te sugerimos realizar un pedido hoy.`;

        // Guardar alerta en la base de datos
        const alerta = await Alerta.create({
          producto_id: producto.id,
          dias_restantes: diasRestantes,
          velocidad_venta_dia: velocidadVentaDia,
          dias_entrega: producto.dias_entrega,
          mensaje,
        });

        // Enviar mock de WhatsApp
        await sendWhatsAppAlert({
          ...alerta,
          producto_nombre: producto.nombre,
        });

        // Marcar como enviada
        await Alerta.markSent(alerta.id);
        alertasGeneradas++;

        console.log(`     🔴 ALERTA GENERADA - Se agota en ${Math.round(diasRestantes)} días`);
      } else {
        console.log(`     🟢 OK`);
      }
    }

    console.log('');
    console.log(`🧠 Motor Predictivo finalizado. Alertas generadas: ${alertasGeneradas}`);
    return { alertasGeneradas };
  } catch (error) {
    console.error('❌ Error en el motor predictivo:', error.message);
    throw error;
  }
}

// Si se ejecuta directamente (npm run predict)
if (require.main === module) {
  runPredictiveEngine()
    .then(() => process.exit(0))
    .catch(() => process.exit(1));
}

module.exports = { runPredictiveEngine };
