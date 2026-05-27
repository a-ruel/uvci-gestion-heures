<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../vendor/autoload.php';

function envoyerEmail($destinataire, $sujet, $messageHtml) {
    $mail = new PHPMailer(true);
    try {
        // Configuration SMTP (à adapter selon votre serveur)
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com'; // Remplacez par votre SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'votre@email.com';
        $mail->Password   = 'votre-mot-de-passe';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        // Expéditeur
        $mail->setFrom('no-reply@uvci.ci', 'UVCI Gestion');
        $mail->addAddress($destinataire);
        $mail->isHTML(true);
        $mail->Subject = $sujet;
        $mail->Body    = $messageHtml;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur email : " . $mail->ErrorInfo);
        return false;
    }
}
?>
