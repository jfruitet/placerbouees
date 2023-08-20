# Placement automatique de bouées autonomes de régates de voiliers radiocommandés

### Développement

Par JF en PHP. (cc) jean.fruitet@free.fr

## Présentation
Le script *./placerbouees/php/placer_bouees.php* est la version automatique de l'éditeur de placement web **placerbouees/index.html**.

En entrée il prend le nom du site de navigation, la direction du vent, le nombre de bouées à placer.
En sortie le script enregistre les positions de *n* bouées mobiles sur le plan d'eau ciblé.

Si des bouées fixes sont mobilisables elles le sont, mais cette partie du programme nécessite d'être améliorée.
 

## Utilisation

*./placerbouees/php/placer_bouees.php?site=NOMduSite&twd=60&nbouees=4*

Par exemple pour le site du Plessis :

*http://localhost/placerbouees/php/placer_bouees.php?site=LePlessis&twd=60&nbouees=4*


### Input
- TWD : direction du vent en degrés
- Nom du site (le fichier *nomdusite.json* doit être présent dans le dossier *./placerbouees/json/*)
- nbouees : nombre maximal de bouées autonomes disponibles. 

### Output
Un fichier *./placerbouees/data/robonav_NomDuSite_TWD_aaaammdd_auto.json* qui peut être affiché par la page Web
*./placerbouees/chargerbouees.html*

## Algorithme
L'agorithme est décrit dans le GoogleDoc du projet.

Il consiste, après une rotation ramenant le site de navigation "face au nord" pour faciliter les calculs,
à trouver un rectangle vertical contenu dans le polygone de navigation "au plus près" de la ligne de déplacement
des concurrents et de hauteur supérieure à un seuil donné (50 mètres) et de largeur supérieure à 10 mètres.

Les nMax bouées à placer sont alors positionnées en s'appuyant sur les bouées fixes du plan d'eau pour constituer
- une ligne de départ
- un dog leg au vent
- une porte sous le vent
- une ligne d'arrivée (confondue avec la ligne de départ).

Les coordonnées (longitude, latitudes) des bouées placées sont ensuite enregistrées dans le fichier de sortie.

Dans la version actuelle le placement proposé n'est absolument pas optimal. On en jugera en utilisant la page web *.:placerbouees/chargerbouees.html* 

Pour rendre l'algorithme un peu plus efficient il faudrait ajouter des informations de contexte, par exemple la zone de navigation préférentielle si le plan d'eau est vaste. 
Le programme ne teint pas vraiment compte non plus de la circulation des concurrents, bine que ces informations soient disponibles dans les données importées.

A suivre...
     
### Serveur
Un serveur httpd exécute ce code PHP.

Il y a quelques conditions au fonctionnement du serveur :
  1. La connexion Web doit être activée (ou les *Données mobiles* activées)
  2. Le serveur doit être activé.
  3. Dans la version actuelle l'URL du serveur est celle codée en dur dans le code source du script *./php/include/config.php*.


### Sources
```
./php
    placer_bouees.php
    ./php/include
		config.php
        saisie.php
		geo_utils.php
        algo.php

./data :: Output 
    Fichiers .json de positionnement de bouées mobiles

./doc
  Captures d'écran
  
./json  :: Input
    // Données .json des sites de navigation (zone d'évolution, concurrents, bouées fixes) 
    leplessis.json
    laminais.json
    laplageverte.json
    plandeaudelemprunt.json
	planeauduchêne.json
    etangduboisjoalland.json
	// Liste des sites de navigation disponibles
    plans_eau_robonav.xml

```

  
### Data
#### Output : Placer bouées
Les données produites par *./placerbouees/php/placer_bouees.php* sont stockées dans le dossier *.placerbouees/data/* du serveur

Elles consistent, pour chaque site et chaque orentation du vent, en une liste des bouées 
et de leurs positions GPS, stockées dans des fichiers <*robonav_NonPlanEau_twd_aammdd.json*>, par exemple
<*robonav_LePlessis_45_20230803_auto.json*>, pour l'étang du Plessis et un vent de direction 45°

## Installation
- Recopier l'ensemble des sources dans un dossier ./placerbouees/php de votre serveur httpd
- Modifier le contenu du fichier ./js/config.php pour indiquer l'adresse du serveur.

### Ce qui reste à faire

Automatiser pour tous les sites disponibles et toutes les valeurs de la rose des vents...
   
## Droits d'utilisation et de modification (License)
Tous ce projet est en Open source.

Pour le code source : **MIT** *Free Software, Hell Yeah!* https://github.com/pandao/editor.md/blob/master/LICENSE

Pour les documents : **Creative Commons** http://creativecommons.org/licenses/by-sa/4.0/
