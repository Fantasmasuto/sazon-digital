/**
 * Sazón Digital - Cajero JavaScript
 * Cobrar pedidos y corte de caja
 */

let pedidoACobrar = null;

document.addEventListener('DOMContentLoaded', function() {
    loadCajeroData();
});

async function loadCajeroData() {
    await Promise.all([
        loadPedidosPorCobrar(),
        loadVentasHoy(),
        loadCorteCaja()
    ]);
}

// Cargar pedidos entregados (listos para cobrar)
async function loadPedidosPorCobrar() {
    const pedidos = await apiFetch('api/pedidos.php');
    if (!pedidos || !Array.isArray(pedidos)) return;

    // Filtrar solo pedidos en estado "Entregado" (estado_id = 4)
    const porCobrar = pedidos.filter(p => p.estado_id == 4);
    const tbody = document.getElementById('pedidos-cobrar-body');

    if (porCobrar.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty-state">No hay pedidos por cobrar</td></tr>';
        return;
    }

    tbody.innerHTML = porCobrar.map(pedido => `
        <tr>
            <td><strong>#${pedido.id}</strong></td>
            <td>Mesa ${pedido.mesa_numero || 'S/N'}</td>
            <td>${pedido.mesero_nombre || '-'}</td>
            <td>${pedido.total_productos || '-'} productos</td>
            <td><strong style="font-size: 1.1rem; color: #FF6B00;">${formatPrice(pedido.total)}</strong></td>
            <td>
                <select class="form-control" id="metodo-${pedido.id}" style="min-width: 120px;">
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </td>
            <td>
                <button class="btn btn-success btn-sm" onclick="abrirCobro(${pedido.id})">
                    Cobrar
                </button>
            </td>
        </tr>
    `).join('');
}

// Abrir modal de cobro con detalle del pedido
async function abrirCobro(pedidoId) {
    const pedido = await apiFetch('api/pedidos.php?id=' + pedidoId);
    if (!pedido) return;

    pedidoACobrar = pedido;

    document.getElementById('cobrar-pedido-num').textContent = pedido.id;
    document.getElementById('cobrar-mesa').textContent = 'Mesa ' + (pedido.mesa_numero || 'S/N');
    document.getElementById('cobrar-mesero').textContent = pedido.mesero_nombre || '-';
    document.getElementById('cobrar-total').textContent = formatPrice(pedido.total);

    // Mostrar detalle de productos
    let detalleHtml = '<table style="width:100%; margin-top:10px; font-size:0.9rem;">';
    detalleHtml += '<tr style="border-bottom:1px solid #ddd;"><th style="text-align:left;">Producto</th><th>Cant.</th><th style="text-align:right;">Subtotal</th></tr>';
    if (pedido.detalle && Array.isArray(pedido.detalle)) {
        pedido.detalle.forEach(item => {
            detalleHtml += `<tr>
                <td>${item.producto_nombre || item.nombre || '-'}</td>
                <td style="text-align:center;">${item.cantidad}</td>
                <td style="text-align:right;">${formatPrice(item.subtotal)}</td>
            </tr>`;
        });
    }
    detalleHtml += '</table>';
    document.getElementById('cobrar-detalle').innerHTML = detalleHtml;

    // Recuperar método de pago seleccionado en la tabla
    const selectMetodo = document.getElementById('metodo-' + pedidoId);
    if (selectMetodo) {
        document.getElementById('metodo-pago').value = selectMetodo.value;
    }

    document.getElementById('cobrar-modal').classList.add('active');
}

function closeCobrarModal() {
    document.getElementById('cobrar-modal').classList.remove('active');
    pedidoACobrar = null;
}

// Confirmar cobro: finalizar pedido + crear venta con método de pago
async function confirmarCobro() {
    if (!pedidoACobrar) return;

    const metodoPago = document.getElementById('metodo-pago').value;
    const pedidoId = pedidoACobrar.id;

    // Paso 1: Cambiar estado del pedido a Finalizado (5)
    const resultEstado = await apiFetch('api/pedidos.php?id=' + pedidoId, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ estado_id: 5 })
    });

    if (!resultEstado || !resultEstado.success) {
        showNotification(resultEstado?.message || 'Error al finalizar pedido', 'error');
        return;
    }

    // Paso 2: Actualizar el método de pago de la venta
    const resultPago = await apiFetch('api/ventas.php?action=update_metodo', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            pedido_id: pedidoId,
            metodo_pago: metodoPago
        })
    });

    showNotification('Pedido #' + pedidoId + ' cobrado exitosamente (' + metodoPago + ')');
    closeCobrarModal();
    loadCajeroData();
}

