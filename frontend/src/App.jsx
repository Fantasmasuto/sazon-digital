import { BrowserRouter, Routes, Route, NavLink } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { ShoppingCart, Package, AlertTriangle, LayoutDashboard, Wifi, WifiOff } from 'lucide-react';
import { useOnlineStatus } from './hooks/useOnlineStatus';
import { registerSyncListeners } from './services/syncService';
import { countPendingVentas } from './services/offlineDB';
import Dashboard from './pages/Dashboard';
import POS from './pages/POS';
import Inventario from './pages/Inventario';
import ProductoForm from './pages/ProductoForm';
import Alertas from './pages/Alertas';
import './styles.css';

function App() {
  const isOnline = useOnlineStatus();
  const [pendingCount, setPendingCount] = useState(0);
  const [syncNotification, setSyncNotification] = useState(null);

  useEffect(() => {
    const updatePending = async () => {
      const count = await countPendingVentas();
      setPendingCount(count);
    };

    updatePending();
    const interval = setInterval(updatePending, 5000);

    const cleanup = registerSyncListeners((result) => {
      updatePending();
      if (result.synced > 0) {
        setSyncNotification(`${result.synced} venta(s) sincronizada(s)`);
        setTimeout(() => setSyncNotification(null), 4000);
      }
    });

    return () => {
      clearInterval(interval);
      cleanup();
    };
  }, []);

  return (
    <BrowserRouter>
      <div className="app">
        {/* Barra de estado de conexión */}
        <div className={`connection-bar ${isOnline ? 'online' : 'offline'}`}>
          {isOnline ? (
            <>
              <Wifi size={14} /> En línea
              {pendingCount > 0 && <span className="pending-badge">{pendingCount} pendiente(s)</span>}
            </>
          ) : (
            <>
              <WifiOff size={14} /> Sin conexión - Modo offline activo
              {pendingCount > 0 && <span className="pending-badge">{pendingCount} pendiente(s)</span>}
            </>
          )}
        </div>

        {/* Notificación de sincronización */}
        {syncNotification && (
          <div className="sync-notification">{syncNotification}</div>
        )}

        {/* Sidebar de navegación */}
        <nav className="sidebar">
          <div className="sidebar-header">
            <h1>Micro-ERP</h1>
            <span className="sidebar-subtitle">POS Predictivo</span>
          </div>
          <ul className="nav-links">
            <li>
              <NavLink to="/" end>
                <LayoutDashboard size={20} /> Dashboard
              </NavLink>
            </li>
            <li>
              <NavLink to="/pos">
                <ShoppingCart size={20} /> Punto de Venta
                {pendingCount > 0 && <span className="nav-badge">{pendingCount}</span>}
              </NavLink>
            </li>
            <li>
              <NavLink to="/inventario">
                <Package size={20} /> Inventario
              </NavLink>
            </li>
            <li>
              <NavLink to="/alertas">
                <AlertTriangle size={20} /> Alertas
              </NavLink>
            </li>
          </ul>
        </nav>

        {/* Contenido principal */}
        <main className="main-content">
          <Routes>
            <Route path="/" element={<Dashboard />} />
            <Route path="/pos" element={<POS />} />
            <Route path="/inventario" element={<Inventario />} />
            <Route path="/inventario/nuevo" element={<ProductoForm />} />
            <Route path="/inventario/editar/:id" element={<ProductoForm />} />
            <Route path="/alertas" element={<Alertas />} />
          </Routes>
        </main>
      </div>
    </BrowserRouter>
  );
}

export default App;
