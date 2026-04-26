/**
 * ============================================
 * SAZÓN DIGITAL - FUNCIONES JAVASCRIPT GLOBALES
 * ============================================
 *
 * Este archivo contiene funciones utilitarias que se usan
 * en TODAS las páginas de la aplicación. Se carga en el footer.
 *
 * Funciones incluidas:
 *   - apiFetch()          → Llamar a la API del servidor
 *   - showNotification()  → Mostrar mensajes tipo "toast"
 *   - formatPrice()       → Formatear precios ($12.50)
 *   - formatDate()        → Formatear fechas legibles
 *   - confirmAction()     → Confirmar acciones destructivas
 */

/**
 * Hace una petición HTTP a la API y retorna los datos.
 *
 * ¿Qué es fetch()?
 * Es la función moderna de JavaScript para hacer peticiones HTTP
 * (reemplaza a XMLHttpRequest). Es "asíncrona" porque no bloquea
 * la página mientras espera la respuesta del servidor.
 *
 * ¿Qué es async/await?
 * Es una forma moderna de manejar código asíncrono.
 * "await" pausa la función hasta que la promesa se resuelve.
 * Sin async/await, usaríamos .then() y .catch() (callbacks).
 *
 * @param {string} url - URL del endpoint API
 * @param {object} options - Opciones de fetch (method, body, headers)
 * @returns {object|null} Datos JSON del servidor o null si hay error
 */
async function apiFetch(url, options = {}) {
    try {
        const response = await fetch(url, options);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        showNotification('Error de conexión con el servidor', 'error');
        return null;
    }
}

/**
 * Muestra una notificación temporal tipo "toast".
 *
 * Tipos: 'success' (verde), 'error' (rojo), 'warning' (amarillo)
 *
 * La notificación aparece con animación, se mantiene 3 segundos
 * y desaparece automáticamente. Solo se muestra una a la vez.
 *
 * @param {string} message - Texto del mensaje
 * @param {string} type - Tipo: 'success', 'error', 'warning'
 */
function showNotification(message, type = 'success') {
    // Eliminar notificación anterior si existe
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();

    // Crear elemento de notificación
    const div = document.createElement('div');
    div.className = 'notification';
    div.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 8px;
        color: #fff;
        font-size: 0.9rem;
        z-index: 9999;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease;
        max-width: 400px;
    `;

    // Color según el tipo de notificación
    switch (type) {
        case 'success':
            div.style.background = '#27ae60';
            break;
        case 'error':
            div.style.background = '#e74c3c';
            break;
        case 'warning':
            div.style.background = '#f39c12';
            break;
        default:
            div.style.background = '#3498db';
    }

    div.textContent = message;
    document.body.appendChild(div);

    // Eliminar automáticamente después de 3 segundos
    setTimeout(() => {
        div.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => div.remove(), 300);
    }, 3000);
}

/**
 * Formatea un número como precio con símbolo de dólar.
 * Ejemplo: formatPrice(12.5) → "$12.50"
 */
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

/**
 * Formatea una fecha ISO a formato legible en español.
 * Ejemplo: "2024-07-20T14:30:00" → "20/07/2024, 02:30 p.m."
 */
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Formatea una fecha a solo YYYY-MM-DD.
 */
function formatDateOnly(dateStr) {
    if (!dateStr) return '-';
    return dateStr.substring(0, 10);
}

/**
 * Muestra un diálogo de confirmación antes de acciones destructivas.
 * Ejemplo: if (confirmAction('¿Eliminar?')) { ... }
 *
 * @param {string} message - Pregunta para el usuario
 * @returns {boolean} true si confirmó, false si canceló
 */
function confirmAction(message) {
    return confirm(message);
}

// ============================================
// Agregar animaciones CSS para notificaciones
// Se inyectan dinámicamente al cargar la página
// ============================================
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
