import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { Plus, Search, Pencil, Trash2 } from 'lucide-react';
import { api } from '../utils/api';

export default function Inventario() {
  const [productos, setProductos] = useState([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const loadProductos = async () => {
    setLoading(true);
    try {
      const res = await api.getProductos(search ? `search=${encodeURIComponent(search)}` : '');
      setProductos(res.data);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadProductos();
  }, [search]);

  const handleDelete = async (id, nombre) => {
    if (!window.confirm(`¿Desactivar el producto "${nombre}"?`)) return;
    try {
      await api.deleteProducto(id);
      loadProductos();
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <div>
      <div className="page-header">
        <h2>Inventario</h2>
        <Link to="/inventario/nuevo" className="btn btn-primary">
          <Plus size={18} /> Nuevo Producto
        </Link>
      </div>

      {error && <div className="error-msg">{error}</div>}

      <div className="card">
        <div className="pos-search" style={{ marginBottom: 16 }}>
          <Search size={18} style={{ position: 'absolute', left: 14, top: 14, color: 'var(--text-secondary)' }} />
          <input
            type="text"
            placeholder="Buscar producto por nombre o SKU..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            style={{ paddingLeft: 42 }}
          />
        </div>

        {loading ? (
          <div className="loading">Cargando productos...</div>
        ) : (
          <div className="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>SKU</th>
                  <th>P. Compra</th>
                  <th>P. Venta</th>
                  <th>Stock</th>
                  <th>Mínimo</th>
                  <th>Días Entrega</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {productos.map((p) => (
                  <tr key={p.id}>
                    <td><strong>{p.nombre}</strong></td>
                    <td style={{ fontFamily: 'monospace' }}>{p.sku}</td>
                    <td>${parseFloat(p.precio_compra).toFixed(2)}</td>
                    <td>${parseFloat(p.precio_venta).toFixed(2)}</td>
                    <td className={p.stock_actual <= p.stock_minimo ? 'stock-low' : ''}>
                      {p.stock_actual}
                    </td>
                    <td>{p.stock_minimo}</td>
                    <td>{p.dias_entrega}d</td>
                    <td>
                      <div style={{ display: 'flex', gap: 6 }}>
                        <Link to={`/inventario/editar/${p.id}`} className="btn btn-outline btn-sm">
                          <Pencil size={14} />
                        </Link>
                        <button
                          className="btn btn-danger btn-sm"
                          onClick={() => handleDelete(p.id, p.nombre)}
                        >
                          <Trash2 size={14} />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
                {productos.length === 0 && (
                  <tr>
                    <td colSpan="8" style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>
                      No se encontraron productos
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
