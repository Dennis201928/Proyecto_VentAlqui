<?php
/**
 * Procesador de formulario de contacto
 * Envía mensajes del formulario de contacto al correo personal
 */

require_once __DIR__ . '/../includes/SimpleContactService.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Validar campos requeridos
if (empty($_POST['name']) || empty($_POST['subject']) || empty($_POST['message']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Por favor completa todos los campos correctamente']);
    exit();
}

// Sanitizar datos de entrada
$name = strip_tags(htmlspecialchars($_POST['name']));
$email = strip_tags(htmlspecialchars($_POST['email']));
$m_subject = strip_tags(htmlspecialchars($_POST['subject']));
$message = strip_tags(htmlspecialchars($_POST['message']));

// Crear instancia del servicio de contacto
$contactService = new SimpleContactService();

// Guardar el mensaje de contacto
if ($contactService->sendContactEmail($name, $email, $m_subject, $message)) {
    echo json_encode(['success' => true, 'message' => 'Mensaje enviado exitosamente. Te contactaremos pronto.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al enviar el mensaje. Por favor intenta de nuevo.']);
}
?>
