import { useEffect, useState } from 'react';
import { Package, ShoppingCart, AlertTriangle, DollarSign } from 'lucide-react';
import { api } from '../utils/api';

export default function Dashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.getDashboard()
      .then((res) => setData(res.data))
      .catch((err) => console.error('Error cargando dashboard:', err))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading">Cargando dashboard...</div>;

  return (
    <div>
      <div className="page-header">
        <h2>Dashboard</h2>
      </div>

      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-icon blue">
            <Package size={24} />
          </div>
          <div className="stat-info">
            <h3>{data?.productos?.total || 0}</h3>
            <p>Productos activos</p>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon green">
            <ShoppingCart size={24} />
          </div>
          <div className="stat-info">
            <h3>{data?.ventas_hoy?.total_ventas || 0}</h3>
            <p>Ventas hoy</p>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon amber">
            <DollarSign size={24} />
          </div>
          <div className="stat-info">
            <h3>${parseFloat(data?.ventas_hoy?.ingresos_totales || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 })}</h3>
            <p>Ingresos hoy</p>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon red">
            <AlertTriangle size={24} />
          </div>
          <div className="stat-info">
            <h3>{data?.alertas_activas || 0}</h3>
            <p>Alertas activas</p>
          </div>
        </div>
      </div>

      <div className="card">
        <h3 style={{ marginBottom: 12 }}>Acerca del Sistema</h3>
        <p style={{ color: 'var(--text-secondary)', fontSize: '0.9rem', lineHeight: 1.7 }}>
          <strong>Micro-ERP & POS Predictivo</strong> es un sistema SaaS B2B diseñado para
          ferreterías y abarrotes. Incluye punto de venta offline-first, gestión de inventario
          y un motor predictivo que analiza la velocidad de venta para alertar antes de que
          se agote el stock.
        </p>
      </div>
    </div>
  );
}
