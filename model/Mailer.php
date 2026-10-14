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

    // Après une inscription : lien cliquable pour confirmer l'adresse (valable 24 heures).
    public static function sendRegistrationConfirmation(UserMapping $user, string $verificationUrl): bool{
        $name = (string) $user->getUsername();
        $text = 'Bonjour ' . $name . ",\n\n"
            . "Merci pour votre inscription sur le site de La Maison Rosalie.\n"
            . "Pour activer votre compte, confirmez votre adresse e-mail en ouvrant ce lien (valable 24 heures) :\n\n"
            . $verificationUrl . "\n\n"
            . "Si vous n'êtes pas à l'origine de cette inscription, ignorez simplement ce message.\n\n"
            . "À très bientôt,\nLa Maison Rosalie";

        $html = '<p>Bonjour ' . self::escape($name) . ',</p>'
            . '<p>Merci pour votre inscription sur le site de La Maison Rosalie.<br>'
            . 'Pour activer votre compte, confirmez votre adresse e-mail (lien valable 24 heures) :</p>'
            . '<p><a href="' . self::escape($verificationUrl) . '" style="display:inline-block;padding:10px 20px;'
            . 'background:#976700;color:#ffffff;text-decoration:none;border-radius:20px;">Confirmer mon adresse e-mail</a></p>'
            . '<p style="font-size:13px;color:#5a392b;">Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>'
            . self::escape($verificationUrl) . '</p>'
            . '<p>Si vous n\'êtes pas à l\'origine de cette inscription, ignorez simplement ce message.</p>'
            . '<p>À très bientôt,<br>La Maison Rosalie</p>';

        return self::send((string) $user->getEmail(), $name, 'Confirmez votre adresse e-mail - La Maison Rosalie', $text, $html);
    }

    // Accusé de réception envoyé à la personne qui a écrit via le formulaire de contact.
    public static function sendContactAcknowledgement(ContactMessageMapping $message): bool{
        $body = 'Bonjour ' . $message->getName() . ",\n\n"
            . "Nous avons bien reçu votre message et nous vous répondrons dès que possible.\n\n"
            . 'Sujet : ' . $message->getSubject() . "\n\n"
            . "Votre message :\n" . $message->getMessage() . "\n\n"
            . "Merci de votre intérêt,\nLa Maison Rosalie";

        $html = '<p>' . nl2br(self::escape($body)) . '</p>';

        return self::send((string) $message->getEmail(), (string) $message->getName(), 'Nous avons bien reçu votre message', $body, $html);
    }

    // Texte saisi par un visiteur, inoffensif dans la version HTML de l'e-mail
    private static function escape(string $value): string{
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    // Envoi en HTML + version texte (meilleure délivrabilité). Ne lève jamais d'exception :
    // un e-mail qui ne part pas ne doit pas faire échouer l'inscription ou le contact déjà enregistrés.
    private static function send(string $toEmail, string $toName, string $subject, string $text, string $html): bool{
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
            // Anti-spam : identifiants du message au nom du domaine d'envoi (pas « localhost »),
            // adresse de retour cohérente et pas d'en-tête « X-Mailer: PHPMailer »
            $mail->Hostname = substr((string) strrchr(MAIL_FROM, '@'), 1);
            $mail->Sender = MAIL_FROM;
            $mail->addReplyTo(MAIL_FROM, defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'La Maison Rosalie');
            $mail->XMailer = ' ';
            // les noms viennent des formulaires : sauts de ligne retirés par sécurité
            $mail->addAddress($toEmail, str_replace(["\r", "\n"], ' ', $toName));
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = '<!doctype html><html lang="fr"><body style="font-family:Arial,sans-serif;color:#5a392b;">'
                . $html . '</body></html>';
            $mail->AltBody = $text;

            return $mail->send();
        } catch (Throwable $exception) {
            error_log('Envoi d\'e-mail impossible : ' . $exception->getMessage());
            return false;
        }
    }
}