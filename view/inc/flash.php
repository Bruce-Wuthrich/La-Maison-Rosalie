<?php
// path: view/inc/flash.php
//message temp apres refirection sur tt pages
// page contact à son emplacement reservé au dess de form

if (is_array($flashMessage) && isset($flashMessage['message']) && $page !== 'contact'):
    $flashIsSuccess = ($flashIsSuccess['type'] ?? '') === 'sucess';
    ?>

        <div class="container">
            <p class="flash-message flash-message-<?= $flashIsSuccess ? 'success' : 'error' ?>"
                role="<?= $flashIsSuccess ? 'status' : 'alert' ?>">
                <?= htmlspecialchars((string) $flashMessage['message'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
<?php endif; ?>