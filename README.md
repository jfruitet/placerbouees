# Placement de bouées de régate radiocommandées
Positionnement manuel de bouées pour les régates de voliers radiocommandés.

Application **Web** :  *placerbouees/index.html* 

### Développée par JF en javascript et PHP.
(cc) jean.fruitet@free.fr

Cette page web permet d'assigner des positions GPS à des balises mobiles autonomes sur une carte OpenStreetMap de l'étang du Plessis à Sainte Luce / Loire, 44980.

Elle est un composant logiciel du projet RoBoNav mené depuis février 2023 avec l'ICAM de Nantes.

## Ecran
### Partie supérieure
Sélection d'un site de navigation et chargement des données.

Les fichiers json des sites disponibles sont placés dans le dossier ./data/

### Partie droite 
Affichage de la carte du plan d'eau du Plessis avec le périmètre de navigation , la zone des concurrents et les bouées ancrées (bouées fixes, à demeure).

### Partie gauche  
Pointage à la souris des bouées du parcours d'une régate, en utilisant aussi bien  les bouées ancrées (fixes) que des bouées mobiles (des balises autonomes ancrées virtuellement).

## Fonctionnalités
Affichage sur une carte OpenStreetMap de la partie de l'étang du Plessis réservée au modélisme, de la zone réservée aux concurrents et des bouées fixes. 
Après positionnement d'un jeu de bouées mobiles à la souris sur la partie gauche de l'écran, les bouées sont affichées sur la carte et leurs coordonnées GPS (longitude, latitude) sont envoyées au serveur pour transmission à RoBoNav.

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
      
Les bouées mobiles sont surmontées d'un drapeau de couleur verte (bouée à laisser à tribord) ou rouge (bouée à laisser à bâbord).

## Connexion avec un serveur externe

Le bouton "**Transmettre**" envoie à un serveur externe les coordonnées géographiques (Longitude, Latitude) des bouées et balises retenues pour constituer le parcours ; ces coordonnées seront disponibles pour l'applet **RoBoNav** de pilotage des balises autonomes.

Il y a quelques conditions au fonctionnement du serveur :
  1. La connexion Web doit être activée (ou les *Données mobiles* activées)
  2. Le serveur doit être activé.
  3. Dans la version actuelle l'URL du serveur est codée en dur dans le code source.
  
## Ce qui reste à faire
- Intégrer l'application au projet **RoBoNav* de positionnement et de pilotage de bouées de régate avec ancrage virtuel par GPS

## Difficultés rencontrées
Il m'a fallut reprendre complètement mes notions de javascript, de canvas, de création de cartes et l'ajout de markers et autres éléments graphiques.

Je me suis appuyé sur l'excellente librairie javascript **LeafLet** pour la création des cartes **OpenStreetMap"", ainsi que sur les dizaines de contributions disponibles en ligne sur les forums ad hoc.

L'adaptation à une langue différente du français ne me paraît pas trop compliquée...

## Edition des sites

Pour ajouter de nouveaux sites, modifier sous éditeur texte le fichier ./data/plans_eau_robonav.xml.

Puis ajouter dans ./data un fichier <*nomdusite.json*> sur le modèle du fichier *leplessis.json*.
Le plus efficace est de saisir la zone de navigation (polygone), le chemin des concurrents (Linestring) et les éventuellement les pontons et bouées fixes, 
directement sur l'éditeur en ligne **geojson.io** ; recopier ensuite dans le fichier <*nomdusite.json*> que vous modifiez sous éditeur texte. 

## Sources
```
./placerbouees
  index.html
  icones.html
./js
  myscript.js
  geo_utils.js
  le-plessis.js
  maps.js
  canavas.js
  bouees.js  
./css
  style.css
  materiel.css
  material-icons.css
./php
  placer-bouees.php
  sauverbouees.php
./images
./data
  Fichiers de positionnement de bouées par direction du vent
    leplessis.json
    laminais.json
    laplageverte.json
    planeauduchene.json
    boisjoalland.json
./doc
  Captures d'écran
```
  
## Liens
MIT App Inventor http://ai2.appinventor.mit.edu/

GeoJSON https://geojson.io/

JSON Editor OnLine https://jsoneditoronline.org/

Leaflet https://leafletjs.com/

Umap https://umap.openstreetmap.fr/fr/

## License
Pour le code source : **MIT** *Free Software, Hell Yeah!* https://github.com/pandao/editor.md/blob/master/LICENSE

Pour les documents : **Creative Commons** http://creativecommons.org/licenses/by-sa/4.0/
