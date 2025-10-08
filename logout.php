<?php
require_once 'includes/auth.php';

$auth = new Auth();
$result = $auth->logout();

// Redirigir al inicio con mensaje
header('Location: index.php?message=' . urlencode('Sesión cerrada exitosamente'));
exit();
?>
