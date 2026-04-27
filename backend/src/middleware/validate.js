/**
 * Middleware de validación para requests
 */

/**
 * Valida que los campos requeridos existan en req.body
 */
const requireFields = (fields) => {
  return (req, res, next) => {
    const missing = fields.filter((field) => {
      const value = req.body[field];
      return value === undefined || value === null || value === '';
    });

    if (missing.length > 0) {
      return res.status(400).json({
        success: false,
        error: `Campos requeridos faltantes: ${missing.join(', ')}`,
      });
    }
    next();
  };
};

/**
 * Valida que el ID sea un número entero positivo
 */
const validateId = (req, res, next) => {
  const id = parseInt(req.params.id, 10);
  if (isNaN(id) || id <= 0) {
    return res.status(400).json({
      success: false,
      error: 'ID inválido: debe ser un número entero positivo',
    });
  }
  req.params.id = id;
  next();
};

module.exports = { requireFields, validateId };
