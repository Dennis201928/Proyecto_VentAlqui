-- Agregar campo comprobante_pago a la tabla ventas
-- Este campo almacenará la ruta relativa del comprobante de pago subido

ALTER TABLE ventas 
ADD COLUMN IF NOT EXISTS comprobante_pago VARCHAR(255) NULL;

-- Comentario para documentar el campo
COMMENT ON COLUMN ventas.comprobante_pago IS 'Ruta relativa del comprobante de pago (para transferencias bancarias)';

