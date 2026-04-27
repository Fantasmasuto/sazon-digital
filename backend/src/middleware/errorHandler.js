/**
 * Middleware de manejo global de errores
 */
const errorHandler = (err, req, res, _next) => {
  console.error('Error:', err.message);

  if (err.code === '23505') {
    return res.status(409).json({
      success: false,
      error: 'El registro ya existe (duplicado)',
      detail: err.detail,
    });
  }

  if (err.code === '23503') {
    return res.status(400).json({
      success: false,
      error: 'Referencia inválida: el registro relacionado no existe',
      detail: err.detail,
    });
  }

  const statusCode = err.statusCode || 500;
  res.status(statusCode).json({
    success: false,
    error: err.message || 'Error interno del servidor',
    ...(process.env.NODE_ENV === 'development' && { stack: err.stack }),
  });
};

module.exports = errorHandler;
