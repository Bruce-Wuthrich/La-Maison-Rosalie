# Documentation des points d'accès (BE-73)

Ce document liste chaque point d'accès du backend de La Maison Rosalie : son rôle, qui peut l'utiliser, les données attendues et les réponses possibles.

Le site n'utilise pas de framework. Toutes les requêtes passent par un point d'entrée unique, `public/index.php`, qui choisit le contrôleur selon le paramètre `pg` de l'adresse.

## 1. Règles communes à tous les points d'accès

### 1.1 Routage

| Paramètre `pg` | Contrôleur | Méthode |
|---|---|---|
| `accueil` (par défaut) | `HomeController` | GET |
| `a-propos` | `HomeController` | GET |
| `mentions-legales` | `HomeController` | GET |
| `recettes` | `RecipeController` | GET |
| `recette` | `RecipeController` | GET |
| `contact` | `ContactController` | GET (page) et POST (envoi) |
| `compte` | `AuthController` | POST uniquement |
| `noter` | `RatingController` | POST uniquement |
| `commentaires` | `CommentListController` | GET uniquement (lecture JSON) |
| `commentaire` | `CommentController` | POST uniquement |
| `favori` | `FavoriteController` | POST uniquement (bonus favoris) |
| `verification` | `VerificationController` | GET (lien de confirmation reçu par e-mail) |
| `sitemap` | `SitemapController` | GET (plan du site XML) |
| `mon-compte` | `AccountController` | GET uniquement (espace membre, onglets) |
| `admin` | `AdminController` | GET (page, onglets) et POST (actions sur les recettes) |
| toute autre valeur | aucun | réponse **404** et page d'erreur |

### 1.2 Sécurité appliquée partout

