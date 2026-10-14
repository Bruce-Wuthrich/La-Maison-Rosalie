<?php
// path: config-dev.php

const DB_TYPE = "mysql";
const DB_HOST = "localhost";
const DB_PORT = 3307;
const DB_NAME = "maison_rosalie";
const DB_LOGIN = "rosalie_app";
const DB_PWD = "";
const DB_CHARSET = "utf8mb4";

const RACINE_PATH = __DIR__;
// adresse du site, utilisée aussi pour le lien de confirmation envoyé par e-mail
const RACINE_URL = "http://maison-rosalie/";
// affichage des erreurs PHP à l'écran : false par défaut (SEC-10), true seulement pour déboguer en local
const DISPLAY_ERRORS = false;

// Bonus mailer (SMTP). Laisser MAIL_HOST vide pour ne rien envoyer : le site fonctionne sans.
// Ne jamais mettre le vrai mot de passe dans ce fichier modèle (SEC-08) : seulement dans config-dev.php.
const MAIL_HOST = "";            // ex. smtp.exemple.be
const MAIL_PORT = 587;           // 587 avec tls, 465 avec ssl
const MAIL_ENCRYPTION = "tls";   // "tls", "ssl" ou "" (aucun)
const MAIL_USERNAME = "";
const MAIL_PASSWORD = "";
const MAIL_FROM = "";            // adresse d'expédition autorisée par le serveur SMTP
const MAIL_FROM_NAME = "La Maison Rosalie";