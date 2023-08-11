<?php
// Calcul du placement de bouées de régate en fonction de la direction du vent
// Ce code est tout à fait inachevé...
// A suivre.

define("DATAPATH_INPUT", "../json/"); // Les données seront lues dans ce dossier.
define("DATAPATH_OUTPUT", "../data/"); // Les données seront sauvegardées dans ce dossier.

include ("./include/geo_utils.php");
include ("./include/algo.php");

$debug = false;
$debug1 = false;

$site='';
$reponse_ok = '{"ok":1}';
$reponse_not_ok = '{"ok":0}';
$data=null;
$reponse=$reponse_not_ok;


// Get the JSON contents
if (isset($_SERVER['REQUEST_METHOD']) && (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')) {
  throw new Exception('Only GET requests are allowed');
}
if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false)) {
  throw new Exception('Content-Type must be application/json');
}

if (isset($_GET) && !empty($_GET)){
    if ($debug){
        echo '$_GET '."\n";
        print_r($_GET);  
    }
    if (isset($_GET['twd'])){
        if (is_numeric($_GET['twd']))
        {
            $twd=intval($_GET['twd']);
            if ($twd>=0 && $twd<=360){
                $twd_degre=$twd;          
            }
        }        
    }
    if (!empty($_GET['site'])){
        $site=strtolower(str_replace(' ','',urldecode($_GET['site'])));
    }
}


// Le calcul commence
/*
B° → A radian : A = (PI / 180 * (270 - B)) MODULO 2PI
*/
if ($debug || $debug1){
    echo ("<html><head></head><body><h3>Placer_bouees.php</h3><p>Placement automatique des bouées de régate mobiles<br>(cc)jean.fruitet@free.fr</p>");
}
$twd_radian = (M_PI / 180.0) * ((450 - $twd_degre) % 360);

if ($debug){
    $msg=sprintf("Site:%s TWD°:%d, TWD radian:%f<br>\n",$site,$twd_degre, $twd_radian);
    echo "<br />$msg\n";
    file_put_contents("debug_test.txt", $msg);
}

// Charger les information de site
// Elles sont dans un fichier DATAPATH_INPUT."nomsite.json"

$filename_input=$site.".json";
if ($debug){
    echo ("Fichier Input: ".DATAPATH_INPUT.$filename_input."<br>\n");
    file_put_contents("debug_test.txt", $data, FILE_APPEND);    
}

if (file_exists(DATAPATH_INPUT.$filename_input)){
    if ($data=file_get_contents(DATAPATH_INPUT.$filename_input)){
        $dataObject=json_decode($data,false);
        if ($debug){
            file_put_contents("debug_test.txt", $data, FILE_APPEND);
        }        
        if ($debug){
            file_put_contents("debug_test.txt", $dataObject, FILE_APPEND);
        }
    }
} 

/******************************************
 * Chargement des données en input 
 * ****************************************/
if (!empty($dataObject)){
    // Zone des Concurrents 
      
    $zoneconc_lon=array();
    $zoneconc_lat=array();
    
    $zonenav_lon=array();
    $zonenav_lat=array();
    
    $balises_name=array();
    $balises_lon=array();
    $balises_lat=array();    

    foreach ($dataObject->geojsonZoneConcurrents->features[0]->geometry->coordinates as $key => list($lon,$lat)){
        $zoneconc_lon[$key]=$lon;
        $zoneconc_lat[$key]=$lat;
    }
    
    // Zone de navigation
    $zonenav_lon=array();
    $zonenav_lat=array();
    
    foreach ($dataObject->geojsonZoneNav->features[0]->geometry->coordinates[0] as $key => list($lon,$lat)){
        $zonenav_lon[$key]=$lon;
        $zonenav_lat[$key]=$lat;
    }

    // Bouées fixes
    $index=0;
    foreach ($dataObject->geojsonBalises->features as $feature){
        $balises_name[$index]=$feature->properties->name;
        $index++;
    }

    $index=0;
    foreach ($dataObject->geojsonBalises->features as $feature){
        $balises_lon[$index]=$feature->geometry->coordinates[0];
        $balises_lat[$index]=$feature->geometry->coordinates[1];
        $index++;
    }
    
    if ($debug1){    
    echo "<br>Zone Concurrents<br>\n";    
    echo "<br>ZC_lon<br>\n";
    print_r($zoneconc_lon);
    echo "<br>zoneconc_lat<br>\n";
    print_r($zoneconc_lat);
    echo "<br>Zone Navigation<br>\n";
    echo "<br>zonenav_lon<br>\n";
    print_r($zonenav_lon);
    echo "<br>zonenav_lat<br>\n";
    print_r($zonenav_lat);

    echo "<br>balises fixes<br>\n";   
    echo "<br>balises_name<br>\n";
    print_r($balises_name);
               
    echo "<br>balises_lon<br>\n";
    print_r($balises_lon);
    echo "<br>balises_lat<br>\n";
    print_r($balises_lat);
    }    
    echo "<br>\n";
}


