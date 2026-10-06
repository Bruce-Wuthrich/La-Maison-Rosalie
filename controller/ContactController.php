<?php
// path: controller/ContactController.php

declare(strict_types=1);

use model\manager\ContactMessageManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

$redirect = '?pg=contact';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $csrfToken = csrfToken();
    $contactFormData = pullFormData('contact');
    require RACINE_PATH . '/view/publicView/contactView.php';
    return;
}

// Protège le formulaire et garde les données non sensibles en cas d'erreur.
requirePostMethod($redirect);
validateCsrfToken($_POST, $redirect);
storeFormData('contact', $_POST);
$ipAddress = clientIpAddress();
$contactMessageManager = new ContactMessageManager($db);

try {
    if ($contactMessageManager->countRecentByIp($ipAddress, 60) >= 3) {
        throw new DomainException('Trop de messages ont été envoyés. Merci de réessayer plus tard.');
    }

    $contactMessageManager->create($_POST, $ipAddress);
    unset($_SESSION['form_data']['contact']);
    setFlashMessage('success', 'Votre message a bien été envoyé.');
} catch (InvalidArgumentException|DomainException $exception) {
    setFlashMessage('error', $exception->getMessage());
} catch (Throwable $exception) {
    error_log('Erreur dans ContactController : ' . $exception->getMessage());
    setFlashMessage('error', 'Le formulaire de contact est momentanément indisponible.');
}

redirectTo($redirect);
