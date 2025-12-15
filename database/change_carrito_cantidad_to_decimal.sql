-- Cambiar el campo cantidad de INTEGER a DECIMAL para permitir kilogramos
-- Este cambio permite almacenar valores decimales para productos vendidos por kilogramos

ALTER TABLE carrito ALTER COLUMN cantidad TYPE DECIMAL(10,3);
