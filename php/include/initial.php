<?php

    // Zone des Concurrents 
 
//-----------------------
function traitement_initial($dataObject){    

global $debug1;
global $twd_degre;
global $twd_radian;
global $lonmin; // en degré géographique
global $latmin;
global $lonmax; // en degré géographique EST ligne de changement d'horaire
global $latmax;

global $milieu_lon;
global $milieu_lat;

global $poly_xecran; // Tableau des coordonnées écran de la zone de navigation
global $poly_yecran;
global $ligne_xecran; // Tableau des coordonnées écran de la ligne de déambulation des concurrents
global $ligne_yecran;

global $balisesEcran;      // Tableau de Objets balises fixes dans le repère écran
global $balises_xsaisie;           // coordonnées des balises fixes dans le repère de saisie
global $balises_ysaisie;

global $poly_xsaisie; // Tableau des coordonnées écran de la zone de navigation après rotation face au vent
global $poly_ysaisie;
global $ligne_xsaisie; // Tableau des coordonnées écran de la ligne de déambulation des concurrents après rotation face au vent
global $ligne_ysaisie;

global $zonenav_lon;   // Tableau des coordonnées géographiques (longitude) de la zone de navigation
global $zonenav_lat;   // Tableau des coordonnées géographiques (latitude) de la zone de navigation
global $zoneconc_lon;  // Tableau des coordonnées géographiques (longitude) de la zone des concurrents
global $zoneconc_lat;  // Tableau des coordonnées géographiques (latitude) de la zone des concurrents

global $balises_name;
global $balises_lon;
global $balises_lat;   
global $nbouees; // nombre max de bouées mobiles à placer.
global $ecartBoueesXmetres;
global $ecartBoueesYmetres;
global $tab_distances;
global $deltaXpixelsSite;
global $deltaYpixelsSite;
global $ecartbordure; // Deux mètres pour éviter de taper la berge
global $deltaBordure;
 
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
    echo "<br>\n";
}


/*******************************************************************
 * Transformation en coordonnées "écran" pour accélérer l'algorithme 
 * *****************************************************************/
init_ecran_ZN();
init_ecran_bouees_fixes();

