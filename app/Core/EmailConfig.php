<?php
namespace App\Core;

/**
 * Configuración de email
 */
class EmailConfig {
    const FROM_EMAIL = 'noreply@alquivent.com';
    const FROM_NAME = 'AlquiVenta';
    const CONTACT_EMAIL = 'info@alquivent.com';
    const MAIL_TYPE = 'text/plain';
    const CHARSET = 'UTF-8';
    
    // Configuración PHPMailer
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_USERNAME = 'dennis2000.david@gmail.com';
    const SMTP_PASSWORD = 'twvy ieuz unri nclp';
    const SMTP_SECURE = true;
    const SMTP_PORT = 587;
}

