-- Agregar tabla para tokens de recuperación de contraseña
-- Ejecutar este script para agregar la funcionalidad de recuperación de contraseña

-- Tabla para tokens de recuperación de contraseña
CREATE TABLE password_recovery_tokens (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT
);

-- Índice para mejorar el rendimiento
CREATE INDEX idx_password_recovery_tokens_token ON password_recovery_tokens(token);
CREATE INDEX idx_password_recovery_tokens_usuario ON password_recovery_tokens(usuario_id);
CREATE INDEX idx_password_recovery_tokens_expires ON password_recovery_tokens(expires_at);

-- Limpiar tokens expirados (se puede ejecutar como tarea programada)
-- DELETE FROM password_recovery_tokens WHERE expires_at < NOW() OR used = true;
