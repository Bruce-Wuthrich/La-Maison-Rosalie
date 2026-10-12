-- Ajoute les réactions aux commentaires et aligne leur longueur sur la maquette.
-- Migration non destructive et rejouable sur MariaDB 11.

START TRANSACTION;

ALTER TABLE comments
  MODIFY message VARCHAR(1000) NOT NULL,
  DROP CONSTRAINT chk_comments_message,
  ADD CONSTRAINT chk_comments_message CHECK (CHAR_LENGTH(message) BETWEEN 3 AND 1000);

CREATE TABLE IF NOT EXISTS comment_reactions (
  comment_id INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  reaction   ENUM('like', 'dislike') NOT NULL,
  reacted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (comment_id, user_id),
  KEY idx_comment_reactions_user (user_id),
  CONSTRAINT fk_comment_reactions_comment FOREIGN KEY (comment_id)
    REFERENCES comments (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comment_reactions_user FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

COMMIT;
