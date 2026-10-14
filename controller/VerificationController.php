<?php
// path: controller/VerificationController.php
// GET ?pg=verification&token=... : lien reçu par e-mail après l'inscription.

declare(strict_types=1);

use model\manager\EmailVerificationManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

$token = stringInput($_GET['token'] ?? null);

try {
    if ((new EmailVerificationManager($db))->verify($token)) {
        setFlashMessage('success', 'Votre adresse e-mail est confirmée. Vous pouvez maintenant vous connecter.');
        redirectTo('?pg=accueil#authModal');
    }
    setFlashMessage('error', 'Ce lien de confirmation est invalide ou a expiré. Connectez-vous pour en recevoir un nouveau.');
} catch (Throwable $exception) {
    error_log('Erreur dans VerificationController : ' . $exception->getMessage());
    setFlashMessage('error', 'La confirmation est momentanément indisponible. Merci de réessayer plus tard.');
}

redirectTo('?pg=accueil');