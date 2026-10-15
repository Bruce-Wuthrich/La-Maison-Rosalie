# Rapport technique : La Maison Rosalie

## 1. Choix de la stack

| Couche | Choix | Raison |
|---|---|---|
| Backend | PHP 8 orienté objet, sans framework | Une classe Mapping (validation) et une classe Manager (SQL) par table : responsabilités claires, validation centralisée, code facile à défendre. Architecture MVC vue en formation, avec des contrôleurs procéduraux. |
| Base de données | MariaDB | Gratuite, fournie avec WAMP, très bien intégrée à PHP via PDO, et capable de porter les contraintes d'intégrité (clés étrangères, `UNIQUE`, `CHECK`). |
| Frontend | HTML5, Bootstrap 5.3, CSS personnalisé, JavaScript natif | Imposé. Bootstrap pour la grille responsive et la navigation repliable, notre CSS pour l'identité caramel, brun et doré. |

**Bibliothèques externes** : Bootstrap 5.3.8 (imposé, chargé depuis le CDN jsDelivr) et la police Poppins (Google Fonts, pour la lisibilité et le ton chaleureux de la marque). Aucune autre bibliothèque.

## 2. Modèle de données

11 tables en `utf8mb4` (accents, apostrophes et caractères spéciaux pris en charge partout) :

| Table | Rôle | Contraintes principales |
|---|---|---|
| `users` | Comptes | `username` et `email` uniques, rôle `ENUM('member', 'admin')` (les deux seuls rôles du CDC, identiques à `UserMapping`) |
| `categories` | Gâteaux, Mousses, Boissons, Glacé | `title` et `slug` uniques |
| `recipes` | Recettes | `slug` unique, `CHECK (servings > 0)`, difficulté en `ENUM`, auteur en clé étrangère |
| `recipe_categories` | Lien recettes et catégories (plusieurs à plusieurs) | Clé primaire composée |
| `ingredients` | Ingrédients réutilisables | `name` unique, donc aucun doublon d'une recette à l'autre |
| `recipe_ingredients` | Quantités par recette | Quantité facultative (`NULL` pour "une pincée"), `CHECK (quantity > 0)`, `UNIQUE (recipe_id, ingredient_id)` : un ingrédient n'apparaît qu'une fois par recette |
| `steps` | Étapes | `UNIQUE (recipe_id, step_number)` |
| `ratings` | Notes | `UNIQUE (user_id, recipe_id)`, `CHECK (rating BETWEEN 1 AND 5)` |
| `comments` | Avis (commentaire, note facultative stockée dans `ratings`) | `UNIQUE (author_id, recipe_id)` : un seul avis par personne et par recette, modifiable. `CHECK (CHAR_LENGTH(TRIM(message)) BETWEEN 3 AND 500)`, sujet de 120 caractères maximum, statut de publication |
| `contact_messages` | Messages du formulaire | Statut `new`, `read` ou `processed`, adresse IP pour l'anti-spam |
| `login_attempts` | Tentatives de connexion | Index sur l'email et l'IP avec la date |
| `favorites` | Recettes favorites des membres (bonus) | Clé primaire composée `(user_id, recipe_id)` : aucun doublon, suppression en cascade |
| `email_verifications` | Jetons de confirmation de l'adresse e-mail | Un jeton par compte, stocké haché (SHA-256), `UNIQUE`, valable 24 heures |

**Intégrité** : supprimer une recette supprime en cascade ses étapes, ses ingrédients liés, ses catégories liées, ses notes et ses commentaires (`ON DELETE CASCADE`). Il ne reste aucune donnée orpheline.

**Valeurs calculées, jamais stockées** : la note moyenne (`AVG`), le nombre de votes (`COUNT`) et le temps total (préparation + cuisson) sont calculés à chaque lecture. Ils ne peuvent donc jamais être faux ou périmés.

**Script d'import unique** : `data/maison_rosalie_structure.sql` supprime et recrée la base, puis insère les données de démonstration. Il peut être rejoué à volonté.

## 3. Mesures de sécurité

| Menace | Mesure |
|---|---|
| Injection SQL | Requêtes préparées avec `bindValue` partout, aucune requête construite avec des données reçues |
| XSS | `htmlspecialchars` sur toute donnée affichée, y compris les messages flash et les noms d'utilisateur |
| CSRF | Jeton de 64 caractères (`random_bytes`) en session, vérifié avec `hash_equals` sur toutes les actions qui modifient des données, déconnexion comprise |
| Vol de mots de passe | `password_hash` (bcrypt), `password_verify`, politique de 10 caractères avec majuscule, minuscule et chiffre |
| Force brute et énumération des comptes | 5 échecs en 15 minutes par email ou par IP, puis blocage. Message d'erreur identique que l'email ou le mot de passe soit faux, et temps de réponse identique : `password_verify` est exécuté avec un hash factice quand l'email n'existe pas. |
| Vol de session | Identifiant renouvelé à la connexion, cookie `httponly` et `samesite=Lax`, mode strict, expiration après 30 minutes d'inactivité, destruction réelle à la déconnexion |
| Accès non autorisé | Contrôle côté serveur sur chaque action. La suppression de commentaire vérifie les droits **dans la requête SQL elle-même**. Le rôle administrateur est relu en base. |
| Données invalides | Validation dans les setters des classes Mapping, doublée par les contraintes de la base. Le JavaScript n'est qu'un confort. |
| Spam | 5 commentaires par 10 minutes et par utilisateur, 3 messages de contact par heure et par IP |
| Fuite d'informations | Aucune erreur technique affichée (`display_errors` désactivé, gestionnaire d'exceptions global), détails dans le journal serveur |
| Clickjacking et contenus détournés | En-têtes HTTP sur toutes les pages : `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin` |
| Secrets | `config-dev.php` hors du dépôt, modèle `config-dev-exemple.php` fourni, seul `public/` est servi, historique Git vérifié |
| Droits excessifs | En local, compte MariaDB `rosalie_app` limité à `SELECT`, `INSERT`, `UPDATE` et `DELETE`. Sur l'hébergement mutualisé, un seul utilisateur est fourni par base : en vraie production, nous demanderions un second compte sans droits de structure |
| Comptes créés avec une adresse d'autrui | Confirmation de l'adresse par lien envoyé par e-mail : jeton aléatoire, stocké haché (SHA-256), à usage unique, valable 24 heures |

