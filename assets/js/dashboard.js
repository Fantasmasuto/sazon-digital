/**
 * Sazón Digital - Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

async function loadDashboardData() {
    // Load summary data in parallel
    const [pedidos, ventas, productos, mesas] = await Promise.all([
        apiFetch('api/pedidos.php'),
        apiFetch('api/ventas.php?action=daily'),
        apiFetch('api/productos.php'),
        apiFetch('api/pedidos.php?action=mesas')
    ]);

    // Update summary cards
    if (pedidos) {
        // Count today's orders
        const today = new Date().toISOString().slice(0, 10);
        const todayOrders = Array.isArray(pedidos) ?
            pedidos.filter(p => p.created_at && p.created_at.startsWith(today)) : [];
        document.getElementById('pedidos-hoy').textContent = todayOrders.length;
    }

    if (ventas) {
        document.getElementById('ventas-hoy').textContent = formatPrice(ventas.ingresos_totales || 0);
    }

    if (productos) {
        const active = Array.isArray(productos) ?
            productos.filter(p => p.estado === 'activo').length : 0;
        document.getElementById('productos-activos').textContent = active;
    }

    if (mesas) {
        document.getElementById('mesas-disponibles').textContent = Array.isArray(mesas) ? mesas.length : 0;
    }

    // Load recent orders
    if (pedidos && Array.isArray(pedidos)) {
        const recentOrders = pedidos.slice(0, 5);
        const tbody = document.getElementById('recent-orders');

        if (recentOrders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="empty-state">No hay pedidos recientes</td></tr>';
            return;
        }

        tbody.innerHTML = recentOrders.map(order => `
            <tr style="cursor: pointer;" onclick="window.location='index.php?page=pedido-detalle&id=${order.id}'">
                <td>#${order.id}</td>
                <td>Mesa ${order.mesa_numero || 'S/N'}</td>
                <td>${formatPrice(order.total)}</td>
                <td><span class="badge" style="background: ${order.estado_color}">${order.estado_nombre}</span></td>
            </tr>
        `).join('');
    }
}
