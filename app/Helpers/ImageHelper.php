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
        
        // Rutas relativas antiguas
        // img/products/... -> baseUrl/assets/img/products/...
        if (strpos($imagePath, 'img/products/') === 0) {
            return $baseUrl . '/assets/' . $imagePath;
        }
        
        // img/... -> baseUrl/assets/img/...
        if (strpos($imagePath, 'img/') === 0) {
            return $baseUrl . '/assets/' . $imagePath;
        }
        
        // public/assets/... -> baseUrl/assets/...
        if (strpos($imagePath, 'public/assets/') === 0) {
            return $baseUrl . '/' . str_replace('public/', '', $imagePath);
        }
        
        // Si no coincide con ningún patrón, asumir que es relativa a assets/img/
        return $baseUrl . '/assets/img/' . ltrim($imagePath, '/');
    }
    
    /**
     * Obtener URL completa de una imagen
     */
    public static function getImageUrl($imagePath, $baseUrl = null) {
        if (!$baseUrl) {
            $baseUrl = Config::SITE_URL;
        }
        
        $normalized = self::normalizeImagePathWithBase($imagePath, $baseUrl);
        if (!$normalized) {
            return $baseUrl . '/assets/img/reference/product-1.jpg'; // Imagen por defecto
        }
        return $normalized;
    }
}

