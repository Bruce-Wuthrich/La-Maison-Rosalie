-- Ajoute les tags dynamiques aux recettes existantes.
-- Migration rejouable pour MariaDB 11.4.

CREATE TABLE IF NOT EXISTS tags (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(80)  NOT NULL,
  slug          VARCHAR(100) NOT NULL,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tags_name (name),
  UNIQUE KEY uq_tags_slug (slug),
  KEY idx_tags_display_order (display_order)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS recipe_tags (
  recipe_id INT UNSIGNED NOT NULL,
  tag_id    INT UNSIGNED NOT NULL,
  PRIMARY KEY (recipe_id, tag_id),
  KEY idx_recipe_tags_tag (tag_id),
  CONSTRAINT fk_recipe_tags_recipe FOREIGN KEY (recipe_id)
    REFERENCES recipes (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_recipe_tags_tag FOREIGN KEY (tag_id)
    REFERENCES tags (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

INSERT INTO tags (name, slug, display_order) VALUES
  ('chocolat', 'chocolat', 1),
  ('rapide', 'rapide', 2),
  ('famille', 'famille', 3),
  ('four', 'four', 4),
  ('chocolat noir', 'chocolat-noir', 5),
  ('populaire', 'populaire', 6),
  ('brownies', 'brownies', 7),
  ('fête', 'fete', 8),
  ('enfants', 'enfants', 9),
  ('sans cuisson', 'sans-cuisson', 10),
  ('crémeux', 'cremeux', 11)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  display_order = VALUES(display_order);

INSERT IGNORE INTO recipe_tags (recipe_id, tag_id)
SELECT r.id, t.id
FROM recipes r
CROSS JOIN tags t
WHERE
  (r.slug = 'chocolat-chaud-a-l-ancienne' AND t.slug IN ('chocolat', 'rapide', 'famille', 'chocolat-noir', 'populaire', 'enfants'))
  OR (r.slug = 'mousse-au-chocolat-noir' AND t.slug IN ('chocolat', 'rapide', 'famille', 'chocolat-noir', 'enfants', 'cremeux'))
  OR (r.slug = 'brownies-aux-noix' AND t.slug IN ('chocolat', 'famille', 'four', 'chocolat-noir', 'populaire', 'brownies', 'fete', 'enfants'))
  OR (r.slug = 'fondant-au-coeur-coulant' AND t.slug IN ('chocolat', 'four', 'chocolat-noir', 'populaire', 'fete'))
  OR (r.slug = 'glace-au-chocolat-sans-sorbetiere' AND t.slug IN ('chocolat', 'rapide', 'famille', 'populaire', 'enfants', 'sans-cuisson', 'cremeux'));
