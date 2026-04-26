/**
 * Sazón Digital - Orders JavaScript
 */

let cart = [];
let orderProducts = [];

document.addEventListener('DOMContentLoaded', function() {
    // Orders list page
    if (document.getElementById('orders-table-body')) {
        loadOrders();
    }

    // Create order page
    if (document.getElementById('order-products-list')) {
        loadOrderFormData();
    }

    // Order detail page
    if (document.getElementById('pedido-id')) {
        loadOrderDetail();
    }
});

// ============================================
// ORDERS LIST
// ============================================

async function loadOrders() {
    const orders = await apiFetch('api/pedidos.php');
    if (!orders || !Array.isArray(orders)) return;

    const tbody = document.getElementById('orders-table-body');

    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty-state">No hay pedidos registrados</td></tr>';
        return;
    }

    tbody.innerHTML = orders.map(order => `
        <tr>
            <td>#${order.id}</td>
            <td>Mesa ${order.mesa_numero || 'S/N'}</td>
            <td>${order.mesero_nombre}</td>
            <td>${formatDate(order.created_at)}</td>
            <td><strong>${formatPrice(order.total)}</strong></td>
            <td><span class="badge" style="background: ${order.estado_color}">${order.estado_nombre}</span></td>
            <td>
                <div class="btn-group">
                    <a href="index.php?page=pedido-detalle&id=${order.id}" class="btn btn-info btn-sm">Ver</a>
                    <button class="btn btn-danger btn-sm" onclick="deleteOrder(${order.id})">Eliminar</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function filterOrders() {
    const filter = document.getElementById('filter-pedido').value.toLowerCase();
    const rows = document.querySelectorAll('#orders-table-body tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

async function deleteOrder(id) {
    if (!confirmAction('¿Estás seguro de eliminar este pedido?')) return;

    const result = await apiFetch('api/pedidos.php?id=' + id, { method: 'DELETE' });
    if (result && result.success) {
        showNotification('Pedido eliminado');
        loadOrders();
    } else {
        showNotification(result?.message || 'Error al eliminar', 'error');
    }
}

// ============================================
// CREATE ORDER
// ============================================

async function loadOrderFormData() {
    // Load products and tables in parallel
    const [productos, mesas, categorias] = await Promise.all([
        apiFetch('api/productos.php'),
        apiFetch('api/pedidos.php?action=mesas'),
        apiFetch('api/productos.php?action=categorias')
    ]);

    // Render available products
    if (productos && Array.isArray(productos)) {
        orderProducts = productos.filter(p => p.estado === 'activo');
        renderOrderProducts(orderProducts);
    }

    // Populate tables dropdown
    if (mesas && Array.isArray(mesas)) {
        const select = document.getElementById('order-mesa');
        mesas.forEach(mesa => {
            const option = document.createElement('option');
            option.value = mesa.id;
            option.textContent = `Mesa ${mesa.numero} (${mesa.capacidad} personas)`;
            select.appendChild(option);
        });
    }

    // Populate categories filter
    if (categorias && Array.isArray(categorias)) {
        const select = document.getElementById('order-cat-filter');
        categorias.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.nombre;
            select.appendChild(option);
        });
    }
}

function renderOrderProducts(products) {
    const container = document.getElementById('order-products-list');

    if (products.length === 0) {
        container.innerHTML = '<div class="empty-state">No hay productos disponibles</div>';
        return;
    }

    container.innerHTML = products.map(p => `
        <div class="product-item">
            <div class="product-item-info">
                <h4>${p.nombre}</h4>
                <p>${p.categoria_nombre} ${p.descripcion ? ' - ' + p.descripcion : ''}</p>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="product-item-price">${formatPrice(p.precio)}</span>
                <button class="btn btn-primary btn-sm" onclick="addToCart(${p.id}, '${p.nombre.replace(/'/g, "\\'")}', ${p.precio})">
                    Agregar
                </button>
            </div>
        </div>
    `).join('');
}

function filterOrderProducts() {
    const catId = document.getElementById('order-cat-filter').value;
    if (catId) {
        const filtered = orderProducts.filter(p => p.categoria_id == catId);
        renderOrderProducts(filtered);
    } else {
        renderOrderProducts(orderProducts);
    }
}

function addToCart(productId, nombre, precio) {
    const existing = cart.find(item => item.producto_id === productId);
    if (existing) {
        existing.cantidad++;
    } else {
        cart.push({
            producto_id: productId,
            nombre: nombre,
            precio_unitario: precio,
            cantidad: 1,
            notas: ''
        });
    }
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateCartQty(index, change) {
    cart[index].cantidad += change;
    if (cart[index].cantidad <= 0) {
        cart.splice(index, 1);
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cart-items');

    if (cart.length === 0) {
        container.innerHTML = '<div class="empty-state">Agrega productos al pedido</div>';
        document.getElementById('cart-total').textContent = '$0.00';
        return;
    }

    let total = 0;
    container.innerHTML = cart.map((item, index) => {
        const subtotal = item.cantidad * item.precio_unitario;
        total += subtotal;
        return `
            <div class="cart-item">
                <div>
                    <strong>${item.nombre}</strong>
                    <div style="font-size: 0.8rem; color: #888;">${formatPrice(item.precio_unitario)} c/u</div>
                </div>
                <div class="cart-item-qty">
                    <button onclick="updateCartQty(${index}, -1)">-</button>
                    <span>${item.cantidad}</span>
                    <button onclick="updateCartQty(${index}, 1)">+</button>
                </div>
                <div>
                    <strong>${formatPrice(subtotal)}</strong>
                    <button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})" style="margin-left: 5px;">X</button>
                </div>
            </div>
        `;
    }).join('');

    document.getElementById('cart-total').textContent = formatPrice(total);
}

async function submitOrder() {
    if (cart.length === 0) {
        showNotification('Agrega al menos un producto al pedido', 'warning');
        return;
    }

    const orderData = {
        mesa_id: document.getElementById('order-mesa').value || 0,
        notas: document.getElementById('order-notas').value,
        productos: cart.map(item => ({
            producto_id: item.producto_id,
            cantidad: item.cantidad,
            precio_unitario: item.precio_unitario,
            notas: item.notas
        }))
    };

    const result = await apiFetch('api/pedidos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    });

    if (result && result.success) {
        showNotification('Pedido creado exitosamente');
        setTimeout(() => window.location.href = 'index.php?page=pedido-detalle&id=' + result.id, 1000);
    } else {
        showNotification(result?.message || 'Error al crear pedido', 'error');
    }
}

// ============================================
// ORDER DETAIL
// ============================================

async function loadOrderDetail() {
    const id = document.getElementById('pedido-id').value;
    const order = await apiFetch('api/pedidos.php?id=' + id);

    if (!order || order.error) {
        showNotification('Pedido no encontrado', 'error');
        return;
    }

    // Fill order info
    document.getElementById('detail-mesero').textContent = order.mesero_nombre || '-';
    document.getElementById('detail-fecha').textContent = formatDate(order.created_at);
    document.getElementById('detail-mesa').textContent = order.mesa_numero ? 'Mesa ' + order.mesa_numero : 'Sin mesa';
    document.getElementById('detail-estado').innerHTML =
        `<span class="badge" style="background: ${order.estado_color}">${order.estado_nombre}</span>`;

    // Notes
    if (order.notas) {
        document.getElementById('detail-notas').style.display = 'block';
        document.getElementById('detail-notas-text').textContent = order.notas;
    }

    // Products
    const tbody = document.getElementById('detail-products');
    if (order.detalle && order.detalle.length > 0) {
        tbody.innerHTML = order.detalle.map(item => `
            <tr>
                <td>${item.producto_nombre}</td>
                <td>${item.cantidad}</td>
                <td>${formatPrice(item.precio_unitario)}</td>
                <td><strong>${formatPrice(item.subtotal)}</strong></td>
            </tr>
        `).join('');
    } else {
        tbody.innerHTML = '<tr><td colspan="4" class="empty-state">No hay productos en este pedido</td></tr>';
    }

    // Totals
    const total = parseFloat(order.total);
    const tax = total * 0.16;
    document.getElementById('detail-subtotal').textContent = formatPrice(total);
    document.getElementById('detail-tax').textContent = formatPrice(tax);
    document.getElementById('detail-total').textContent = formatPrice(total + tax);

    // Status badge
    document.getElementById('detail-status-badge').innerHTML =
        `<span class="badge" style="background: ${order.estado_color}; font-size: 1rem; padding: 8px 20px;">${order.estado_nombre}</span>`;

    // Status action buttons
    renderStatusActions(order);
}

async function renderStatusActions(order) {
    const container = document.getElementById('status-actions');
    const estados = await apiFetch('api/pedidos.php?action=estados');
    if (!estados) return;

    const currentEstadoId = parseInt(order.estado_id);
    const statusLabels = {
        1: { label: 'Marcar como Listo', class: 'btn-warning', nextId: 2 },
        2: { label: 'Pedido en Cocina', class: 'btn-info', nextId: 3 },
        3: { label: 'Pedido Listo', class: 'btn-success', nextId: 5 },
        4: { label: 'Entregar Pedido', class: 'btn-success', nextId: 5 },
    };

    let html = '<h3 style="margin-bottom: 10px;">Cambiar Estado</h3>';

    estados.forEach(estado => {
        const isActive = estado.id == currentEstadoId;
        const disabled = isActive ? 'disabled' : '';
        html += `
            <button class="btn btn-sm ${isActive ? 'btn-info' : ''}"
                    style="background: ${isActive ? estado.color : '#eee'}; color: ${isActive ? '#fff' : '#333'};"
                    ${disabled}
                    onclick="changeOrderStatus(${order.id}, ${estado.id})">
                ${estado.nombre}
            </button> `;
    });

    // Cancel button
    if (currentEstadoId < 5) {
        html += `<button class="btn btn-danger btn-sm" onclick="changeOrderStatus(${order.id}, 6)">Cancelar Pedido</button>`;
    }

    container.innerHTML = html;
}

async function changeOrderStatus(orderId, estadoId) {
    if (!confirmAction('¿Cambiar el estado de este pedido?')) return;

    const result = await apiFetch('api/pedidos.php?id=' + orderId, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ estado_id: estadoId })
    });

    if (result && result.success) {
        showNotification('Estado actualizado');
        loadOrderDetail();
    } else {
        showNotification(result?.message || 'Error al actualizar', 'error');
    }
}