- **Requêtes SQL préparées** (`prepare` + `bindValue`) pour toutes les données reçues (SEC-01).
- **Échappement à l'affichage** : toute donnée venant d'un visiteur passe par `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` (SEC-02).
- **Jeton CSRF** : toute action qui modifie des données exige un champ `csrf_token` égal au jeton de la session, comparé avec `hash_equals` (SEC-03). Jeton absent ou faux : message "La session du formulaire a expiré. Rechargez la page puis réessayez." (400 en JSON), aucune écriture.
- **Méthode POST obligatoire** pour les actions qui modifient des données. Une requête GET reçoit le message "Cette action doit être envoyée depuis un formulaire." (405 en JSON, avec l'en-tête `Allow: POST`) et rien n'est modifié.
- **Contrôle d'accès côté serveur** à chaque action (SEC-06) : connexion vérifiée par `requireLogin()`, droits d'auteur ou d'administrateur relus en base.
- **Validation serveur de chaque donnée** dans les classes Mapping, indépendamment du JavaScript (SEC-07, BE-72).
- **Aucune erreur technique visible** : les exceptions sont écrites dans le journal (`error_log`), le visiteur reçoit un message neutre (SEC-10).

### 1.3 Format des réponses

**Réponse actuelle (formulaire HTML classique)** : après une action, le serveur enregistre un message temporaire en session (`setFlashMessage`) puis redirige (code **302**) vers la page concernée. Le message s'affiche une seule fois, en notification (`view/inc/flash.php`). Ce schéma "POST puis redirection" évite qu'un rafraîchissement renvoie le formulaire.

**Réponse JSON (requêtes `fetch`, FE-34, FE-35 et FE-60)** : quand la requête contient l'en-tête `Accept: application/json`, les points d'accès `noter`, `commentaire`, `compte`, `favori` et les actions de recettes de l'admin répondent en JSON avec une structure unique (BE-70), sans redirection.

Ce fonctionnement repose sur trois fonctions de `controller/ControllerHelpers.php` : `isJsonRequest()` détecte l'en-tête, `jsonResponse()` envoie la réponse avec le bon code HTTP, et `respond()` choisit entre JSON et message flash + redirection. Les vérifications communes (`requirePostMethod`, `requireLogin`, `validateCsrfToken`) passent aussi par `respond()`, donc elles répondent en JSON au JavaScript.

```json
{
  "success": true,
  "message": "Texte lisible par le visiteur",
  "data": { }
}
```

- `success` : `true` si l'action a réussi, `false` sinon.
- `message` : le même texte que le message flash, affichable tel quel.
- `data` : les données utiles au JavaScript (`null` en cas d'erreur).

### 1.4 Codes HTTP utilisés (BE-71)

| Code | Signification | Exemple |
|---|---|---|
| 200 | Succès | note enregistrée, commentaire supprimé |
| 201 | Élément créé | commentaire ajouté, compte créé |
| 302 | Redirection après un formulaire classique | toute action en mode HTML |
| 400 | Données invalides | note de 6, message trop court, jeton CSRF absent |
| 401 | Non connecté | noter sans compte |
| 403 | Accès interdit | supprimer le commentaire d'un autre, page admin pour un membre |
| 404 | Élément introuvable | recette inconnue, page inconnue |
| 405 | Mauvaise méthode | action envoyée en GET |
| 429 | Trop de requêtes | 5 tentatives de connexion en 15 minutes |
| 500 | Erreur inattendue | exception non prévue |
| 503 | Service indisponible | base de données coupée |

## 2. Pages publiques (lecture)

### GET `?pg=accueil`

- **Rôle** : page d'accueil, avec le top 3 des recettes les mieux notées et les recettes les plus rapides.
- **Accès** : tout le monde.
- **Données attendues** : aucune.
- **Top 3 (BE-30)** : calculé par `RecipeManager::getTopThree()`. Ordre : recettes notées d'abord, puis note moyenne décroissante, puis nombre de votes décroissant, puis recette la plus récente. S'il y a moins de 3 recettes notées, la liste est complétée par les plus récentes. La moyenne est toujours calculée avec `AVG()` sur la table `ratings`, jamais stockée (BE-23), donc une nouvelle note est prise en compte au chargement suivant (BE-31).
- **Réponses** : 200 avec la page. 503 si la base ne répond pas : la page s'affiche avec un message, sans erreur technique.

### GET `?pg=a-propos`

- **Rôle** : histoire, valeurs et chiffres clés de la maison.
- **Accès** : tout le monde.
- **Réponses** : 200.

### GET `?pg=mentions-legales`

- **Rôle** : mentions légales, données personnelles et conditions d'utilisation (FE-07).
- **Accès** : tout le monde.
- **Réponses** : 200.

### GET `?pg=recettes`

- **Rôle** : liste des recettes avec note moyenne et nombre de votes (BE-11).
- **Accès** : tout le monde.

| Paramètre | Type | Obligatoire | Règle |
|---|---|---|---|
| `categorie` | texte | non | `gateaux`, `mousses`, `boissons` ou `glace`. Toute autre valeur est ignorée (toutes les recettes s'affichent). |
| `recherche` | texte | non | Coupé à 80 caractères. Recherche dans le titre, la description, la catégorie et le nom des ingrédients. `%` et `_` sont cherchés comme du texte. |
| `difficulte` | texte | non | Bonus. `easy`, `medium` ou `hard`. Toute autre valeur est ignorée. |
| `temps` | entier | non | Bonus. Temps total maximum (préparation + cuisson) : `20`, `30` ou `45`. Toute autre valeur est ignorée. |
| `favoris` | texte | non | Bonus. `1` : uniquement les recettes favorites du membre connecté (filtre « Mes favoris »). Ignoré pour un visiteur. |

- **Combinaison** : tous les critères se cumulent (ET), via `RecipeManager::filter()` en requête préparée. Le temps total est calculé dans la requête, jamais stocké.
- **Réponses** : 200 avec la liste, ou le message "Aucune recette ne correspond à votre recherche.". 503 avec un message si la base ne répond pas (FE-21).

### GET `?pg=recette&slug={slug}`

- **Rôle** : fiche complète d'une recette (BE-12) : informations, catégories, ingrédients avec quantités, étapes dans l'ordre avec photo, note moyenne, nombre de votes, note de l'utilisateur connecté s'il en a une, commentaires.
- **Accès** : tout le monde.

| Paramètre | Type | Obligatoire | Règle |
|---|---|---|---|
| `slug` | texte | oui | Uniquement `a-z`, `0-9` et `-`. |
| `comment_page` | entier | non | Page de commentaires (10 par page, du plus récent au plus ancien, BE-40). Une valeur trop grande est ramenée à la dernière page. |
| `comment_sort` | texte | non | `date` (par défaut), `mieux` ou `pire`. Toute autre valeur revient à `date`. |

- **Temps total (BE-14)** : calculé par `RecipeMapping::getTotalTime()` (préparation + cuisson), jamais saisi.
- **Tags** : tirés de la base par `RecipeManager::getTags()` : la catégorie de la recette puis ses ingrédients, chacun avec le nombre de recettes qui l'utilisent. Un tag de catégorie mène à `?pg=recettes&categorie=...`, un tag d'ingrédient à `?pg=recettes&recherche=...`.
- **Autres recettes (FE-37)** : `RecipeManager::getOthers()` renvoie 4 autres recettes, affichées en fin de fiche.
- **Réponses** :
  - 200 avec la fiche.
  - 404 si le slug est invalide ou ne correspond à aucune recette, avec une page d'erreur et un lien de retour (BE-13, FE-38).
  - 503 si la base ne répond pas.

### GET `?pg=sitemap`

- **Rôle** : plan du site XML pour les moteurs de recherche (déclaré dans `public/robots.txt`).
- **Accès** : tout le monde.
- **Contenu** : l'accueil, les pages Recettes, À propos, Contact et Mentions légales, puis une adresse par recette, lues en base (même liste que le menu déroulant). Une recette ajoutée en base y apparaît sans modifier le code.
- **Réponse** : 200, `Content-Type: application/xml`. Les adresses sont construites à partir de `RACINE_URL`.

### Fichiers publics pour les robots

- `public/robots.txt` : tout le site public peut être exploré, sauf `?pg=admin`, `?pg=mon-compte` et `?pg=verification`.
- `public/llms.txt` : présentation du site pour les assistants IA (moteurs génératifs).
- Chaque page publique contient une adresse canonique, des balises Open Graph et, selon la page, des données structurées schema.org en JSON-LD (`Recipe` et `BreadcrumbList` sur une fiche, `Organization` et `WebSite` sur l'accueil). Les pages admin, Mon compte et d'erreur sont en `noindex`, sans ces données.

## 3. Comptes utilisateurs

Toutes les actions de `?pg=compte` exigent **POST** et un `csrf_token` valide. Elles redirigent vers l'accueil.

### POST `?pg=compte&action=register`

- **Rôle** : créer un compte (BE-01).
- **Accès** : visiteur.

| Champ | Règle |
|---|---|
| `username` | 3 à 50 caractères : lettres, chiffres, `-` et `_`. Unique. |
| `email` | Adresse valide, 254 caractères maximum. Unique. |
| `password` | 10 caractères minimum, avec au moins une majuscule, une minuscule et un chiffre. |
| `password_confirm` | Identique à `password`. |
| `csrf_token` | Jeton de la session. |

- **Traitement** : le mot de passe est haché avec `password_hash(..., PASSWORD_DEFAULT)` (bcrypt), jamais stocké en clair (SEC-04). Le rôle est `member` par défaut. L'unicité est aussi garantie par la base (index `UNIQUE`).
- **Réponses** :
  - Succès (201 en JSON) :
    - avec SMTP configuré : "Votre compte a bien été créé. Un e-mail vient de vous être envoyé : cliquez sur le lien pour confirmer votre adresse, puis connectez-vous." Un jeton est créé (`email_verifications`) et l'e-mail de confirmation part (`Mailer::sendRegistrationConfirmation`) ;
    - sans SMTP : "Votre compte a bien été créé. Vous pouvez maintenant vous connecter." Le compte est confirmé immédiatement.
  - Erreurs (400) : "Tous les champs sont obligatoires.", "Les mots de passe ne correspondent pas.", "Le mot de passe doit faire au moins 10 caractères, avec une majuscule, une minuscule et un chiffre.", "Ce nom d'utilisateur est déjà pris.", "Cet email est déjà utilisé.", ainsi que les messages de validation du nom et de l'email.
  - Erreur serveur (500 ou 503) : "Le service des comptes est momentanément indisponible."
  - Les champs saisis (sauf les mots de passe) sont conservés en cas d'erreur.

### POST `?pg=compte&action=login`

- **Rôle** : se connecter (BE-02).
- **Accès** : visiteur.

| Champ | Règle |
|---|---|
| `email` | Texte. |
| `password` | Texte. |
| `login_mode` | `user` (par défaut) ou `admin` (onglet Admin de la fenêtre de connexion). |
| `csrf_token` | Jeton de la session. |

- **Traitement** :
  - Vérification avec `password_verify`.
  - Message d'erreur **identique** que l'email ou le mot de passe soit faux, pour ne rien révéler (BE-02).
  - **Protection contre les tentatives répétées (BE-03)** : au-delà de 5 échecs en 15 minutes pour un même email ou une même adresse IP, la connexion est bloquée 15 minutes (table `login_attempts`).
  - Identifiant de session renouvelé à la connexion avec `session_regenerate_id(true)` (SEC-05).
  - En mode `admin`, un compte qui n'est pas administrateur est refusé et déconnecté.
  - **Adresse non confirmée** (SMTP configuré) : avec le bon mot de passe, la connexion est refusée et un nouveau lien de confirmation est envoyé. Avec un mauvais mot de passe, la réponse reste le message habituel (401), donc rien ne révèle l'état du compte.
- **Réponses** :
  - Succès (200) : "Connexion réussie. Bienvenue {nom} !". En HTML : redirection vers l'accueil (vers `?pg=admin` pour un administrateur). En JSON : `"data": { "user": { "username": "Meidhy", "role": "member" }, "csrfToken": "..." }`. Le jeton CSRF est renvoyé pour que le JavaScript mette à jour les formulaires de la page sans la recharger.
  - Erreurs : "L'adresse e-mail ou le mot de passe est incorrect." (401), "Trop de tentatives. Merci de patienter 15 minutes." (429), "Ce compte ne possède pas les droits administrateur." (403), "Confirmez d'abord votre adresse e-mail : un nouveau lien de confirmation vient de vous être envoyé." (403).

### GET `?pg=verification&token={jeton}`

- **Rôle** : confirmer l'adresse e-mail d'un compte (lien reçu après l'inscription).
- **Accès** : tout le monde (le jeton fait office de preuve).
- **Traitement** : `EmailVerificationManager::verify()`. Le jeton (64 caractères hexadécimaux) est comparé à son empreinte SHA-256 en base, doit dater de moins de 24 heures et ne sert qu'une fois. Un seul jeton actif par compte : un nouvel envoi remplace l'ancien.
- **Réponses** : redirection vers l'accueil avec un message.
  - Succès : "Votre adresse e-mail est confirmée. Vous pouvez maintenant vous connecter." (la fenêtre de connexion s'ouvre).
  - Jeton absent, invalide, expiré ou déjà utilisé : "Ce lien de confirmation est invalide ou a expiré. Connectez-vous pour en recevoir un nouveau."
- **Lien** : construit à partir de `RACINE_URL` (configuration), jamais à partir de l'en-tête `Host` envoyé par le navigateur.

### POST `?pg=compte&action=logout`

- **Rôle** : se déconnecter.
- **Accès** : utilisateur connecté.
- **Champs** : `csrf_token`.
- **Traitement** : session vidée, cookie supprimé, `session_destroy()`, puis nouvelle session avec un nouvel identifiant (SEC-05). La déconnexion passe par un formulaire POST avec jeton, et non par un lien, pour ne pas pouvoir être déclenchée depuis un autre site (SEC-03).
- **Réponses** : succès (200) "Vous êtes maintenant déconnecté.", en JSON `"data": { "csrfToken": "..." }` (nouveau jeton de la nouvelle session, l'ancien n'est plus valable). Erreur 401 "Vous devez être connecté pour effectuer cette action."

### État de connexion (BE-04)

Chaque page reçoit la variable `$currentUser`, préparée dans `public/index.php` : `null` pour un visiteur, ou `['id' => ..., 'username' => ..., 'role' => ...]` pour un utilisateur connecté. La navigation affiche alors le nom de l'utilisateur et le bouton de déconnexion sur toutes les pages (FE-53).

Session : cookie `httponly`, `samesite=Lax`, `secure` en HTTPS, mode strict, expiration après 30 minutes d'inactivité (SEC-05).

## 4. Notes en étoiles

### POST `?pg=noter`

- **Rôle** : noter une recette, ou remplacer sa note (BE-20, BE-21).
- **Accès** : utilisateur connecté, sauf l'auteur de la recette.

| Champ | Règle |
|---|---|
| `recipe_id` | Entier positif. |
| `slug` | Slug de la recette, qui doit correspondre à `recipe_id`. |
| `rating` | Entier de 1 à 5 uniquement. |
| `csrf_token` | Jeton de la session. |

- **Traitement** :
  - Valeur contrôlée par `RatingMapping` (1 à 5) et par la contrainte `CHECK (rating BETWEEN 1 AND 5)` de la base.
  - Une seule requête `INSERT ... ON DUPLICATE KEY UPDATE` sur l'index unique `(user_id, recipe_id)` : noter à nouveau remplace l'ancienne note, et deux envois simultanés ne peuvent pas créer de doublon (BE-21).
  - Renvoie la nouvelle moyenne (une décimale) et le nombre de votes, calculés depuis la table `ratings` (BE-22, BE-23).
- **Réponses** :
  - Succès (200) : "Votre note a été enregistrée. Nouvelle moyenne : 4,2/5 (6 avis)."
    En JSON : `"data": { "average": 4.2, "count": 6, "userRating": 4 }`.
  - Erreurs : 401 non connecté, 400 "La recette ou la note envoyée est invalide." (note de 0, 6, texte...), 404 "La recette envoyée ne correspond pas à la page consultée.", 403 "Vous ne pouvez pas noter votre propre recette.", 503 "Le service de notation est momentanément indisponible."

## 5. Commentaires

La lecture se fait en GET sur `?pg=commentaires` (avec un **s**). Toutes les actions d'écriture de `?pg=commentaire` exigent **POST**, une connexion et un `csrf_token` valide. Elles reviennent sur la recette (ancre `#comments`).

### GET `?pg=commentaires&slug={slug}`

- **Rôle** : charger les commentaires d'une recette **par tranches de 10** (BE-40, FE-35), sans recharger la page : bouton "Voir plus" et tri.
- **Accès** : tout le monde (lecture publique). Toute autre méthode que GET reçoit **405**.

| Paramètre | Règle |
|---|---|
| `slug` | Obligatoire, lettres minuscules, chiffres et tirets. Sinon **400**. |
| `page` | Facultatif, entier positif (1 par défaut). Une page trop grande est ramenée à la dernière page disponible. |
| `sort` | Facultatif : `date` (défaut, du plus récent au plus ancien), `mieux` ou `pire` (selon la note de l'auteur). Toute autre valeur donne `date`. |

- **Traitement** : seuls les commentaires `published` sont lus. La réponse porte `Cache-Control: no-store`, pour qu'un ancien état ne soit jamais servi après une modification.
- **Réponses** :
  - Succès (200) : `"data": { "comments": [ { "id", "author", "subject", "message", "createdAt", "authorRating", "canDelete" } ], "total": 23, "page": 1, "hasNext": true, "user": { "id", "username", "email" } ou null, "csrfToken": "...", "userRating": 4 ou null, "canRate": true, "rating": { "average": 4.2, "count": 12 } }`. `canDelete` est calculé côté serveur (auteur du commentaire ou administrateur) : le bouton n'est qu'un affichage, la suppression revérifie les droits (BE-43). `user` ne contient que le membre connecté lui-même.
  - Erreurs : 400 slug invalide, 404 "Cette recette est introuvable.", 405 méthode refusée, 503 "Les commentaires sont momentanément indisponibles."

### POST `?pg=commentaire&action=add`

- **Rôle** : ajouter son avis (commentaire, avec ou sans note) sur une recette, ou modifier son avis existant (BE-41). Un utilisateur a **un seul avis par recette**.
- **Accès** : utilisateur connecté.

| Champ | Règle |
|---|---|
| `recipe_id` | Entier positif. |
| `slug` | Slug de la recette, qui doit correspondre à `recipe_id`. |
| `subject` | Facultatif, 120 caractères maximum. |
| `message` | 3 à 500 caractères une fois les espaces superflus retirés. |
| `rating` | Facultatif : entier de 1 à 5. Le CDC sépare note et commentaire : sans note, seul le commentaire est enregistré (la note se donne aussi au clic via `?pg=noter`). Une valeur envoyée hors de 1 à 5 est refusée (400). |
| `csrf_token` | Jeton de la session. |

- **Traitement** :
  - L'auteur de la recette peut la commenter, mais pas la noter : s'il envoie une note, la demande est refusée (403).
  - Quand une note est envoyée, le commentaire et la note sont enregistrés dans une **transaction** : soit les deux sont enregistrés, soit aucun. La note remplace l'éventuelle note précédente de l'utilisateur.
  - **Un seul avis par utilisateur et par recette** (contrainte `UNIQUE (author_id, recipe_id)` en base). Si l'utilisateur a déjà un avis, un nouvel envoi le **remplace** (texte, sujet et note) et sa date est remise à jour : il remonte en tête de liste et compte dans la limite anti-abus.
  - Validation par `CommentMapping`, et contrainte `CHECK (CHAR_LENGTH(TRIM(message)) BETWEEN 3 AND 500)` en base (les espaces superflus ne comptent pas, même pour une insertion faite hors du site).
  - **Limite anti-abus (BE-44)** : 5 commentaires maximum par utilisateur sur 10 minutes.
  - Le commentaire créé est relu en base et renvoyé complet (identifiant, auteur, date) pour un affichage immédiat (BE-42).
  - Le texte est toujours affiché échappé : du HTML ou du JavaScript s'affiche comme du simple texte (BE-45).
- **Réponses** :
  - Succès : 201 "Votre commentaire a bien été ajouté." à la création, 200 "Votre avis a bien été modifié." à la modification.
    En JSON : `"data": { "comment": { "id": 12, "author": "marie", "subject": null, "message": "...", "createdAt": "2026-10-12 10:30:00", "authorRating": 5, "canDelete": true }, "rating": { "average": 4.4, "count": 5 }, "updated": false }`. `authorRating` vaut la note envoyée, sinon la note déjà donnée par le membre (0 s'il n'a jamais noté, aucune étoile n'est alors affichée). `updated` vaut `true` quand un avis existant a été modifié : le JavaScript remplace alors l'avis affiché au lieu d'en ajouter un. Le JavaScript affiche ces valeurs avec `textContent`, jamais `innerHTML` (BE-45).
  - Erreurs : 401 non connecté, 400 "Le message doit faire entre 3 et 500 caractères.", "Le sujet ne doit pas dépasser 120 caractères." ou "Choisissez une note comprise entre 1 et 5 étoiles.", 403 "Vous ne pouvez pas noter votre propre recette.", 404 recette introuvable, 429 "Vous avez envoyé trop de commentaires. Merci de patienter quelques minutes.", 503 "Le service des commentaires est momentanément indisponible."

### POST `?pg=commentaire&action=delete`

- **Rôle** : supprimer un commentaire (BE-43).
- **Accès** : l'auteur du commentaire, ou un administrateur.

| Champ | Règle |
|---|---|
| `comment_id` | Entier positif. |
| `slug` | Slug de la recette (pour revenir sur la bonne page). |
| `from` | Facultatif : `admin` pour revenir sur la page admin. |
| `csrf_token` | Jeton de la session. |

- **Traitement** : le contrôleur relit le commentaire et l'utilisateur en base, puis la requête SQL elle-même vérifie les droits (`WHERE id = :id AND (author_id = :user_id OR :is_admin = 1)`). Une requête fabriquée à la main pour supprimer le commentaire d'un autre est donc refusée, même si elle contourne le contrôleur.
- **Réponses** :
  - Succès (200) : "Le commentaire a bien été supprimé." En JSON : `"data": { "commentId": 12 }`.
  - Erreurs : 401 non connecté, 403 "Vous ne pouvez pas supprimer ce commentaire." (commentaire d'un autre ou inexistant).

### POST `?pg=commentaire&action=status`

- **Rôle** : modération (bonus) : **masquer** un avis du site sans le supprimer, ou le **republier**.
- **Accès** : administrateur uniquement. Le rôle est relu en base **avant** de chercher l'avis : un membre ne peut même pas savoir si un identifiant existe.

| Champ | Règle |
|---|---|
| `comment_id` | Entier positif, avis existant. |
| `status` | `hidden` (masquer) ou `published` (republier) uniquement. `pending` et toute autre valeur sont refusés. |
| `from` | Facultatif : `admin` pour revenir sur la page admin. |
| `csrf_token` | Jeton de la session. |

- **Traitement** : un avis masqué disparaît de la fiche, du compteur et de la lecture `?pg=commentaires`, mais reste en base et dans la page admin. Les nouveaux avis sont publiés directement (`published` par défaut en base) : c'est une modération **a posteriori**, qui garde le scénario 10 du CDC (le commentaire apparaît sans rechargement).
- **Réponses** :
  - Succès (200) : "L’avis est masqué sur le site." ou "L’avis est de nouveau visible sur le site." En JSON : `"data": { "commentId": 12, "status": "hidden" }`.
  - Erreurs : 401 non connecté, 403 "Cette action est réservée aux administrateurs.", 404 "Cet avis est introuvable.", 400 statut invalide.

## 5 bis. Recettes favorites (bonus)

### POST `?pg=favori`

- **Rôle** : ajouter une recette aux favoris du membre connecté, ou l'en retirer.
- **Accès** : membre connecté (`requireLogin`). Le cœur d'un visiteur ouvre la fenêtre de connexion.

| Champ | Règle |
|---|---|
| `recipe_id` | Entier positif, doit correspondre au slug. |
| `slug` | Slug de la recette consultée. |
| `favorite` | `1` pour ajouter, `0` pour retirer. Toute autre valeur est refusée. |
| `csrf_token` | Jeton de la session. |

- **Traitement** : `FavoriteManager::setFavorite()`. La clé primaire `(user_id, recipe_id)` et `INSERT IGNORE` empêchent tout doublon, même si la même demande arrive deux fois. Les favoris d'une recette ou d'un compte supprimés partent en cascade.
- **Réponses** :
  - 200 "La recette est ajoutée à vos favoris." ou "La recette est retirée de vos favoris.", avec `"data": { "recipeId": 2, "favorite": true }`. Formulaire classique : retour sur la fiche avec le message.
  - 400 champ invalide ou jeton CSRF absent, 401 non connecté, 404 id et slug qui ne correspondent pas, 405 en GET, 503 base indisponible.

### GET `?pg=mon-compte&onglet={onglet}`

- **Rôle** : espace du membre connecté. Il y retrouve ses recettes favorites, les notes qu'il a données et les avis qu'il a écrits.
- **Accès** : membre ou administrateur connecté (`requireLogin`). Le lien « Mon compte » du menu n'apparaît qu'une fois connecté ; un visiteur qui ouvre l'adresse est renvoyé vers l'accueil avec un message (401).

| Paramètre | Type | Obligatoire | Règle |
|---|---|---|---|
| `onglet` | texte | non | `favoris` (par défaut), `notes` ou `avis`. Toute autre valeur revient à `favoris`. |

- **Données** : uniquement celles du membre de la session, jamais d'un identifiant reçu dans l'adresse.
  - Favoris : `RecipeManager::filter()` avec l'identifiant du membre, mêmes cartes que la page Recettes.
  - Notes : `RatingManager::getByUserId()`, de la plus récente à la plus ancienne, avec le titre et le lien de la recette.
  - Avis : `CommentManager::getByAuthorId()`, du plus récent au plus ancien. Les avis masqués par la modération apparaissent avec leur statut.
- **Réponses** : 200 avec la page, 302 vers l'accueil pour un visiteur (401 en JSON), message d'erreur propre si la base ne répond pas. Page en `noindex`.

## 6. Contact

### GET `?pg=contact`

- **Rôle** : page de contact, avec le formulaire, les coordonnées et la carte.
- **Accès** : tout le monde.

### POST `?pg=contact`

- **Rôle** : envoyer un message à la chocolaterie (BE-50).
- **Accès** : tout le monde.

| Champ | Règle |
|---|---|
| `name` | 2 à 100 caractères. |
| `email` | Adresse valide, 254 caractères maximum. |
| `subject` | 3 à 120 caractères. |
| `message` | 10 à 2000 caractères. |
| `csrf_token` | Jeton de la session. |

- **Traitement** :
  - Validation complète côté serveur par `ContactMessageMapping`. Le JavaScript (`public/js/contact.js`) applique les mêmes règles, mais seulement pour le confort du visiteur.
  - **Protection anti-spam** : 3 messages maximum par adresse IP sur une heure, en plus du jeton CSRF.
  - Le message est enregistré dans la table `contact_messages` (statut `new`), avec la date et l'adresse IP.
- **Réponses** :
  - Succès : "Votre message a bien été envoyé." Si le SMTP est configuré, un accusé de réception part à l'adresse du visiteur (`Mailer::sendContactAcknowledgement`), sans bloquer l'enregistrement si l'envoi échoue.
  - Erreurs : messages de validation (400), "Trop de messages ont été envoyés. Merci de réessayer plus tard." (429), "Le formulaire de contact est momentanément indisponible." (503). La saisie est conservée en cas d'erreur (FE-42).

## 7. Administration

L'espace d'administration (bonus « panneau d'administration ») tient sur une seule page, découpée en 3 onglets choisis par l'adresse. Toutes les adresses de cette section exigent un administrateur : `requireAdmin()` relit le rôle en base à chaque requête, pas seulement dans la session.

- Visiteur : redirection vers l'accueil avec "Vous devez être connecté pour effectuer cette action." (401 en JSON).
- Membre : redirection avec "Cette page est réservée aux administrateurs." (403).

### GET `?pg=admin&onglet={onglet}`

| Paramètre | Type | Obligatoire | Règle |
|---|---|---|---|
| `onglet` | texte | non | `commentaires` (par défaut), `contact` ou `recettes`. Toute autre valeur revient à `commentaires`. |

- **Onglet Commentaires** : tous les avis du site avec leur recette et leur statut.
  - **Masquer / republier** : `POST ?pg=commentaire&action=status` (section 5). L'avis masqué reste affiché dans l'admin, grisé, avec le statut "Masqué".
  - **Supprimer** : `POST ?pg=commentaire&action=delete` (section 5), avec toutes ses protections. Le champ `from=admin` renvoie sur cet onglet après un formulaire classique. Avec JavaScript, la suppression se fait sans rechargement, après confirmation.
- **Onglet Contact** : derniers messages de contact, avec leur statut (sans l'adresse IP).
- **Onglet Recettes** : toutes les recettes en cartes (photo, titre, catégorie, difficulté, temps total), avec les boutons Modifier et Supprimer, et un lien "Ajouter une recette".
- **Réponses** : 200 pour un administrateur.

### GET `?pg=admin&onglet=recettes&recette={nouvelle|slug}`

- **Rôle** : formulaire d'ajout (`recette=nouvelle`) ou de modification (`recette={slug}`, prérempli depuis la base), affiché dans l'onglet Recettes.
- **Réponses** : 200 avec le formulaire. Slug invalide ou inconnu : retour à l'onglet avec "Cette recette est introuvable." (404).

### POST `?pg=admin&onglet=recettes&action=save[&recette={slug}]`

- **Rôle** : enregistrer une nouvelle recette, ou modifier la recette du slug.
- **Envoi** : `multipart/form-data`, avec le `csrf_token`.

| Champ | Règle |
|---|---|
| `title` | 3 à 150 caractères. Le slug est créé à partir du titre à l'ajout (rendu unique), puis ne change plus. |
| `description` | Obligatoire, 2000 caractères maximum. |
| `prep_time`, `cook_time` | Entiers de 0 à 1440 (minutes). |
| `servings` | Entier de 1 à 50. |
| `difficulty` | `easy`, `medium` ou `hard`. |
| `categories[]` | Au moins une catégorie existante. |
| `ingredients[i][name]` | 1 à 50 ingrédients, nom de 120 caractères maximum, une seule fois par recette (accents et majuscules ignorés). `quantity` (nombre), `unit` et `details` sont facultatifs. |
| `steps[i][title]`, `steps[i][instructions]` | 4 à 20 étapes (FE-33), titre 120 caractères et texte 1000 caractères maximum. |
| `main_image` | Obligatoire à l'ajout, facultatif en modification (la photo actuelle est gardée). |
| `step_images[i]` | Une photo par étape, nouvelle ou déjà en place (`steps[i][current_image]`, accepté seulement s'il appartient déjà à la recette). |

- **Images** : WebP, JPEG ou PNG uniquement, vérifiés par `finfo` et `getimagesize`, 300 Ko maximum chacune (FE-66). Le serveur choisit le nom du fichier et le range dans `public/uploads/recettes/{slug}/` (dossier ignoré par git). Une image remplacée est supprimée, seulement si elle venait de `uploads/` : les images d'origine dans `assets/` ne sont jamais effacées.
- **Enregistrement** : en transaction. Recette, catégories, ingrédients (créés s'ils n'existent pas encore) et étapes sont écrits ensemble, ou pas du tout. En cas d'erreur, les images envoyées pendant la requête sont effacées.
- **Réponses** :
  - JSON : 201 (ajout) ou 200 (modification), "La recette a bien été ajoutée." ou "La recette a bien été modifiée.", `data.url` et `data.slug`. Formulaire classique : redirection vers la fiche de la recette.
  - 400 avec un message précis : champ invalide, ingrédient en double, moins de 4 étapes, photo manquante, image trop lourde ou d'un mauvais format. Sans JavaScript, le formulaire est réaffiché avec la saisie et l'erreur.
  - 503 si la base ne répond pas.

### POST `?pg=admin&onglet=recettes&action=delete&recette={slug}`

| Champ | Règle |
|---|---|
| `recipe_id` | Doit correspondre au slug de l'adresse, sinon rien n'est supprimé. |
| `csrf_token` | Jeton de la session. |

- **Traitement** : la recette est supprimée avec ses catégories, ingrédients, étapes, notes, commentaires et favoris (clés étrangères en cascade). Ses images de `uploads/` et son dossier vide sont effacés.
- **Réponses** : 200 "La recette a bien été supprimée." (`data.recipeId`), la carte disparaît sans rechargement. 404 si l'identifiant ne correspond pas. 503 si la base ne répond pas.

## 8. Tableau récapitulatif

| Point d'accès | Méthode | Accès | CSRF | Limite |
|---|---|---|---|---|
| `?pg=accueil` | GET | tous | non | non |
| `?pg=a-propos` | GET | tous | non | non |
| `?pg=mentions-legales` | GET | tous | non | non |
| `?pg=recettes` | GET | tous | non | non |
| `?pg=recette&slug=` | GET | tous | non | non |
| `?pg=contact` | GET / POST | tous | oui (POST) | 3 par heure et par IP |
| `?pg=compte&action=register` | POST | visiteur | oui | non |
| `?pg=compte&action=login` | POST | visiteur | oui | 5 échecs par 15 min |
| `?pg=compte&action=logout` | POST | connecté | oui | non |
| `?pg=noter` | POST | connecté (pas l'auteur) | oui | une note par recette |
| `?pg=commentaires&slug=` | GET | tous | non | non |
| `?pg=commentaire&action=add` | POST | connecté (note interdite à l'auteur de la recette) | oui | 5 par 10 min |
| `?pg=commentaire&action=delete` | POST | auteur ou admin | oui | non |
| `?pg=commentaire&action=status` | POST | admin | oui | non |
| `?pg=favori` | POST | connecté | oui | un favori par recette |
| `?pg=mon-compte&onglet=` | GET | connecté | non | non |
| `?pg=verification&token=` | GET | tous | non (jeton à usage unique) | 24 h de validité |
| `?pg=sitemap` | GET | tous | non | non |
| `?pg=admin&onglet=` | GET | admin | non | non |
| `?pg=admin&onglet=recettes&action=save` | POST | admin | oui | images de 300 Ko max |
| `?pg=admin&onglet=recettes&action=delete` | POST | admin | oui | non |