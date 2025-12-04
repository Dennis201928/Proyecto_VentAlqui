-- Agregar campo fecha_entrega a la tabla ventas
-- Este campo almacena la fecha seleccionada por el cliente para la entrega

ALTER TABLE ventas ADD COLUMN IF NOT EXISTS fecha_entrega DATE;

-- Comentario para el campo
COMMENT ON COLUMN ventas.fecha_entrega IS 'Fecha seleccionada por el cliente para la entrega del producto';
