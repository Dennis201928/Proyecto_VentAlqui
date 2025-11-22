<?php
namespace App\Core;

/**
 * Configuración general del sistema
 */
class Config {
    const SITE_NAME = 'AlquiVenta';
    const SITE_URL = 'http://localhost/Proyecto_VentAlqui/public';
    const ADMIN_EMAIL = 'admin@alquivent.com';
    const CURRENCY = 'USD';
    const TAX_RATE = 0.19;
    const MIN_RENTAL_DAYS = 1;
    const MAX_RENTAL_DAYS = 365;
    
    const SESSION_LIFETIME = 3600;
    
    const UPLOAD_PATH = 'uploads/';
    const MAX_FILE_SIZE = 5242880;
    
    const PRODUCTS_PER_PAGE = 12;
    const ORDERS_PER_PAGE = 10;
}