# Placement et affichage de bouées autonomes de régates de voiliers radiocommandés

## Présentation
Le positionnement et l'ancrage virtuel par GPS de bouées de régates de voiliers radiocommandés est un projet initié en février 2023 entre l'ARBL (Association Radiomodéliste des Bords de Loire) et l'ICAM de Nantes.

Il consiste à proposer une ensemble logiciel et matériel permettant de positionner par radiocommande puis de maintenir en place une constellation de bouées asservies chacune à une position GPS.

L'application **Web** :  *placerbouees/index.html* est une composante de ce projet. 

### Développement: JF en javascript et PHP.
(cc) jean.fruitet@free.fr

La page web *./placerbouees/index.html* permet d'affecter des positions GPS sur une carte OpenStreetMap à des balises mobiles autonomes, en fonction de la direction du vent.

La page web *./placerbouees/chargerbouees.html* affiche ces bouées autonomes sur une carte OpenStreetMap.

## Page index.html

### Ecran
#### Partie supérieure
Sélection d'un site de navigation et chargement des données afférentes.

Les fichiers json des sites disponibles sont placés dans le dossier ./json/

#### Partie droite 
Affichage sur une carte OpenStreetmap du plan d'eau sélectionné, du périmètre de navigation, de la zone de déambulation des concurrents et, éventuellement, des bouées ancrées (bouées fixes, à demeure).

#### Partie gauche  
Pointage à la souris des bouées du parcours d'une régate, en s'appuyant aussi bien sur les bouées ancrées (fixes) que sur des bouées mobiles (des balises autonomes ancrées virtuellement).

### Fonctionnalités
Après positionnement à la souris d'un jeu de bouées organisées en *ligne de départ*, *ligne d'arrivée*, *porte* et *dog leg* sur la partie gauche de l'écran, les bouées sont affichées sur la carte et leurs coordonnées GPS (longitude, latitude) 
transmises au serveur pour être mises à disposition de l'application de pilotage pour smartphone **RoBoNav**.

### Interface
- Informations
    - TWD°:<*valeur*> TWD radian:<*valeur*>, Zoom:<*valeur*>
    - X: Y : *position*  du pointeur souris en coocrdonnées écran
    - Lon: Lat : *position géographique* du pointeur souris
    - Distance : distance entre deux points en mètres            
