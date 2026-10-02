<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="recipes-page">

    <section class="recipes-header">
        <div class="container">
            <p class="recipes-eyebrow">Maison Rosalie</p>

            <h1>Nos recettes</h1>

            <p class="recipes-introduction">
                Découvrez nos créations chocolatées et trouvez la recette qui vous fera plaisir.
            </p>
        </div>
    </section>

    <section class="recipes-catalog">
        <div class="container">

            <nav class="recipe-filters" aria-label="Filtrer les recettes">
                <a class="recipe-filter is-active" href="?pg=recettes" aria-current="page">
                    Toutes
                </a>

                <a class="recipe-filter" href="?pg=recettes&categorie=tartes">
                    Tartes
                </a>

                <a class="recipe-filter" href="?pg=recettes&categorie=gateaux">
                    Gâteaux
                </a>

                <a class="recipe-filter" href="?pg=recettes&categorie=glaces">
                    Glaces
                </a>

                <a class="recipe-filter" href="?pg=recettes&categorie=mousses">
                    Mousses
                </a>

                <a class="recipe-filter" href="?pg=recettes&categorie=patisseries">
                    Pâtisseries
                </a>
            </nav>

            <div class="recipes-grid">

                <article class="recipe-card">
                    <a class="recipe-card-image-link" href="?pg=recette&slug=brownies-au-chocolat-noir">

                        <img class="recipe-card-image" src="assets/img/recettes/brownie.png"
                            alt="Brownies au chocolat noir">
                    </a>

                    <div class="recipe-card-content">
                        <p class="recipe-card-category">Gâteaux</p>

                        <h2>
                            <a href="?pg=recette&slug=brownies-au-chocolat-noir">
                                Brownies au chocolat noir
                            </a>
                        </h2>

                        <p class="recipe-card-description">
                            Un brownie gourmand, fondant et riche en chocolat.
                        </p>

                        <div class="recipe-card-footer">
                            <span>★★★★☆</span>
                            <span>25 min</span>
                        </div>
                    </div>
                </article>

            </div>
        </div>
    </section>

</main>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>