<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="contact-page">
    <section class="contact-section">
        <div class="container">

            <div class="row align-items-center g-5">

                <div class="col-12 col-lg-6">
                    <section class="contact-presentation">
                        <p class="contact-eyebrow">Maison Rosalie</p>
                        <h1>Contactez-nous</h1>

                        <div class="contact-eye" aria-hidden="true">
                            <img class="contact-eye-base" src="assets/img/contact/eye.svg" alt="">

                            <img class="contact-eye-pupil" src="assets/img/contact/pupil-center.svg" alt="">
                        </div>

                        <address class="contact-information">
                            <a href="tel:+32476000000">
                                <img src="assets/img/contact/phone.svg" alt="" aria-hidden="true">

                                <span>0476 000 00 00</span>
                            </a>

                            <a href="mailto:contact@example.com">
                                <img src="assets/img/contact/email.svg" alt="" aria-hidden="true">

                                <span>contact@example.com</span>
                            </a>
                        </address>
                    </section>
                </div>

                <div class="col-12 col-lg-6">
                    <section class="contact-form-panel">
                        <h2>Envoyez-nous un message.</h2>

                        <form class="contact-form" action="?pg=contact" method="post">

                            <div class="contact-field">
                                <label for="contactName">Nom et prénom</label>
                                <input id="contactName" name="name" type="text" maxlength="100" autocomplete="name"
                                    placeholder="Votre nom" required>
                            </div>

                            <div class="contact-field">
                                <label for="contactEmail">E-mail</label>
                                <input id="contactEmail" name="email" type="email" maxlength="254" autocomplete="email"
                                    placeholder="Votre e-mail" required>
                            </div>

                            <div class="contact-field">
                                <label for="contactSubject">Sujet</label>
                                <input id="contactSubject" name="subject" type="text" maxlength="120"
                                    placeholder="Comment pouvons-nous vous aider ?" required>
                            </div>

                            <div class="contact-field">
                                <label for="contactMessage">Message</label>
                                <textarea id="contactMessage" name="message" rows="7" maxlength="2000"
                                    placeholder="Écrivez votre message" required></textarea>

                                <small class="contact-counter">0/2000 caractères</small>
                            </div>

                            <button class="contact-submit" type="submit">
                                Envoyer un message
                            </button>
                        </form>
                    </section>
                </div>

            </div>
            <div class="contact-social">
                <p>Nous contacter</p>

                <div class="contact-social-icons">
                    <img src="assets/img/social/youtube.svg" alt="YouTube">
                    <img src="assets/img/social/facebook.svg" alt="Facebook">
                    <img src="assets/img/social/instagram.svg" alt="Instagram">
                </div>
            </div>
        </div>
    </section>
</main>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>