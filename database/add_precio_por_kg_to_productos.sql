-- Agregar campo precio_por_kg a la tabla productos
-- Este campo almacena el precio por kilogramo para productos de Materiales Pétreos

ALTER TABLE productos ADD COLUMN IF NOT EXISTS precio_por_kg DECIMAL(10,2);

-- Comentario para el campo
COMMENT ON COLUMN productos.precio_por_kg IS 'Precio por kilogramo para productos de Materiales Pétreos que se venden por peso';

