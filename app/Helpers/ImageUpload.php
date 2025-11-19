<?php
namespace App\Helpers;

/**
 * Clase para manejo de subida de imágenes
 */
class ImageUpload {
    private $upload_dir;
    private $allowed_types;
    private $max_size;
    
    public function __construct($upload_dir = 'public/assets/img/products/') {
        $this->upload_dir = $upload_dir;
        $this->allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $this->max_size = 5 * 1024 * 1024; // 5MB
        
        // Crear directorio si no existe
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    /**
     * Subir una imagen
     */
    public function uploadImage($file, $prefix = '') {
        // Validar archivo
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo: ' . $this->getUploadError($file['error']));
        }
        
        // Validar tipo
        if (!in_array($file['type'], $this->allowed_types)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten JPG, PNG, GIF y WebP');
        }
        
        // Validar tamaño
        if ($file['size'] > $this->max_size) {
            throw new Exception('El archivo es demasiado grande. Máximo 5MB');
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $this->upload_dir . $filename;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filepath;
        } else {
            throw new Exception('Error al mover el archivo al directorio de destino');
        }
    }
    
    /**
     * Subir múltiples imágenes
     */
    public function uploadMultipleImages($files, $prefix = '') {
        $uploaded_files = [];
        
        if (is_array($files['name'])) {
            // Múltiples archivos
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    
                    try {
                        $uploaded = $this->uploadImage($file, $prefix . '_' . $i);
                        $uploaded_files[] = $uploaded;
                    } catch (Exception $e) {
                        // Continuar con otros archivos si uno falla
                        error_log('Error uploading image: ' . $e->getMessage());
                    }
                }
            }
        } else {
            // Un solo archivo
            if ($files['error'] === UPLOAD_ERR_OK) {
                try {
                    $uploaded = $this->uploadImage($files, $prefix);
                    $uploaded_files[] = $uploaded;
                } catch (Exception $e) {
                    throw $e;
                }
            }
        }
        
        return $uploaded_files;
    }
    
    /**
     * Redimensionar imagen
     */
    public function resizeImage($source_path, $max_width = 800, $max_height = 600, $quality = 80) {
        $image_info = getimagesize($source_path);
        if (!$image_info) {
            throw new Exception('No se pudo obtener información de la imagen');
        }
        
        $original_width = $image_info[0];
        $original_height = $image_info[1];
        $mime_type = $image_info['mime'];
        
        // Calcular nuevas dimensiones manteniendo proporción
        $ratio = min($max_width / $original_width, $max_height / $original_height);
        $new_width = intval($original_width * $ratio);
        $new_height = intval($original_height * $ratio);
        
        // Crear imagen desde archivo
        switch ($mime_type) {
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;
            case 'image/webp':
                $source_image = imagecreatefromwebp($source_path);
                break;
            default:
                throw new Exception('Tipo de imagen no soportado para redimensionamiento');
        }
        
        // Crear imagen redimensionada
        $resized_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preservar transparencia para PNG y GIF
        if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
            imagealphablending($resized_image, false);
            imagesavealpha($resized_image, true);
            $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
            imagefilledrectangle($resized_image, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Redimensionar
        imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, 
                          $new_width, $new_height, $original_width, $original_height);
        
        // Guardar imagen redimensionada
        $resized_path = $this->upload_dir . 'resized_' . basename($source_path);
        
        switch ($mime_type) {
            case 'image/jpeg':
                imagejpeg($resized_image, $resized_path, $quality);
                break;
            case 'image/png':
                imagepng($resized_image, $resized_path, 9);
                break;
            case 'image/gif':
                imagegif($resized_image, $resized_path);
                break;
            case 'image/webp':
                imagewebp($resized_image, $resized_path, $quality);
                break;
        }
        
        // Liberar memoria
        imagedestroy($source_image);
        imagedestroy($resized_image);
        
        return $resized_path;
    }
    
    /**
     * Eliminar imagen
     */
    public function deleteImage($image_path) {
        if (file_exists($image_path)) {
            return unlink($image_path);
        }
        return false;
    }
    
    /**
     * Obtener mensaje de error de subida
     */
    private function getUploadError($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'El archivo excede el tamaño máximo permitido por el servidor';
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo excede el tamaño máximo permitido por el formulario';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo fue subido parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'No se seleccionó ningún archivo';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta el directorio temporal';
            case UPLOAD_ERR_CANT_WRITE:
                return 'No se pudo escribir el archivo al disco';
            case UPLOAD_ERR_EXTENSION:
                return 'La subida fue detenida por una extensión';
            default:
                return 'Error desconocido al subir el archivo';
        }
    }
}
?>
