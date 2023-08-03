# Placement de bouées de régate radiocommandées

## Présentation
Le positionnement de bouées de régates de voiliers radiocommandés est un projet initié en février 2023 entre l'ARBL (Association Radiomodéliste des Bords de Loire) et l'ICAM de Nantes.

Il consiste à proposer une ensemble logiciel et matériel permettant de positionner par radiocommande puis de maintenir en place une constellation de bouées asservies chacune à une position GPS.

L'application **Web** :  *placerbouees/index.html* est une composante de ce projet. 

### Développement: JF en javascript et PHP.
(cc) jean.fruitet@free.fr

La page web *./placerbouees/index.html* permet d'assigner des positions GPS à des balises mobiles autonomes sur une carte OpenStreetMap.

## Ecran
### Partie supérieure
Sélection d'un site de navigation et chargement des données afférentes.

Les fichiers json des sites disponibles sont placés dans le dossier ./json/

### Partie droite 
Affichage sur une carte OpenStreetmap du plan d'eau sélectionné, du périmètre de navigation, de la zone de déambulation des concurrents et, éventuellement, des bouées ancrées (bouées fixes, à demeure).

### Partie gauche  
Pointage à la souris des bouées du parcours d'une régate, en s'appuyant aussi bien sur les bouées ancrées (fixes) que sur des bouées mobiles (des balises autonomes ancrées virtuellement).

## Fonctionnalités
Après positionnement à la souris d'un jeu de bouées organisées en *ligne de départ*, *ligne d'arrivée*, *porte* et *dog leg* sur la partie gauche de l'écran, les bouées sont affichées sur la carte et leurs coordonnées GPS (longitude, latitude) 
transmises au serveur pour ^tre mises à disposition de l'application de pilotage pour smartphone **RoBoNav**.

## Interface
- Boutons 
  - *TWD* : Saisie de la direction d'où soufle le vent (entrer la valeur TWD en degrés) ; cliquer sur le bouton "**Soumettre**" ;
  - *Zoom* : "+", "-", "1": remise à l'échelle 1 ;
  - Bouton "**Bouées**" : Saisie des positions à la souris;
- Légendes
  1. Tracé d'une flèche indiquant la **direction vers laquelle soufle le vent**, *TWD* : true wind direction, en  ° ;
  2. "**Bouées fixes**" telles que lues dans le fichier geojson attaché au plan d'eau du Plessis ;
  3. **Balises mobiles** :
    * Bouées de départ en jaune
    * Bouée d'arrivée en bleu
    * Bouée de dog leg en noir
    * Bouée d'une porte en violet
      
Les bouées placées sont surmontées d'un drapeau de couleur verte (bouée à laisser à tribord) ou rouge (bouée à laisser à bâbord).

## Connexion avec un serveur externe

Le bouton "**Transmettre**" envoie à un serveur externe les coordonnées géographiques (Longitude, Latitude) des bouées et balises retenues pour constituer le parcours ; ces coordonnées seront disponibles pour l'applet **RoBoNav** de pilotage des balises autonomes.

### Fonctionnement du serveur
Il y a quelques conditions au fonctionnement du serveur :
  1. La connexion Web doit être activée (ou les *Données mobiles* activées)
  2. Le serveur doit être activé.
  3. Dans la version actuelle l'URL du serveur est codée en dur dans le code source.
  
## Ce qui reste à faire
- Intérfacer l'application **PlacerBouees** au projet **RoBoNav* de positionnement et de pilotage de bouées de régate avec ancrage virtuel par GPS;

### Difficultés rencontrées
Il m'a fallut reprendre complètement mes notions de javascript, de canvas, de création de cartes et l'ajout de markers et autres éléments graphiques.

Je me suis appuyé sur l'excellente librairie javascript **LeafLet** pour la création des cartes **OpenStreetMap"", ainsi que sur les dizaines de contributions disponibles en ligne sur les forums ad hoc.

L'adaptation à une langue différente du français ne me paraît pas trop compliquée...

## Edition des sites

Pour ajouter de nouveaux sites, modifier sous éditeur de texte type PSPad ou Notepad++ le fichier *./json/plans_eau_robonav.xml*.

Puis ajouter dans le dossier *./json/* un fichier <*nomdusite.json*> sur le modèle du fichier <*leplessis.json*>.

Le plus efficace est de saisir la zone de navigation (polygone), le chemin des concurrents (Linestring continue) et les éventuellement les pontons et bouées fixes, 
directement avec l'éditeur en ligne **geojson.io** ; recopier ensuite ces *scripts geojson* dans le fichier <*nomdusite.json*> sous éditeur texte. 

## Sources
```
./placerbouees
  index.html

./js
    ajax.js
    bouees.js
    canavas.js
    geo_utils.js
    iconse.js
    maps.js
    myscript.js
    sitnenavigation.js   

./css
  style.css
  materiel.css
  material-icons.css

./data

./doc
  Captures d'écran
  
./php
    getsite.php
    placer-bouees.php
    plans_eau.php
    sauverbouees.php
./images

./json
    leplessis.json
    laminais.json
    laplageverte.json
    planeauduchene.json
    boisjoalland.json
    plans-eau_robonav.xml

```
  
## Data
Les données produites sont placées dans le dossier du serveur *./data/*
Elles consistent, pour chaque site et chaque orentation du vent en une liste des bouées et leur position GPS, stockées dans des fichiers .json
sur le format <*leplessis-45_20230803.json*> pour l'étang du Plessis et un vent de direction 45 °
   
## Liens
MIT App Inventor http://ai2.appinventor.mit.edu/

GeoJSON https://geojson.io/

JSON Editor OnLine https://jsoneditoronline.org/

Leaflet https://leafletjs.com/

Umap https://umap.openstreetmap.fr/fr/

## License
Pour le code source : **MIT** *Free Software, Hell Yeah!* https://github.com/pandao/editor.md/blob/master/LICENSE

Pour les documents : **Creative Commons** http://creativecommons.org/licenses/by-sa/4.0/
