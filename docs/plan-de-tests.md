# Plan de tests (QC-07)

Ce plan couvre toutes les fonctionnalités de la section 6.2 du cahier des charges, toutes les exigences de sécurité (6.4) et les 17 scénarios de la recette finale (section 13).

- **Résultat obtenu** : à remplir au moment du test (ce qui s'est réellement passé).
- **Statut** : ✅ conforme, ❌ non conforme, ⏳ pas encore testable.
- Tester sur une base fraîchement réimportée (`data/maison_rosalie_structure.sql`), avec les comptes de test du README.
- Avant une série de tests de connexion, vider la table `login_attempts` (`DELETE FROM login_attempts;`) pour ne pas être bloqué par la limite.

## Outils pour les requêtes fabriquées à la main

Le formateur testera avec des requêtes envoyées directement au backend, sans passer par l'interface. Deux façons de faire :

**1. La console du navigateur (F12, onglet Console)**, sur une page du site :

```js
// Envoie une note de 6 à la recette 1, avec le vrai jeton de la page
const token = document.querySelector('input[name="csrf_token"]').value;
fetch("?pg=noter", {
  method: "POST",
  headers: { "Content-Type": "application/x-www-form-urlencoded" },
  body: new URLSearchParams({ csrf_token: token, recipe_id: "1", slug: "chocolat-chaud-a-l-ancienne", rating: "6" }),
}).then((r) => console.log(r.status, r.url));
```

**2. curl dans PowerShell** (`curl.exe`, pas l'alias `curl` de PowerShell), par exemple pour une requête sans session ni jeton :

```powershell
curl.exe -i -X POST "http://maison-rosalie/?pg=noter" -d "recipe_id=1&slug=chocolat-chaud-a-l-ancienne&rating=4"
```

## 1. Comptes utilisateurs

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-01 | BE-01 | Inscription valide | Créer `testeur` / `testeur@example.com` / `Chocolat2026` (deux fois) | Message "Votre compte a bien été créé", ligne dans `users` avec `role = member` | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-02 | BE-01 | Email déjà utilisé | S'inscrire avec `marie@example.com` | "Cet email est déjà utilisé.", aucun compte créé | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-03 | BE-01 | Nom déjà pris | S'inscrire avec le nom `marie` | "Ce nom d'utilisateur est déjà pris." | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-04 | BE-01 | Mot de passe trop faible | `abc123`, puis `motdepasselong` (sans majuscule ni chiffre) | Message sur la politique de mot de passe | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-05 | BE-01 | Confirmation différente | Deux mots de passe différents | "Les mots de passe ne correspondent pas." | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-06 | BE-01 | Email invalide | `testeur@` (en retirant `type="email"` avec F12) | Refus côté serveur | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-07 | BE-02 | Connexion valide | `meidhy@example.com` / `Rosalie-Membre-2026` | Notification verte, nom affiché dans la navigation | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-08 | BE-02 | Mauvais mot de passe | Bon email, mauvais mot de passe | "L'adresse e-mail ou le mot de passe est incorrect." | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-09 | BE-02 | Email inconnu | `inconnu@example.com` | **Le même message** que T-08 | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-10 | BE-02 | Déconnexion | Cliquer sur "Déconnexion" | Retour visiteur, la session est détruite | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-11 | BE-03 | Tentatives répétées | 5 mauvais mots de passe, puis le bon | "Trop de tentatives. Merci de patienter 15 minutes.", même avec le bon mot de passe | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-12 | BE-04 | État connecté visible partout | Se connecter puis visiter les 4 pages | Nom et déconnexion visibles sur chaque page | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |
| T-13 | BE-02 | Onglet Admin avec un membre | Onglet Admin, compte membre | "Ce compte ne possède pas les droits administrateur." | Conforme (testé par Lass pendant le développement, 12/10) | ✅ |

## 2. Recettes

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-20 | BE-10 | Menu déroulant | Ouvrir "Recettes" dans la navigation | Les 5 recettes, avec les noms venant de la base | Conforme (testé par Lass, 12/10) | ✅ |
| T-21 | BE-10 | 6e recette ajoutée en base | Insérer une recette en SQL, recharger | Elle apparaît dans le menu sans modifier le code | 12/10 : recette "Truffes de test" insérée en SQL, elle apparaît dans le menu sans modifier le code | ✅ |
| T-22 | BE-11 | Liste des recettes | Ouvrir `?pg=recettes` | 5 cartes avec note moyenne et nombre de votes | Conforme (testé par Lass, 12/10) | ✅ |
| T-23 | BE-12 | Fiche complète | Ouvrir chaque recette | Ingrédients avec quantités, 4 étapes avec photo dans l'ordre, note moyenne, nombre de votes | Conforme (testé par Lass, 12/10) | ✅ |
| T-24 | BE-12 | Note de l'utilisateur rappelée | Noter une recette, recharger | La note personnelle est rappelée | Conforme (testé par Lass, 12/10) | ✅ |
| T-25 | BE-13 | Recette inconnue | `?pg=recette&slug=nexiste-pas` | Code 404, message convivial, lien de retour, aucune erreur technique | Conforme (testé par Lass, 12/10) | ✅ |
| T-26 | BE-13 | Slug malveillant | `?pg=recette&slug=<script>` et `?pg=recette&slug=' OR 1=1 --` | 404 propre, rien n'est exécuté | 12/10 : 404 propre pour les deux slugs, rien n'est exécuté | ✅ |
| T-27 | BE-14 | Temps total calculé | Comparer avec la base | Temps total = préparation + cuisson | 12/10 : total = préparation + cuisson pour les 5 recettes (15, 30, 40, 30, 25 min), identique au site | ✅ |
| T-28 | Bonus | Recherche | Chercher "noix", puis `<b>test</b>` | Brownies trouvés, le HTML s'affiche comme du texte | Conforme (testé par Lass, 12/10) | ✅ |
| T-29 | Section 7 | Tags tirés de la base | Ouvrir Brownies, cliquer sur le tag de catégorie puis sur un tag d'ingrédient | Catégorie puis ingrédients avec leur nombre de recettes ; chaque lien affiche autant de recettes que le nombre indiqué | 13/10 : `Gâteaux(2)` donne 2 recettes, `chocolat noir 70 %(5)` en donne 5, `cerneaux de noix(1)` en donne 1 | ✅ |
| T-29b | Bonus | Recherche par ingrédient | Chercher `cerneaux de noix`, puis `chocolat noir 70 %` | Brownies seuls, puis les 5 recettes (`%` cherché comme du texte) | 13/10 : conforme | ✅ |

## 3. Notes en étoiles

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-30 | BE-20 | Note par un visiteur (interface) | Déconnecté, ouvrir une recette | Invitation à se connecter à la place des étoiles | Conforme (testé par Lass, 12/10) | ✅ |
| T-31 | BE-20 | Note par un visiteur (requête) | curl sans session (voir les outils) | Refus, aucune ligne ajoutée dans `ratings` | 12/10 : requête sans cookie de session : 401 "Vous devez être connecté pour effectuer cette action." | ✅ |
| T-32 | BE-20 | Note falsifiée | Console : `rating` à 0, 6, `abc`, `4.5` | "La recette ou la note envoyée est invalide.", aucune écriture | 12/10 : 0, abc et 4.5 : 400 "La recette ou la note envoyée est invalide." ; 6 : 400 "La note doit se situer entre 1 et 5." ; aucune écriture | ✅ |
| T-33 | BE-21 | Noter à nouveau | Noter 3 puis 5 la même recette | Une seule ligne dans `ratings`, valeur 5 | Conforme (testé par Lass, 12/10) | ✅ |
| T-34 | BE-21 | Envois simultanés | Console : 5 `fetch` lancés en même temps | Toujours une seule ligne (index unique et `ON DUPLICATE KEY UPDATE`) | 12/10 : 5 requêtes simultanées (notes 1 à 5) : 5 réponses 200, une seule ligne en base pour l'utilisateur et la recette | ✅ |
| T-35 | BE-22 | Nouvelle moyenne renvoyée | Noter 4 | Moyenne à une décimale et nombre de votes à jour | 12/10 : 200, "Nouvelle moyenne : 4,4/5 (5 avis)" + `data.average`, `data.count` | ✅ |
| T-36 | BE-23 | Moyenne calculée | Modifier une note en SQL, recharger | La moyenne suit, rien n'est stocké à la main | 12/10 : `UPDATE ratings SET rating = 1 WHERE recipe_id = 2` : la moyenne affichée suit au rechargement | ✅ |
| T-37 | Règle | Noter sa propre recette | Connecté en admin (auteur), noter | "Vous ne pouvez pas noter votre propre recette." | Conforme (testé par Lass, 12/10) | ✅ |
| T-38 | FE-34 | Sans rechargement | Noter une recette | Moyenne et nombre d'avis mis à jour sans recharger la page | 13/10 : clic sur une étoile, moyenne et nombre d'avis mis à jour sans rechargement, message "Votre note a été enregistrée" | ✅ |

## 4. Top 3 de l'accueil

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-40 | BE-30 | Ordre du classement | Comparer l'accueil avec un `SELECT` des moyennes | Moyenne décroissante, puis nombre de votes, puis la plus récente | Conforme (testé par Lass, 12/10) | ✅ |
| T-41 | BE-30 | Moins de 3 recettes notées | `DELETE FROM ratings WHERE recipe_id > 1;` puis accueil | 3 cartes : la recette notée puis les plus récentes, aucune carte vide | 12/10 : notes supprimées sauf recette 1 : top 3 complété par les recettes les plus récentes, aucune carte vide | ✅ |
| T-42 | BE-31 | Une nouvelle note change le top | Donner plusieurs 5 à une recette hors du top, recharger | Le classement change | 12/10 : trois notes de 5 ajoutées à la recette 4 : elle passe en tête au rechargement | ✅ |

## 5. Commentaires

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-50 | BE-40 | Ordre et tranches | Recette avec plus de 10 commentaires | Du plus récent au plus ancien, 10 par tranche | 12/10 : du plus récent au plus ancien, le bouton "Voir plus" charge la tranche suivante | ✅ |
| T-51 | BE-41 | Commentaire valide | Connecté, message de 20 caractères | "Votre commentaire a bien été ajouté.", visible en tête de liste | Conforme (testé par Lass, 12/10) | ✅ |
| T-52 | BE-41 | Message trop court ou trop long | `ab`, `   ab   `, 501 caractères | "Le message doit faire entre 3 et 500 caractères." | Conforme (testé par Lass, 12/10) | ✅ |
| T-53 | BE-41 | Sujet trop long | Sujet de 121 caractères | "Le sujet ne doit pas dépasser 120 caractères." | 12/10 : 400 "Le sujet ne doit pas dépasser 120 caractères." | ✅ |
| T-54 | BE-41 | Commentaire par un visiteur | curl sans session | Refus, aucune ligne ajoutée | 12/10 : requête sans cookie de session : 401 "Vous devez être connecté pour effectuer cette action." | ✅ |
| T-55 | BE-42 | Commentaire renvoyé complet | Ajouter un commentaire en mode JSON | Réponse avec id, auteur, date et message | 12/10 : 201, `data.comment` avec id, author, message, createdAt, authorRating, canDelete + `data.rating` | ✅ |
| T-56 | BE-43 | Supprimer son commentaire | Bouton Supprimer sur son commentaire | Commentaire supprimé | Conforme (testé par Lass, 12/10) | ✅ |
| T-57 | BE-43 | Supprimer celui d'un autre (requête) | Console : `comment_id` d'un autre membre | "Vous ne pouvez pas supprimer ce commentaire.", rien n'est supprimé | 12/10 : 403 "Vous ne pouvez pas supprimer ce commentaire.", commentaire toujours en base | ✅ |
| T-58 | BE-43 | Suppression par l'admin | Admin, commentaire d'un membre | Commentaire supprimé | Conforme (testé par Lass, 12/10) | ✅ |
| T-59 | BE-44 | Limite de commentaires | 6 commentaires en moins de 10 minutes | Le 6e est refusé | 12/10 : envois 1 à 5 en 201, 6e en 429 "Vous avez envoyé trop de commentaires. Merci de patienter quelques minutes." | ✅ |
| T-60 | BE-45 | Code dans un commentaire | `<script>alert('xss')</script>` et `<img src=x onerror=alert(1)>` | Affichés comme du texte, aucune alerte | 12/10 : le code s'affiche comme du texte, aucune alerte | ✅ |
| T-61 | FE-35 | Sans rechargement | Ajouter un commentaire | Inséré sans recharger, toujours visible après actualisation | Conforme (testé par Lass, 13/10) | ✅ |
| T-62 | BE-41 | Commentaire sans note | Connecté, message valide, aucune étoile choisie | Accepté (201), aucune ligne ajoutée dans `ratings`, aucune étoile sous le commentaire | 13/10 : commentaire accepté sans note, moyenne inchangée | ✅ |
| T-63 | BE-40 | Lecture par tranches | `?pg=commentaires&slug=brownies-aux-noix&page=2` | JSON avec 10 commentaires maximum, `total`, `hasNext` | Conforme (testé par Lass, 13/10) | ✅ |
| T-64 | BE-40 | Lecture : requêtes invalides | Même adresse en POST, puis `slug=../x` | 405 puis 400 | Conforme (testé par Lass, 13/10) | ✅ |
| T-65 | Bonus | Masquer un avis (admin) | Admin, bouton Masquer | L'avis disparaît de la fiche et du compteur, reste grisé dans l'admin, Republier le remet | 13/10 : conforme | ✅ |
| T-66 | SEC-06 | Masquer par un membre | Console d'un membre : `action=status`, `status=hidden` | 403, aucun changement | 13/10 : 403 | ✅ |
| T-67 | SEC-07 | Statut inventé | Admin : `status=pending` | 400, aucun changement | 13/10 : 400 | ✅ |

## 6. Contact

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-70 | BE-50 | Message valide | Remplir correctement | "Votre message a bien été envoyé.", ligne dans `contact_messages` | Conforme (testé par Lass, 12/10) | ✅ |
| T-71 | FE-61 | Validation côté client | Champs vides ou trop courts | Messages sous les champs, rien n'est envoyé | Conforme (testé par Lass, 12/10) | ✅ |
| T-72 | BE-50 | Validation côté serveur | Désactiver le JS (ou F12), envoyer "a" partout | Refus du serveur, saisie conservée | 12/10 : JavaScript désactivé, attributs HTML retirés, "a" partout : refus du serveur avec messages d'erreur, saisie conservée | ✅ |
| T-73 | BE-50 | Anti-spam | 4 messages en moins d'une heure | Le 4e est refusé | Conforme (testé par Lass, 12/10) | ✅ |
| T-74 | SEC-03 | Sans jeton | curl POST sur `?pg=contact` sans `csrf_token` | Refus, aucune ligne ajoutée | 12/10 : POST sans `csrf_token` : 400 "La session du formulaire a expiré...", aucun message enregistré | ✅ |

## 7. Données de démonstration

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-80 | BE-60 | Contenu du script | Importer le script sur une base vide | 5 recettes, 4 catégories, au moins 20 notes, 5 comptes dont 1 admin, des commentaires | Conforme (testé par Lass, 12/10) | ✅ |
| T-81 | BE-61 | Script rejouable | Importer deux fois de suite | Aucune erreur, le site fonctionne juste après | Conforme (testé par Lass, 12/10) | ✅ |
| T-82 | 6.1 | Suppression en cascade | `DELETE FROM recipes WHERE id = 5;` | Étapes, ingrédients liés, notes et commentaires supprimés, aucune donnée orpheline | 12/10 : avant suppression de la recette 5 : 4 étapes, 4 ingrédients, 1 catégorie, 1 commentaire ; `DELETE` accepté sans erreur de clé étrangère grâce à `ON DELETE CASCADE` | ✅ |
| T-83 | 6.1 | Contraintes portées par la base | `INSERT` d'une note de 7, d'un doublon d'email, d'un commentaire de 2 caractères | Refusés par la base elle-même | 12/10 : note de 7 : `CONSTRAINT chk_ratings_value failed` ; email en double : `Duplicata ... uq_users_email` ; commentaire de 2 caractères : `CONSTRAINT chk_comments_message failed` | ✅ |
| T-84 | 6.1 | Accents et apostrophes | Commentaire "L'été, crème brûlée à 35 °C" | Affiché correctement partout | 12/10 : "L'été, crème brûlée à 35 °C" affiché correctement | ✅ |

## 8. Échanges frontend et backend

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-90 | BE-70 | Structure JSON | Requête `fetch` avec `Accept: application/json` | `{ success, message, data }` | 12/10 : structure reçue sur `noter` et `commentaire`, `data` à `null` en cas d'erreur | ✅ |
| T-91 | BE-71 | Codes HTTP | Note invalide, visiteur, commentaire d'un autre, recette inconnue | 400, 401, 403, 404 | 12/10 : 400 (note 9, message trop court, sans jeton), 403 (commentaire d'un autre), 404 (slug différent), 405 (GET), 201 et 200 en succès, 401 (déconnecté) | ✅ |
| T-92 | BE-72 | Backend sans JavaScript | Désactiver le JS, refaire T-32, T-52, T-72 | Toutes les règles restent appliquées | 12/10 : requêtes envoyées depuis la console sans passer par le JS des formulaires (note 9, message "a", sans jeton) : toutes refusées par le serveur (400). Depuis le 13/10, un commentaire sans note est accepté (note facultative, voir T-62) | ✅ |
| T-93 | BE-73 | Documentation | Lire `docs/API.md` | Chaque point d'accès est documenté | 12/10 : `docs/API.md` relu et mis à jour (format JSON, codes HTTP). 13/10 : ajout de `?pg=commentaires`, `action=status` et de la note facultative, puis des onglets admin, des routes de recettes, des tags et de la recherche par ingrédient. 14/10 : ajout de `?pg=favori` et `?pg=verification` | ✅ |

## 9. Sécurité

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-100 | SEC-01 | Injection SQL à la connexion | Email `' OR '1'='1' -- ` et mot de passe quelconque | Refus normal, aucune connexion | 12/10 : connexion refusée avec le message standard | ✅ |
| T-101 | SEC-01 | Injection SQL dans les paramètres | `?pg=recettes&recherche=' OR 1=1 --`, `recipe_id=1 OR 1=1` | Aucun effet, aucune erreur SQL affichée | 12/10 : aucune erreur, aucun contournement du filtre | ✅ |
| T-102 | SEC-02 | XSS | `<script>` dans le nom d'utilisateur, le commentaire, le contact, la recherche | Toujours affiché comme du texte | 12/10 : `<script>` dans la recherche, le contact et l'inscription : affiché comme du texte ou refusé, aucune alerte exécutée | ✅ |
| T-103 | SEC-03 | CSRF | Envoyer chaque action (note, commentaire, suppression, contact, déconnexion) sans jeton ou avec un faux jeton | Refus, aucune modification | 12/10 : faux jeton sur note, commentaire, suppression, contact et déconnexion : 400 pour les cinq, aucune écriture | ✅ |
| T-104 | SEC-04 | Mots de passe illisibles | `SELECT password_hash FROM users;` | Uniquement des hachages `$2y$...` | 12/10 : uniquement des hashs bcrypt `$2y$10$...` | ✅ |
| T-105 | SEC-05 | Identifiant renouvelé | Noter le cookie `PHPSESSID` (F12, Application), se connecter | Le cookie change à la connexion | 12/10 : `PHPSESSID` différent avant et après la connexion (`d0n59...` puis `rr719...`) | ✅ |
| T-106 | SEC-05 | Cookie protégé | F12, Application, Cookies | `HttpOnly` coché, `SameSite = Lax` | 12/10 : `PHPSESSID` en HttpOnly, SameSite Lax, cookie de session (supprimé à la fermeture du navigateur) | ✅ |
| T-107 | SEC-05 | Expiration | 30 minutes sans activité, puis recharger | Plus connecté | 12/10 : délai réduit temporairement à 60 s pour le test : après plus d'une minute d'inactivité, la page rechargée affiche l'état déconnecté ; délai remis à 1800 s | ✅ |
| T-108 | SEC-05 | Déconnexion réelle | Copier le cookie, se déconnecter, réutiliser l'ancien cookie avec curl | L'ancienne session ne fonctionne plus | 12/10 : même cookie `PHPSESSID` envoyé avec curl : connecté, 400 (session reconnue, jeton CSRF absent) ; après la déconnexion, 401 "Vous devez être connecté..." : l'ancienne session est bien détruite | ✅ |
| T-109 | SEC-06 | Page admin par un membre | Connecté en membre, `?pg=admin` | "Cette page est réservée aux administrateurs." | 12/10 : accès refusé au compte membre | ✅ |
| T-110 | SEC-07 | Données invalides | Types et longueurs incorrects sur tous les formulaires | Tous refusés côté serveur | 12/10 : couvert par T-32, T-52, T-53, T-72 et les requêtes console (types et longueurs incorrects) : tout est refusé côté serveur | ✅ |
| T-111 | SEC-08 | Secrets hors du dépôt et du web | `git ls-files` ne contient pas `config-dev.php`, et http://maison-rosalie/config-dev.php ainsi que http://maison-rosalie/data/maison_rosalie_structure.sql renvoient une erreur 404 (seul `public/` est servi) | Aucun secret exposé | 12/10 : `config-dev.php` absent de `git ls-files`, les deux adresses renvoient une 404 | ✅ |
| T-112 | SEC-09 | Compte limité | Connecté en `rosalie_app` : `DROP TABLE users;` | Refusé (droits insuffisants) | 12/10 : `ERROR 1142 : La commande 'DROP' est interdite à l'utilisateur 'rosalie_app'` | ✅ |
| T-113 | SEC-10 | Base coupée | Arrêter MariaDB dans WAMP, ouvrir le site | Message propre, aucune erreur technique ni chemin de fichier | 12/10 : MariaDB arrêté, message neutre "Le site est momentanément indisponible. Merci de réessayer plus tard.", aucune erreur technique | ✅ |
| T-114 | SEC-11 | Historique Git | `git log -p --all -S "DB_PWD"` et recherche de mots de passe | Aucun secret, même supprimé | 12/10 : `git log -p --all -S "DB_PWD"` ne trouve que le modèle `create-app-user.example.sql` (sans vrai mot de passe) ; la recherche du vrai mot de passe dans tout l'historique ne renvoie rien | ✅ |
| T-115 | SEC-12 | Mesures avant mise en ligne | Lire le rapport technique | Section présente | 12/10 : section 6 du rapport technique relue et mise à jour (en-têtes déjà en place, CSP à prévoir) | ✅ |

## 10. Administration des recettes (bonus)

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-120 | Bonus | Onglets | Admin : `?pg=admin`, puis chaque onglet | Commentaires par défaut, l'onglet actif est repéré, l'adresse change (`onglet=`) | 14/10 (requêtes scriptées, base réimportée) : les 3 onglets répondent 200, l'onglet actif est repéré | ✅ |
| T-121 | Bonus | Ajout d'une recette | Onglet Recettes, Ajouter : infos, 1 catégorie, 3 ingrédients, 4 étapes avec photo | 201, redirection vers la fiche, la recette apparaît dans le menu et la liste | 14/10 (requêtes scriptées, base réimportée) : 201, la recette apparaît dans le menu et sa fiche affiche les 4 photos d'étape | ✅ |
| T-122 | Bonus | Modification | Modifier le titre d'une étape et remplacer la photo principale | Changements visibles sur la fiche, le slug ne change pas, l'ancienne photo de `uploads/` est supprimée | 14/10 (requêtes scriptées, base réimportée) : 200, titre et étape modifiés visibles, slug inchangé | ✅ |
| T-123 | FE-66 | Image refusée | Photo de plus de 300 Ko, puis un fichier `.txt` renommé en `.jpg` | 400 avec un message clair, aucun fichier écrit | 14/10 (requêtes scriptées, base réimportée) : PNG de 5,5 Mo : 400 "Chaque image doit peser 300 Ko maximum." ; faux JPG : 400 "Les images doivent être au format WebP, JPEG ou PNG." | ✅ |
| T-124 | FE-33 | Règles de la recette | Moins de 4 étapes, puis le même ingrédient deux fois | 400 avec un message précis, rien n'est enregistré | 14/10 (requêtes scriptées, base réimportée) : 3 étapes : 400 "Une recette compte au moins 4 étapes." ; ingrédient en double : 400 avec le nom de l'ingrédient | ✅ |
| T-125 | Bonus | Suppression | Supprimer la recette de T-121 | La carte disparaît sans rechargement, notes, avis et étapes supprimés en cascade, dossier `uploads/` vidé | 14/10 (requêtes scriptées, base réimportée) : 200, la fiche renvoie 404, le dossier `uploads/recettes/{slug}` est supprimé | ✅ |
| T-126 | SEC-06 | Accès d'un membre | Membre connecté : `?pg=admin&onglet=recettes`, puis POST `action=save` depuis la console | Refus (403), aucune écriture | 14/10 (requêtes scriptées, base réimportée) : le membre ne voit pas l'onglet Recettes ; suppression forgée : 403 | ✅ |
| T-127 | SEC-03 | Sans jeton CSRF | Admin : POST `action=delete` avec un faux jeton | 400, rien n'est supprimé | 14/10 (requêtes scriptées, base réimportée) : 400, rien n'est supprimé | ✅ |

## 10 bis. Recettes favorites et impression (bonus)

| ID | Exigence | Cas testé | Étapes | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T-130 | Bonus | Cœur d'un visiteur | Déconnecté, cliquer sur le cœur d'une fiche | La fenêtre de connexion s'ouvre | 14/10 : la fenêtre de connexion s'ouvre | ✅ |
| T-131 | Bonus | Ajout et retrait | Connecté, cliquer sur le cœur, recharger, recliquer | Cœur plein sans rechargement et après actualisation, puis vide | 14/10 : conforme, message de confirmation à chaque clic | ✅ |
| T-132 | Bonus | Mes favoris | Page Recettes, filtre « Mes favoris », puis avec une catégorie | Seules les recettes enregistrées ; message clair si aucune | 14/10 : conforme | ✅ |
| T-133 | SEC-06 | Favori sans compte | `POST ?pg=favori` sans session | 401, rien n'est écrit | 14/10 : 401 | ✅ |
| T-134 | SEC-03 / SEC-07 | Requêtes forgées | Faux jeton, `favorite=2`, id d'une autre recette, `recipe_id=2 OR 1=1`, GET | 400, 400, 404, 400, 405 | 14/10 : 400, 400, 404, 400, 405 | ✅ |
| T-135 | 6.1 | Pas de doublon | Deux ajouts de suite pour la même recette | Une seule ligne dans `favorites` | 14/10 : une seule ligne | ✅ |
| T-141 | Bonus | Portions | Brownies : 12 → 6 personnes ; Chocolat chaud : 2 → 4 | 100 g de chocolat et 1,5 œuf ; 100 cl de lait, la cannelle reste « une pincée » ; − désactivé à 1 | 14/10 : conforme, utilisable au clavier | ✅ |
| T-142 | Bonus | Recherche en temps réel | Page Recettes, taper « noix », puis « crème » | Les cartes se filtrent pendant la frappe (titre et ingrédients) | 14/10 : « noix » affiche les brownies, « crème » le chocolat chaud et la glace, un mot inconnu masque toutes les cartes, sans rechargement | ✅ || T-170 | SEO | Données structurées | Code source d'une fiche, puis le test des résultats enrichis de Google une fois en ligne | Bloc JSON-LD `Recipe` valide : temps, portions, ingrédients, 4 étapes avec photo, note moyenne | 14/10 : JSON-LD valide en local (Recipe, BreadcrumbList) ; test Google à faire en ligne | ✅ |
| T-171 | SEO | Plan du site | Ouvrir `?pg=sitemap`, puis ajouter une recette depuis l'admin | XML avec les 5 pages et une adresse par recette ; la nouvelle recette apparaît | 14/10 : 10 adresses, générées depuis la base | ✅ |
| T-172 | SEO | Canonique et partage | Code source de l'accueil, d'une fiche et de `?pg=recettes&categorie=gateaux` | `canonical` sans filtres, `og:title`, `og:description`, `og:image` en JPG ou WebP | 14/10 : conforme ; admin et 404 sans canonique, en `noindex` | ✅ |
| T-173 | SEC-08 | Modèles de configuration | `git ls-files` | `config-dev-exemple.php` et `config-prod-exemple.php` présents, sans aucun secret ; `config-dev.php` et `config-prod.php` absents | 14/10 : seuls les deux modèles sont suivis, les vrais fichiers de config sont ignorés | ✅ || T-160 | Bonus | E-mail de confirmation | S'inscrire avec une vraie adresse (SMTP configuré) | E-mail reçu avec un bouton « Confirmer mon adresse e-mail » | 14/10 : e-mail reçu (en indésirables, voir rapport) | ✅ |
| T-161 | Bonus | Connexion avant confirmation | Se connecter sans avoir cliqué | 403 « Confirmez d'abord votre adresse e-mail », nouveau lien envoyé | 14/10 : 403, second e-mail reçu, nouveau lien différent | ✅ |
| T-162 | Bonus | Lien de confirmation | Cliquer le lien | « Adresse confirmée », fenêtre de connexion ouverte, connexion acceptée | 14/10 : conforme | ✅ |
| T-163 | SEC-07 | Liens invalides | Ancien lien remplacé, lien déjà utilisé, `token=abc` | « Lien invalide ou expiré », rien n'est confirmé | 14/10 : conforme pour les trois cas | ✅ |
| T-164 | BE-61 | Sans SMTP | Supprimer les constantes `MAIL_*`, s'inscrire puis se connecter | Compte créé et connexion directe, aucun e-mail | 14/10 : 201 puis 200 | ✅ |
| T-165 | Bonus | Accusé de réception du contact | Envoyer un message valide (SMTP configuré) | E-mail reçu avec le sujet et le message | 14/10 : conforme | ✅ |
| T-166 | QC-05 | SMTP injoignable | Mettre un mauvais port SMTP, s'inscrire et écrire via le contact | Compte et message enregistrés, aucune erreur visible | 14/10 : 201 et message enregistré, erreur écrite dans le journal | ✅ |
| T-150 | FE-68 | HTML valide | Validateur W3C (Nu Html Checker) sur 15 pages : 8 pages visiteur (dont 404 et recherche), 2 pages membre, 5 pages admin | Aucune erreur | 14/10 : 0 erreur sur les 15 pages | ✅ |
| T-136 | Bonus | Lien Mon compte | Visiteur, puis connexion d'un membre sans rechargement, puis déconnexion | Lien absent, puis visible dans le menu, puis masqué | 14/10 : conforme, sans rechargement | ✅ |
| T-137 | Bonus | Onglets de Mon compte | Membre : `?pg=mon-compte`, puis Mes notes et Mes avis | Favoris en cartes, notes avec étoiles et lien vers la recette, avis avec leur statut, compteurs justes | 14/10 : 2 favoris, 5 notes, 2 avis pour marie | ✅ |
| T-138 | SEC-06 | Mon compte sans session | `GET ?pg=mon-compte` déconnecté, puis en JSON | Retour à l'accueil avec un message, 401 en JSON | 14/10 : 302 puis 401 | ✅ |
| T-139 | SEC-06 | Lien Administration | Menu d'un membre, puis d'un administrateur | Absent pour le membre, présent et repéré comme actif pour l'admin ; `?pg=admin` reste refusé au membre (403) | 14/10 : conforme | ✅ |
| T-140 | Bonus | Impression d'une fiche | Bouton « Imprimer » sur chaque recette | Une page A4 propre : en-tête, ingrédients, étapes avec vignettes, sans navigation ni commentaires | 14/10 : les 5 recettes tiennent sur une page | ✅ |

## 11. Scénarios de la recette finale (section 13)

| N° | Scénario | Tests liés | Résultat obtenu | Statut |
|---|---|---|---|---|
| 1 | Un visiteur parcourt les 4 pages et ouvre les 5 recettes depuis le menu | T-20, T-23 | 14/10 (requêtes scriptées, base réimportée) : les 5 pages répondent 200, les 5 recettes du menu s'ouvrent | ✅ |
| 2 | Une 6e recette ajoutée en base apparaît dans le menu | T-21 | 14/10 (requêtes scriptées, base réimportée) : une recette ajoutée depuis l'admin apparaît dans le menu sans modifier le code | ✅ |
| 3 | Chaque recette affiche ingrédients, étapes avec photos, temps et difficulté | T-23, T-27 | 14/10 (requêtes scriptées, base réimportée) : les 5 fiches affichent ingrédients, étapes avec photo, temps et difficulté | ✅ |
| 4 | Un visiteur ne peut ni noter ni commenter (interface et requête) | T-30, T-31, T-54 | 14/10 (requêtes scriptées, base réimportée) : note et commentaire envoyés sans session : 401 | ✅ |
| 5 | Inscription invalide ou email déjà pris refusés avec des messages clairs | T-02 à T-06 | 14/10 (requêtes scriptées, base réimportée) : mots de passe différents : 400 ; email déjà pris : 400 "Cet email est déjà utilisé." | ✅ |
| 6 | Après connexion, une note de 4 met à jour la moyenne sans rechargement | T-35, T-38 | 13/10 : mise à jour sans rechargement (T-38) ; 14/10 : 200 avec la nouvelle moyenne | ✅ |
| 7 | Noter à nouveau remplace la note | T-33 | 14/10 (requêtes scriptées, base réimportée) : note 4 puis 5 : le nombre de votes ne change pas | ✅ |
| 8 | Note falsifiée (0, 6, texte) refusée | T-32 | 14/10 (requêtes scriptées, base réimportée) : 0, 6, `abc` et `4.5` : 400 | ✅ |
| 9 | Le top 3 change quand les notes le justifient | T-42 | 14/10 (requêtes scriptées, base réimportée) : 4 notes de 5 sur le chocolat chaud, il passe de hors classement à n°1 au rechargement de l'accueil | ✅ || 10 | Un commentaire apparaît sans rechargement et reste après actualisation | T-61 | 14/10 (requêtes scriptées, base réimportée) : 201, toujours visible après rechargement | ✅ |
| 11 | Un commentaire avec du HTML ou du JavaScript s'affiche comme du texte | T-60 | 14/10 (requêtes scriptées, base réimportée) : `<script>` affiché comme du texte | ✅ |
| 12 | Impossible de supprimer le commentaire d'un autre, même par requête | T-57 | 14/10 (requêtes scriptées, base réimportée) : suppression du commentaire d'un autre : 403 | ✅ |
| 13 | Le contact refuse les données invalides (client et serveur) et enregistre un message valide | T-70 à T-72 | 14/10 (requêtes scriptées, base réimportée) : données invalides refusées par le serveur avec un message, message valide enregistré en base | ✅ |
| 14 | Les injections SQL n'ont aucun effet | T-100, T-101 | 14/10 (requêtes scriptées, base réimportée) : connexion avec `' OR '1'='1' -- ` : 401 ; recherche `' OR 1=1 --` : aucune erreur | ✅ |
| 15 | Site utilisable à 360 px de large | Vérification visuelle de chaque page (F12, mode responsive) | 14/10 (requêtes scriptées, base réimportée) : aucun défilement horizontal à 360 px sur les 6 pages | ✅ |
| 16 | Base coupée : message propre | T-113 | 14/10 (requêtes scriptées, base réimportée) : MariaDB arrêtée : 503 "Le site est momentanément indisponible." sur les pages et en JSON, aucune erreur technique | ✅ |
| 17 | Base vide + script + README suffisent | T-80, T-81 et installation complète en suivant le README | 14/10 (requêtes scriptées, base réimportée) : le script s'importe deux fois de suite sans erreur (5 recettes, 20 notes, 5 comptes, 8 commentaires) et le site fonctionne | ✅ |