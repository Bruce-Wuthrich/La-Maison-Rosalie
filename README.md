# La-Maison-Rosalie

## Point de départ

Le modèle et les 10 tables ont été créés ; l'import MariaDB fonctionne d'après les essais de bruce et Bryan. L'arborescence existe. Ces résultats ne signifient pas encore que T03 (toutes les contraintes) et T06 (import avec données, rejouable) sont terminés. Le dernier ZIP examiné contenait des fichiers PHP vides ; vérifier les changements de chaque membre avant de répartir le travail.

Stack de préparation : PHP objet sans framework, PDO, MariaDB, HTML5, Bootstrap 5, CSS et JavaScript natif. Faire confirmer le choix objet par l'équipe et l'inscrire au README. Les maquettes sont fournies par les designers selon votre organisation : coordonner T04/T05 avec eux.


## Choix de DB 

Notre choix c'est porté sur MariaDB pour plusieur raison : 
- elle est gratuite
- elle fonctionne très bien avec PHP (Fonctionnement du backend uniquement en PHP)
- elle utilise le langage SQL qui est le langage utilisé pour la base de donnée de ce projet
- elle permet de protéger les données avec des utilisateurs et des mots de passe
- elle est majoritairement utilisé en Europe

## Choix des liaisons de la base de donnée
En résumé :

- Utilisateur:
- peut écrire plusieurs recettes
- peut écrire plusieurs commentaires et les cacher
- peut noter les recettes disponibles sauf la sienne 
- 




- Admin
-  peut supprimer ou modifier les commentaires et recettes. 

