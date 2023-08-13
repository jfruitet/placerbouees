# Placement automatique de bouées autonomes de régates de voiliers radiocommandés

### Développement
Par JF en PHP. (cc) jean.fruitet@free.fr

## Présentation
Le script **placer_bouees.php** se substitue à la page web **placerbouees/index.html** pour
enregistrer de façon automatique les positions optimales de *n* bouées mobiles sur un plan d'eau
de régates virtuelles. 

### Input
- TWD : direction du vent en degrés
- Nom du site (le fichier nomdusite.json doit être présent dans le dossier *./placerbouees/json/*)
- nbouees : nombre maximal de bouées autonomes disponibles. 

### Output
Un fichier *./placerbouees/data/robonav_NomDuSite_TWD_aaaammdd.json* qui peut être affiché par la page Web
*./placerbouees/chargerbouees.html*

## Algorithme
L'agorithme est décrit dans le GoogleDoc du projet.

Il consiste, après une rotation ramenant le site de navigation "face au nord" pour faciliter les calculs,
à trouver un rectangle vertical contenu dans le polygone de navigation "au plus près" de la ligne de déplacement
des concurrents et de hauteur supérieure à un seuil donné (50 mètres) et de largeur supérieure à 20 mètres.

Les nMax bouées à placer sont alors positinnées en s'appuyant sur les bouées fixes du plan d'eau pour constituer
- un ligne de départ
- un dog leg au vent
- une porte sous le vent
- une ligne d'arrivée (confodue avec la ligne de départ).

Les coordonnées (longitude, lattitudes) des bouées placées sont ensuite enregistrées dans le fichier de sortie.
     
## Serveur
Un serveur externe exécute ce code PHP.

Il y a quelques conditions au fonctionnement du serveur :
  1. La connexion Web doit être activée (ou les *Données mobiles* activées)
  2. Le serveur doit être activé.
  3. Dans la version actuelle l'URL du serveur est celle codée en dur dans le code source du script *./js/config.js*.


## Sources
```
./php
    placer_bouees.php
    ./php/include
        geo_utils.php
        algo.php

./data :: Output 
    Fichiers .json de positionnement de bouées mobiles

./doc
  Captures d'écran
  
./json  :: Input
    // Données .json des sites de navigation (zonz d'évolution, concurrents, bouées fixes) 
    leplessis.json
    laminais.json
    laplageverte.json
    planeauduchene.json
    boisjoalland.json
    plans-eau_robonav.xml

```

  
## Data
### Output : Placer bouées
Les données produites par *./placrbouees/index.html* sont placées dans le dossier du serveur *./data/*

Elles consistent, pour chaque site et chaque orentation du vent en une liste des bouées 
et de leurs positions GPS, stockées dans des fichiers <*robonav_NonPlanEau_twd_aammdd.json*>, par exemple
<*robonav_LePlessis_45_20230803.json*>, pour l'étang du Plessis et un vent de direction 45°

## Installation
- Recopier l'ensemble des sources dans un dossier ./placerbouees/php de votre serveur httpd
- Modifier le contenu du fichier ./js/config.js pour indiquer l'adresse du serveur.
   
## Droits d'utilisation et de modification (License)
Tous ce projet est en Open source.

Pour le code source : **MIT** *Free Software, Hell Yeah!* https://github.com/pandao/editor.md/blob/master/LICENSE

Pour les documents : **Creative Commons** http://creativecommons.org/licenses/by-sa/4.0/