/*******************************************************************
 * Transformation en coordonnées "écran" pour accélerer l'algorithme 
 * *****************************************************************/
init_ecran_ZN();

if ($debug1){
    echo "<br>Polygone de navigation<br>\n<table border=\"1\">\n<tr>\n";
    foreach ($poly_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($poly_yecran as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n<br>\n";

    echo "Ligne des concurrents<br>\n<table border=\"1\">\n<tr>\n";
    foreach ($ligne_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($ligne_yecran as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n";
}

/******************************************
 * Transformations 
 * ****************************************/
// Convertir les polygones de navigation et les lignes de déplacement des concurrents dans le repère dit "de saisie"

// Appliquer une transformation pour ramener la figure face au vent 
// La figure est tournée dans une direction apparente du vent de 0°
// Désormais il est facile de déterminer un alignement de bouées face au vent :
// Bouées en travers du vent : y=constante
// Bouées dans le sens du vent : x=constante
// Dog leg au vent : y=minimum
// Porte sous le vent : y=maximum
// Départ : minimum<y<maximum au plus proche du chemin des concurrent 
// Ecart entre les bouées de départ, d'arrivée, porte, dog leg : entre 10 m et 20m

rotation_ecran_ZN($twd_radian);

if ($debug1){
    echo "<br>Polygone de navigation  APRES rotation <br>\n<table border=\"1\">\n<tr>\n";
    foreach ($poly_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($poly_ysaisie as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table><br>\n";

    echo "Ligne des concurrents   APRES rotation <br>\n<table border=\"1\">\n<tr>\n";
    foreach ($ligne_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($ligne_ysaisie as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n";
}
 
// Vérification
if ($debug1){
    echo "<br>VERIFICATION DES TRANSFORMATIONS<br>\n";
    echo "<br>Milieu (lon,lat) : ".$milieu_lon.", ".$milieu_lat."\n";
    
    // convertir en coordonnées écran
    $ecranX=get_Xecran_lon($milieu_lon);
    $ecranY=get_Yecran_lat($milieu_lat);
    echo "<br>Passage en coordonnées écran<br>Milieu (ecranX,ecranY) : ".$ecranX.", ".$ecranY."\n";
    // rotation
    $saisieX = setDisplayToSaisieX($ecranX,$ecranY, $twd_radian);
    $saisieY = setDisplayToSaisieY($ecranX,$ecranY, $twd_radian);    
    echo "<br>Passage en coordonnées de saisie<br>Milieu (saisieX,saisieY) : ".$saisieX.", ".$saisieY."\n";
    
    // revenir aux coordonnées écran
    $ecranX2=setSaisieToDisplayX($saisieX, $saisieY, $twd_radian);    
    $ecranY2=setSaisieToDisplayY($saisieX, $saisieY, $twd_radian);
    echo "<br>Retour en coordonnées écran<br>Milieu (saisieX,saisieY) : ".$ecranX2.", ".$ecranY2."\n";
    
    // repasser en coordonnées géographiques
    $milieu_lon2=get_lon_Xecran($ecranX2);
    $milieu_lat2=get_lat_Yecran($ecranY2);
    echo "<br>Retour en coordonnées géographiques<br>Milieu (lon,lat) : ".$milieu_lon2.", ".$milieu_lat2."\n";   
}    

if ($debug1){
    echo "<br>Données chargées avec succès. Transformations vérifiées. Début de l'algorithme calcul<br>\n";
}

      
/******************************************
 * Début de l'algorithme de positionnement
 * ****************************************/
 
// Déterminer la boîte englobante des coordonnées de saisie pour accélérer la recherche des rectangles de navigation 
$xminPoly=1000000; // en pixels
$xmaxPoly=-1000000; // en pixels
$yminPoly=1000000; // en pixels
$ymaxPoly=-1000000; // en pixels
    foreach ($poly_xsaisie as $x){
        if ($x<=$xminPoly){ $xminPoly=$x;}
        if ($x>$xmaxPoly){ $xmaxPoly=$x;}
    }
    foreach ($poly_ysaisie as $y){
        if ($y<$yminPoly){ $yminPoly=$y;}
        if ($y<$ymaxPoly){ $ymaxPoly=$y;}
    }

 
$distance_H_MillePixels=distanceHorizontalePixels(0,0,1000);
$distance_V_MillePixels=distanceVerticalePixels(0,0,1000); 

$deltaXpixels=howMuchXPixelsForMeters(20.0);
$deltaYpixels=howMuchYPixelsForMeters(50.0);
if ($debug1){
    echo "<br>Distance horizontale pour 1000 \"pixels\": ".$distance_H_MillePixels."\n";
    echo "<br>Distance verticale pour 1000 \"pixels\": ".$distance_V_MillePixels."\n";
    echo "<br>Nombre de \"pixels\" pour une distance horizontale de 20 mètres: ".$deltaXpixels."\n";
    echo "<br>Nombre de \"pixels\" pour une distance verticale de 50 mètres: ".$deltaYpixels."\n";
}

// Calcule la distance entre chaque sommet du polygone et la ligne des concurrents
calculeTableDistances(false);

if ($debug1){    
    echo "<br>Points d'intersection trouvés\n";
    for ($i=0;$i<count($tab_distances); $i++){
        echo ("<br>".$tab_distances[$i]);
    }
}

// Rechercher le sommet du polygone le plus proche de la zone concurrents
$distanceecranmin=1000000;
$sommetmin=0;
$coordonneesmin=array();
$intersectionmin=array();
$distanceterrainmin=1000000;

    for ($i=0;$i<count($tab_distances);$i++){
        // list($sommet_poly,$coordonnees,$intersection,$distanceecran,$distanceterrain)=json_encode($tab_distances[$i]);
        //echo ($sommet_poly.",".$coordonnees.",".$intersection.",".$distanceecran.",".$distanceterrain);
        //$tab_d=explode(',',$tab_distances[$i]); 
        //echo "<br>\n";
        //print_r($tab_d);
        $tab_d=json_decode($tab_distances[$i],false);
        //echo "<br>\n";
        //print_r($tab_d);
        $sommet_poly=$tab_d->sommet_poly;
        $coordonnees=$tab_d->coordonnees;
        $segment_ligne=$tab_d->segment_ligne;
        $intersection=$tab_d->intersection;
        $distanceecran=$tab_d->distanceecran;
        $distanceterrain=$tab_d->distanceterrain;
        
        
        if ($distanceecran<$distanceecranmin){
            $distanceecranmin=$distanceecran;
            $sommetmin=$sommet_poly;
            $coordonneesmin=$coordonnees;
            $intersectionmin=$intersection;            
            $distanceterrainmin=$distanceterrain;
        }                     
    }

if ($debug1 || true){    
    echo "<br>Minimas trouvés<br>\n";
    echo ("Sommet ".$sommetmin.", x0:".$coordonneesmin[0].", y0:".$coordonneesmin[1].", Ix:".$intersectionmin[0].", Iy:".$intersectionmin[1].", Distance (pixels) ".$distanceecranmin.", Distance (m) ".$distanceterrainmin);
    echo "<br>\n";
}

// Balayer l'axe X par des droites x=constante pour déterminer les points d'intersection avec le plygone.
// Calculer la distance entre ces points d'intersection
// Si cette distance est supérieure au seuil enregistrer les points d'intersection.  

// Tracer des droites verticlae (x=cte)
$seuilDistanceVertical = 50000; // Environ 50m
$seuilDistanceHorizontal= 20000; // Environ 20m


$x0=$coordonneesmin[0]; // abscisse du Sommet le plus proche de la ligne des concurrents
$y0=$coordonneesmin[1]; // ordonnée 
$xC=$intersectionmin[0]; // abscisse du point d'intersection de y=$y0 avec la ligne des concurrents
$yC=$intersectionmin[1]; // ordonnée


// Progresser vers l'Est ou vers l'Ouest selon le cas
if ($x0>=$xC){
    $sensprogression=1;
}
else {
    $sensprogression=-1;
}

$posDebX=$xminPoly; // La valeur minimale pour x
$posFinX=$xmaxPoly; // La valeur maximale pour x

$incrementX=$sensprogression*1000; // Environ 1m vers l'Est ou vers l'Ouest
if ($sensprogression==1){
    $x0=$posDebX;
}
else{
    $x0=$posFinX;
}

/**************************************
 *     Première passe
 **************************************/
$numZoneExploree=0; // On a besoin de 2 zones contigües pour placer les bouées
      
$x=$x0; // Démarrer la recherche à 0 mètres du sommet

if ($debug1){    
    echo "<br>Progression ".$sensprogression."\n";
    echo "<br>Valeurs de départ <br>\n";
    echo (" x0:".$x0.", x:".$x);
    echo "<br>\n";
}

// Droite d'équation x=constante
// Tant que x<$posxFin

$encore=true;
$DistanceH=0;    // Ecart horizontal entre deux droites verticales du rectangle de navigation
$maxDistanceV=0;    // Maximas sur l'écart vertical entre deux points du polygone de navigation
$indexMax=0;
$xMax=$x;
$yMax0=50000;
$yMax1=-50000;

while ($encore) { // On cherche les intersections avec le polygone
    if ($sensprogression>0){
        $encore = ($x<=$posFinX);
    }
    else{
        $encore = ($x>=$posDebX);    
    }     
    $nbintersections=0;           
    $tab_Intersections=array(); // Les valeurs y d'intersection
    if ($debug1){    
        echo "<br>Exploration <br>\n";
        echo (" x0:".$x0.", x:".$x);
        echo "<br>\n";
    }

    for ($i=0; $i<count($poly_ysaisie); $i++){
        // calculer l'intersection avec le polygone
        $xp1=$poly_xsaisie[$i];
        $yp1=$poly_ysaisie[$i];
        if ($i<count($poly_ysaisie)-1) {
            $i2=$i+1;
        } 
        else {
            $i2=0;
        }
        $xp2=$poly_xsaisie[$i2];
        $yp2=$poly_ysaisie[$i2];
        if ($debug1){  
            echo "<br>Sommet ".$i.": [".$xp1.",".$yp1."]\n";
            // echo "<br>Sommet ".$i2.": [".$xp2.",".$yp2."]\n";            
        } 
    
        if ($xp1>$xp2){ // échanger
            $aux=$xp2;
            $xp2=$xp1;
            $xp1=$aux;
            $aux=$yp2;
            $yp2=$yp1;
            $yp1=$aux;
        }      
    
        if (($x>=$xp1) && ($x<$xp2)) { // Intersection possible 
            $tab_Intersections[$nbintersections]=intersectionVerticale($x,$xp1,$yp1,$xp2,$yp2);
            $nbintersections++;
        }             
    }

if ($debug1){    
    echo "<br>".$nbintersections." intersections.<br>Table des intersections<br>\n";
    print_r($tab_Intersections);
    echo "<br>\n";
}
    
    switch ($nbintersections) {
        case 0 : break; // Sortie du polygone
        case 1 : break; // Sommet, on ne traite pas
        case 2 :  // Intrieur. Calculer la dimension verticale entre deux intersections
            $distanceVerticale =abs($tab_Intersections[1]-$tab_Intersections[0]);
            if ($distanceVerticale >= $seuilDistanceVertical){
                // On a un rectangle candidat
                // Passer au placement des bouées
                if ($distanceVerticale>$maxDistanceV){
                    $maxDistanceV=$distanceVerticale; 
                    $indexMax=$i;
                    $xMax=$x;
                    $yMax0=$tab_Intersections[0];
                    $yMax1=$tab_Intersections[1];
                    $DistanceH=abs($x0-$x);                
                }
                if ($debug1 || true){    
                    echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".abs($x0-$x)."\n";
                }
                
                droiteVerticale($numZoneExploree, $x0, $tab_Intersections[0], $x, $tab_Intersections[1],$y0);
                $numZoneExploree++;
                $encore=false; // Traitement  1ère passe terminé
            }
            
            break; 
        case 3 : // // polygone concave + passage par un sommet ; il faut faire un grand pas vers l'Est (ou vers l'Ouest)
            $x0 = $x+$incrementX;
            $x=$x0+$incrementX;
            break; 
        default : // polygone convexe avec au moins une concavité selon l'axe Nord / Sud
                // On traite les deux premiers couples
                // Ordonner les Y dans le sens croissant
            if (sort($tab_Intersections)){
                $distanceVerticale = abs($tab_Intersections[1]-$tab_Intersections[0]);
                $distanceVerticale2 =  abs($tab_Intersections[3]-$tab_Intersections[2]);  

                if (($distanceVerticale>$maxDistanceV) || ($distanceVerticale2>$maxDistanceV)){
                    $maxDistanceV=max($distanceVerticale, $distanceVerticale2); 
                    $indexMax=$i;
                    $xMax=$x;
                    $yMax0=$tab_Intersections[0];
                    $yMax1=$tab_Intersections[1];
                    $DistanceH=abs($x0-$x);                
                }
                            
                if (($distanceVerticale >= $distanceVerticale2) && ($distanceVerticale >= $seuilDistanceVertical)){
                    // On a un rectangle candidat
                    // Passer au placement des bouées
                    if ($debug1 || true){    
                        echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".$DistanceH."\n";
                    }
                    
                    droiteVerticale($numZoneExploree,$x0, $tab_Intersections[0], $x, $tab_Intersections[1],$y0);
                    $numZoneExploree++; 
                    $encore=false;                        
                }
                else{
                    if ($distanceVerticale2 >= $seuilDistanceVertical){
                        // On a un rectangle candidat
                        // Passer au placement des bouées
                        if ($debug1 || true){    
                            echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".$DistanceH."\n";
                        }
                        
                        droiteVerticale($numZoneExploree,$x0, $tab_Intersections[2], $x, $tab_Intersections[3],$y0);
                        $numZoneExploree++;
                        $encore=false;
                    }
                }                   
            }                        
            break;             
    }
    $x=$x+$incrementX; 
}

if ($debug1 || true){    
    echo "<br><b>Premier passage</b>. Distance verticale maximale: ".$maxDistanceV." Distance  horizontale: ".$DistanceH." Y0:".$yMax0." Y1:".$yMax1;
    echo "<br>\n";
}
 

/***************************************
 * Seconde passe
 * *************************************/
 
$x0=$x+$incrementX; // Démarrer la recherche immédiatement à proximité
$x=$x+$incrementX; // $seuilDistanceHorizontal Largeur minimale de la zone à trouver

if ($debug1){    
    echo "<br>Progression ".$sensprogression."\n";
    echo "<br>Valeurs de départ <br>\n";
    echo (" x0:".$x0.", x:".$x);
    echo "<br>\n";
}

// Droite d'équation x=constante
// Tant que x<$posxFin

$encore=true;


$DistanceH=0;    // Ecart horizontal entre deux droites verticales du rectangle de navigation
$maxDistanceV=0;    // Maximas sur l'écart vertical entre deux points du polygone de navigation
$indexMax=0;
$xMax=$x;
$yMax0=50000;
$yMax1=-50000;

while ($encore) { // On cherche les intersections avec le polygone
    if ($sensprogression>0){
        $encore = ($x<=$posFinX);
    }
    else{
        $encore = ($x>=$posDebX);    
    }     
    $nbintersections=0;           
    $tab_Intersections=array(); // Les valeurs y d'intersection
    if ($debug1 || true){    
        echo "<br>Exploration <br>\n";
        echo (" x0:".$x0.", x:".$x);
        echo "<br>\n";
    }

    for ($i=0; $i<count($poly_ysaisie); $i++){
        // calculer l'intersection avec le polygone
        $xp1=$poly_xsaisie[$i];
        $yp1=$poly_ysaisie[$i];
        if ($i<count($poly_ysaisie)-1) {
            $i2=$i+1;
        } 
        else {
            $i2=0;
        }
        $xp2=$poly_xsaisie[$i2];
        $yp2=$poly_ysaisie[$i2];
        if ($debug1){  
            echo "<br>Sommet ".$i.": [".$xp1.",".$yp1."]\n";
            // echo "<br>Sommet ".$i2.": [".$xp2.",".$yp2."]\n";            
        } 
    
        if ($xp1>$xp2){ // échanger
            $aux=$xp2;
            $xp2=$xp1;
            $xp1=$aux;
            $aux=$yp2;
            $yp2=$yp1;
            $yp1=$aux;
        }      
    
        if (($x>=$xp1) && ($x<$xp2)) { // Intersection possible 
            $tab_Intersections[$nbintersections]=intersectionVerticale($x,$xp1,$yp1,$xp2,$yp2);
            $nbintersections++;
        }             
    }

if ($debug1){    
    echo "<br>".$nbintersections." intersections.<br>Table des intersections<br>\n";
    print_r($tab_Intersections);
    echo "<br>\n";
}
    
    switch ($nbintersections) {
        case 0 : break; // Sortie du polygone
        case 1 : break; // Sommet, on ne traite pas
        case 2 :  // Calculer la dimension verticale entre deux intersections
            $distanceVerticale =abs($tab_Intersections[1]-$tab_Intersections[0]);
            if ($distanceVerticale>$maxDistanceV){
                $maxDistanceV=$distanceVerticale; 
                $indexMax=$i;
                $xMax=$x;
                $yMax0=$tab_Intersections[0];
                $yMax1=$tab_Intersections[1];
                $DistanceH=abs($x0-$x);                
            }

            if ($distanceVerticale >= $seuilDistanceVertical){
                // On a un rectangle candidat
                // Passer au placement des bouées
                if ($debug1 || true){    
                    echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".abs($x0-$x)."\n";
                }
                droiteVerticale($numZoneExploree, $x0, $tab_Intersections[0], $x, $tab_Intersections[1],$y0);
                $numZoneExploree++;
                if (abs($x0-$x) >= $seuilDistanceHorizontal){
                    $encore=false;
                }              
            }
            
            break; // Intérieur
        case 3 : // polygone concave, passage par un sommet concave; il faut faire un pas de plus
                $x0 = $x+$incrementX;
                $x=$x0+$incrementX;
            break; 
        default : // polygone convexe avec au moins une concavité selon l'axe Nord / Sud
                // On traite les deux premiers couples
                // Ordonner les Y dans le sens croissant
            if (sort($tab_Intersections)){
                $distanceVerticale = abs($tab_Intersections[1]-$tab_Intersections[0]);
                $distanceVerticale2 =  abs($tab_Intersections[3]-$tab_Intersections[2]);  

                if (($distanceVerticale>$maxDistanceV) || ($distanceVerticale2>$maxDistanceV)){
                    $maxDistanceV=max($distanceVerticale, $distanceVerticale2); 
                    $indexMax=$i;
                    $xMax=$x;
                    $yMax0=$tab_Intersections[0];
                    $yMax1=$tab_Intersections[1];
                    $DistanceH=abs($x0-$x);                
                }
                            
                if (($distanceVerticale >= $distanceVerticale2) && ($distanceVerticale >= $seuilDistanceVertical)){
                    // On a un rectangle candidat
                    // Passer au placement des bouées
                    if ($debug1 || true){    
                        echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".abs($x0-$x)."\n";
                    }
                    
                    droiteVerticale($numZoneExploree,$x0, $tab_Intersections[0], $x, $tab_Intersections[1],$y0);
                    $numZoneExploree++; 
                    if (abs($x0-$x) >= $seuilDistanceHorizontal){
                        $encore=false;
                    }    
                }
                else{
                    if ($distanceVerticale2 >= $seuilDistanceVertical){
                        // On a un rectangle candidat
                        // Passer au placement des bouées
                        if ($debug1 || true){    
                            echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".abs($x0-$x)."\n";
                        }
                        
                        droiteVerticale($numZoneExploree,$x0, $tab_Intersections[2], $x, $tab_Intersections[3],$y0);
                        $numZoneExploree++;
                        if (abs($x0-$x) >= $seuilDistanceHorizontal){
                            $encore=false;
                        }    
                    }
                }                   
            }                        
            break;         
    }
    //$x0=$x0+$incrementX; // 
    $x=$x+$incrementX; 
}

if ($debug1 || true){    
    echo "<br><b>Deuxième passe</b>. Distance verticale maximale: ".$maxDistanceV." Distance  horizontale: ".$DistanceH." Y0:".$yMax0." Y1:".$yMax1;
    echo "<br>\n";
}
 
/******************************************
 * Sauvegarder les position
 * ****************************************/ 


// Sauvegarder les information de site
// Elles sont placer dans un fichier DATAPATH_OUTPUT.

/*    
$filename_output="robonav_".$site."_".$twd_deg."_".date("Ymd").".json";
if ($handle = fopen(DATAPATH.$filename, "w")){
    fwrite($handle, $data);
    fclose($handle);
}

*/

if ($debug || $debug1){
    echo ("</body></head></html>");
}
?>
