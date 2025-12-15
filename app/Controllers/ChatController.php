<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

/**
 * Controlador del chatbot
 */
class ChatController extends Controller {
    
    /**
     * Procesar mensaje del chatbot
     */
    public function processMessage() {
        header('Content-Type: application/json');
        
        $message = trim($this->post('message', ''));
        
        if (empty($message)) {
            $this->json([
                'success' => false,
                'response' => 'Por favor, escribe un mensaje.'
            ], 400);
        }
        
        $response = $this->processChatMessage($message);
        
        $this->json([
            'success' => true,
            'response' => $response['text'],
            'type' => $response['type'] ?? 'text',
            'data' => $response['data'] ?? null
        ]);
    }
    
    /**
     * Procesar mensaje del chat usando reglas
     */
    private function processChatMessage($message) {
        $message_lower = mb_strtolower($message, 'UTF-8');
        $message_lower = trim($message_lower);
        
        // Normalizar mensaje (remover acentos para mejor matching)
        $message_normalized = $this->normalizeString($message_lower);
        
        // 1. SALUDOS
        if ($this->matchesPattern($message_normalized, ['hola', 'buenos dias', 'buenos días', 'buenas tardes', 'buenas noches', 'saludos', 'hi', 'hello'])) {
            return [
                'text' => '¡Hola! Bienvenido a AlquiVenta. Estoy aquí para ayudarte con información sobre nuestros productos de maquinaria pesada y materiales pétreos. ¿En qué puedo ayudarte?',
                'type' => 'text'
            ];
        }
        
        // 2. BÚSQUEDA DE PRODUCTOS
        if ($this->matchesPattern($message_normalized, ['buscar', 'busco', 'encontrar', 'mostrar', 'listar', 'ver productos', 'productos disponibles', 'catalogo', 'catálogo'])) {
            $searchTerm = $this->extractSearchTerm($message);
            
            if (!empty($searchTerm)) {
                return $this->searchProducts($searchTerm);
            } else {
                return [
                    'text' => '¿Qué producto estás buscando? Puedo ayudarte a encontrar maquinaria o materiales. Por ejemplo: "buscar excavadora" o "mostrar materiales disponibles".',
                    'type' => 'text'
                ];
            }
        }
        
        // 3. INFORMACIÓN DE PRECIOS
        if ($this->matchesPattern($message_normalized, ['precio', 'precios', 'cuesta', 'cuanto', 'cuánto', 'costo', 'tarifa', 'tarifas'])) {
            $productName = $this->extractProductName($message);
            if (!empty($productName)) {
                return $this->getProductPrice($productName);
            } else {
                return [
                    'text' => 'Para consultar precios, necesito saber qué producto te interesa. Por ejemplo: "precio de excavadora" o "cuánto cuesta el alquiler de una retroexcavadora".',
                    'type' => 'text'
                ];
            }
        }
        
        // 4. DISPONIBILIDAD Y STOCK
        if ($this->matchesPattern($message_normalized, ['disponible', 'disponibilidad', 'stock', 'hay', 'tienen', 'tiene', 'inventario'])) {
            $productName = $this->extractProductName($message);
            if (!empty($productName)) {
                if ($this->matchesPattern($message_normalized, ['stock', 'inventario'])) {
                    return $this->getProductStock($productName);
                } else {
                    return $this->getProductAvailability($productName);
                }
            } else {
                return [
                    'text' => 'Para verificar disponibilidad o stock, necesito saber qué producto te interesa. Por ejemplo: "¿hay excavadoras disponibles?" o "stock de grava".',
                    'type' => 'text'
                ];
            }
        }
        
        // 5. INFORMACIÓN DE CONTACTO
        if ($this->matchesPattern($message_normalized, ['contacto', 'contactar', 'telefono', 'teléfono', 'email', 'correo', 'direccion', 'dirección', 'ubicacion', 'ubicación'])) {
            return [
                'text' => '**Información de Contacto:**\n\nDirección: 123 Street, New York, USA\nEmail: info@alquivent.com\nTeléfono: +593 345 67890\n\nTambién puedes visitar nuestra página de contacto para enviarnos un mensaje directo.',
                'type' => 'text',
                'data' => [
                    'link' => '/contacto',
                    'linkText' => 'Ir a Contacto'
                ]
            ];
        }
        
        // 6. HORARIOS
        if ($this->matchesPattern($message_normalized, ['horario', 'horarios', 'hora', 'abierto', 'abren', 'cierran', 'atencion', 'atención'])) {
            return [
                'text' => '**Horarios de Atención:**\n\nLunes a Viernes: 8:00 AM - 6:00 PM\nSábados: 9:00 AM - 2:00 PM\nDomingos: Cerrado\n\nEstamos disponibles para atenderte en estos horarios. Para consultas urgentes, puedes contactarnos por teléfono o email.',
                'type' => 'text'
            ];
        }
        
        // 7. CÓMO ALQUILAR
        if ($this->matchesPattern($message_normalized, ['como alquilar', 'cómo alquilar', 'proceso alquiler', 'alquilar', 'alquiler', 'rentar'])) {
            return [
                'text' => '**Proceso de Alquiler:**\n\n1. Explora nuestro catálogo de maquinaria disponible\n2. Selecciona el producto que necesitas\n3. Elige las fechas de alquiler\n4. Completa el proceso de checkout\n5. Realiza el pago\n6. ¡Recibe tu maquinaria!\n\n¿Te gustaría ver nuestros productos disponibles para alquiler?',
                'type' => 'text',
                'data' => [
                    'link' => '/alquiler',
                    'linkText' => 'Ver Maquinaria para Alquiler'
                ]
            ];
        }
        
        // 8. MÉTODOS DE PAGO
        if ($this->matchesPattern($message_normalized, ['pago', 'pagos', 'metodo pago', 'método pago', 'formas pago', 'tarjeta', 'efectivo', 'transferencia'])) {
            return [
                'text' => '**Métodos de Pago Aceptados:**\n\n• Tarjeta de crédito/débito\n• Transferencia bancaria\n• Efectivo (en tienda)\n• Cheque certificado\n\nAceptamos los principales métodos de pago para tu comodidad.',
                'type' => 'text'
            ];
        }
        
        // 9. GARANTÍA
        if ($this->matchesPattern($message_normalized, ['garantia', 'garantía', 'devolucion', 'devolución', 'reembolso'])) {
            return [
                'text' => '**Garantía y Políticas:**\n\nOfrecemos garantía en todos nuestros productos y servicios. Nuestro equipo se asegura de que toda la maquinaria esté en perfecto estado antes de entregarla.\n\nPara más detalles sobre nuestras políticas de garantía y devolución, te recomendamos contactarnos directamente.',
                'type' => 'text'
            ];
        }
        
        // 10. NAVEGACIÓN - ALQUILER
        if ($this->matchesPattern($message_normalized, ['alquiler', 'maquinaria', 'alquilar maquinaria'])) {
            return [
                'text' => 'Tenemos una amplia variedad de maquinaria pesada disponible para alquiler. ¿Te gustaría ver nuestro catálogo?',
                'type' => 'text',
                'data' => [
                    'link' => '/alquiler',
                    'linkText' => 'Ver Maquinaria para Alquiler'
                ]
            ];
        }
        
        // 11. NAVEGACIÓN - VENTA
        if ($this->matchesPattern($message_normalized, ['venta', 'comprar', 'materiales', 'materiales petreos', 'materiales pétreos'])) {
            return [
                'text' => 'Ofrecemos materiales pétreos de alta calidad para construcción. ¿Te gustaría ver nuestro catálogo de materiales?',
                'type' => 'text',
                'data' => [
                    'link' => '/venta',
                    'linkText' => 'Ver Materiales para Venta'
                ]
            ];
        }
        
        // 12. DESPEDIDAS
        if ($this->matchesPattern($message_normalized, ['gracias', 'adios', 'adiós', 'hasta luego', 'chao', 'bye', 'nos vemos'])) {
            return [
                'text' => '¡De nada! Estoy aquí cuando necesites ayuda. ¡Que tengas un excelente día!',
                'type' => 'text'
            ];
        }
        
        // 13. AYUDA GENERAL
        if ($this->matchesPattern($message_normalized, ['ayuda', 'help', 'que puedo', 'qué puedo', 'opciones', 'menu', 'menú'])) {
            return [
                'text' => '**¿En qué puedo ayudarte?**\n\nPuedo ayudarte con:\n• Búsqueda de productos\n• Información de precios\n• Disponibilidad de stock\n• Proceso de alquiler\n• Información de contacto\n• Horarios de atención\n• Métodos de pago\n\nSolo escribe tu pregunta y te ayudo. Por ejemplo: "buscar excavadora" o "precio de grava".',
                'type' => 'text'
            ];
        }
        
        // RESPUESTA POR DEFECTO
        $products = $this->tryGenericSearch($message);
        if (!empty($products)) {
            return $products;
        }
        return [
            'text' => 'Lo siento, no entendí tu pregunta.\n\nPuedo ayudarte con:\n• Búsqueda de productos\n• Información de precios y disponibilidad\n• Proceso de alquiler\n• Información de contacto\n\nIntenta reformular tu pregunta o escribe "ayuda" para ver más opciones.',
            'type' => 'text'
        ];
    }
    
