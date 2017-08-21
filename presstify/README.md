# PresstiFy - Accélerateur de développement basé sur Wordpress

## Présentation

### Origines du projet

PresstiFy est un Framework d’entreprise développé à l’origine par la société Milkcreation, aujourd’hui devenue Tigre Blanc Digital.

Comme la plupart des Frameworks d’entreprise, il a d’abord été développé pour répondre à des besoins clients, là où les produits du marché ne permettait pas ou partiellement de traiter certaines des problématiques projets.

Depuis il a permis de produire plus d’une centaine de sites internet et/ou applications web; sans avoir recourt à la moindre extension tierce (plugins).

### Nature du projet

PresstiFy est développé en PHP orienté objet. Dans sa version originelle, il permettait de produire des projets web sous environnement WordPress. Il est à présent enrichi d’une version Standalone permettant de réaliser des applications totalement indépendante de l’interface d'administration WordPress.

### Concept

PresstiFy a été développé en respectant les spécifications d'écriture induite par PHPFig.
Son code est dès lors documenté selon les preconisations PHPdoc.

Le but de la solution PresstiFy est d'offrir un outil à la fois simple à prendre en main et complet en terme de possibilités.
Il s'adresse aussi bien aux administrateurs de site, aux intégrateurs, aux développeurs plus ou moins chevronnés.

## Principes de fonctionnement

### Les composants du coeur (core)

Il s'agit des composants natifs de presstiFy. Ils peuvent être nécessaires au fonctionnement de la solution. Actif dès le démarrage, ils ne nécessite aucune activation.

### Les composants dynamiques (components)

Inclues dans le dépôt de PresstiFy, il s'agit de fonctionnalités complémentaires non requises. Ils requièrent une activation.

### Les extensions (plugins)

Non incluses dans le dépôt de PresstiFy, il s'agit de solutions complètes répondant à un besoin standardisé de l'application ou du site web (Newsletter, Gestion d'événement, Référencement ...).

### Les jeux de fonctionnalités (set)

Ensemble de fonctionnalités du coeur, des composants dynamiques ou des extensions répondant à un besoin spécifique. Bien que très similaire, il s'agit d'une alternative simple aux extensions. Leur structuration est moins restrictive et plus rapide à mettre en oeuvre. Ils sont le plus généralement directement intégré au thème.
 
## Installation de PresstiFy pour Wordpress

### METHODE 1 - Téléchargement depuis presstify.com


### METHODE 2 - Depuis le dépôt SVN

Rendez-vous dans le repertoire d'installation de presstiFy de votre site
%racine du site%/wp-content/mu-plugins/presstify

```bash
$ cd %racine du site%/wp-content
$ mkdir -p mu-plugins/presstify
$ cd mu-plugins/presstify
$ svn export http://svn.milkcreation.fr/presstify/core/trunk ./ --force
```