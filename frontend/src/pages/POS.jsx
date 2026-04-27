import { useEffect, useState, useCallback } from 'react';
import { ShoppingCart, Trash2, Search } from 'lucide-react';
import { api } from '../utils/api';
import { useOnlineStatus } from '../hooks/useOnlineStatus';
import { saveOfflineVenta, getCachedProductos, cacheProductos } from '../services/offlineDB';

export default function POS() {
  const isOnline = useOnlineStatus();
  const [productos, setProductos] = useState([]);
  const [search, setSearch] = useState('');
  const [cart, setCart] = useState([]);
  const [metodoPago, setMetodoPago] = useState('efectivo');
  const [processing, setProcessing] = useState(false);
  const [message, setMessage] = useState(null);

  const loadProductos = useCallback(async () => {
    try {
      if (isOnline) {
        const res = await api.getProductos(search ? `search=${encodeURIComponent(search)}` : '');
        setProductos(res.data);
        // Only cache the full product list, not filtered search results
        if (!search) {
          await cacheProductos(res.data);
        }
      } else {
        // Cargar desde cache offline
        const cached = await getCachedProductos();
        setProductos(
          search
            ? cached.filter((p) =>
                p.nombre.toLowerCase().includes(search.toLowerCase()) ||
                p.sku.toLowerCase().includes(search.toLowerCase())
              )
            : cached
        );
      }
    } catch (error) {
      console.error('Error cargando productos:', error);
      // Fallback a cache
      const cached = await getCachedProductos();
      setProductos(cached);
    }
  }, [isOnline, search]);

  useEffect(() => {
    loadProductos();
  }, [loadProductos]);

  const addToCart = (producto) => {
    setCart((prev) => {
      const existing = prev.find((item) => item.producto_id === producto.id);
      if (existing) {
        if (existing.cantidad >= producto.stock_actual) {
          showMessage('Stock insuficiente', 'error');
          return prev;
        }
        return prev.map((item) =>
          item.producto_id === producto.id
            ? { ...item, cantidad: item.cantidad + 1 }
            : item
        );
      }
      return [...prev, {
        producto_id: producto.id,
        nombre: producto.nombre,
        sku: producto.sku,
        precio_unit: parseFloat(producto.precio_venta),
        cantidad: 1,
        stock_disponible: producto.stock_actual,
      }];
    });
  };

  const updateQty = (productoId, delta) => {
    setCart((prev) =>
      prev.map((item) => {
        if (item.producto_id !== productoId) return item;
        const newQty = item.cantidad + delta;
        if (newQty <= 0) return item;
        if (newQty > item.stock_disponible) {
          showMessage('Stock insuficiente', 'error');
          return item;
        }
        return { ...item, cantidad: newQty };
      })
    );
  };

  const removeFromCart = (productoId) => {
    setCart((prev) => prev.filter((item) => item.producto_id !== productoId));
  };

  const getTotal = () => {
    return cart.reduce((sum, item) => sum + item.precio_unit * item.cantidad, 0);
  };

  const showMessage = (text, type = 'success') => {
    setMessage({ text, type });
    setTimeout(() => setMessage(null), 3000);
  };

  const processSale = async () => {
    if (cart.length === 0) return;
    setProcessing(true);

    const ventaData = {
      total: getTotal(),
      metodo_pago: metodoPago,
      offline_id: `offline-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      items: cart.map(({ producto_id, cantidad, precio_unit }) => ({
        producto_id,
        cantidad,
        precio_unit,
      })),
    };

    try {
      if (isOnline) {
        await api.createVenta(ventaData);
        showMessage('Venta registrada exitosamente');
      } else {
        await saveOfflineVenta(ventaData);
        showMessage('Venta guardada offline. Se sincronizará al recuperar conexión.', 'warning');
      }
      setCart([]);
      loadProductos();
    } catch (error) {
      // Si falla online, guardar offline
      try {
        await saveOfflineVenta(ventaData);
        showMessage('Error de conexión. Venta guardada offline.', 'warning');
        setCart([]);
      } catch (offlineError) {
        showMessage(`Error: ${error.message}`, 'error');
      }
    } finally {
      setProcessing(false);
    }
  };

  return (
    <div>
      <div className="page-header">
        <h2>Punto de Venta</h2>
        {!isOnline && (
          <span className="btn btn-outline" style={{ color: '#b45309', borderColor: '#f59e0b' }}>
            Modo Offline
          </span>
        )}
      </div>

      {message && (
        <div className={`error-msg ${message.type === 'success' ? 'success-msg' : ''}`}
          style={message.type === 'success' ? { background: '#dcfce7', borderColor: '#86efac', color: '#166534' } :
            message.type === 'warning' ? { background: '#fef3c7', borderColor: '#fcd34d', color: '#92400e' } : {}}>
          {message.text}
        </div>
      )}

      <div className="pos-layout">
        {/* Catálogo de productos */}
        <div>
          <div className="pos-search">
            <Search size={18} style={{ position: 'absolute', left: 14, top: 14, color: 'var(--text-secondary)' }} />
            <input
              type="text"
              placeholder="Buscar por nombre o SKU..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              style={{ paddingLeft: 42 }}
            />
          </div>

          <div className="pos-products">
            {productos.map((producto) => (
              <div
                key={producto.id}
                className="pos-product-card"
                onClick={() => addToCart(producto)}
              >
                <h4>{producto.nombre}</h4>
                <div className="sku">{producto.sku}</div>
                <div className="price">${parseFloat(producto.precio_venta).toFixed(2)}</div>
                <div className={`stock ${producto.stock_actual <= producto.stock_minimo ? 'stock-low' : ''}`}>
                  Stock: {producto.stock_actual}
                </div>
              </div>
            ))}

            {productos.length === 0 && (
              <div className="empty-state" style={{ gridColumn: '1 / -1' }}>
                <h3>No se encontraron productos</h3>
                <p>Intenta con otro término de búsqueda</p>
              </div>
            )}
          </div>
        </div>

        {/* Carrito */}
        <div className="cart">
          <div className="cart-header">
            <span><ShoppingCart size={18} /> Carrito</span>
            <span>{cart.length} item(s)</span>
          </div>

          <div className="cart-items">
            {cart.length === 0 ? (
              <div className="cart-empty">
                <ShoppingCart size={40} style={{ opacity: 0.3, marginBottom: 12 }} />
                <p>Haz clic en un producto para agregarlo</p>
              </div>
            ) : (
              cart.map((item) => (
                <div key={item.producto_id} className="cart-item">
                  <div className="cart-item-info">
                    <h4>{item.nombre}</h4>
                    <span className="price">${item.precio_unit.toFixed(2)}</span>
                  </div>
                  <div className="cart-item-qty">
                    <button onClick={() => updateQty(item.producto_id, -1)}>-</button>
                    <span>{item.cantidad}</span>
                    <button onClick={() => updateQty(item.producto_id, 1)}>+</button>
                  </div>
                  <div className="cart-item-subtotal">
                    ${(item.precio_unit * item.cantidad).toFixed(2)}
                  </div>
                  <button
                    className="cart-item-remove"
                    onClick={() => removeFromCart(item.producto_id)}
                  >
                    <Trash2 size={16} />
                  </button>
                </div>
              ))
            )}
          </div>

          <div className="cart-footer">
            <div className="cart-total">
              <span>Total</span>
              <span>${getTotal().toFixed(2)}</span>
            </div>

            <div className="cart-payment">
              <button
                className={metodoPago === 'efectivo' ? 'active' : ''}
                onClick={() => setMetodoPago('efectivo')}
              >
                Efectivo
              </button>
              <button
                className={metodoPago === 'tarjeta' ? 'active' : ''}
                onClick={() => setMetodoPago('tarjeta')}
              >
                Tarjeta
              </button>
            </div>

            <button
              className="btn btn-success btn-lg"
              style={{ width: '100%', justifyContent: 'center' }}
              onClick={processSale}
              disabled={cart.length === 0 || processing}
            >
              {processing ? 'Procesando...' : `Cobrar $${getTotal().toFixed(2)}`}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
