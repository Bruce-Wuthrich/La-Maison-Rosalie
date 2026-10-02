<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="recipe-page">
    <section class="recipe-summary">
        <div class="container">
            <nav class="recipe-breadcrumb" aria-label="Fil d’Ariane">
                <a href="?pg=accueil">Accueil</a>
                <span aria-hidden="true">›</span>

                <a href="?pg=recettes">Recettes</a>
                <span aria-hidden="true">›</span>

                <a href="?pg=recettes&categorie=patisseries">Pâtisseries</a>
                <span aria-hidden="true">›</span>

                <span aria-current="page">Brownies</span>
            </nav>
            <div class="row align-items-center g-5">

                <div class="col-12 col-lg-5">
                    <figure class="recipe-figure">
                        <button class="recipe-favorite" type="button" aria-label="Ajouter aux favoris">
                            <img src="assets/img/recettes/icons/favorite-outline.svg" alt="" aria-hidden="true">
                        </button>
                        <img class="recipe-image" src="assets/img/recettes/brownie.png" alt="Brownies au chocolat noir">
                    </figure>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="recipe-introduction">

                        <h1>Brownies au chocolat noir</h1>

                        <div class="recipe-rating" aria-label="Note : 4 étoiles sur 5">
                            <div class="recipe-rating-stars" aria-hidden="true">
                                <img src="assets/img/recettes/icons/star-filled.svg" alt="">
                                <img src="assets/img/recettes/icons/star-filled.svg" alt="">
                                <img src="assets/img/recettes/icons/star-filled.svg" alt="">
                                <img src="assets/img/recettes/icons/star-filled.svg" alt="">
                                <img src="assets/img/recettes/icons/star-outline.svg" alt="">
                            </div>

                            <a href="#comments">(15 commentaires)</a>
                        </div>
                        <ul class="recipe-meta">
                            <li>
                                <img src="assets/img/recettes/icons/servings.svg" alt="" aria-hidden="true">
                                <span>4 pers</span>
                            </li>

                            <li>
                                <img src="assets/img/recettes/icons/difficulty.svg" alt="" aria-hidden="true">
                                <span>Moyen</span>
                            </li>

                            <li>
                                <img src="assets/img/recettes/icons/time.svg" alt="" aria-hidden="true">
                                <span>25 min</span>
                            </li>
                        </ul>
                        <div class="recipe-actions">
                            <button class="recipe-action" type="button" id="printRecipe">
                                <img src="assets/img/recettes/icons/print.svg" alt="" aria-hidden="true">
                                <span>Imprimer</span>
                            </button>

                            <button class="recipe-action" type="button" id="shareRecipe">
                                <img src="assets/img/recettes/icons/share.svg" alt="" aria-hidden="true">
                                <span>Partager</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <section class="recipe-content">
        <div class="container">
            <div class="row g-5">

                <div class="col-12 col-lg-5">
                    <h2>Ingrédients</h2>

                    <ul class="ingredient-list">
                        <li>250 g de chocolat pâtissier</li>
                        <li>150 g de beurre</li>
                        <li>150 g de sucre</li>
                        <li>60 g de farine</li>
                        <li>1 sachet de sucre vanillé</li>
                        <li>3 œufs</li>
                    </ul>
                </div>

                <div class="col-12 col-lg-7">
                    <h2>Préparation</h2>

                    <ol class="recipe-steps">
                        <li>
                            <h3>Étape 1</h3>
                            <p>
                                Faites fondre le chocolat cassé en morceaux avec le beurre.
                            </p>
                        </li>

                        <li>
                            <h3>Étape 2</h3>
                            <p>
                                Battez les œufs avec le sucre jusqu’à ce que le mélange blanchisse.
                            </p>
                        </li>

                        <li>
                            <h3>Étape 3</h3>
                            <p>
                                Ajoutez la farine, le sucre vanillé et le mélange au chocolat.
                            </p>
                        </li>

                        <li>
                            <h3>Étape 4</h3>
                            <p>
                                Versez la préparation dans un moule et enfournez à 180 °C.
                            </p>
                        </li>

                        <li>
                            <h3>Étape 5</h3>
                            <p>Laissez refroidir avant de servir.</p>
                        </li>
                    </ol>
                </div>

            </div>
        </div>
    </section>
    <section class="recipe-comments" id="comments">
        <div class="container">

            <div class="comments-heading">
                <img src="assets/img/recettes/icons/comment.svg" alt="" aria-hidden="true">

                <h2>Commentaires</h2>
            </div>

            <div class="row g-5">
                <div class="col-12 col-lg-5">
                    <div class="comment-form-panel">
                        <h3>Partagez votre expérience</h3>

                        <!-- Le formulaire sera ajouté ici -->
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="comments-list-panel">
                        <!-- Les commentaires seront ajoutés ici -->
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>