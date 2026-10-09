-- Migration non destructive : corrige les cinq images principales existantes.
-- À exécuter une seule fois sur une base maison_rosalie déjà installée.

USE maison_rosalie;

START TRANSACTION;

UPDATE recipes
SET main_image = CASE slug
    WHEN 'chocolat-chaud-a-l-ancienne'
        THEN 'assets/img/recettes/pexels-tatianeherder-120156012.webp'
    WHEN 'mousse-au-chocolat-noir'
        THEN 'assets/img/recettes/pexels-skylake-173032212.webp'
    WHEN 'brownies-aux-noix'
        THEN 'assets/img/recettes/pexels-livilla-latini-1678510737-278500292.webp'
    WHEN 'fondant-au-coeur-coulant'
        THEN 'assets/img/recettes/pexels-ali-dashti-506667798-171922342.webp'
    WHEN 'glace-au-chocolat-sans-sorbetiere'
        THEN 'assets/img/recettes/pexels-julias-torten-und-tortchen-434418-382756832.webp'
    ELSE main_image
END
WHERE slug IN (
    'chocolat-chaud-a-l-ancienne',
    'mousse-au-chocolat-noir',
    'brownies-aux-noix',
    'fondant-au-coeur-coulant',
    'glace-au-chocolat-sans-sorbetiere'
);

COMMIT;
