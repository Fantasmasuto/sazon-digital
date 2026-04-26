/**
 * Sazón Digital - Products JavaScript
 */

let allProducts = [];

document.addEventListener('DOMContentLoaded', function() {
    loadCategories();

    // Check if we're on the list page or form page
    if (document.getElementById('products-table-body')) {
        loadProducts();
    }
    if (document.getElementById('product-form')) {
        initProductForm();
    }
});

// Load categories for filter and form
async function loadCategories() {
    const categorias = await apiFetch('api/productos.php?action=categorias');
    if (!categorias) return;

    // Filter select (list page)
    const filterSelect = document.getElementById('filter-categoria');
    if (filterSelect) {
        categorias.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.nombre;
            filterSelect.appendChild(option);
        });
    }

    // Form select
    const formSelect = document.getElementById('categoria_id');
    if (formSelect) {
        categorias.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.nombre;
            formSelect.appendChild(option);
        });
    }
}

// Load products list
async function loadProducts(categoriaId = null) {
    let url = 'api/productos.php';
    if (categoriaId) url += '?categoria=' + categoriaId;

    const products = await apiFetch(url);
    if (!products) return;

    allProducts = products;
    renderProducts(products);
}

// Render products table
function renderProducts(products) {
    const tbody = document.getElementById('products-table-body');

    if (!Array.isArray(products) || products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No hay productos registrados</td></tr>';
        return;
    }

    tbody.innerHTML = products.map((p, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>
                ${p.imagen
                    ? `<img src="uploads/productos/${p.imagen}" alt="${p.nombre}" class="img-preview">`
                    : '<span style="color:#ccc;">Sin imagen</span>'}
            </td>
            <td><strong>${p.nombre}</strong></td>
            <td>${p.descripcion || '-'}</td>
            <td>${p.categoria_nombre}</td>
            <td><strong>${formatPrice(p.precio)}</strong></td>
            <td>
                <span class="badge ${p.estado === 'activo' ? 'badge-active' : 'badge-inactive'}">
                    ${p.estado}
                </span>
            </td>
            <td>
                <div class="btn-group">
                    <a href="index.php?page=producto-form&id=${p.id}" class="btn btn-warning btn-sm">Editar</a>
                    <button class="btn btn-danger btn-sm" onclick="deleteProduct(${p.id})">Eliminar</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Filter products by category
function filterProducts() {
    const categoriaId = document.getElementById('filter-categoria').value;
    loadProducts(categoriaId || null);
}

// Filter products by name
function filterByName() {
    const filter = document.getElementById('filter-nombre').value.toLowerCase();
    const filtered = allProducts.filter(p => p.nombre.toLowerCase().includes(filter));
    renderProducts(filtered);
}

// Delete product
async function deleteProduct(id) {
    if (!confirmAction('¿Estás seguro de eliminar este producto?')) return;

    const result = await apiFetch('api/productos.php?id=' + id, { method: 'DELETE' });
    if (result && result.success) {
        showNotification('Producto eliminado exitosamente');
        loadProducts();
    } else {
        showNotification(result?.message || 'Error al eliminar producto', 'error');
    }
}

// Initialize product form
async function initProductForm() {
    const productId = document.getElementById('product-id').value;

    // If editing, load product data
    if (productId > 0) {
        const product = await apiFetch('api/productos.php?id=' + productId);
        if (product) {
            document.getElementById('nombre').value = product.nombre || '';
            document.getElementById('descripcion').value = product.descripcion || '';
            document.getElementById('precio').value = product.precio || '';
            document.getElementById('categoria_id').value = product.categoria_id || '';
            document.getElementById('estado').value = product.estado || 'activo';
            if (product.imagen) {
                document.getElementById('image-preview').innerHTML =
                    `<img src="uploads/productos/${product.imagen}" alt="Preview" style="max-width: 200px; border-radius: 8px;">`;
            }
        }
    }

    // Form submission
    document.getElementById('product-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const id = document.getElementById('product-id').value;
        let url = 'api/productos.php';
        if (id > 0) url += '?id=' + id;

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message);
                setTimeout(() => window.location.href = 'index.php?page=productos', 1000);
            } else {
                showNotification(result.message || 'Error al guardar', 'error');
            }
        } catch (error) {
            showNotification('Error de conexión', 'error');
        }
    });
}
