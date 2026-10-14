<?php
// path: config-prod.php (modèle pour la mise en ligne)
// Copier ce fichier en config-prod.php À LA RACINE du site sur l'hébergement (au-dessus de public/).
// public/index.php charge automatiquement config-prod.php s'il existe, à la place de config-dev.php.
// config-prod.php est ignoré par Git (.gitignore) : il ne contient les vrais mots de passe que sur le serveur (SEC-08).

const DB_TYPE = "mysql";
const DB_HOST = "";              // hôte MySQL/MariaDB fourni par l'hébergeur
const DB_PORT = 3306;            // port fourni par l'hébergeur
const DB_NAME = "";              // nom de la base créée par l'hébergeur
const DB_LOGIN = "";             // utilisateur de la base (un seul compte sur un hébergement mutualisé)
const DB_PWD = "";
const DB_CHARSET = "utf8mb4";

const RACINE_PATH = __DIR__;
// adresse publique du site en HTTPS, avec la barre finale : utilisée pour le lien de confirmation par e-mail,
// l'adresse canonique, les aperçus de partage et le plan du site
const RACINE_URL = "https://www.exemple.be/";
// jamais d'erreur PHP à l'écran en ligne (SEC-10) : les erreurs vont dans le journal du serveur
const DISPLAY_ERRORS = false;

// E-mails (SMTP de l'hébergeur). Laisser MAIL_HOST vide pour ne rien envoyer.
const MAIL_HOST = "";            // ex. smtp.exemple.be
const MAIL_PORT = 587;           // 587 avec tls, 465 avec ssl
const MAIL_ENCRYPTION = "tls";   // "tls", "ssl" ou "" (aucun)
const MAIL_USERNAME = "";        // souvent l'adresse e-mail complète
const MAIL_PASSWORD = "";
const MAIL_FROM = "";            // adresse d'expédition autorisée par le serveur SMTP (même domaine que le login)
const MAIL_FROM_NAME = "La Maison Rosalie";