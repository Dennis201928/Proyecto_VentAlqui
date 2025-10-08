<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['name'];
    $correo = $_POST['email'];
    $asunto = $_POST['subject'];
    $mensaje = $_POST['message'];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'isaacj122001@gmail.com';
        $mail->Password = 'sgoahzptxcrquxyl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom('isaacj122001@gmail.com', 'VentAlqui');
        $mail->addAddress('isaacj122001@gmail.com');

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = nl2br($mensaje . "<br><br>Atentamente:<br>" . $nombre . "<br>Correo: " . $correo);

        $mail->send();
        echo "<script>alert('Correo enviado exitosamente'); window.location='contact.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error al enviar el correo: {$mail->ErrorInfo}'); window.location='contact.php';</script>";
    }
}
?>
