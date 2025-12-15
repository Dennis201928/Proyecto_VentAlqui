-- Agregar campo tipo_venta a la tabla productos
-- Este campo almacena si el producto se vende por stock o por kilogramos

ALTER TABLE productos ADD COLUMN IF NOT EXISTS tipo_venta VARCHAR(20) DEFAULT 'stock' CHECK (tipo_venta IN ('stock', 'kilogramos'));

-- Comentario para el campo
COMMENT ON COLUMN productos.tipo_venta IS 'Tipo de venta: stock (unidades) o kilogramos (peso)';

