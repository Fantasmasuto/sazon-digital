import { useEffect, useState } from 'react';
import { AlertTriangle, Play, Clock } from 'lucide-react';
import { api } from '../utils/api';

export default function Alertas() {
  const [alertas, setAlertas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [running, setRunning] = useState(false);
  const [error, setError] = useState(null);
  const [runResult, setRunResult] = useState(null);

  const loadAlertas = async () => {
    try {
      const res = await api.getAlertas();
      setAlertas(res.data);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadAlertas();
  }, []);

  const handleRunEngine = async () => {
    setRunning(true);
    setRunResult(null);
    try {
      const res = await api.runMotorPredictivo();
      setRunResult(res.data);
      loadAlertas();
    } catch (err) {
      setError(err.message);
    } finally {
      setRunning(false);
    }
  };

  const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleString('es-MX', {
      dateStyle: 'medium',
      timeStyle: 'short',
    });
  };

  return (
    <div>
      <div className="page-header">
        <h2>Alertas Predictivas</h2>
        <button
          className="btn btn-primary"
          onClick={handleRunEngine}
          disabled={running}
        >
          <Play size={18} /> {running ? 'Ejecutando...' : 'Ejecutar Motor Predictivo'}
        </button>
      </div>

      {error && <div className="error-msg">{error}</div>}

      {runResult && (
        <div className="card" style={{ marginBottom: 16, background: '#f0fdf4', borderColor: '#86efac' }}>
          Motor predictivo ejecutado. Alertas generadas: <strong>{runResult.alertasGeneradas}</strong>
        </div>
      )}

      {loading ? (
        <div className="loading">Cargando alertas...</div>
      ) : alertas.length === 0 ? (
        <div className="empty-state">
          <AlertTriangle size={48} style={{ opacity: 0.3, marginBottom: 16 }} />
          <h3>Sin alertas</h3>
          <p>No hay alertas predictivas. Ejecuta el motor predictivo para analizar el inventario.</p>
        </div>
      ) : (
        alertas.map((alerta) => {
          const isCritical = parseFloat(alerta.dias_restantes) <= alerta.dias_entrega;
          return (
            <div key={alerta.id} className={`alert-card ${isCritical ? 'critical' : ''}`}>
              <div className={`alert-icon ${isCritical ? 'critical' : 'warning'}`}>
                <AlertTriangle size={20} />
              </div>
              <div className="alert-body">
                <h4>
                  {alerta.producto_nombre}
                  <span style={{ fontWeight: 400, color: 'var(--text-secondary)', fontSize: '0.8rem', marginLeft: 8 }}>
                    ({alerta.producto_sku})
                  </span>
                </h4>
                <p>{alerta.mensaje}</p>
                <div className="alert-meta">
                  <Clock size={12} style={{ verticalAlign: 'middle', marginRight: 4 }} />
                  {formatDate(alerta.created_at)}
                  {' | '}
                  Stock actual: {alerta.stock_actual}
                  {' | '}
                  Velocidad: {parseFloat(alerta.velocidad_venta_dia).toFixed(2)}/día
                  {' | '}
                  Canal: {alerta.canal}
                  {alerta.enviada ? ' (enviada)' : ' (pendiente)'}
                </div>
              </div>
            </div>
          );
        })
      )}
    </div>
  );
}
