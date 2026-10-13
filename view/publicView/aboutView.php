<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="about-page">
    <header class="about-intro" data-about-reveal>
        <h1>Qui sommes nous</h1>
        <img src="assets/img/interface/title-spark.svg" alt="" aria-hidden="true">
    </header>

    <section class="about-team" aria-labelledby="aboutFounderTitle">
        <div class="about-team-gallery" data-about-reveal>
            <figure class="about-team-member"
                data-name="Lucas Delcourt"
                data-role="Chocolatier créateur"
                data-biography="Né à Namur dans une famille de boulangers, Lucas a grandi au milieu des parfums de cacao et de pâte chaude. Après une formation de chocolatier, il a parcouru plusieurs maisons belges avant de rejoindre Rosalie. Il imagine aujourd’hui les nouvelles recettes et veille à préserver l’équilibre entre tradition, précision et gourmandise.">
                <button class="about-team-select" type="button" aria-label="Découvrir la vie de Lucas Delcourt">
                    <img src="assets/img/about/team-lucas.webp" alt="Portrait de Lucas Delcourt">
                </button>
                <figcaption>Lucas Delcourt</figcaption>
            </figure>

            <figure class="about-team-member"
                data-name="Camille Moreau"
                data-role="Responsable de l’atelier"
                data-biography="Camille est née à Bruxelles et s’est très tôt passionnée pour les gestes minutieux de la pâtisserie. Organisée et attentive, elle accompagne chaque étape de fabrication, de la sélection des ingrédients aux dernières finitions. Elle transmet aussi aux nouveaux artisans les méthodes et les valeurs qui font l’identité de Maison Rosalie.">
                <button class="about-team-select" type="button" aria-label="Découvrir la vie de Camille Moreau">
                    <img src="assets/img/about/team-camille.webp" alt="Portrait de Camille Moreau">
                </button>
                <figcaption>Camille Moreau</figcaption>
            </figure>

            <figure class="about-team-member is-active"
                data-name="Rosalie Moreau"
                data-role="Fondatrice"
                data-biography="Rosalie a grandi dans une maison où les recettes se transmettaient de génération en génération. Animée par le désir de faire vivre cet héritage, elle a créé Maison Rosalie comme un lieu de partage, d’élégance et de création. Entourée de son équipe, elle continue d’imaginer une chocolaterie profondément belge, généreuse et tournée vers l’avenir.">
                <button class="about-team-select" type="button" aria-label="Découvrir la vie de Rosalie Moreau" aria-current="true">
                    <img src="assets/img/about/team-rosalie.webp" alt="Portrait de Rosalie Moreau">
                </button>
                <figcaption>Rosalie Moreau</figcaption>
            </figure>
        </div>

        <div class="about-founder-copy" data-about-reveal aria-live="polite">
            <h2 id="aboutFounderTitle">Rosalie Moreau</h2>
            <p class="about-founder-role">Fondatrice</p>
            <p class="about-founder-biography">
                Rosalie a grandi dans une maison où les recettes se transmettaient de génération en génération.
                Animée par le désir de faire vivre cet héritage, elle a fondé Maison Rosalie à Liège en 1987, comme un lieu de partage,
                d’élégance et de création. Entourée de son équipe, elle continue d’imaginer une chocolaterie
                profondément belge, généreuse et tournée vers l’avenir.
            </p>
            <button class="about-round-link" type="button" aria-label="Afficher le personnage suivant">
                <svg viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M10 24h27M28 14l10 10-10 10" />
                </svg>
            </button>
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
                L’élégance du chocolat belge depuis 1987<br>
                Inspirée par l’art de vivre belge et les grandes maisons européennes, Rosalie
                a imaginé des chocolats conçus comme des bijoux gustatifs. Chaque création est
                une rencontre entre la noblesse des matières premières, la précision artisanale
                et une élégance intemporelle.<br>
                Plus qu’une chocolaterie, Maison Rosalie est une maison où chaque détail célèbre
                l’art du chocolat, la finesse, la délicatesse et le raffinement.
            </p>
        </aside>
    </section>

    <section class="about-figures" aria-labelledby="aboutFiguresTitle" data-about-reveal>
        <h2 id="aboutFiguresTitle">Nos valeurs en chiffres</h2>
        <ul class="about-figures-list">
            <li><strong>1987</strong> Création de la maison à Liège</li>
            <li><strong>3</strong> Générations de chocolatiers</li>
            <li><strong>100 %</strong> Chocolat belge</li>
            <li><strong>5</strong> Recettes du livre familial</li>
        </ul>
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
