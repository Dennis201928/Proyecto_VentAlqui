<?php
namespace App\Helpers;

use App\Core\Config;

/**
 * Helper para manejar rutas de imágenes
 */
class ImageHelper {
    
    public static function normalizeImagePath($imagePath) {
        if (empty($imagePath)) {
            return null;
        }
        
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }
        
        if (strpos($imagePath, '/') === 0) {
            if (strpos($imagePath, '/Proyecto_VentAlqui/public/assets') === 0) {
                return $imagePath;
            }

            if (strpos($imagePath, '/assets') === 0) {
                return '/Proyecto_VentAlqui/public' . $imagePath;
            }
            return '/Proyecto_VentAlqui/public' . $imagePath;
        }
        
        if (strpos($imagePath, 'img/products/') === 0) {
            return '/Proyecto_VentAlqui/public/assets/' . $imagePath;
        }
        
        if (strpos($imagePath, 'img/') === 0) {
            return '/Proyecto_VentAlqui/public/assets/' . $imagePath;
        }
        
        if (strpos($imagePath, 'public/assets/') === 0) {
            return '/Proyecto_VentAlqui/' . $imagePath;
        }
        
        return '/Proyecto_VentAlqui/public/assets/img/' . ltrim($imagePath, '/');
    }
    
    public static function normalizeImagePathWithBase($imagePath, $baseUrl = null) {
        if (empty($imagePath)) {
            return null;
        }
        
        if (!$baseUrl) {
            $baseUrl = Config::SITE_URL;
        }
        
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }
        
        if (strpos($imagePath, '/') === 0) {
            if (strpos($imagePath, $baseUrl) === 0) {
                return $imagePath;
            }

            if (strpos($imagePath, '/assets') === 0) {
                return $baseUrl . $imagePath;
            }
            return $baseUrl . $imagePath;
        }
        
        if (strpos($imagePath, 'public/public/assets/') === 0) {
            $relativePath = str_replace('public/public/', '', $imagePath);
            $baseUrl = rtrim($baseUrl, '/');
            $relativePath = '/' . ltrim($relativePath, '/');
            return $baseUrl . $relativePath;
        }
        

        if (strpos($imagePath, 'public/assets/') === 0) {
            $relativePath = str_replace('public/', '', $imagePath);
            $baseUrl = rtrim($baseUrl, '/');
            $relativePath = '/' . ltrim($relativePath, '/');
            return $baseUrl . $relativePath;
        }
        
        if (strpos($imagePath, 'assets/') === 0) {
            $baseUrl = rtrim($baseUrl, '/');
            $relativePath = '/' . ltrim($imagePath, '/');
            return $baseUrl . $relativePath;
        }
        
        if (strpos($imagePath, 'img/products/') === 0) {
            return $baseUrl . '/assets/' . $imagePath;
        }
        
        if (strpos($imagePath, 'img/') === 0) {
            return $baseUrl . '/assets/' . $imagePath;
        }
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

