/**
 * Servicio de Alertas - Mock de envío por WhatsApp (Twilio)
 */

/**
 * Envía un mensaje simulado de WhatsApp vía Twilio
 * En producción, aquí se usaría el SDK de Twilio
 */
const sendWhatsAppAlert = async (alerta) => {
  const { producto_nombre, dias_restantes, dias_entrega, mensaje } = alerta;

  // --- MOCK: Simulación de envío por Twilio ---
  const twilioConfig = {
    accountSid: process.env.TWILIO_ACCOUNT_SID,
    authToken: process.env.TWILIO_AUTH_TOKEN,
    from: process.env.TWILIO_WHATSAPP_FROM,
    to: process.env.TWILIO_WHATSAPP_TO,
  };

  console.log('═══════════════════════════════════════════════════');
  console.log('📱 MOCK WhatsApp Alert (Twilio)');
  console.log('═══════════════════════════════════════════════════');
  console.log(`  From: ${twilioConfig.from}`);
  console.log(`  To:   ${twilioConfig.to}`);
  console.log(`  Producto: ${producto_nombre}`);
  console.log(`  Días restantes: ${dias_restantes}`);
  console.log(`  Días entrega proveedor: ${dias_entrega}`);
  console.log(`  Mensaje: ${mensaje}`);
  console.log('═══════════════════════════════════════════════════');

  /*
   * En producción, descomentar y usar:
   *
   * const twilio = require('twilio');
   * const client = twilio(twilioConfig.accountSid, twilioConfig.authToken);
   * await client.messages.create({
   *   from: twilioConfig.from,
   *   to: twilioConfig.to,
   *   body: mensaje,
   * });
   */

  return {
    success: true,
    channel: 'whatsapp_mock',
    message: mensaje,
    timestamp: new Date().toISOString(),
  };
};

module.exports = { sendWhatsAppAlert };
