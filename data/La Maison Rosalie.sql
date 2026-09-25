-- Maison Rosalie - structure de la base
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