if ($debug1){
    echo "<br>Polygone de navigation en coordonées écran<br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($poly_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
    foreach ($poly_yecran as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n<br>\n";

    echo "Ligne des concurrents<br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($ligne_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
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
rotation_ecran_Balises($twd_radian);

if ($debug1){
    echo "<br><b>Coordonnées des Balises dans l'écran de saisie</b>\n";
    for ($index=0; $index<count($balises_xsaisie); $index++){
        echo "<br>Balise ".$index." Id:".$balisesEcran[$index]->id."  Nom: ".$balisesEcran[$index]->name." X:".$balises_xsaisie[$index]." Y:".$balises_ysaisie[$index]."\n";
    }
    echo "<br><br>\n";
}

if ($debug1){
    echo "<br>Polygone de navigation  APRES rotation <br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($poly_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
    foreach ($poly_ysaisie as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table><br>\n";

    echo "Ligne des concurrents   APRES rotation <br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($ligne_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
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

    
    $t_ecranX=array();
    $t_ecranY=array();
    for ($i=0; $i<count($poly_xsaisie); $i++){
        $t_ecranX[$i]=setSaisieToDisplayX($poly_xsaisie[$i], $poly_ysaisie[$i], $twd_radian);
        $t_ecranY[$i]=setSaisieToDisplayY($poly_xsaisie[$i], $poly_ysaisie[$i], $twd_radian);
    }
    echo "<br>Polygone de navigation  APRES rotation inverse <br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($t_ecranX as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
    foreach ($t_ecranY as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table><br>\n";


    echo "<br>Coordonnées géographiques initiales de la zone de navigation <br>\n<table border=\"1\">\n<tr><th>Lon</th>\n";
    foreach ($zonenav_lon as $x){
        echo "<td>".$x."</td>";
    }    
    echo "</tr><tr><th>Lat</th>\n";
    foreach ($zonenav_lat as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table><br>\n";
 
    echo "<br>Retour en coordonnées géographiques après transformation <br>\n<table border=\"1\">\n<tr><th>Lon</th>\n";
    foreach ($t_ecranX as $x){
        echo "<td>".get_lon_Xecran($x)."</td>";
    }
    echo "</tr><tr><th>Lat</th>\n";
    foreach ($t_ecranY as $y){
        echo "<td>".get_lat_Yecran($y)."</td>";
    }
    echo "</tr>\n</table><br>\n";
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
        if ($y>$ymaxPoly){ $ymaxPoly=$y;}
    }

 
$distance_H_MillePixels=distanceHorizontalePixels(0,0,1000);
$distance_V_MillePixels=distanceVerticalePixels(0,0,1000); 

// Grands plans d'eau
$ecartBordure=2; // Deux mètres pour éviter de taper la berge
$deltaBordure=howMuchXPixelsForMeters($ecartBordure);

$ecartBoueesXmetres=14; // Distance entre bouées de la porte Distance du dog leg à la porte pour les grands plans d'eau en tenant compte de la bordure de sécurité
$ecartBoueesYmetres=64;  // Distance du dog leg à la porte pour les grands plans d'eau en tenant compte de la bordure de sécurité

$deltaXpixelsDixMetres=howMuchXPixelsForMeters($ecartBoueesXmetres+2*$ecartBordure);
$deltaYpixelsCinquanteMetres=howMuchYPixelsForMeters($ecartBoueesYmetres+2*$ecartBordure);

// Petits plans d'eau
if (abs($xminPoly-$xmaxPoly) < 10 * $deltaXpixelsDixMetres){
    $ecartBoueesXmetres=10; // Ecart des portes : 6 mètres 
    $deltaXpixelsDixMetres=howMuchXPixelsForMeters($ecartBoueesXmetres+2*$ecartBordure);
}

if (abs($yminPoly-$ymaxPoly) < 2 * $deltaYpixelsCinquanteMetres){
    $ecartBoueesYmetres=50; // 46 mètres de dog leg à porte sous le vent
    $deltaYpixelsCinquanteMetres=howMuchYPixelsForMeters($ecartBoueesYmetres+2*$ecartBordure);
}

// Seconde adaptation aux dimensions du plan d'eau
$deltaXpixelsSite=$deltaXpixelsDixMetres;

// $deltaXpixelsSite=min(round(abs($xmaxPoly-$xminPoly)/2.0),$deltaXpixelsDixMetres);
if (round(abs($ymaxPoly-$yminPoly)/2.0) > $deltaYpixelsCinquanteMetres){
    $deltaYpixelsSite=min(round(abs($ymaxPoly-$yminPoly)/2.0),round(3*$deltaYpixelsCinquanteMetres/2.0));
}
else{
    $deltaYpixelsSite=$deltaYpixelsCinquanteMetres;
}

if ($debug1){
    echo "<br>Boîte englobante du polygone : (Xmin,Ymin):(".$xminPoly.",".$yminPoly.") (Xmax, Ymax):(".$xmaxPoly.",".$ymaxPoly.")\n";
    echo "<br>Largeur : distance(Xmin,Xmax): ".abs($xminPoly-$xmaxPoly)." Hauteur : distance(Ymin,Ymax): ".abs($ymaxPoly-$yminPoly)."\n";
    echo "<br>Distance horizontale pour 1000 \"pixels\": ".$distance_H_MillePixels."\n";
    echo "<br>Distance verticale pour 1000 \"pixels\": ".$distance_V_MillePixels."\n";
    echo "<br>Nombre de \"pixels\" pour une distance horizontale de ".$ecartBoueesXmetres." mètres: ".$deltaXpixelsDixMetres."\n";
    echo "<br>Nombre de \"pixels\" pour une distance verticale de ".$ecartBoueesYmetres." mètres: ".$deltaYpixelsCinquanteMetres."\n";
    echo "<br><b>Après optimisation</b><br>Nombre de \"pixels\" entre les bouées de départ : ".$deltaXpixelsSite."\n";
    echo "<br>Nombre de \"pixels\" entre la porte et le dog leg: ".$deltaYpixelsSite."\n";
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
        $tab_d=json_decode($tab_distances[$i],false);
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

if ($debug1){    
    echo "<br><br><b>Distance minimale du polygone à la ligne</b><br>\n";
    echo ("Sommet du polygone ".$sommetmin.", (x0:".$coordonneesmin[0].", y0:".$coordonneesmin[1].")<br>Intersection avec la ligne : (Ix:".$intersectionmin[0].", Iy:".$intersectionmin[1]."),<br>Distance (pixels) ".$distanceecranmin.", Distance (m) ".$distanceterrainmin);
    echo "<br>\n";
}

// Balayer l'axe X par des droites x=constante pour déterminer les points d'intersection avec le polygone de navigation.
// Calculer la distance entre ces points d'intersection
// Si cette distance est supérieure au seuil enregistrer les points d'intersection.  

// Tracer des droites verticlae (x=cte)
// on va balayer tout le plan d'eau sans se préoccuper de la distance à la zone des concurrents
// <br>Boîte englobante du polygone : (Xmin,Ymin):(".$xminPoly.",".$yminPoly.") (Xmax, Ymax):(".$xmaxPoly.",".$ymaxPoly.")\n";

// Cette partie doit être améliorée



// Version sans tenir compte de la position de la ligne des concurrents
/****************************************
    $sensprogression=1;
    $xInitial=$xminPoly;
    $xFinal=$xmaxPoly;
****************************************/

/* *******************************************************************************
*  Tentative pour améliorer le placement au plus près de la ligne des concurrents 
*  ******************************************************************************/
if ($coordonneesmin[0]>=$intersectionmin[0]){
    $sensprogression=1;
    $xInitial=$xminPoly;
    $xFinal=$xmaxPoly;
    $x0=$xminPoly;
}
else{
    $sensprogression=-1;
    $xInitial=$xmaxPoly;
    $xFinal=$xminPoly;    
    $x0=$xmaxPoly;
}


$incrementX=$sensprogression*INCREMENT; // Environ 1m vers l'Est ou vers l'Ouest
    
$encore=true;
$succes=false;
while ($encore && !$succes){
    // nouvelle recherche
    $succes= rechercher_rectangle_utile($incrementX, $xInitial, $xFinal, $sensprogression); 
    if (!$succes){
        $x0+=$sensprogression * GRAND_INCREMENT;
        if ($sensprogression==1){
            $encore= ($x0 <= $xFinal);
        }
        else{
            $encore= ($x0 >= $xFinal);
        }    
    }
}

return $succes;
}

?>
