<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="about-page">
    <header class="about-intro" data-about-reveal>
        <h1>Qui sommes nous</h1>
        <img src="assets/img/interface/title-spark.svg" alt="" aria-hidden="true">
    </header>

    <section class="about-team" aria-labelledby="aboutFounderTitle">
        <div class="about-team-gallery" data-about-reveal>
            <figure class="about-team-member">
                <img src="assets/img/about/team-lucas.webp" alt="Portrait de Lucas Delcourt">
                <figcaption>Lucas Delcourt</figcaption>
            </figure>

            <figure class="about-team-member">
                <img src="assets/img/about/team-camille.webp" alt="Portrait de Camille Moreau">
                <figcaption>Camille Moreau</figcaption>
            </figure>

            <figure class="about-team-member about-team-founder">
                <img src="assets/img/about/team-rosalie.webp" alt="Portrait de Rosalie Lambert">
                <figcaption>Rosalie Lambert</figcaption>
            </figure>
        </div>

        <div class="about-founder-copy" data-about-reveal>
            <h2 id="aboutFounderTitle">Rosalie Lambert</h2>
            <p class="about-founder-role">Fondatrice</p>
            <p>
                Rosalie imagine une maison où le chocolat belge se transmet avec élégance,
                précision et générosité. Entourée de son équipe, elle fait vivre un savoir-faire
                familial tourné vers la création et le partage.
            </p>
            <a class="about-round-link" href="#about-story" aria-label="Découvrir l’histoire de Maison Rosalie">
                <svg viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M10 24h27M28 14l10 10-10 10" />
                </svg>
            </a>
        </div>
    </section>

    <section id="about-story" class="about-story-sequence" aria-label="L’histoire de Maison Rosalie">
        <div class="about-story-gallery">
            <figure class="about-story-visual" data-about-reveal>
                <img src="assets/img/about/legacy-1928.webp"
                    alt="Une grand-mère partageant son savoir-faire en cuisine avec une enfant">
            </figure>

            <figure class="about-story-visual" data-about-reveal>
                <img src="assets/img/about/craft.webp" alt="Une pâtissière travaillant avec précision en cuisine">
            </figure>

            <figure class="about-story-visual" data-about-reveal>
                <img src="assets/img/about/family.webp" alt="Une grand-mère et un enfant préparant une recette ensemble">
            </figure>
        </div>

        <aside class="about-story-copy" data-about-reveal>
            <h2>Maison Rosalie</h2>
            <p>
                L’élégance du chocolat belge depuis 1928<br>
                Inspirée par l’art de vivre belge et les grandes maisons européennes, Rosalie
                a imaginé des chocolats conçus comme des bijoux gustatifs. Chaque création est
                une rencontre entre la noblesse des matières premières, la précision artisanale
                et une élégance intemporelle.<br>
                Plus qu’une chocolaterie, Maison Rosalie est une maison où chaque détail célèbre
                l’art du chocolat, la finesse, la délicatesse et le raffinement.
            </p>
        </aside>
    </section>

    <section class="about-recipes" data-about-reveal>
        <img src="assets/img/about/recipes-banner.webp"
            alt="Une enfant et une adulte préparant une recette au chocolat">
        <div class="about-recipes-overlay">
            <p>
                Rosalie a rencontré un tel succès qu’elle a voulu se lancer dans la création de recettes
                à base de son chocolat, celui qu’elle utilise pour la création de ses délicieuses pralines.<br>
                Vous découvrirez ici quelques merveilleuses recettes facilement réalisables.
            </p>
        </div>
        <p class="about-tasting">Bonne dégustation.</p>
    </section>
</main>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>