// Cargar ventas del día
async function loadVentasHoy() {
    const today = new Date().toISOString().slice(0, 10);
    const ventas = await apiFetch('api/ventas.php?fecha_inicio=' + today + '&fecha_fin=' + today);

    const tbody = document.getElementById('ventas-hoy-body');

    if (!ventas || !Array.isArray(ventas) || ventas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No hay ventas hoy</td></tr>';
        return;
    }

    tbody.innerHTML = ventas.map(venta => `
        <tr>
            <td>#${venta.id}</td>
            <td>#${venta.pedido_numero || venta.pedido_id}</td>
            <td>${formatDate(venta.created_at)}</td>
            <td><strong>${formatPrice(venta.total)}</strong></td>
            <td><span class="badge badge-active">${venta.metodo_pago}</span></td>
            <td>${venta.cajero_nombre}</td>
        </tr>
    `).join('');
}

// Cargar resumen de corte de caja
async function loadCorteCaja() {
    const today = new Date().toISOString().slice(0, 10);

    const [summary, ventas] = await Promise.all([
        apiFetch('api/ventas.php?action=summary&fecha_inicio=' + today + '&fecha_fin=' + today),
        apiFetch('api/ventas.php?fecha_inicio=' + today + '&fecha_fin=' + today)
    ]);

    if (summary) {
        document.getElementById('caja-total-ventas').textContent = summary.total_ventas || 0;
        document.getElementById('caja-ingresos').textContent = formatPrice(summary.ingresos_totales || 0);
    }

    // Calcular totales por método de pago
    let efectivo = 0, tarjeta = 0, transferencia = 0;
    if (ventas && Array.isArray(ventas)) {
        ventas.forEach(v => {
            const total = parseFloat(v.total) || 0;
            switch (v.metodo_pago) {
                case 'efectivo': efectivo += total; break;
                case 'tarjeta': tarjeta += total; break;
                case 'transferencia': transferencia += total; break;
            }
        });
    }

    document.getElementById('caja-efectivo').textContent = formatPrice(efectivo);
    document.getElementById('caja-tarjeta').textContent = formatPrice(tarjeta);
    document.getElementById('caja-transferencia').textContent = formatPrice(transferencia);
}

// Imprimir corte de caja
function printCorteCaja() {
    const totalVentas = document.getElementById('caja-total-ventas').textContent;
    const ingresos = document.getElementById('caja-ingresos').textContent;
    const efectivo = document.getElementById('caja-efectivo').textContent;
    const tarjeta = document.getElementById('caja-tarjeta').textContent;
    const transferencia = document.getElementById('caja-transferencia').textContent;
    const fecha = new Date().toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
    const hora = new Date().toLocaleTimeString('es-MX');

    const ventasRows = document.getElementById('ventas-hoy-body').innerHTML;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Corte de Caja - Sazón Digital</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
                h1 { color: #FF6B00; text-align: center; }
                h2 { color: #333; border-bottom: 2px solid #FF6B00; padding-bottom: 5px; }
                .info { display: flex; justify-content: space-between; margin-bottom: 20px; }
                .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px; }
                .summary-item { background: #f9f9f9; padding: 15px; border-radius: 8px; text-align: center; }
                .summary-item .label { font-size: 0.8rem; color: #666; }
                .summary-item .value { font-size: 1.3rem; font-weight: bold; color: #FF6B00; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background: #1a1a2e; color: white; }
                tr:nth-child(even) { background: #f9f9f9; }
                .total-row { font-weight: bold; font-size: 1.1rem; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <h1>Sazón Digital</h1>
            <h2>Corte de Caja</h2>
            <div class="info">
                <p><strong>Fecha:</strong> ${fecha}</p>
                <p><strong>Hora:</strong> ${hora}</p>
            </div>
            <div class="summary">
                <div class="summary-item">
                    <div class="label">Total Ventas</div>
                    <div class="value">${totalVentas}</div>
                </div>
                <div class="summary-item">
                    <div class="label">Ingresos Totales</div>
                    <div class="value">${ingresos}</div>
                </div>
                <div class="summary-item">
                    <div class="label">Efectivo</div>
                    <div class="value">${efectivo}</div>
                </div>
                <div class="summary-item">
                    <div class="label">Tarjeta</div>
                    <div class="value">${tarjeta}</div>
                </div>
                <div class="summary-item">
                    <div class="label">Transferencia</div>
                    <div class="value">${transferencia}</div>
                </div>
            </div>
            <h2>Detalle de Ventas</h2>
            <table>
                <thead>
                    <tr>
                        <th># Venta</th>
                        <th>Pedido</th>
                        <th>Hora</th>
                        <th>Total</th>
                        <th>Método de Pago</th>
                        <th>Cajero</th>
                    </tr>
                </thead>
                <tbody>${ventasRows}</tbody>
            </table>
            <br>
            <p style="text-align:center; color:#888;">— Corte generado por Sazón Digital —</p>
            <script>window.print();</script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
