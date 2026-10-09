<?php
$escapeAuth = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$loginFormData = pullFormData('login');
$registerFormData = pullFormData('register');
$initialAuthPanel = ($registerFormData['form_name'] ?? '') === 'register' ? 'register' : 'login';
?>

<div
    class="auth-modal"
    id="authModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="authModalTitle"
    data-initial-panel="<?= $escapeAuth($initialAuthPanel) ?>"
    hidden>
    <div class="auth-modal-backdrop" data-auth-close></div>

    <div class="auth-modal-content">
        <button class="auth-modal-close" type="button" data-auth-close aria-label="Fermer la fenêtre de connexion">
            <span aria-hidden="true"></span>
        </button>

        <?php if (is_array($flashMessage) && isset($flashMessage['message'])): ?>
            <p class="auth-message auth-message-<?= ($flashMessage['type'] ?? '') === 'success' ? 'success' : 'error' ?>" role="status">
                <?= $escapeAuth($flashMessage['message']) ?>
            </p>
        <?php endif; ?>

        <div class="auth-panels">
        <section class="auth-panel auth-login-panel" data-auth-panel="login">
            <header class="auth-modal-header">
                <h2 id="authModalTitle">Se connecter</h2>

                <div class="auth-role-tabs" role="tablist" aria-label="Type de connexion">
                    <button class="is-active" type="button" role="tab" aria-selected="true" data-auth-role="user">Utilisateur</button>
                    <button type="button" role="tab" aria-selected="false" data-auth-role="admin">Admin</button>
                </div>
            </header>

            <div class="auth-modal-grid">
                <div class="auth-side-panel">
                    <h3>Mon espace personnel</h3>
                    <button class="auth-text-link" type="button" data-auth-show="register">Créer un compte</button>
                </div>

                <div class="auth-form-panel">
                    <h3>Information</h3>

                    <form class="auth-form" action="?pg=compte&amp;action=login" method="post">
                        <input type="hidden" name="csrf_token" value="<?= $escapeAuth($csrfToken) ?>">
                        <input type="hidden" name="form_name" value="login">
                        <input type="hidden" name="login_mode" value="user" data-auth-login-mode>

                        <div class="auth-fields auth-fields-login">
                            <label>
                                <span class="visually-hidden">Votre e-mail</span>
                                <input
                                    type="email"
                                    name="email"
                                    maxlength="254"
                                    autocomplete="email"
                                    placeholder="Votre e-mail"
                                    value="<?= $escapeAuth($loginFormData['email'] ?? '') ?>"
                                    required>
                            </label>

                            <label>
                                <span class="visually-hidden">Votre mot de passe</span>
                                <input
                                    type="password"
                                    name="password"
                                    autocomplete="current-password"
                                    placeholder="Votre mot de passe"
                                    required>
                            </label>
                        </div>

                        <label class="auth-checkbox">
                            <input type="checkbox" name="remember_email" value="1" data-auth-remember-email>
                            <span aria-hidden="true"></span>
                            Se souvenir de mon e-mail
                        </label>

                        <button class="auth-submit" type="submit">
                            <span>Se connecter</span>
                            <span aria-hidden="true">⟶</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="auth-panel auth-register-panel" data-auth-panel="register" hidden>
            <header class="auth-modal-header">
                <h2>Créer un compte</h2>
            </header>

            <div class="auth-modal-grid">
                <div class="auth-side-panel">
                    <h3>Créer mon compte</h3>
                    <button class="auth-text-link" type="button" data-auth-show="login">J’ai déjà un compte</button>
                </div>

                <div class="auth-form-panel">
                    <h3>Information</h3>

                    <form class="auth-form" action="?pg=compte&amp;action=register" method="post">
                        <input type="hidden" name="csrf_token" value="<?= $escapeAuth($csrfToken) ?>">
                        <input type="hidden" name="form_name" value="register">

                        <div class="auth-fields auth-fields-register">
                            <label>
                                <span class="visually-hidden">Votre nom d’utilisateur</span>
                                <input
                                    type="text"
                                    name="username"
                                    minlength="3"
                                    maxlength="50"
                                    pattern="[A-Za-zÀ-ÿ0-9_-]{3,50}"
                                    autocomplete="username"
                                    placeholder="Votre nom d’utilisateur"
                                    value="<?= $escapeAuth($registerFormData['username'] ?? '') ?>"
                                    required>
                            </label>

                            <label>
                                <span class="visually-hidden">Votre e-mail</span>
                                <input
                                    type="email"
                                    name="email"
                                    maxlength="254"
                                    autocomplete="email"
                                    placeholder="Votre e-mail"
                                    value="<?= $escapeAuth($registerFormData['email'] ?? '') ?>"
                                    required>
                            </label>

                            <label>
                                <span class="visually-hidden">Votre mot de passe</span>
                                <input
                                    type="password"
                                    name="password"
                                    minlength="10"
                                    autocomplete="new-password"
                                    placeholder="Votre mot de passe"
                                    aria-describedby="authPasswordHelp"
                                    required>
                            </label>

                            <label>
                                <span class="visually-hidden">Confirmez votre mot de passe</span>
                                <input
                                    type="password"
                                    name="password_confirm"
                                    minlength="10"
                                    autocomplete="new-password"
                                    placeholder="Confirmez le mot de passe"
                                    required>
                            </label>
                        </div>

                        <p class="auth-password-help" id="authPasswordHelp">
                            10 caractères minimum, avec une majuscule, une minuscule et un chiffre.
                        </p>

                        <button class="auth-submit" type="submit">
                            <span>Créer le compte</span>
                            <span aria-hidden="true">⟶</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
        </div>
    </div>
</div>
