<?php
// path: model/Mailer.php
// Envoi des e-mails du site par SMTP (bonus) : confirmation d'inscription et accusé de réception du contact.
// Réglages dans config-dev.php (MAIL_*). Sans réglages, rien n'est envoyé et le site fonctionne normalement.

declare(strict_types=1);

namespace model;

use model\mapping\ContactMessageMapping;
use model\mapping\UserMapping;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

require_once RACINE_PATH . '/lib/PHPMailer/Exception.php';
require_once RACINE_PATH . '/lib/PHPMailer/PHPMailer.php';
require_once RACINE_PATH . '/lib/PHPMailer/SMTP.php';

class Mailer{

    // Un envoi n'est tenté que si l'hôte SMTP et l'expéditeur sont renseignés.
    public static function isConfigured(): bool{
        return defined('MAIL_HOST') && MAIL_HOST !== ''
            && defined('MAIL_FROM') && MAIL_FROM !== '';
    }

    // Confirmation envoyée après la création d'un compte.
    public static function sendRegistrationConfirmation(UserMapping $user): bool{
        $body = 'Bonjour ' . $user->getUsername() . ",\n\n"
            . "Votre compte sur le site de La Maison Rosalie a bien été créé avec cette adresse e-mail.\n"
            . "Vous pouvez maintenant vous connecter pour noter les recettes, laisser vos avis et enregistrer vos favorites.\n\n"
            . "Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer ce message.\n\n"
            . "À très bientôt,\nLa Maison Rosalie";

        return self::send((string) $user->getEmail(), (string) $user->getUsername(), 'Bienvenue à La Maison Rosalie', $body);
    }

    // Accusé de réception envoyé à la personne qui a écrit via le formulaire de contact.
    public static function sendContactAcknowledgement(ContactMessageMapping $message): bool{
        $body = 'Bonjour ' . $message->getName() . ",\n\n"
            . "Nous avons bien reçu votre message et nous vous répondrons dès que possible.\n\n"
            . 'Sujet : ' . $message->getSubject() . "\n\n"
            . "Votre message :\n" . $message->getMessage() . "\n\n"
            . "Merci de votre intérêt,\nLa Maison Rosalie";

        return self::send((string) $message->getEmail(), (string) $message->getName(), 'Nous avons bien reçu votre message', $body);
    }

    // Envoi en texte brut (aucun HTML venant d'un visiteur). Ne lève jamais d'exception :
    // un e-mail qui ne part pas ne doit pas faire échouer l'inscription ou le contact déjà enregistrés.
    private static function send(string $toEmail, string $toName, string $subject, string $body): bool{
        if (!self::isConfigured()) {
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->Port = defined('MAIL_PORT') ? (int) MAIL_PORT : 587;
            $mail->Timeout = 10; // la page n'attend jamais plus de 10 secondes
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $encryption = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : 'tls';
            $mail->SMTPSecure = match ($encryption) {
                'ssl' => PHPMailer::ENCRYPTION_SMTPS,
                'tls' => PHPMailer::ENCRYPTION_STARTTLS,
                default => '',
            };
            $mail->SMTPAutoTLS = $encryption !== '';

            $username = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
            if ($username !== '') {
                $mail->SMTPAuth = true;
                $mail->Username = $username;
                $mail->Password = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
            }

            $mail->setFrom(MAIL_FROM, defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'La Maison Rosalie');
            // les noms viennent des formulaires : sauts de ligne retirés par sécurité
            $mail->addAddress($toEmail, str_replace(["\r", "\n"], ' ', $toName));
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $body;

            return $mail->send();
        } catch (Throwable $exception) {
            error_log('Envoi d\'e-mail impossible : ' . $exception->getMessage());
            return false;
        }
    }
}