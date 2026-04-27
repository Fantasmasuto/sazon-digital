import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Save, ArrowLeft } from 'lucide-react';
import { api } from '../utils/api';

const initialForm = {
  nombre: '',
  sku: '',
  precio_compra: '',
  precio_venta: '',
  stock_actual: 0,
  stock_minimo: 5,
  dias_entrega: 3,
};

export default function ProductoForm() {
  const navigate = useNavigate();
  const { id } = useParams();
  const isEditing = Boolean(id);

  const [form, setForm] = useState(initialForm);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (isEditing) {
      setLoading(true);
      api.getProducto(id)
        .then((res) => {
          const p = res.data;
          setForm({
            nombre: p.nombre,
            sku: p.sku,
            precio_compra: p.precio_compra,
            precio_venta: p.precio_venta,
            stock_actual: p.stock_actual,
            stock_minimo: p.stock_minimo,
            dias_entrega: p.dias_entrega,
          });
        })
        .catch((err) => setError(err.message))
        .finally(() => setLoading(false));
    }
  }, [id, isEditing]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError(null);

    try {
      const payload = {
        ...form,
        precio_compra: parseFloat(form.precio_compra),
        precio_venta: parseFloat(form.precio_venta),
        stock_actual: parseInt(form.stock_actual, 10),
        stock_minimo: parseInt(form.stock_minimo, 10),
        dias_entrega: parseInt(form.dias_entrega, 10),
      };

      if (isEditing) {
        await api.updateProducto(id, payload);
      } else {
        await api.createProducto(payload);
      }

      navigate('/inventario');
    } catch (err) {
      setError(err.message);
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div className="loading">Cargando producto...</div>;

  return (
    <div>
      <div className="page-header">
        <h2>{isEditing ? 'Editar Producto' : 'Nuevo Producto'}</h2>
        <button className="btn btn-outline" onClick={() => navigate('/inventario')}>
          <ArrowLeft size={18} /> Volver
        </button>
      </div>

      {error && <div className="error-msg">{error}</div>}

      <div className="card">
        <form onSubmit={handleSubmit}>
          <div className="form-grid">
            <div className="form-group">
              <label>Nombre del Producto *</label>
              <input
                type="text"
                name="nombre"
                value={form.nombre}
                onChange={handleChange}
                placeholder="Ej: Cemento Portland 50kg"
                required
              />
            </div>

            <div className="form-group">
              <label>SKU (Código) *</label>
              <input
                type="text"
                name="sku"
                value={form.sku}
                onChange={handleChange}
                placeholder="Ej: CEM-001"
                required
              />
            </div>

            <div className="form-group">
              <label>Precio de Compra *</label>
              <input
                type="number"
                name="precio_compra"
                value={form.precio_compra}
                onChange={handleChange}
                placeholder="0.00"
                step="0.01"
                min="0"
                required
              />
            </div>

            <div className="form-group">
              <label>Precio de Venta *</label>
              <input
                type="number"
                name="precio_venta"
                value={form.precio_venta}
                onChange={handleChange}
                placeholder="0.00"
                step="0.01"
                min="0"
                required
              />
            </div>

            <div className="form-group">
              <label>Stock Actual</label>
              <input
                type="number"
                name="stock_actual"
                value={form.stock_actual}
                onChange={handleChange}
                min="0"
              />
            </div>

            <div className="form-group">
              <label>Stock Mínimo de Alerta</label>
              <input
                type="number"
                name="stock_minimo"
                value={form.stock_minimo}
                onChange={handleChange}
                min="0"
              />
            </div>

            <div className="form-group">
              <label>Días de Entrega del Proveedor</label>
              <input
                type="number"
                name="dias_entrega"
                value={form.dias_entrega}
                onChange={handleChange}
                min="0"
              />
            </div>
          </div>

          <div className="form-actions">
            <button type="submit" className="btn btn-primary btn-lg" disabled={saving}>
              <Save size={18} /> {saving ? 'Guardando...' : 'Guardar Producto'}
            </button>
            <button
              type="button"
              className="btn btn-outline btn-lg"
              onClick={() => navigate('/inventario')}
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
