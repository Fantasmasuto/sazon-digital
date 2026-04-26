/**
 * Sazón Digital - Main JavaScript
 * Common utility functions
 */

// Helper: API fetch with error handling
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

// Helper: Show notification
function showNotification(message, type = 'success') {
    // Remove existing notification
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();

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

    // Auto remove after 3 seconds
    setTimeout(() => {
        div.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => div.remove(), 300);
    }, 3000);
}

// Helper: Format price
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

// Helper: Format date
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

// Helper: Format date only
function formatDateOnly(dateStr) {
    if (!dateStr) return '-';
    return dateStr.substring(0, 10);
}

// Helper: Confirm action
function confirmAction(message) {
    return confirm(message);
}

// Add CSS animation keyframes
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