- Saisie 
  - Textbox *TWD* : Saisie de la direction d'où soufle le vent (entrer la valeur TWD en degrés puis cliquer sur le bouton "**Soumettre**") ;
  - *Zoom* : "+", "-", "1"  (remise à l'échelle 1) ;
  - Boutons 
    - "**Bouées**" : Saisie des positions à la souris ;   
    - "**Retirer**" : Supprime la dernière position saisie ;        
    - "**Vider**" : Supprime toutes les positions saisies ;    
    - "**Valider**" : Met fin à la saisie et affiche les bouées saisies sur la carte OpenStreetMap ;
    - "**Transférer**" : Sauvegarde des positions saisies dans un fichier <*./data/robonav_NomPlanEau_TWD_AAAAMMDD.json*>
    - Radio boutons         
        -Radio boutons : **Bâbord**, **Tribord* : indication du franchissement
        -Radio boutons : **Départ**, **Arrivée**, **Porte**, **Dog leg** : Rôles dans le parcours de régate 
    
- Légende
  1. Tracé d'une flèche indiquant la **direction vers laquelle soufle le vent**, *TWD* (true wind direction), en  ° ;
  2. "**Bouées fixes**" telles que lues dans le fichier geojson attaché au plan d'eau considéré ;
  3. **Balises mobiles** :
    * Bouées de départ en jaune
    * Bouée d'arrivée en bleu
    * Bouée de dog leg en noir
    * Bouée d'une porte en violet
      
Les bouées placées sont surmontées d'un drapeau de couleur verte (bouée à laisser à tribord) ou rouge (bouée à laisser à bâbord).

### Connexion avec un serveur externe

Le bouton "**Transmettre**" envoie à un serveur externe les coordonnées géographiques (Longitude, Latitude) des bouées et balises retenues pour constituer le parcours ; 
ces coordonnées sont disponibles pour la page Web **chargerbouees.html** et pour l'applet **RoBoNav** de pilotage des balises autonomes.


## Page chargerbouees.html
### Ecran
#### Partie supérieure
 - Sélection d'un site de navigation.
 - Saisie de la direction du vent et du site 

Le fichier XML des sites renseignés est placé dans le dossier ./json/
Les fichiers de position des balises mobiles par site et par direction du vent sont placés dans le dossier *./data/*

#### Partie inférieure 

- A gauche 
    - *TWD* : Saisie de la direction d'où soufle le vent (entrer la valeur TWD en degrés) ; cliquer sur le bouton "**Soumettre**" ;
    - Liste des fichiers de position disponibles pour ce site et ce choix de direction du vent
    - Autres fichiers de position pour ce site
- A droite
    - Affichage sur une carte OpenStreetmap du plan d'eau sélectionné, du périmètre de navigation, de la zone de déambulation des concurrents, des bouées ancrées (bouées fixes, à demeure) et des bouées mobiles à leur position GPS.

## Connexion avec un serveur externe
Un serveur externe stocke et retourne tous les fichiersutiles à l'application :
- Fichier .XML des sites enregistrés
- Fichiers .json des données de plan d'eai
- Fichiers .json coordonnées géographiques (Longitude, Latitude) des bouées et balises constituant un parcours de régate pour une direction de vent donnée ; 
    Ces dernier sont les mêmes que ceux utilisés par l'applet **RoBoNav** de pilotage des balises autonomes.

### Fonctionnement du serveur
Il y a quelques conditions au fonctionnement du serveur :
  1. La connexion Web doit être activée (ou les *Données mobiles* activées)
  2. Le serveur doit être activé.
  3. Dans la version actuelle l'URL du serveur est codée en dur dans le code source du script *./js/ajax.js*.

## Edition des sites

Pour ajouter de nouveaux sites, modifier sous éditeur de texte type PSPad ou Notepad++ le fichier *./data/plans_eau_robonav.xml*.

Puis ajouter dans le dossier *./json/* un fichier <*nomdusite.json*> sur le modèle du fichier <*leplessis.json*>.

Le plus efficace est de saisir à la souris la zone de navigation (polygone), 
le chemin des concurrents (Linestring continue) et les éventuelles positions des pontons et bouées fixes, 
avec l'éditeur en ligne **geojson.io** ; 

Replacer ensuite les contenus des *{"type":"Featurecollection"}* de ces scripts *geojson* 
dans le fichier <*nomdusite.json*> sous éditeur texte type **PSPad**.

### Chaîne de production des données 

C'est l'application placerbouees/index.html qui produit les fichiers d'entrée de l'application ./placerbouees/chargerbouees.html


## Sources
```
./placerbouees
    index.html
    chargerbouees.html
    
./js
    ajax.js
    ajax2.js
    bouees.js
    canavas.js
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
    getsite.php
    placer-bouees.php
    plans_eau.php
    sauverbouees.php
./images

./json
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

### Input : Charger bouées
Les données ci-dessus sont lues dans le dossier du serveur *./data/* 
  
## Ce qui reste à faire
- Interfacer l'application **PlacerBouees** avec le projet **RoBoNav** de positionnement et de pilotage de bouées de régate avec ancrage virtuel par GPS ; cela consiste à implnater l'application **ChargerBouees** sur smartphone.
- Automatiser le placement des bouées en fonction d'un site et d'une direction du vent.
    - La plupart des fonctions nécessaires sont déjà présentes, il suffit d'implanter l'algorithme décrit par ailleurs. 

### Difficultés rencontrées
Il m'a fallu réactualiser complètement mes notions de javascript, de canvas, de création de cartes et l'ajout de markers et autres éléments graphiques, bien oubliées, je dois dire...

Je me suis appuyé sur l'excellente librairie javascript **LeafLet** pour la création des cartes **OpenStreetMap"", ainsi que sur les dizaines de contributions disponibles en ligne sur les forums ad hoc.

L'adaptation à une langue différente du français ne me paraît pas trop compliquée car il y a très peu de chaînes à traduire...

   
## Outils et liens 

OpenStreetMap https://www.openstreetmap.org/ : alternative libre et ouverte à GoogleMaps

MIT App Inventor http://ai2.appinventor.mit.edu/ : langage de programmation par blocs pour smartphones Android

GeoJSON https://geojson.io/ : éditeur en ligne de fichier geojson

JSON Editor OnLine https://jsoneditoronline.org/ : comme sont nom l'indique. 

Leaflet https://leafletjs.com/ : bibliothèque Javascript pour les cartes OpenStreetMap

Umap https://umap.openstreetmap.fr/fr/ : éditeur de cartes collaboratif 

Xampp https://www.apachefriends.org/fr/index.html : serveur httpd pour Windows

PSPad http://www.pspad.com/fr/ : éditeur de fichiers sources

dnGrep https://dngrep.github.io/ : recherche de chaînes de caractères multi dossier

Figma https://www.figma.com/ : éditeur de pages web et création d'objets vectoriels au format svn

## License
Pour le code source : **MIT** *Free Software, Hell Yeah!* https://github.com/pandao/editor.md/blob/master/LICENSE

Pour les documents : **Creative Commons** http://creativecommons.org/licenses/by-sa/4.0/