    /**
     * Normalizar string (remover acentos)
     */
    private function normalizeString($str) {
        $unwanted_array = [
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        ];
        return strtr($str, $unwanted_array);
    }
    
    /**
     * Verificar si el mensaje coincide con algún patrón
     */
    private function matchesPattern($message, $patterns) {
        foreach ($patterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Extraer término de búsqueda del mensaje
     */
    private function extractSearchTerm($message) {
        $message_lower = mb_strtolower($message, 'UTF-8');
        
        // Remover palabras comunes de búsqueda
        $remove_words = ['buscar', 'busco', 'encontrar', 'mostrar', 'listar', 'ver', 'quiero', 'necesito', 'productos', 'producto', 'de', 'la', 'el', 'las', 'los', 'un', 'una', 'unos', 'unas'];
        $words = explode(' ', $message_lower);
        $search_words = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            if (!empty($word) && !in_array($word, $remove_words) && strlen($word) > 2) {
                $search_words[] = $word;
            }
        }
        
        return implode(' ', $search_words);
    }
    
    /**
     * Extraer nombre de producto del mensaje
     */
    private function extractProductName($message) {
        $message_lower = mb_strtolower($message, 'UTF-8');
        $remove_words = ['precio', 'precios', 'cuesta', 'cuanto', 'cuánto', 'costo', 'disponible', 'disponibilidad', 'stock', 'hay', 'tienen', 'tiene', 'inventario', 'de', 'la', 'el', 'las', 'los', 'un', 'una', 'del', 'al', 'el', 'producto', 'productos', 'que', 'qué'];
        $words = explode(' ', $message_lower);
        $product_words = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            $word = preg_replace('/[.,;:!?¿¡]/', '', $word);
            if (!empty($word) && !in_array($word, $remove_words) && strlen($word) > 2) {
                $product_words[] = $word;
            }
        }
        
        $result = implode(' ', $product_words);
        
        // Si no se encontró nada, intentar extraer después de palabras clave
        if (empty($result)) {
            if (preg_match('/(?:cuanto|cuánto|precio|cuesta|costo).*(?:de|del|la|el)?\s+([a-záéíóúñ\s]+)/i', $message, $matches)) {
                $result = trim($matches[1]);
            }
            // Buscar patrones como "stock de [producto]" o "hay [producto]"
            elseif (preg_match('/(?:stock|disponibilidad|hay|tiene|tienen).*(?:de|del|la|el)?\s+([a-záéíóúñ\s]+)/i', $message, $matches)) {
                $result = trim($matches[1]);
            }
        }
        
        return $result;
    }
    
