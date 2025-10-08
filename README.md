# Sistema de Venta y Alquiler de Maquinaria

Sistema web desarrollado en PHP y PostgreSQL para la venta y alquiler de maquinaria pesada y materiales pétreos.

## Características

- **Gestión de Productos**: CRUD completo para productos de maquinaria y materiales
- **Sistema de Alquileres**: Gestión de alquileres con verificación de disponibilidad
- **Carrito de Compras**: Sistema completo de carrito para ventas y alquileres
- **Autenticación de Usuarios**: Sistema de login/registro con diferentes tipos de usuario
- **Panel de Administración**: Gestión completa del sistema para administradores
- **API REST**: APIs para integración con frontend y aplicaciones móviles
- **Búsqueda y Filtros**: Sistema avanzado de búsqueda y filtrado de productos

## Requisitos del Sistema

- **PHP**: 7.4 o superior
- **PostgreSQL**: 12 o superior
- **Servidor Web**: Apache o Nginx
- **Extensiones PHP**:
  - PDO PostgreSQL
  - JSON
  - Session
  - GD (para manejo de imágenes)

## Instalación

### 1. Clonar el Repositorio

```bash
git clone [url-del-repositorio]
cd Proyecto_VentAlqui
```

### 2. Configurar Base de Datos

1. Crear una base de datos PostgreSQL:
```sql
CREATE DATABASE venta_alquiler_db;
```

2. Crear un usuario para la aplicación:
```sql
CREATE USER app_user WITH PASSWORD 'tu_password';
GRANT ALL PRIVILEGES ON DATABASE venta_alquiler_db TO app_user;
```

### 3. Ejecutar Instalador

1. Acceder a `http://tu-dominio/install.php`
2. Seguir los pasos del instalador:
   - Configurar conexión a base de datos
   - Crear tablas automáticamente
   - Finalizar instalación

### 4. Configurar Permisos

```bash
chmod 755 uploads/
chmod 755 logs/
chmod 644 config/database.php
```

## Estructura del Proyecto

```
Proyecto_VentAlqui/
├── api/                    # APIs REST
│   ├── products.php
│   ├── categories.php
│   ├── cart.php
│   └── rentals.php
├── config/                 # Configuración
│   └── database.php
├── database/               # Scripts de base de datos
│   └── schema.sql
├── includes/               # Clases PHP
│   ├── auth.php
│   ├── product.php
│   ├── cart.php
│   └── rental.php
├── uploads/                # Archivos subidos
│   ├── products/
│   └── users/
├── css/                    # Estilos CSS
├── js/                     # JavaScript
├── img/                    # Imágenes estáticas
├── index.php              # Página principal
├── login.php              # Autenticación
├── install.php            # Instalador
└── README.md              # Este archivo
```

## Uso del Sistema

### Usuarios Regulares

1. **Registro/Login**: Crear cuenta o iniciar sesión
2. **Navegación**: Explorar productos de maquinaria y materiales
3. **Compras**: Agregar productos al carrito y realizar compras
4. **Alquileres**: Solicitar alquileres de maquinaria
5. **Mi Cuenta**: Gestionar perfil, pedidos y alquileres

### Administradores

1. **Gestión de Productos**: Crear, editar y eliminar productos
2. **Gestión de Categorías**: Administrar categorías de productos
3. **Gestión de Usuarios**: Ver y gestionar usuarios del sistema
4. **Gestión de Pedidos**: Procesar pedidos y alquileres
5. **Estadísticas**: Ver reportes y estadísticas del sistema

## APIs Disponibles

### Productos
- `GET /api/products.php` - Listar productos
- `GET /api/products.php?id={id}` - Obtener producto específico
- `POST /api/products.php` - Crear producto (admin)
- `PUT /api/products.php?id={id}` - Actualizar producto (admin)
- `DELETE /api/products.php?id={id}` - Eliminar producto (admin)

### Carrito
- `GET /api/cart.php` - Obtener carrito del usuario
- `POST /api/cart.php` - Agregar producto al carrito
- `PUT /api/cart.php` - Actualizar cantidad
- `DELETE /api/cart.php` - Eliminar item del carrito

### Alquileres
- `GET /api/rentals.php` - Listar alquileres
- `POST /api/rentals.php` - Crear alquiler
- `PUT /api/rentals.php?id={id}` - Actualizar estado (admin)
- `DELETE /api/rentals.php?id={id}` - Cancelar alquiler

## Configuración

### Variables de Configuración

Editar `config/database.php` para configurar:

```php
class Config {
    const SITE_NAME = 'AlquiVenta';
    const SITE_URL = 'http://tu-dominio.com';
    const ADMIN_EMAIL = 'admin@tu-dominio.com';
    const CURRENCY = 'USD';
    const TAX_RATE = 0.19; // 19%
    const MIN_RENTAL_DAYS = 1;
    const MAX_RENTAL_DAYS = 365;
}
```

### Configuración de Base de Datos

```php
class Database {
    private $host = 'localhost';
    private $db_name = 'venta_alquiler_db';
    private $username = 'tu_usuario';
    private $password = 'tu_password';
    private $port = '5432';
}
```

## Seguridad

- Contraseñas hasheadas con `password_hash()`
- Validación de entrada en todas las APIs
- Protección contra SQL injection con PDO
- Verificación de sesiones y permisos
- Sanitización de datos de entrada

## Desarrollo

### Agregar Nuevas Funcionalidades

1. Crear clase en `includes/`
2. Crear API en `api/`
3. Actualizar base de datos si es necesario
4. Actualizar frontend

### Base de Datos

Para modificar la base de datos:

1. Editar `database/schema.sql`
2. Ejecutar migración manualmente
3. Actualizar clases PHP correspondientes

## Soporte

Para soporte técnico o reportar bugs, contactar a:
- Email: admin@alquivent.com
- Teléfono: +012 345 6789

## Licencia

Este proyecto está bajo la licencia MIT. Ver archivo LICENSE para más detalles.

## Changelog

### v1.0.0
- Sistema inicial de venta y alquiler
- Autenticación de usuarios
- Gestión de productos
- Carrito de compras
- Sistema de alquileres
- Panel de administración básico
