<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="contact-page">
    <div class="contact-section">
        <div class="container">

            <div class="row align-items-center g-4">

                <div class="col-12 col-lg-6">
                    <div class="contact-presentation">
                        <p class="contact-eyebrow">Maison Rosalie</p>
                        <h1>Contactez-nous</h1>

                        <div class="contact-eye" aria-hidden="true">
                            <img class="contact-eye-base" src="assets/img/contact/eye.svg" alt="">

                            <img class="contact-eye-pupil" src="assets/img/contact/pupil-center.svg" alt="">
                        </div>

                        <address class="contact-information">
                            <p>
                                <img src="assets/img/contact/email.svg" alt="" aria-hidden="true">
                                <span>Rue Léopold 12, 4000 Liège</span>
                            </p>

                            <a href="tel:+3242000000">
                                <img src="assets/img/contact/phone.svg" alt="" aria-hidden="true">
                                <span>04 200 00 00</span>
                            </a>

                            <a href="mailto:contact@maisonrosalie.be">
                                <img src="assets/img/contact/email.svg" alt="" aria-hidden="true">
                                <span>contact@maisonrosalie.be</span>
                            </a>
                        </address>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <section class="contact-form-panel">
                        <h2>Envoyez-nous un message.</h2>

                        <?php if (is_array($flashMessage) && isset($flashMessage['message'])) { ?>
                            <p class="flash-message flash-message-<?php echo ($flashMessage['type'] ?? '') === 'success' ? 'success' : 'error'; ?>"
                                role="<?php echo ($flashMessage['type'] ?? '') === 'success' ? 'status' : 'alert'; ?>">
                                <?php echo htmlspecialchars((string) $flashMessage['message'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        <?php } ?>

                        <form class="contact-form" action="?pg=contact" method="post">
                            <input type="hidden" name="csrf_token"
                                value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="contact-field">
                                <label for="contactName">Nom et prénom</label>
                                <input id="contactName" name="name" type="text" maxlength="100" autocomplete="name"
                                    placeholder="Votre nom" required
                                    value="<?php echo htmlspecialchars($contactFormData['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="contact-field">
                                <label for="contactEmail">E-mail</label>
                                <input id="contactEmail" name="email" type="email" maxlength="254" autocomplete="email"
                                    placeholder="Votre e-mail" required
                                    value="<?php echo htmlspecialchars($contactFormData['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="contact-field">
                                <label for="contactSubject">Sujet</label>
                                <input id="contactSubject" name="subject" type="text" maxlength="120"
                                    placeholder="Comment pouvons-nous vous aider ?" required
                                    value="<?php echo htmlspecialchars($contactFormData['subject'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="contact-field">
                                <label for="contactMessage">Message</label>
                                <textarea id="contactMessage" name="message" rows="7" maxlength="2000"
                                    placeholder="Écrivez votre message" required><?php echo htmlspecialchars($contactFormData['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                                <small class="contact-counter">0/2000 caractères</small>
                            </div>

                            <button class="contact-submit" type="submit">
                                Envoyer un message
                            </button>
                        </form>
                    </section>
                </div>

            </div>

            <section class="contact-map-section" aria-labelledby="contactMapTitle">
                <h2 id="contactMapTitle" class="contact-map-title">Nous trouver</h2>

                <div class="contact-map-wrapper">
                    <iframe class="contact-map"
                        src="https://www.google.com/maps?q=Rue+L%C3%A9opold+12,+4000+Li%C3%A8ge&amp;z=16&amp;output=embed"
                        title="Carte : Maison Rosalie, Rue Léopold 12 à Liège"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </section>

            <div class="contact-social">
                <p>Nous contacter</p>

                <div class="contact-social-icons">
                    <img src="assets/img/social/youtube.svg" alt="YouTube">
                    <img src="assets/img/social/facebook.svg" alt="Facebook">
                    <img src="assets/img/social/instagram.svg" alt="Instagram">
                </div>
            </div>
        </div>
    </div>
</main>

<script src="js/contact.js" defer></script>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>