    /**
     * Buscar productos
     */
    private function searchProducts($searchTerm) {
        $product = new Product();
        $products = $product->searchProducts($searchTerm, ['limit' => 5]);
        
        if (empty($products) || (isset($products['error']))) {
            return [
                'text' => "No encontré productos que coincidan con '{$searchTerm}'.\n\nIntenta con otros términos o explora nuestras categorías:\n• Maquinaria pesada\n• Materiales pétreos",
                'type' => 'text',
                'data' => [
                    'links' => [
                        ['url' => '/alquiler', 'text' => 'Ver Maquinaria'],
                        ['url' => '/venta', 'text' => 'Ver Materiales']
                    ]
                ]
            ];
        }
        
        $response_text = "Encontré " . count($products) . " producto(s) relacionado(s) con '{$searchTerm}':\n\n";
        
        foreach ($products as $index => $prod) {
            $response_text .= ($index + 1) . ". **{$prod['nombre']}**\n";
            
            if (!empty($prod['descripcion'])) {
                $desc = substr($prod['descripcion'], 0, 80);
                $response_text .= "   " . $desc . (strlen($prod['descripcion']) > 80 ? '...' : '') . "\n";
            }
            
            if ($prod['precio_alquiler_dia']) {
                $response_text .= "   Alquiler: $" . number_format($prod['precio_alquiler_dia'], 2) . "/día\n";
            }
            
            if ($prod['precio_venta']) {
                $response_text .= "   Venta: $" . number_format($prod['precio_venta'], 2) . "\n";
            }
            
            $response_text .= "   Stock: {$prod['stock_disponible']}\n";
            $response_text .= "   Ver más: /producto/{$prod['id']}\n\n";
        }
        
        return [
            'text' => $response_text,
            'type' => 'products',
            'data' => [
                'products' => $products
            ]
        ];
    }
    
