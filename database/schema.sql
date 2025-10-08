-- Base de datos para sistema de venta y alquiler de maquinaria y materiales pétreos
-- PostgreSQL Schema

-- Crear la base de datos (ejecutar como superusuario)
-- CREATE DATABASE venta_alquiler_db;

-- Conectar a la base de datos
-- \c venta_alquiler_db;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    password_hash VARCHAR(255) NOT NULL,
    tipo_usuario VARCHAR(20) DEFAULT 'cliente' CHECK (tipo_usuario IN ('cliente', 'admin', 'empleado')),
    activo BOOLEAN DEFAULT true,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE categorias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('maquinaria', 'material')),
    activa BOOLEAN DEFAULT true,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE productos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria_id INTEGER REFERENCES categorias(id),
    precio_venta DECIMAL(10,2),
    precio_alquiler_dia DECIMAL(10,2),
    stock_disponible INTEGER DEFAULT 0,
    stock_minimo INTEGER DEFAULT 0,
    imagen_principal VARCHAR(255),
    imagenes_adicionales TEXT[], -- Array de URLs de imágenes
    especificaciones JSONB, -- Especificaciones técnicas en formato JSON
    estado VARCHAR(20) DEFAULT 'disponible' CHECK (estado IN ('disponible', 'alquilado', 'mantenimiento', 'vendido')),
    activo BOOLEAN DEFAULT true,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de alquileres
CREATE TABLE alquileres (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    producto_id INTEGER REFERENCES productos(id),
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    precio_dia DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'confirmado', 'en_curso', 'finalizado', 'cancelado')),
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de ventas
CREATE TABLE ventas (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    total DECIMAL(10,2) NOT NULL,
    impuestos DECIMAL(10,2) DEFAULT 0,
    descuento DECIMAL(10,2) DEFAULT 0,
    metodo_pago VARCHAR(50),
    estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'confirmada', 'enviada', 'entregada', 'cancelada')),
    direccion_entrega TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de detalles de venta
CREATE TABLE venta_detalles (
    id SERIAL PRIMARY KEY,
    venta_id INTEGER REFERENCES ventas(id) ON DELETE CASCADE,
    producto_id INTEGER REFERENCES productos(id),
    cantidad INTEGER NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL
);

-- Tabla de carrito de compras
CREATE TABLE carrito (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    producto_id INTEGER REFERENCES productos(id),
    cantidad INTEGER NOT NULL DEFAULT 1,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('venta', 'alquiler')),
    fecha_inicio DATE, -- Para alquileres
    fecha_fin DATE, -- Para alquileres
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(usuario_id, producto_id, tipo, fecha_inicio, fecha_fin)
);

-- Tabla de favoritos
CREATE TABLE favoritos (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    producto_id INTEGER REFERENCES productos(id),
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(usuario_id, producto_id)
);

-- Tabla de contactos/mensajes
CREATE TABLE contactos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    asunto VARCHAR(200),
    mensaje TEXT NOT NULL,
    leido BOOLEAN DEFAULT false,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de configuración del sistema
CREATE TABLE configuracion (
    id SERIAL PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para mejorar el rendimiento
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_estado ON productos(estado);
CREATE INDEX idx_alquileres_usuario ON alquileres(usuario_id);
CREATE INDEX idx_alquileres_producto ON alquileres(producto_id);
CREATE INDEX idx_alquileres_fechas ON alquileres(fecha_inicio, fecha_fin);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);
CREATE INDEX idx_carrito_usuario ON carrito(usuario_id);
CREATE INDEX idx_favoritos_usuario ON favoritos(usuario_id);

-- Insertar datos iniciales
INSERT INTO categorias (nombre, descripcion, tipo) VALUES
('Volquetas', 'Maquinaria pesada para transporte de materiales', 'maquinaria'),
('Retroexcavadoras', 'Maquinaria para excavación y carga', 'maquinaria'),
('Gallinetas', 'Maquinaria para movimiento de tierra', 'maquinaria'),
('Rodillos', 'Maquinaria para compactación', 'maquinaria'),
('Excavadoras', 'Maquinaria pesada para excavación', 'maquinaria'),
('Compactadoras', 'Maquinaria para compactación de suelos', 'maquinaria'),
('Cargadores Frontales', 'Maquinaria para carga y descarga', 'maquinaria'),
('Granito', 'Material pétreo de alta calidad', 'material'),
('Arena', 'Material pétreo fino para construcción', 'material'),
('Grava', 'Material pétreo grueso para construcción', 'material'),
('Piedra Coco', 'Material pétreo decorativo', 'material');

-- Insertar configuración inicial
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('empresa_nombre', 'AlquiVenta - Maquinaria y Materiales', 'Nombre de la empresa'),
('empresa_telefono', '+012 345 6789', 'Teléfono de contacto'),
('empresa_email', 'info@alquivent.com', 'Email de contacto'),
('empresa_direccion', '123 Street, New York, USA', 'Dirección de la empresa'),
('impuesto_porcentaje', '19', 'Porcentaje de impuestos'),
('moneda', 'USD', 'Moneda del sistema'),
('dias_alquiler_minimo', '1', 'Días mínimos de alquiler'),
('dias_alquiler_maximo', '365', 'Días máximos de alquiler');

-- Crear usuario administrador por defecto
INSERT INTO usuarios (nombre, apellido, email, password_hash, tipo_usuario) VALUES
('Admin', 'Sistema', 'admin@alquivent.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Contraseña: password
