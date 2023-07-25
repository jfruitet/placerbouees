# placementbouées
Positionnement manuel de bouées de régate radiocommandée
**Web** *placerbouees* 

### Développée par JF en javascript et PHP.
(cc) jean.fruitet@free.fr
Cette page web autorise le positionnement de bouées fixes et mobiles autonomes sur une carte OpenStreetMap 
de l'étang du Plessis à Sainte Luce / Loire, 44980.
Elle entre comme composant logiciel dans le projet RoBoNav mené depuis février 2023 avec l'ICAM de Nantes.

## Ecran
### Partie droite 

Affichage de la carte du plan d'eau du Plessis avec le périmètre de navigation , la zone des concurrents et les 
bouées ancrées (bouées fixes, à demeure).

### Partie gauche  

Pointage à la souris des des bouées d'un parcours de régate, en s'appuyant aussi bien sur les bouées fixes que mobiles.

## Fonctionnalités

Cartographie de l'étang du Plessis réservé au modélisme. Les bouées fixes ne peuvent être déplacées. 
Après positionnement les coordonnées GPS des bouées sélctionnées sont affichées sur la carte et envoyée au serveur pour transmission à RoBoNav.

- Boutons :
  1. TWD : Saisie de la direction d'où soufle le vent (entrer la valeur TWD en degrés) ; cliquer sur le bouton "Soumettre" ;
  2. Zoom : "+", "-", "1": remize à 0
  3. Bouton "Bouées" : Saisie des positions à la souris;
- Légendes
  1. Affiche une flèche indiquant la **direction vers laquelle soufle le vent**, TWD : true wind direction en  ° ;
  2. "Bouées fixes" telles que lues dans le fichier geojson attaché au plan d'eau du Plessis;
  3. Balises mobiles :
    * Bouées de départ en jaune
    * Bouée d'arrivée en bleu
    * Bouée de dog leg en noir
    * Bouée d'une porte en violet
Les bouées mobiles sont surmontées d'un drapeua de couleur verte (laisser à tribordà ou rouge (laisser à bâbord).

## Connexion avec un serveur externe

La page Web envoie à un serveur externe les corconnées géographiques (Longitude, Latitude) à un serveur 
qui peut être consulté par l'applet RoBoNav.

Il y a quelques conditions à cela :
  1. La connexion Web doit être activée (ou les Données mobiles activées)
  2. Un serveur doit être activé.
  3. Dans la version actuelle l'URL du serveur est codée en dur dans le code source.
  
## Ce qui reste à faire
- Importer un plan d'eau différent et les bouées fixes de celui-ci au format geoJSON...
- Intégrer l'application au projet RoBoNav de positionnement de bouées de régate avec ancrage virtuel par GPS

## Difficultés rencontrées
Il m'a afllut reprendre complètement mes notions de javascript.
Je me suis appuyé sur l'excellente librairies LeafLet pour la création des cartes
L'adaptation à une langue différente ne me paraît pas trop compliquée...

## Liens
MIT App Inventor http://ai2.appinventor.mit.edu/

GeoJSON https://geojson.io/

JSON Editor OnLine https://jsoneditoronline.org/

Leaflet https://leafletjs.com/

## License
Pour le code source : **MIT** *Free Software, Hell Yeah!* https://github.com/pandao/editor.md/blob/master/LICENSE

Pour les documents : **Creative Commons** http://creativecommons.org/licenses/by-sa/4.0/
