-- Modèle de création du compte applicatif rosalie_app (SEC-09).
-- À exécuter en root APRÈS l'import de data/maison_rosalie_structure.sql.
-- Avant de l'exécuter : copier ce fichier en data/create-app-user.sql (ignoré par Git),
-- remplacer CHANGER_CE_MOT_DE_PASSE par votre mot de passe,
-- et reporter ce même mot de passe dans DB_PWD de config-dev.php.
-- Ne jamais commiter une copie de ce fichier contenant le vrai mot de passe.

CREATE USER IF NOT EXISTS 'rosalie_app'@'localhost' IDENTIFIED BY 'CHANGER_CE_MOT_DE_PASSE';

-- Droits limités : lire et écrire les données, rien d'autre (pas de CREATE, DROP, ALTER).
GRANT SELECT, INSERT, UPDATE, DELETE ON maison_rosalie.* TO 'rosalie_app'@'localhost';

FLUSH PRIVILEGES;