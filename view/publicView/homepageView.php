<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>
<main>
    <section class="hero-home">
        <h1 class="visually-hidden">Maison Rosalie</h1>
        <div class="hero-emblem">
            <button class="hero-wheel" type="button" aria-label="Faire tourner les catégories">
                <img class="hero-sunburst" src="assets/img/logo/sunburst-home.png" alt="" aria-hidden="true">
            </button>

            <img class="hero-logo" src="assets/img/logo/logo.svg" alt="Maison Rosalie">

            <a class="hero-category-current is-active" href="?pg=recettes&categorie=tartes" aria-live="polite">
                Tartes
            </a>
        </div>
        <p class="hero-tagline">Des créations d’exception <br>
            pour des moments précieux</p>
    </section>
</main>
<?php require RACINE_PATH . '/view/inc/footer.php'; ?>