## 4. Difficultés rencontrées

**Organisation et calendrier**

- **Visuels arrivés tard** : la maquette a reçu des corrections jusqu'en fin de projet. L'intégration du style a dû attendre les versions définitives, et certaines pages ont été reprises plusieurs fois.
- **Consignes parfois différentes** entre le cahier des charges et les indications données en cours. Nous avons pris le cahier des charges comme référence et documenté nos arbitrages.
- **Absences pour maladie** de deux membres de l'équipe (Bryan et Meidhy) pendant le projet.
- **Rendez-vous client repoussé plusieurs fois** : le premier rendez-vous a finalement eu lieu 24 heures avant la présentation, et ses retours ont été intégrés dans la foulée.

Malgré ces contretemps, la communication et la répartition des tâches se sont faites naturellement : chacun savait sur quoi travailler, et nous ne nous sommes jamais réellement retrouvés en difficulté.

**Sur le plan technique**

- **Coordination sur un même dépôt** : beaucoup de Pull Requests en parallèle, des branches à remettre à jour avec `main` et des conflits à éviter en séparant bien les fichiers de chacun.
- **Évolutions de la base en cours de projet** : les migrations ont d'abord servi à mettre à jour les bases existantes, puis ont été intégrées au script principal pour garder un seul script d'installation.
- **Photos des étapes** : impossible de cuisiner les 5 recettes pendant le projet, d'où des photos générées par IA, déclarées dans le README.

## 5. Ce que nous referions autrement

- **Travailler de façon plus globale** : avancer exigence par exigence, ticket par ticket, nous a parfois fait faire puis défaire, surtout sur les bonus. Penser chaque fonctionnalité dans son ensemble dès le départ aurait évité ces retours en arrière.
- **Moins se disperser** : avec beaucoup de petites tâches en parallèle, il était facile de passer de l'une à l'autre. Nous fixerions des priorités plus nettes : d'abord l'obligatoire, ensuite les bonus.
- **Comparer la maquette au cahier des charges avant l'intégration**, pour ne pas coder des éléments à modifier ou retirer ensuite.
- **Définir dès la semaine 1 le format des réponses JSON** et la documentation des points d'accès, pour que les vues, les contrôleurs et le JavaScript avancent en parallèle.

## 6. Mesures à prévoir avant une mise en ligne réelle (SEC-12)

1. **HTTPS obligatoire**, avec redirection de tout le trafic HTTP. Le cookie de session passe alors automatiquement en `secure`. Ajouter l'en-tête `Strict-Transport-Security` (HSTS).
2. **Compléter les en-têtes de sécurité HTTP** : `X-Frame-Options`, `X-Content-Type-Options` et `Referrer-Policy` sont déjà envoyés. Il reste à ajouter `Content-Security-Policy` (limiter les scripts, styles et iframes aux sources connues : notre domaine, jsDelivr pour Bootstrap, Google Fonts et Google Maps).
3. **Fichier `config-prod.php`** propre à l'hébergement, avec un compte de base de données et un mot de passe différents de ceux du développement, et des permissions de fichiers restreintes.
4. **Masquer les informations du serveur** (versions de PHP et d'Apache dans les en-têtes, `expose_php = Off`).
5. **Journaux** : rotation des fichiers, surveillance des erreurs, alertes sur les pics de tentatives de connexion.
6. **Limitation de débit au niveau du serveur** ou d'un proxy, en plus des limites applicatives.
7. **Anti-spam renforcé** sur l'inscription et le contact (captcha respectueux de la vie privée ou champ piège). La vérification de l'adresse e-mail à l'inscription est déjà en place.
13. **Délivrabilité des e-mails** : enregistrements SPF, DKIM et DMARC sur le domaine d'envoi, pour que les e-mails du site n'arrivent plus dans les indésirables.
8. **Sauvegardes automatiques** de la base, avec un test de restauration régulier.
9. **Mises à jour** régulières de PHP, MariaDB et Bootstrap.
10. **Mot de passe oublié** : réinitialisation par email avec un lien à usage unique et durée limitée.
11. **RGPD** : mentions légales et politique de confidentialité complètes, durée de conservation des messages de contact et des tentatives de connexion, possibilité de supprimer son compte.
12. **Nettoyage planifié** des tables `login_attempts` et `contact_messages` anciennes.
