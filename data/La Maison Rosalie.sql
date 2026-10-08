-- Maison Rosalie - structure et données de démonstration
-- Rejouable : supprime et recrée tout.

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS maison_rosalie;
CREATE DATABASE maison_rosalie
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE maison_rosalie;

CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(50)  NOT NULL,
  email         VARCHAR(254) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('member', 'author', 'admin') NOT NULL DEFAULT 'member',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email)
) ENGINE = InnoDB;

CREATE TABLE categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title       VARCHAR(80)  NOT NULL,
  slug        VARCHAR(100) NOT NULL,
  description VARCHAR(500) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_title (title),
  UNIQUE KEY uq_categories_slug (slug)
) ENGINE = InnoDB;

CREATE TABLE recipes (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title             VARCHAR(150) NOT NULL,
  slug              VARCHAR(170) NOT NULL,
  description       TEXT         NOT NULL,
  main_image        VARCHAR(500) NOT NULL,
  prep_time_minutes INT UNSIGNED NOT NULL,
  cook_time_minutes INT UNSIGNED NOT NULL,
  servings          INT UNSIGNED NOT NULL,
  difficulty        ENUM('easy', 'medium', 'hard') NOT NULL,
  author_id         INT UNSIGNED NOT NULL,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_recipes_slug (slug),
  KEY idx_recipes_author (author_id),
  KEY idx_recipes_created_at (created_at),
  CONSTRAINT chk_recipes_servings CHECK (servings > 0),
  CONSTRAINT fk_recipes_author FOREIGN KEY (author_id)
    REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE recipe_categories (
  recipe_id   INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (recipe_id, category_id),
  KEY idx_recipe_categories_category (category_id),
  CONSTRAINT fk_recipe_categories_recipe FOREIGN KEY (recipe_id)
    REFERENCES recipes (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_recipe_categories_category FOREIGN KEY (category_id)
    REFERENCES categories (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE ingredients (
  id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name  VARCHAR(120) NOT NULL,
  image VARCHAR(200) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ingredients_name (name)
) ENGINE = InnoDB;

CREATE TABLE recipe_ingredients (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  recipe_id     INT UNSIGNED NOT NULL,
  ingredient_id INT UNSIGNED NOT NULL,
  quantity      DECIMAL(10,3) NULL,
  unit          VARCHAR(40)  NULL,
  details       VARCHAR(120) NULL,
  sort_order    INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_recipe_ingredients_order (recipe_id, sort_order),
  KEY idx_recipe_ingredients_ingredient (ingredient_id),
  CONSTRAINT chk_recipe_ingredients_quantity CHECK (quantity IS NULL OR quantity > 0),
  CONSTRAINT chk_recipe_ingredients_order CHECK (sort_order > 0),
  CONSTRAINT fk_recipe_ingredients_recipe FOREIGN KEY (recipe_id)
    REFERENCES recipes (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_recipe_ingredients_ingredient FOREIGN KEY (ingredient_id)
    REFERENCES ingredients (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE steps (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  recipe_id    INT UNSIGNED NOT NULL,
  step_number  INT UNSIGNED NOT NULL,
  title        VARCHAR(120)  NOT NULL,
  instructions VARCHAR(1000) NOT NULL,
  image        VARCHAR(500)  NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_steps_recipe_number (recipe_id, step_number),
  CONSTRAINT chk_steps_number CHECK (step_number > 0),
  CONSTRAINT fk_steps_recipe FOREIGN KEY (recipe_id)
    REFERENCES recipes (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE ratings (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id   INT UNSIGNED NOT NULL,
  recipe_id INT UNSIGNED NOT NULL,
  rating    TINYINT UNSIGNED NOT NULL,
  rated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ratings_user_recipe (user_id, recipe_id),
  KEY idx_ratings_recipe (recipe_id),
  CONSTRAINT chk_ratings_value CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT fk_ratings_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ratings_recipe FOREIGN KEY (recipe_id)
    REFERENCES recipes (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE comments (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  author_id          INT UNSIGNED NOT NULL,
  recipe_id          INT UNSIGNED NOT NULL,
  subject            VARCHAR(120) NULL,
  message            VARCHAR(500) NOT NULL,
  publication_status ENUM('pending', 'published', 'hidden') NOT NULL DEFAULT 'published',
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_comments_recipe_date (recipe_id, created_at),
  KEY idx_comments_author_date (author_id, created_at),
  CONSTRAINT chk_comments_message CHECK (CHAR_LENGTH(message) BETWEEN 3 AND 500),
  CONSTRAINT fk_comments_author FOREIGN KEY (author_id)
    REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comments_recipe FOREIGN KEY (recipe_id)
    REFERENCES recipes (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE contact_messages (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(100)  NOT NULL,
  email      VARCHAR(254)  NOT NULL,
  subject    VARCHAR(120)  NOT NULL,
  message    VARCHAR(2000) NOT NULL,
  ip_address VARCHAR(45)   NULL,
  status     ENUM('new', 'read', 'processed') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_messages_date (created_at),
  KEY idx_contact_messages_status (status),
  KEY idx_contact_messages_ip_date (ip_address, created_at)
) ENGINE = InnoDB;

CREATE TABLE login_attempts (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email        VARCHAR(254) NOT NULL,
  ip_address   VARCHAR(45)  NOT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_attempts_email_date (email, attempted_at),
  KEY idx_login_attempts_ip_date (ip_address, attempted_at)
) ENGINE = InnoDB;

-- ============================================================
-- Données de démonstration
-- ============================================================

START TRANSACTION;

INSERT INTO users (id, username, email, password_hash, role, created_at) VALUES
  (1, 'admin',  'admin@maisonrosalie.be', '$2y$10$1/HgisbgbBybK97Z.iPX8uS/qjGx6WNqzpUU2O9UqksRAF.7A0dru',  'admin',  NOW() - INTERVAL 60 DAY),
  (2, 'marie',  'marie@example.com',      '$2y$10$TUTfhwaVnffIlHIGOKT.ienZTuk4g3cH0LT/164exv4jpxt/x6jo.', 'member', NOW() - INTERVAL 40 DAY),
  (3, 'Bruce', 'bruce@example.com',     '$2y$10$TUTfhwaVnffIlHIGOKT.ienZTuk4g3cH0LT/164exv4jpxt/x6jo.', 'member', NOW() - INTERVAL 35 DAY),
  (4, 'bryan', 'bryan@example.com',     '$2y$10$TUTfhwaVnffIlHIGOKT.ienZTuk4g3cH0LT/164exv4jpxt/x6jo.', 'member', NOW() - INTERVAL 20 DAY),
  (5, 'Meidhy',  'meidhy@example.com',      '$2y$10$TUTfhwaVnffIlHIGOKT.ienZTuk4g3cH0LT/164exv4jpxt/x6jo.', 'member', NOW() - INTERVAL 10 DAY);

INSERT INTO categories (id, title, slug, description) VALUES
  (1, 'Gâteaux', 'gateaux', 'Fondants, brownies et gâteaux de famille.'),
  (2, 'Mousses', 'mousses', 'Des mousses légères et intenses.'),
  (3, 'Boissons', 'boissons', 'Chocolats chauds et boissons gourmandes.'),
  (4, 'Glacé', 'glace', 'Glaces et desserts glacés au chocolat.');

INSERT INTO recipes (id, title, slug, description, main_image, prep_time_minutes, cook_time_minutes, servings, difficulty, author_id, created_at) VALUES
  (1, 'Chocolat chaud à l''ancienne', 'chocolat-chaud-a-l-ancienne',
   'Un chocolat chaud épais, fait avec du vrai chocolat noir fondu dans le lait, comme à la boutique les jours d''hiver.',
   'assets/img/recettes/pexels-tatianeherder-120156012.webp', 5, 10, 2, 'easy', 1, NOW() - INTERVAL 30 DAY),
  (2, 'Mousse au chocolat noir', 'mousse-au-chocolat-noir',
   'La mousse de Rosalie : seulement du chocolat, des œufs et un peu de beurre, pour une texture aérienne.',
   'assets/img/recettes/pexels-skylake-173032212.webp', 25, 5, 6, 'medium', 1, NOW() - INTERVAL 25 DAY),
  (3, 'Brownies aux noix', 'brownies-aux-noix',
   'Des brownies fondants au cœur, croustillants sur le dessus, avec des noix grossièrement concassées.',
   'assets/img/recettes/pexels-livilla-latini-1678510737-278500292.webp', 15, 25, 12, 'easy', 1, NOW() - INTERVAL 20 DAY),
  (4, 'Fondant au cœur coulant', 'fondant-au-coeur-coulant',
   'Le dessert qui demande de la précision : une cuisson à la minute près pour garder un cœur liquide.',
   'assets/img/recettes/pexels-ali-dashti-506667798-171922342.webp', 20, 10, 4, 'hard', 1, NOW() - INTERVAL 15 DAY),
  (5, 'Glace au chocolat sans sorbetière', 'glace-au-chocolat-sans-sorbetiere',
   'Une glace onctueuse préparée avec une simple crème fouettée, sans machine.',
   'assets/img/recettes/pexels-julias-torten-und-tortchen-434418-382756832.webp', 20, 5, 6, 'medium', 1, NOW() - INTERVAL 5 DAY);

INSERT INTO recipe_categories (recipe_id, category_id) VALUES
  (1, 3), (2, 2), (3, 1), (4, 1), (5, 4);

INSERT INTO ingredients (id, name) VALUES
  (1, 'chocolat noir 70 %'), (2, 'lait entier'), (3, 'crème liquide entière'), (4, 'sucre'),
  (5, 'cannelle'), (6, 'œufs'), (7, 'beurre doux'), (8, 'sel'), (9, 'farine'),
  (10, 'cerneaux de noix'), (11, 'cacao en poudre'), (12, 'lait concentré sucré');

INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit, details, sort_order) VALUES
  (1, 2, 50, 'cl', NULL, 1),
  (1, 1, 100, 'g', 'haché', 2),
  (1, 3, 10, 'cl', NULL, 3),
  (1, 4, 1, 'c. à soupe', NULL, 4),
  (1, 5, NULL, NULL, 'une pincée', 5),

  (2, 1, 200, 'g', NULL, 1),
  (2, 6, 6, NULL, 'blancs et jaunes séparés', 2),
  (2, 7, 30, 'g', NULL, 3),
  (2, 4, 30, 'g', NULL, 4),
  (2, 8, NULL, NULL, 'une pincée', 5),

  (3, 1, 200, 'g', NULL, 1),
  (3, 7, 150, 'g', NULL, 2),
  (3, 4, 180, 'g', NULL, 3),
  (3, 6, 3, NULL, NULL, 4),
  (3, 9, 80, 'g', NULL, 5),
  (3, 10, 100, 'g', 'grossièrement concassés', 6),

  (4, 1, 150, 'g', NULL, 1),
  (4, 7, 100, 'g', 'plus un peu pour les moules', 2),
  (4, 6, 3, NULL, NULL, 3),
  (4, 4, 60, 'g', NULL, 4),
  (4, 9, 30, 'g', NULL, 5),
  (4, 11, 1, 'c. à soupe', 'pour les moules', 6),

  (5, 3, 40, 'cl', 'bien froide', 1),
  (5, 12, 300, 'g', NULL, 2),
  (5, 1, 100, 'g', NULL, 3),
  (5, 11, 20, 'g', NULL, 4);

INSERT INTO steps (recipe_id, step_number, title, instructions, image) VALUES
  (1, 1, 'Hacher le chocolat', 'Hachez finement le chocolat au couteau et placez-le dans un bol.', 'assets/img/recettes/chocolat-chaud-a-l-ancienne/etape-1.jpg'),
  (1, 2, 'Chauffer le lait', 'Faites chauffer le lait, la crème et le sucre à feu moyen pendant 5 minutes, sans laisser bouillir.', 'assets/img/recettes/chocolat-chaud-a-l-ancienne/etape-2.jpg'),
  (1, 3, 'Fondre le chocolat', 'Versez un tiers du lait chaud sur le chocolat et mélangez 1 minute jusqu''à obtenir une pâte lisse.', 'assets/img/recettes/chocolat-chaud-a-l-ancienne/etape-3.jpg'),
  (1, 4, 'Fouetter et servir', 'Ajoutez le reste du lait et la cannelle, fouettez 2 minutes à feu doux et servez aussitôt.', 'assets/img/recettes/chocolat-chaud-a-l-ancienne/etape-4.jpg'),

  (2, 1, 'Fondre le chocolat', 'Faites fondre le chocolat et le beurre au bain-marie pendant 5 minutes, puis laissez tiédir jusqu''à 35 °C.', 'assets/img/recettes/mousse-au-chocolat-noir/etape-1.jpg'),
  (2, 2, 'Ajouter les jaunes', 'Incorporez les jaunes d''œufs un par un en mélangeant bien entre chaque ajout.', 'assets/img/recettes/mousse-au-chocolat-noir/etape-2.jpg'),
  (2, 3, 'Monter les blancs', 'Montez les blancs avec le sel, ajoutez le sucre en fin de course et fouettez jusqu''à ce qu''ils forment un bec.', 'assets/img/recettes/mousse-au-chocolat-noir/etape-3.jpg'),
  (2, 4, 'Assembler et réfrigérer', 'Incorporez les blancs en trois fois à la maryse, répartissez en verrines et réfrigérez 4 heures.', 'assets/img/recettes/mousse-au-chocolat-noir/etape-4.jpg'),

  (3, 1, 'Préchauffer le four', 'Préchauffez le four à 180 °C et chemisez un moule carré de 20 cm avec du papier cuisson.', 'assets/img/recettes/brownies-aux-noix/etape-1.jpg'),
  (3, 2, 'Fondre chocolat et beurre', 'Faites fondre le chocolat et le beurre au bain-marie pendant 5 minutes, puis ajoutez le sucre.', 'assets/img/recettes/brownies-aux-noix/etape-2.jpg'),
  (3, 3, 'Ajouter œufs, farine et noix', 'Incorporez les œufs un par un, puis la farine et les noix, sans trop travailler la pâte.', 'assets/img/recettes/brownies-aux-noix/etape-3.jpg'),
  (3, 4, 'Cuire', 'Enfournez 25 minutes : la lame d''un couteau doit ressortir légèrement humide. Laissez refroidir 1 heure avant de découper.', 'assets/img/recettes/brownies-aux-noix/etape-4.jpg'),

  (4, 1, 'Préparer les moules', 'Beurrez 4 moules individuels puis saupoudrez-les de cacao. Préchauffez le four à 210 °C.', 'assets/img/recettes/fondant-au-coeur-coulant/etape-1.jpg'),
  (4, 2, 'Fondre chocolat et beurre', 'Faites fondre le chocolat et le beurre au bain-marie pendant 4 minutes et lissez le mélange.', 'assets/img/recettes/fondant-au-coeur-coulant/etape-2.jpg'),
  (4, 3, 'Mélanger la pâte', 'Fouettez les œufs et le sucre 2 minutes, ajoutez le chocolat puis la farine, et remplissez les moules aux trois quarts.', 'assets/img/recettes/fondant-au-coeur-coulant/etape-3.jpg'),
  (4, 4, 'Cuire à la minute', 'Enfournez exactement 9 minutes, attendez 1 minute puis démoulez directement sur l''assiette.', 'assets/img/recettes/fondant-au-coeur-coulant/etape-4.jpg'),

  (5, 1, 'Fondre le chocolat', 'Faites fondre le chocolat avec le cacao et 5 cl de crème à feu doux pendant 5 minutes.', 'assets/img/recettes/glace-au-chocolat-sans-sorbetiere/etape-1.jpg'),
  (5, 2, 'Ajouter le lait concentré', 'Hors du feu, incorporez le lait concentré et laissez tiédir 15 minutes.', 'assets/img/recettes/glace-au-chocolat-sans-sorbetiere/etape-2.jpg'),
  (5, 3, 'Fouetter la crème', 'Montez le reste de la crème bien froide en chantilly ferme, environ 3 minutes au batteur.', 'assets/img/recettes/glace-au-chocolat-sans-sorbetiere/etape-3.jpg'),
  (5, 4, 'Mélanger et congeler', 'Incorporez délicatement la chantilly au chocolat, versez dans un moule et congelez 6 heures.', 'assets/img/recettes/glace-au-chocolat-sans-sorbetiere/etape-4.jpg');

INSERT INTO ratings (user_id, recipe_id, rating, rated_at) VALUES
  (1, 1, 4, NOW() - INTERVAL 28 DAY), (2, 1, 4, NOW() - INTERVAL 27 DAY), (3, 1, 3, NOW() - INTERVAL 26 DAY),
  (4, 1, 4, NOW() - INTERVAL 18 DAY), (5, 1, 5, NOW() - INTERVAL 8 DAY),
  (1, 2, 5, NOW() - INTERVAL 24 DAY), (2, 2, 4, NOW() - INTERVAL 23 DAY), (3, 2, 5, NOW() - INTERVAL 22 DAY),
  (4, 2, 4, NOW() - INTERVAL 17 DAY), (5, 2, 5, NOW() - INTERVAL 7 DAY),
  (2, 3, 5, NOW() - INTERVAL 19 DAY), (3, 3, 5, NOW() - INTERVAL 18 DAY), (4, 3, 4, NOW() - INTERVAL 16 DAY),
  (5, 3, 5, NOW() - INTERVAL 6 DAY),
  (1, 4, 5, NOW() - INTERVAL 14 DAY), (2, 4, 4, NOW() - INTERVAL 13 DAY), (3, 4, 4, NOW() - INTERVAL 12 DAY),
  (5, 4, 5, NOW() - INTERVAL 5 DAY),
  (2, 5, 3, NOW() - INTERVAL 4 DAY), (3, 5, 4, NOW() - INTERVAL 3 DAY), (4, 5, 4, NOW() - INTERVAL 2 DAY),
  (5, 5, 3, NOW() - INTERVAL 1 DAY);

INSERT INTO comments (author_id, recipe_id, subject, message, created_at) VALUES
  (2, 1, 'Parfait pour l''hiver', 'Épais comme il faut, j''ai ajouté un peu de vanille et c''était délicieux.', NOW() - INTERVAL 27 DAY),
  (3, 1, NULL, 'Un peu trop sucré pour moi, je mettrai moitié moins de sucre la prochaine fois.', NOW() - INTERVAL 26 DAY),
  (4, 2, 'Aérienne', 'Première mousse réussie ! Bien attendre que le chocolat tiédisse, sinon les blancs retombent.', NOW() - INTERVAL 17 DAY),
  (5, 2, NULL, 'Mes enfants en redemandent.', NOW() - INTERVAL 7 DAY),
  (2, 3, 'Top', 'Les noix apportent du croquant, recette validée par toute la famille.', NOW() - INTERVAL 19 DAY),
  (5, 3, NULL, 'Cuits 22 minutes dans mon four, ils étaient parfaits.', NOW() - INTERVAL 6 DAY),
  (3, 4, 'Attention au temps', 'À 10 minutes le cœur était déjà pris. 9 minutes, c''est vraiment le bon réglage.', NOW() - INTERVAL 12 DAY),
  (4, 5, NULL, 'Très simple et bluffant sans sorbetière.', NOW() - INTERVAL 2 DAY);

COMMIT;
