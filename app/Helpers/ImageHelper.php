<?php
namespace App\Helpers;

use App\Core\Config;

/**
 * Helper para manejar rutas de imágenes
 */
class ImageHelper {
    
    /**
     * Normalizar ruta de imagen para que funcione con la nueva estructura
     * Convierte rutas antiguas (img/products/...) a nuevas (public/assets/img/products/...)
     */
    public static function normalizeImagePath($imagePath) {
        if (empty($imagePath)) {
            return null;
        }
        
        // Si ya es una ruta absoluta (http://), devolverla tal cual
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }
        
        // Si la ruta comienza con /, es absoluta desde la raíz del servidor
        if (strpos($imagePath, '/') === 0) {
            // Si ya apunta a /Proyecto_VentAlqui/public/assets, dejarla así
            if (strpos($imagePath, '/Proyecto_VentAlqui/public/assets') === 0) {
                return $imagePath;
            }
            // Si apunta a /assets, agregar el path del proyecto
            if (strpos($imagePath, '/assets') === 0) {
                return '/Proyecto_VentAlqui/public' . $imagePath;
            }
            // Si es otra ruta absoluta, intentar convertirla
            return '/Proyecto_VentAlqui/public' . $imagePath;
        }
        
        // Rutas relativas antiguas
        // img/products/... -> /Proyecto_VentAlqui/public/assets/img/products/...
        if (strpos($imagePath, 'img/products/') === 0) {
            return '/Proyecto_VentAlqui/public/assets/' . $imagePath;
        }
        
        // img/... -> /Proyecto_VentAlqui/public/assets/img/...
        if (strpos($imagePath, 'img/') === 0) {
            return '/Proyecto_VentAlqui/public/assets/' . $imagePath;
        }
        
        // public/assets/... -> /Proyecto_VentAlqui/public/assets/...
        if (strpos($imagePath, 'public/assets/') === 0) {
            return '/Proyecto_VentAlqui/' . $imagePath;
        }
        
        // Si no coincide con ningún patrón, asumir que es relativa a assets/img/
        return '/Proyecto_VentAlqui/public/assets/img/' . ltrim($imagePath, '/');
    }
    
    /**
     * Normalizar ruta de imagen usando baseUrl dinámico
     */
    public static function normalizeImagePathWithBase($imagePath, $baseUrl = null) {
        if (empty($imagePath)) {
            return null;
        }
        
        if (!$baseUrl) {
            $baseUrl = Config::SITE_URL;
        }
        
        // Si ya es una ruta absoluta (http://), devolverla tal cual
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }
        
        // Si la ruta comienza con /, es absoluta desde la raíz del servidor
        if (strpos($imagePath, '/') === 0) {
            // Si ya apunta a la base correcta, dejarla así
            if (strpos($imagePath, $baseUrl) === 0) {
                return $imagePath;
            }
            // Si apunta a /assets, agregar el baseUrl
            if (strpos($imagePath, '/assets') === 0) {
                return $baseUrl . $imagePath;
            }
            // Si es otra ruta absoluta, intentar convertirla
            return $baseUrl . $imagePath;
        }
        
        // Rutas que comienzan con public/public/assets/... -> baseUrl/assets/...
        // Manejar rutas incorrectas con public duplicado (legacy)
        if (strpos($imagePath, 'public/public/assets/') === 0) {
            // Remover 'public/public/' del inicio y construir la URL correcta
            $relativePath = str_replace('public/public/', '', $imagePath);
            $baseUrl = rtrim($baseUrl, '/');
            $relativePath = '/' . ltrim($relativePath, '/');
            return $baseUrl . $relativePath;
        }
        
        // Rutas que comienzan con public/assets/... -> baseUrl/assets/...
        // Para compatibilidad con rutas antiguas (debe ir antes de assets/)
        if (strpos($imagePath, 'public/assets/') === 0) {
            // Remover 'public/' del inicio y construir la URL correcta
            $relativePath = str_replace('public/', '', $imagePath);
            $baseUrl = rtrim($baseUrl, '/');
            $relativePath = '/' . ltrim($relativePath, '/');
            return $baseUrl . $relativePath;
        }
        
        // Rutas que comienzan con assets/... -> baseUrl/assets/...
        // Esta es la ruta que se guarda cuando se sube una imagen nueva (sin public/)
        if (strpos($imagePath, 'assets/') === 0) {
            $baseUrl = rtrim($baseUrl, '/');
            $relativePath = '/' . ltrim($imagePath, '/');
            return $baseUrl . $relativePath;
        }
        
        // Rutas que comienzan con img/products/... -> baseUrl/assets/img/products/...
        if (strpos($imagePath, 'img/products/') === 0) {
            return $baseUrl . '/assets/' . $imagePath;
        }
        
        // Rutas que comienzan con img/... -> baseUrl/assets/img/...
        if (strpos($imagePath, 'img/') === 0) {
            return $baseUrl . '/assets/' . $imagePath;
        }
        
        // Si no coincide con ningún patrón, asumir que es relativa a assets/img/
        return $baseUrl . '/assets/img/' . ltrim($imagePath, '/');
    }
    
    /**
     * Obtener URL completa de una imagen
     */
    public static function getImageUrl($imagePath, $baseUrl = null) {
        if (empty($imagePath)) {
            if (!$baseUrl) {
                $baseUrl = Config::SITE_URL;
            }
            return $baseUrl . '/assets/img/reference/product-1.jpg';
        }
        
        if (!$baseUrl) {
            $baseUrl = Config::SITE_URL;
        }
        
        $normalized = self::normalizeImagePathWithBase($imagePath, $baseUrl);
        if (!$normalized) {
            return $baseUrl . '/assets/img/reference/product-1.jpg';
        }
        return $normalized;
    }
}

