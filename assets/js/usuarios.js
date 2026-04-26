/**
 * Sazón Digital - Users JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Users list page
    if (document.getElementById('users-table-body')) {
        loadUsers();
    }

    // User form page
    if (document.getElementById('user-form')) {
        initUserForm();
    }
});

// Load users list
async function loadUsers() {
    const users = await apiFetch('api/usuarios.php');
    if (!users || !Array.isArray(users)) return;

    const tbody = document.getElementById('users-table-body');

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No hay usuarios registrados</td></tr>';
        return;
    }

    tbody.innerHTML = users.map((user, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${user.nombre}</td>
            <td>${user.email}</td>
            <td><span class="badge" style="background: ${getRoleColor(user.rol_nombre)}">${user.rol_nombre}</span></td>
            <td>
                <span class="badge ${user.estado === 'activo' ? 'badge-active' : 'badge-inactive'}">
                    ${user.estado}
                </span>
            </td>
            <td>
                <div class="btn-group">
                    <a href="index.php?page=usuario-form&id=${user.id}" class="btn btn-warning btn-sm">Editar</a>
                    <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">Eliminar</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Get role color
function getRoleColor(role) {
    switch (role) {
        case 'Administrador': return '#e74c3c';
        case 'Mesero': return '#3498db';
        case 'Cocina': return '#27ae60';
        default: return '#888';
    }
}

// Filter users
function filterUsers() {
    const filter = document.getElementById('filter-usuario').value.toLowerCase();
    const rows = document.querySelectorAll('#users-table-body tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

// Delete user
async function deleteUser(id) {
    if (!confirmAction('¿Estás seguro de eliminar este usuario?')) return;

    const result = await apiFetch('api/usuarios.php?id=' + id, { method: 'DELETE' });
    if (result && result.success) {
        showNotification('Usuario eliminado');
        loadUsers();
    } else {
        showNotification(result?.message || 'Error al eliminar', 'error');
    }
}

// Initialize user form
async function initUserForm() {
    // Load roles
    const roles = await apiFetch('api/usuarios.php?action=roles');
    if (roles && Array.isArray(roles)) {
        const select = document.getElementById('rol_id');
        roles.forEach(role => {
            const option = document.createElement('option');
            option.value = role.id;
            option.textContent = role.nombre;
            select.appendChild(option);
        });
    }

    const userId = document.getElementById('user-id').value;

    // If editing, load user data
    if (userId > 0) {
        const user = await apiFetch('api/usuarios.php?id=' + userId);
        if (user) {
            document.getElementById('nombre').value = user.nombre || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('rol_id').value = user.rol_id || '';
            document.getElementById('estado').value = user.estado || 'activo';
        }
    }

    // Form submission
    document.getElementById('user-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('nombre', document.getElementById('nombre').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('password', document.getElementById('password').value);
        formData.append('rol_id', document.getElementById('rol_id').value);
        formData.append('estado', document.getElementById('estado').value);

        const id = document.getElementById('user-id').value;
        let url = 'api/usuarios.php';
        if (id > 0) url += '?id=' + id;

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message);
                setTimeout(() => window.location.href = 'index.php?page=usuarios', 1000);
            } else {
                showNotification(result.message || 'Error al guardar', 'error');
            }
        } catch (error) {
            showNotification('Error de conexión', 'error');
        }
    });
}