    /**
     * Obtener precio de producto
     */
    private function getProductPrice($productName) {
        return $this->getProductInfo($productName, 'precio');
    }
    
    /**
     * Obtener información completa del producto (nombre, precio, stock)
     */
    private function getProductInfo($productName, $queryType = 'info') {
        $product = new Product();
        $products = $product->searchProducts($productName, ['limit' => 3]);
        
        if (empty($products) || (isset($products['error']))) {
            $errorMessages = [
                'precio' => "No encontré información de precios para '{$productName}'.\n\n¿Podrías ser más específico? Por ejemplo: 'precio de excavadora' o 'cuánto cuesta grava'.",
                'disponibilidad' => "No encontré información de disponibilidad para '{$productName}'.\n\n¿Podrías ser más específico?",
                'stock' => "No encontré información de stock para '{$productName}'.\n\n¿Podrías ser más específico?",
                'info' => "No encontré el producto '{$productName}'.\n\n¿Podrías ser más específico?"
            ];
            
            return [
                'text' => $errorMessages[$queryType] ?? $errorMessages['info'],
                'type' => 'text'
            ];
        }
        
        $prod = $products[0];
        $stock = (int)$prod['stock_disponible'];
        $estado = $prod['estado'];
        
        // Construir respuesta con formato: "El producto [nombre], [precio], [stock disponible]"
        $response_text = "**El producto {$prod['nombre']}:**\n\n";
        
        // Agregar información de precios
        $precios = [];
        if ($prod['precio_alquiler_dia']) {
            $precios[] = "Alquiler: $" . number_format($prod['precio_alquiler_dia'], 2) . "/día";
        }
        if ($prod['precio_venta']) {
            $precios[] = "Venta: $" . number_format($prod['precio_venta'], 2);
        }
        if (isset($prod['precio_por_kg']) && $prod['precio_por_kg']) {
            $precios[] = "Por kg: $" . number_format($prod['precio_por_kg'], 2);
        }
        
        if (!empty($precios)) {
            $response_text .= implode(" | ", $precios) . "\n";
        } else {
            $response_text .= "Precio: Consultar\n";
        }
        
        // Agregar información de stock
        $response_text .= "Stock disponible: {$stock} unidad(es)\n";
        
        // Agregar estado si no está disponible
        if ($estado !== 'disponible' || $stock <= 0) {
            $response_text .= "Estado: " . ucfirst($estado) . "\n";
        }
        
        $response_text .= "\nVer detalles: /producto/{$prod['id']}";
        
        return [
            'text' => $response_text,
            'type' => 'text',
            'data' => [
                'link' => "/producto/{$prod['id']}",
                'linkText' => 'Ver Producto'
            ]
        ];
    }
    
    /**
     * Obtener disponibilidad de producto
     */
    private function getProductAvailability($productName) {
        return $this->getProductInfo($productName, 'disponibilidad');
    }
    
    /**
     * Obtener stock de producto
     */
    private function getProductStock($productName) {
        return $this->getProductInfo($productName, 'stock');
    }
    
    /**
     * Intentar búsqueda genérica
     */
    private function tryGenericSearch($message) {
        // Si el mensaje tiene más de 3 caracteres, intentar búsqueda
        if (strlen(trim($message)) > 3) {
            $searchTerm = $this->extractSearchTerm($message);
            if (!empty($searchTerm)) {
                return $this->searchProducts($searchTerm);
            }
        }
        return null;
    }
}

