# La Maison Rosalie

## Point de départ

Après une lecture individuelle puis collective du cahier des charges, nous avons défini l’architecture du projet, conçu le modèle de la base de données et choisi les technologies à utiliser.

Nous avons réalisé le modèle avec MySQL Workbench, puis importé le script SQL dans MariaDB.

Nous avons ensuite rencontré l’équipe de designers pour découvrir les deux propositions de maquettes qui seront présentées au client.

## Choix de la base de données

Nous avons choisi MariaDB, un système de gestion de base de données relationnelle libre et gratuit.

Il fonctionne avec PHP via PDO et utilise SQL, que nous avons étudié en formation. Ses clés étrangères et ses contraintes permettent de garantir la cohérence des relations entre les recettes, les ingrédients, les utilisateurs et les notes.

MariaDB est également disponible dans notre environnement de développement WampServer.

## Relations et droits des utilisateurs

### Relations entre les données

- Un utilisateur peut être l’auteur de plusieurs recettes.
- Un utilisateur peut être l’auteur de plusieurs commentaires.
- Un utilisateur peut noter plusieurs recettes, avec une seule note par recette.

### Droits envisagés — à confirmer avec le formateur

- Un utilisateur peut masquer ses propres commentaires.
- Un utilisateur ne peut pas noter sa propre recette.
- Un administrateur peut supprimer ou modifier les commentaires et les recettes.

Ces règles complémentaires doivent être distinguées des exigences obligatoires du cahier des charges, qui prévoient notamment la suppression d’un commentaire par son auteur ou par un administrateur.

## Notes et changements

### 25 septembre 2026

Après confirmation du formateur, le scénario 17 de la section 13,
« Scénarios de recette finale », est retiré :

> ~~Sur une base vide, le script d’import et le README suffisent à obtenir un site fonctionnel.~~

L’exigence BE-73 concernant la documentation de l’API reste à clarifier.
