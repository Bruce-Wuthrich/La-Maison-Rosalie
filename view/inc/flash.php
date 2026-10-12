<?php
// path: view/inc/flash.php
//message temp apres refirection sur tt pages
// page contact à son emplacement reservé au dess de form

if (is_array($flashMessage) && isset($flashMessage['message']) && !in_array($page, ['contact', 'recette'], true)):
    $flashIsSuccess = ($flashMessage['type'] ?? '') === 'success';
?>
    <div class="flash-message flash-toast flash-message-<?= $flashIsSuccess ? 'success' : 'error' ?>"
        role="<?= $flashIsSuccess ? 'status' : 'alert' ?>"
        data-flash<?= $flashIsSuccess ? ' data-flash-autohide' : '' ?>>
        <p class="m-0"><?= htmlspecialchars((string) $flashMessage['message'], ENT_QUOTES, 'UTF-8') ?></p>
        <button type="button" class="flash-toast-close" data-flash-close aria-label="Fermer le message">×</button>
    </div>
    <script src="js/flash.js" defer></script>
<?php endif; ?>