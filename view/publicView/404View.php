<?php
$navbarHome = true;
$stylePage = 'error';
?>
<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="home-main">
    <section class="hero-home error-hero" aria-labelledby="errorTitle">
        <div class="hero-emblem">
            <img class="hero-sunburst" src="assets/img/logo/sunburst-home.png" alt="" aria-hidden="true">
            <img class="hero-logo" src="assets/img/logo/logo.svg?v=<?= (int) $iconVersion ?>" alt="Maison Rosalie">
        </div>

        <div class="error-message">
            <p class="error-code" aria-hidden="true">404</p>
            <h1 id="errorTitle">Oups, cette page s'est envolée</h1>
            <p>
                La page que vous cherchez n'existe pas ou a été déplacée.
                Nos recettes, elles, vous attendent toujours.
            </p>

            <div class="error-actions">
                <a class="error-button" href="?pg=recettes">Voir nos recettes</a>
                <a class="error-button error-button-light" href="?pg=accueil">Retour à l'accueil</a>
            </div>
        </div>
    </section>
</main>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>
