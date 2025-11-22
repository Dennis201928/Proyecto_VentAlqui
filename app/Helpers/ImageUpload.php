<?php
namespace App\Helpers;

/**
 * Clase para manejo de subida de imágenes
 */
class ImageUpload {
    private $upload_dir;
    private $allowed_types;
    private $max_size;
    
    public function __construct($upload_dir = null) {
        if ($upload_dir === null) {
            $project_root = dirname(dirname(dirname(__DIR__)));
            
            $public_index = $project_root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
            if (!file_exists($public_index)) {
                if (isset($_SERVER['SCRIPT_FILENAME'])) {
                    $script_dir = dirname($_SERVER['SCRIPT_FILENAME']);
                    if (basename($script_dir) === 'public') {
                        $project_root = dirname($script_dir);
                    } else {
                        $current = $script_dir;
                        while ($current !== dirname($current)) {
                            if (file_exists($current . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php')) {
                                $project_root = $current;
                                break;
                            }
                            $current = dirname($current);
                        }
                    }
                }
            }
            
            $this->upload_dir = $project_root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
        } else {
            $is_absolute = (strpos($upload_dir, DIRECTORY_SEPARATOR) === 0) || 
                          (strlen($upload_dir) > 1 && $upload_dir[1] === ':') ||
                          (strpos($upload_dir, '\\\\') === 0);
            
            if ($is_absolute) {
                $this->upload_dir = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            } else {
                $project_root = dirname(dirname(dirname(__DIR__)));
                $normalized_path = str_replace('/', DIRECTORY_SEPARATOR, $upload_dir);
                $this->upload_dir = $project_root . DIRECTORY_SEPARATOR . rtrim($normalized_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }
        }
        
        $this->allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $this->max_size = 5 * 1024 * 1024; // 5MB
        
        // Crear directorio si no existe
        if (!file_exists($this->upload_dir)) {
            if (!mkdir($this->upload_dir, 0755, true)) {
                throw new \Exception('No se pudo crear el directorio de subida de imágenes');
            }
        }
        
        // Verificar permisos de escritura
        if (!is_writable($this->upload_dir)) {
            throw new \Exception('El directorio de subida no tiene permisos de escritura');
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
        
        // Verificar que el archivo temporal existe
        if (!file_exists($file['tmp_name'])) {
            throw new Exception('El archivo temporal no existe: ' . $file['tmp_name']);
        }
        
        // Verificar que el directorio de destino existe y es escribible
        if (!is_dir($this->upload_dir)) {
            throw new Exception('El directorio de destino no existe: ' . $this->upload_dir);
        }
        
        if (!is_writable($this->upload_dir)) {
            throw new Exception('El directorio de destino no tiene permisos de escritura: ' . $this->upload_dir);
        }
        
        // Mover archivo
        $moved = @move_uploaded_file($file['tmp_name'], $filepath);
        
        if ($moved) {
            // Verificar que el archivo se haya guardado correctamente
            if (!file_exists($filepath)) {
                throw new Exception('El archivo se movió pero no se encuentra en el destino: ' . $filepath);
            }
            
            // Verificar que el archivo tenga contenido
            if (filesize($filepath) == 0) {
                throw new Exception('El archivo se guardó pero está vacío: ' . $filepath);
            }
            
            // Devolver ruta para guardar en BD
            $project_root = dirname(dirname(dirname(__DIR__)));
            $public_index = $project_root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
            if (!file_exists($public_index) && isset($_SERVER['SCRIPT_FILENAME'])) {
                $script_dir = dirname($_SERVER['SCRIPT_FILENAME']);
                if (basename($script_dir) === 'public') {
                    $project_root = dirname($script_dir);
                } else {
                    $current = $script_dir;
                    while ($current !== dirname($current)) {
                        if (file_exists($current . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php')) {
                            $project_root = $current;
                            break;
                        }
                        $current = dirname($current);
                    }
                }
            }
            
            $relative_path = str_replace($project_root . DIRECTORY_SEPARATOR, '', $filepath);
            $relative_path = str_replace(DIRECTORY_SEPARATOR, '/', $relative_path);
            
            if (strpos($relative_path, 'public/') === 0) {
                $relative_path = substr($relative_path, 7); // Remover 'public/'
            }
            
            return $relative_path;
        } else {
            $error_msg = 'Error al mover el archivo al directorio de destino';
            $last_error = error_get_last();
            if ($last_error) {
                $error_msg .= ': ' . $last_error['message'];
            }
            if (!is_writable($this->upload_dir)) {
                $error_msg .= '. El directorio no tiene permisos de escritura';
            }
            throw new Exception($error_msg);
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
