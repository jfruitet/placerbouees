# Placement automatique de bouées autonomes de régates de voiliers radiocommandés

### Développement

Par JF en PHP. (cc) jean.fruitet@free.fr

## Présentation

Les scripts PHP suivants sont appelés par des appels Ajax les pages charger.html et placerbouees.html de la racine du site.
On peut aussi les appeler directement avec les paramètres ad hoc pour des motifs de débogage.
  
Le script *./placerbouees/php/setsite.php* génère automatiquement pour un site passé en paramètre les fichiers de placement des bouées mobile avec un incrément de 15° de la rose des vents de 0° à 360°.
En entrée ce script prend le nom du site de navigation.

Le script *./placerbouees/php/placer_bouees.php* est la version de débogage de calcul automatique des placements.
En entrée ce dernier prend le nom du site de navigation, la direction du vent, le nombre de bouées à placer.

En sortie ces scripts enregistrent les positions de *n* bouées mobiles sur le plan d'eau ciblé dans des fichier json du dossier **../data**.

Si des bouées fixes sont mobilisables elles le sont, mais cette partie du programme nécessite d'être grandement améliorée.
 

## Utilisation

- Pour toutes les directions de la rose des vents (par pas de de 15°) :
*./placerbouees/php/setsite.php?site=NOMduSite*

- ou pour une seule valeur de direction du vent :
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

https://docs.google.com/document/d/18wlelZs5Ibvc7WwD4XAqUXw9yKVFULB9p6GBVvMNWLg/edit?usp=sharing

Il consiste, après une rotation ramenant le site de navigation "face au nord" pour faciliter les calculs,
à trouver un rectangle vertical contenu dans le polygone de navigation "au plus près" de la ligne de déplacement
des concurrents et de hauteur supérieure à un seuil donné (50 mètres) et de largeur supérieure à 10 mètres.

Les nMax bouées à placer sont alors positionnées par couple en s'appuyant sur les bouées fixes du plan d'eau pour constituer
- une ligne de départ
- un dog leg au vent
- une porte sous le vent
- une ligne d'arrivée (confondue avec la ligne de départ).

Les coordonnées (longitude, latitudes) des bouées placées sont ensuite enregistrées dans le fichier de sortie.

Dans la version actuelle le placement proposé n'est absolument pas optimal. On en jugera en utilisant la page web *.placerbouees/chargerbouees.html*

Le programme recherche le placement "au plus proche" de la polyligne de circulation des concurrents, mais cette information n'est pas suffisante pour un placement optimal.
Il faudrait pouvoir jouter des informations de contexte, par exemple des zones privilégiées quand le plan d'eau est manifestement trop grand ou l'orientation du vent incompatible avec la circulation des concurrents.

Pour rendre l'algorithme un peu plus efficient il faudrait ajouter des informations de contexte, par exemple la zone de navigation préférentielle 
si le plan d'eau est vaste, ou des zones d'exclusion. 

On pourrait aussi indiquer au programme que la distance maximale de la rive à la bouée la plus éloignée ne doit pas excéder un certain seuil...
     
### Serveur
Un serveur httpd exécute ce code PHP.

Il y a quelques conditions au fonctionnement du serveur :
  1. La connexion Web doit être activée (ou les *Données mobiles* activées)
  2. Le serveur doit être activé.
  3. Dans la version actuelle l'URL du serveur est celle codée en dur dans le code source du script *./php/include/config.php*.


### Sources
```
./placerbouees
    index.html
    chargerbouees.html
	placerbouees.html
	README.md
    
./js
    ajax.js
    ajax2.js
	ajax3.js
    bouees.js
    canavas.js
    config.js
    geo_utils.js
    iconse.js
    maps.js
    maps2.js
    myscript.js
    sitnenavigation.js   

./css
  style.css
  materiel.css
  material-icons.css

./data
    Fichiers .json de positionnement de bouées mobiles

./doc
  Captures d'écran

./php
    getdata.php 
	getsite.php
	index.php
    placer_bouees.php
    plans_eau.php
    sauverbouees.php
	setsite.php
	README.md
	./include
		config.php
		saisie.php
		geo_utils.php
		algo.php
		initial.php
		index.php
		
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
#### Output : Placement des bouées

Il y a deux façons de placer des bouées de parcours :
 1. A la main avec l'éditeur
 2. Avec des script php *./placerbouees/php/setsite.php* et *./placerbouees/php/placer_bouees.php*  implantant l'algorithme proposé dans la documentation
    
Les données produites sont stockées dans le dossier *.placerbouees/data/* du serveur.

Elles consistent, pour chaque site et chaque orentation du vent, en une liste des bouées 
et de leurs positions GPS, stockées dans des fichiers <*robonav_NonPlanEau_twd_aammdd_auto.json*>, par exemple
<*robonav_LePlessis_45_20230803_auto.json*>, pour l'étang du Plessis et un vent de direction 45°

Les positionnements effectués à la souris ou corrigés ont le nom <*robonav_NonPlanEau_twd_aammdd.json*>

## Installation
- Recopier l'ensemble des sources dans un dossier ./placerbouees/php de votre serveur httpd
- Modifier le contenu du fichier ./js/config.php pour indiquer l'adresse du serveur.

### Ce qui reste à faire

 1. Proposer plusieurs "solutions" de placement automatique pour une direction de vent données ;
 2. Proposer des parcours autres que DogLeg au vent + porte sous le vent (parcours type IOM avec 6 bouées) ;

   
## Droits d'utilisation et de modification (License)
Tout ce projet est en Open source.

Pour le code source : **MIT** *Free Software, Hell Yeah!* https://github.com/pandao/editor.md/blob/master/LICENSE

Pour les documents : **Creative Commons** http://creativecommons.org/licenses/by-sa/4.0/
