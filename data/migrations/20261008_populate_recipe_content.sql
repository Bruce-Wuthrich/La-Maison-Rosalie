-- Migration non destructive : ajoute ou corrige les ingrédients et les étapes
-- des cinq recettes de démonstration sans recréer la base.
-- À importer dans phpMyAdmin avec un compte administrateur MariaDB.

USE maison_rosalie;

START TRANSACTION;

DROP TEMPORARY TABLE IF EXISTS migration_recipe_ingredients;
CREATE TEMPORARY TABLE migration_recipe_ingredients (
    recipe_slug    VARCHAR(170) NOT NULL,
    ingredient_name VARCHAR(120) NOT NULL,
    quantity       DECIMAL(10,3) NULL,
    unit           VARCHAR(40) NULL,
    details        VARCHAR(120) NULL,
    sort_order     INT UNSIGNED NOT NULL
);

INSERT INTO migration_recipe_ingredients
    (recipe_slug, ingredient_name, quantity, unit, details, sort_order)
VALUES
    ('chocolat-chaud-a-l-ancienne', 'lait entier', 50, 'cl', NULL, 1),
    ('chocolat-chaud-a-l-ancienne', 'chocolat noir 70 %', 100, 'g', 'haché', 2),
    ('chocolat-chaud-a-l-ancienne', 'crème liquide entière', 10, 'cl', NULL, 3),
    ('chocolat-chaud-a-l-ancienne', 'sucre', 1, 'c. à soupe', NULL, 4),
    ('chocolat-chaud-a-l-ancienne', 'cannelle', NULL, NULL, 'une pincée', 5),

    ('mousse-au-chocolat-noir', 'chocolat noir 70 %', 200, 'g', NULL, 1),
    ('mousse-au-chocolat-noir', 'œufs', 6, NULL, 'blancs et jaunes séparés', 2),
    ('mousse-au-chocolat-noir', 'beurre doux', 30, 'g', NULL, 3),
    ('mousse-au-chocolat-noir', 'sucre', 30, 'g', NULL, 4),
    ('mousse-au-chocolat-noir', 'sel', NULL, NULL, 'une pincée', 5),

    ('brownies-aux-noix', 'chocolat noir 70 %', 200, 'g', NULL, 1),
    ('brownies-aux-noix', 'beurre doux', 150, 'g', NULL, 2),
    ('brownies-aux-noix', 'sucre', 180, 'g', NULL, 3),
    ('brownies-aux-noix', 'œufs', 3, NULL, NULL, 4),
    ('brownies-aux-noix', 'farine', 80, 'g', NULL, 5),
    ('brownies-aux-noix', 'cerneaux de noix', 100, 'g', 'grossièrement concassés', 6),

    ('fondant-au-coeur-coulant', 'chocolat noir 70 %', 150, 'g', NULL, 1),
    ('fondant-au-coeur-coulant', 'beurre doux', 100, 'g', 'plus un peu pour les moules', 2),
    ('fondant-au-coeur-coulant', 'œufs', 3, NULL, NULL, 3),
    ('fondant-au-coeur-coulant', 'sucre', 60, 'g', NULL, 4),
    ('fondant-au-coeur-coulant', 'farine', 30, 'g', NULL, 5),
    ('fondant-au-coeur-coulant', 'cacao en poudre', 1, 'c. à soupe', 'pour les moules', 6),

    ('glace-au-chocolat-sans-sorbetiere', 'crème liquide entière', 40, 'cl', 'bien froide', 1),
    ('glace-au-chocolat-sans-sorbetiere', 'lait concentré sucré', 300, 'g', NULL, 2),
    ('glace-au-chocolat-sans-sorbetiere', 'chocolat noir 70 %', 100, 'g', NULL, 3),
    ('glace-au-chocolat-sans-sorbetiere', 'cacao en poudre', 20, 'g', NULL, 4);

INSERT INTO ingredients (name)
SELECT DISTINCT ingredient_name
FROM migration_recipe_ingredients
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO recipe_ingredients
    (recipe_id, ingredient_id, quantity, unit, details, sort_order)
SELECT
    r.id,
    i.id,
    m.quantity,
    m.unit,
    m.details,
    m.sort_order
FROM migration_recipe_ingredients m
JOIN recipes r ON r.slug = m.recipe_slug
JOIN ingredients i ON i.name = m.ingredient_name
ON DUPLICATE KEY UPDATE
    ingredient_id = VALUES(ingredient_id),
    quantity = VALUES(quantity),
    unit = VALUES(unit),
    details = VALUES(details);

DROP TEMPORARY TABLE IF EXISTS migration_recipe_steps;
CREATE TEMPORARY TABLE migration_recipe_steps (
    recipe_slug VARCHAR(170) NOT NULL,
    step_number INT UNSIGNED NOT NULL,
    title VARCHAR(120) NOT NULL,
    instructions VARCHAR(1000) NOT NULL,
    image VARCHAR(500) NOT NULL
);

INSERT INTO migration_recipe_steps
    (recipe_slug, step_number, title, instructions, image)
