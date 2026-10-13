<?php require RACINE_PATH . '/view/inc/authModal.php'; ?>

<footer class="site-footer">
    <div class="container footer-layout">
        <div class="footer-left">
            <nav class="footer-social" aria-label="Réseaux sociaux de Maison Rosalie">
                <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer"
                    aria-label="YouTube de Maison Rosalie (nouvel onglet)">
                    <img src="assets/img/social/youtube.svg" alt="YouTube">
                </a>
                <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer"
                    aria-label="Facebook de Maison Rosalie (nouvel onglet)">
                    <img src="assets/img/social/facebook.svg" alt="Facebook">
                </a>
                <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer"
                    aria-label="Instagram de Maison Rosalie (nouvel onglet)">
                    <img src="assets/img/social/instagram.svg" alt="Instagram">
                </a>
            </nav>

            <p class="footer-copyright">© 2026 Maison Rosalie – Tous droits réservés</p>
        </div>

        <img class="footer-emblem" src="assets/img/logo/picto.svg" alt="" aria-hidden="true">

        <div class="footer-actions">
            <nav class="footer-navigation" aria-label="Navigation du pied de page">
                <ul>
                    <li><a href="?pg=accueil">Accueil</a></li>
                    <li><a href="?pg=recettes">Recettes</a></li>
                    <li><a href="?pg=a-propos">À propos</a></li>
                    <li><a href="?pg=contact">Contact</a></li>
                    <li><a href="?pg=mentions-legales">Mentions légales</a></li>
                </ul>
            </nav>

            <a class="footer-back-to-top" href="#top" aria-label="Revenir en haut de la page">
                <svg viewBox="0 0 44 44" aria-hidden="true" focusable="false">
                    <circle cx="22" cy="22" r="22" />
                    <path d="M22 33V11M13 20L22 11L31 20" />
                </svg>
            </a>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/flash.js"></script>
<script src="js/ajax.js"></script>
<script src="js/scripts.js?v=<?= filemtime(RACINE_PATH . '/public/js/scripts.js') ?>"></script>
<script src="js/auth.js"></script>
<script src="js/recettes.js"></script>
</body>

</html>