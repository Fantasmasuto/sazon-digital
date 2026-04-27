/**
 * Sazón Digital - Sales JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Set default dates (today)
    const today = new Date().toISOString().slice(0, 10);
    document.getElementById('filter-fecha-inicio').value = today;
    document.getElementById('filter-fecha-fin').value = today;

    loadSales();
});

async function loadSales() {
    const fechaInicio = document.getElementById('filter-fecha-inicio').value;
    const fechaFin = document.getElementById('filter-fecha-fin').value;

    // Build URL with filters
    let salesUrl = 'api/ventas.php';
    let summaryUrl = 'api/ventas.php?action=summary';
    const params = [];

    if (fechaInicio) params.push('fecha_inicio=' + fechaInicio);
    if (fechaFin) params.push('fecha_fin=' + fechaFin);

    if (params.length > 0) {
        salesUrl += '?' + params.join('&');
        summaryUrl += '&' + params.join('&');
    }

    // Load data in parallel
    const [sales, summary] = await Promise.all([
        apiFetch(salesUrl),
        apiFetch(summaryUrl)
    ]);

    // Update summary cards
    if (summary) {
        document.getElementById('total-ingresos').textContent = formatPrice(summary.ingresos_totales || 0);
        document.getElementById('ticket-promedio').textContent = formatPrice(summary.ticket_promedio || 0);
        document.getElementById('total-ventas-count').textContent = summary.total_ventas || 0;
    }

    // Render sales table
    const tbody = document.getElementById('sales-table-body');

    if (!sales || !Array.isArray(sales) || sales.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No hay ventas en este período</td></tr>';
        return;
    }

    tbody.innerHTML = sales.map(sale => `
        <tr>
            <td>#${sale.id}</td>
            <td>
                <a href="index.php?page=pedido-detalle&id=${sale.pedido_id}" style="color: #3498db;">
                    #${sale.pedido_numero || sale.pedido_id}
                </a>
            </td>
            <td>${formatDate(sale.created_at)}</td>
            <td>${sale.cajero_nombre}</td>
            <td><strong>${formatPrice(sale.total)}</strong></td>
            <td><span class="badge badge-active">${sale.metodo_pago}</span></td>
        </tr>
    `).join('');
}