VALUES
    ('chocolat-chaud-a-l-ancienne', 1, 'Hacher le chocolat', 'Hachez finement le chocolat au couteau et placez-le dans un bol.', 'assets/img/recettes/chocolat-chaud-a-l-ancienne/etape-1.jpg'),
    ('chocolat-chaud-a-l-ancienne', 2, 'Chauffer le lait', 'Faites chauffer le lait, la crème et le sucre à feu moyen pendant 5 minutes, sans laisser bouillir.', 'assets/img/recettes/chocolat-chaud-a-l-ancienne/etape-2.jpg'),
    ('chocolat-chaud-a-l-ancienne', 3, 'Fondre le chocolat', 'Versez un tiers du lait chaud sur le chocolat et mélangez 1 minute jusqu''à obtenir une pâte lisse.', 'assets/img/recettes/chocolat-chaud-a-l-ancienne/etape-3.jpg'),
    ('chocolat-chaud-a-l-ancienne', 4, 'Fouetter et servir', 'Ajoutez le reste du lait et la cannelle, fouettez 2 minutes à feu doux et servez aussitôt.', 'assets/img/recettes/chocolat-chaud-a-l-ancienne/etape-4.jpg'),

    ('mousse-au-chocolat-noir', 1, 'Fondre le chocolat', 'Faites fondre le chocolat et le beurre au bain-marie pendant 5 minutes, puis laissez tiédir jusqu''à 35 °C.', 'assets/img/recettes/mousse-au-chocolat-noir/etape-1.jpg'),
    ('mousse-au-chocolat-noir', 2, 'Ajouter les jaunes', 'Incorporez les jaunes d''œufs un par un en mélangeant bien entre chaque ajout.', 'assets/img/recettes/mousse-au-chocolat-noir/etape-2.jpg'),
    ('mousse-au-chocolat-noir', 3, 'Monter les blancs', 'Montez les blancs avec le sel, ajoutez le sucre en fin de course et fouettez jusqu''à ce qu''ils forment un bec.', 'assets/img/recettes/mousse-au-chocolat-noir/etape-3.jpg'),
    ('mousse-au-chocolat-noir', 4, 'Assembler et réfrigérer', 'Incorporez les blancs en trois fois à la maryse, répartissez en verrines et réfrigérez 4 heures.', 'assets/img/recettes/mousse-au-chocolat-noir/etape-4.jpg'),

    ('brownies-aux-noix', 1, 'Préchauffer le four', 'Préchauffez le four à 180 °C et chemisez un moule carré de 20 cm avec du papier cuisson.', 'assets/img/recettes/brownies-aux-noix/etape-1.jpg'),
    ('brownies-aux-noix', 2, 'Fondre chocolat et beurre', 'Faites fondre le chocolat et le beurre au bain-marie pendant 5 minutes, puis ajoutez le sucre.', 'assets/img/recettes/brownies-aux-noix/etape-2.jpg'),
    ('brownies-aux-noix', 3, 'Ajouter œufs, farine et noix', 'Incorporez les œufs un par un, puis la farine et les noix, sans trop travailler la pâte.', 'assets/img/recettes/brownies-aux-noix/etape-3.jpg'),
    ('brownies-aux-noix', 4, 'Cuire', 'Enfournez 25 minutes : la lame d''un couteau doit ressortir légèrement humide. Laissez refroidir 1 heure avant de découper.', 'assets/img/recettes/brownies-aux-noix/etape-4.jpg'),

    ('fondant-au-coeur-coulant', 1, 'Préparer les moules', 'Beurrez 4 moules individuels puis saupoudrez-les de cacao. Préchauffez le four à 210 °C.', 'assets/img/recettes/fondant-au-coeur-coulant/etape-1.jpg'),
    ('fondant-au-coeur-coulant', 2, 'Fondre chocolat et beurre', 'Faites fondre le chocolat et le beurre au bain-marie pendant 4 minutes et lissez le mélange.', 'assets/img/recettes/fondant-au-coeur-coulant/etape-2.jpg'),
    ('fondant-au-coeur-coulant', 3, 'Mélanger la pâte', 'Fouettez les œufs et le sucre 2 minutes, ajoutez le chocolat puis la farine, et remplissez les moules aux trois quarts.', 'assets/img/recettes/fondant-au-coeur-coulant/etape-3.jpg'),
    ('fondant-au-coeur-coulant', 4, 'Cuire à la minute', 'Enfournez exactement 9 minutes, attendez 1 minute puis démoulez directement sur l''assiette.', 'assets/img/recettes/fondant-au-coeur-coulant/etape-4.jpg'),

    ('glace-au-chocolat-sans-sorbetiere', 1, 'Fondre le chocolat', 'Faites fondre le chocolat avec le cacao et 5 cl de crème à feu doux pendant 5 minutes.', 'assets/img/recettes/glace-au-chocolat-sans-sorbetiere/etape-1.jpg'),
    ('glace-au-chocolat-sans-sorbetiere', 2, 'Ajouter le lait concentré', 'Hors du feu, incorporez le lait concentré et laissez tiédir 15 minutes.', 'assets/img/recettes/glace-au-chocolat-sans-sorbetiere/etape-2.jpg'),
    ('glace-au-chocolat-sans-sorbetiere', 3, 'Fouetter la crème', 'Montez le reste de la crème bien froide en chantilly ferme, environ 3 minutes au batteur.', 'assets/img/recettes/glace-au-chocolat-sans-sorbetiere/etape-3.jpg'),
    ('glace-au-chocolat-sans-sorbetiere', 4, 'Mélanger et congeler', 'Incorporez délicatement la chantilly au chocolat, versez dans un moule et congelez 6 heures.', 'assets/img/recettes/glace-au-chocolat-sans-sorbetiere/etape-4.jpg');

INSERT INTO steps (recipe_id, step_number, title, instructions, image)
SELECT
    r.id,
    m.step_number,
    m.title,
    m.instructions,
    m.image
FROM migration_recipe_steps m
JOIN recipes r ON r.slug = m.recipe_slug
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    instructions = VALUES(instructions),
    image = VALUES(image);

DROP TEMPORARY TABLE migration_recipe_ingredients;
DROP TEMPORARY TABLE migration_recipe_steps;

COMMIT;
