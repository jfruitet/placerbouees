<?php
// PHP Document
// Fonctions de conversion de données d'un repère à l'autre

$twd_degre=0;       // Direction d'où soufle le vnt dans le repère de la rose des vents (Nord=0, Est=90, etc.)
$twd_radian=0.0;    // Ceci N'EST PAS la simple conversion de la direction du vent en radian mais bien la conversion avec un changement de repère
                    // 0= Est, PI/2 = Nord Est ; PI = Nord ; 3*PI/2 = Nord Ouest; 2 * PI = Ouest; 5*PI/2 = Sud Ouest ; 3 * PI / 2 = Sud; etc.

$windsector=array("N","NNE","NE","ENE","E","ESE","SE","SSE","S","SSW","SW","WSW","W","WNW","NW","NNW","N");

$canvasw=100000; // Pour conserver une certaine cohérence avec les canvas de l'appli web
$canvash=100000;

$coeff_homothetie = 1.0; // On va garder les figures du canvas homothétiques de celles de la carte
$delta_lonlat=360.0; // Maximum de taille de la fenêtre géographique

$lonmin=180; // en degré géographique
$latmin=90;
$lonmax=-179; // en degré géographique EST ligne de changement d'horaire
$latmax=-90;

$milieu_lon=0;
$milieu_lat=0;

$poly_xecran= array(); // Tableau des coordonnées écran de la zone de navigation
$poly_yecran= array();
$ligne_xecran= array(); // Tableau des coordonnées écran de la ligne de déambulation des concurrents
$ligne_yecran= array();

$balisesEcran=array(); // Objets balises fixes

$poly_xsaisie= array(); // Tableau des coordonnées écran de la zone de navigation après rotation face au vent
$poly_ysaisie= array();
$ligne_xsaisie= array(); // Tableau des coordonnées écran de la ligne de déambulation des concurrents après rotation face au vent
$ligne_ysaisie= array();

$zonenav_lon=array();   // Tableau des coordonnées géographiques (longitude) de la zone de navigation
$zonenav_lat=array();   // Tableau des coordonnées géographiques (latitude) de la zone de navigation
$zoneconc_lon=array();  // Tableau des coordonnées géographiques (longitude) de la zone des concurrents
$zoneconc_lat=array();  // Tableau des coordonnées géographiques (latitude) de la zone des concurrents

$balises_name=array();
$balises_lon=array();
$balises_lat=array();    

// angle est exprimé en degré dans le repère géographique
// retourne la valeur de l'angle en radian dans le repère orthonormé direct
/*
Pi is often usefull in con/sin/tan functions..
There are also other Pi related constants.
These are most of them:

M_PI  = 3.14159265358979323846 // pi

// The following were added in PHP 4.0.0
M_PI_2 = 1.57079632679489661923 // pi/2
M_PI_4 = 0.78539816339744830962 // pi/4
M_1_PI = 0.31830988618379067154 // 1/pi
M_2_PI = 0.63661977236758134308 // 2/pi
M_SQRTPI = 1.77245385090551602729 // sqrt(pi) (Only in PHP 4.0.2+)
M_2_SQRTPI = 1.12837916709551257390 // 2/sqrt(pi)
*/

function get_radian_repere_direct($angle){
    return ((M_PI / 180.0) * ((450 - $angle) % 360));
}

// angle est exprimé en radian dans le repère orthonormé direct
// retourne la valeur de l'angle  en degré dans le repère géographique
function get_degre_repere_geographique($angle){
    return ((180.0 * M_1_PI * (5 * M_PI_2 - $angle)) % 360);
}


// Conversion d'un polygone géographique en polygone écran
// Il faut calculer le rectangle englobant puis se ramener par homothétie et translation aux coordonnées du canvas
// Attention : le repère géographique croit d'EST en OUEST (longitude) et de SUD au Nord (latitude)
// A adapter au repère orthogonal normé utilisé dans ce script


// Boite englobante de la zone de navigation + zone d'évolution des concurrents
// ---------------------------------
function rectangle_englobantZN(){
    global $lonmax;
    global $lonmin;
    global $canvasw;
    global $latmax;
    global $latmin;
    global $canvash;
    global $zonenav_lon;
    global $zonenav_lat;
    global $zonenav_lat;
    global $zoneconc_lon;    
    global $zoneconc_lat;
    global $milieu_lon;
    global $milieu_lat;
    
    $lonmin=180; // en degré géographique
    $latmin=90;
    $lonmax=-179; // en degré géographique EST ligne de changement d'horaire
    $latmax=-90;
    $index=0;
    if (($zonenav_lat !== null) && (count($zonenav_lat)>0)){
        while ($index<count($zonenav_lat)){
            if ($zonenav_lat[$index]<=$latmin){ $latmin=$zonenav_lat[$index];}  
            if ($zonenav_lat[$index]>$latmax){ $latmax=$zonenav_lat[$index];}
            if ($zonenav_lon[$index]<=$lonmin){ $lonmin=$zonenav_lon[$index];}  
            if ($zonenav_lon[$index]>$lonmax){ $lonmax=$zonenav_lon[$index];}    
            $index++;
        }
    }
    else {
        echo("geo_utils.php :: ligne 79 :: zonenav_lat vide\n");  
    }  
    if (($zoneconc_lat != null) && (count($zoneconc_lat)>0)){
        $index=0;
        while ($index<count($zoneconc_lat)){
            if ($zoneconc_lat[$index]<=$latmin){ $latmin=$zoneconc_lat[$index];}  
            if ($zoneconc_lat[$index]>$latmax){ $latmax=$zoneconc_lat[$index];}
            if ($zoneconc_lon[$index]<=$lonmin){ $lonmin=$zoneconc_lon[$index];}  
            if ($zoneconc_lon[$index]>$lonmax){ $lonmax=$zoneconc_lon[$index];}    
            $index++;
        }
    }  
    else{
        echo("geo_utils.php :: ligne 92 :: zoneconc_lat vide\n");
    }    
  
    // Pour avoir des figures homothétiques sans cisaillement 
    // N'est pasutilisé pour le moment
    $delta_lat=$latmax-$latmin; 
    $delta_lon=$lonmax-$lonmin;
    //echo("geo_utils.php :: ligne 125 :: delta_lon:".$delta_lon." delta_lat:".$delta_lat."\n");
    
    if (abs($delta_lat)<abs($delta_lon)){
        $coeff_homothetie = $canvasw / abs($delta_lon);
        $delta_lonlat=abs($delta_lon);
    }
    else{
        $coeff_homothetie = $canvash / abs($delta_lat);
        $delta_lonlat=abs($delta_lat);  
    }
    //echo("geo_utils.php :: ligne 135 :: coeff_homothetie:".$coeff_homothetie." delta_lonlat:".$delta_lonlat."\n");
        
    $milieu_lon= ($lonmin + $lonmax) /2.0;
    $milieu_lat= ($latmin + $latmax) /2.0;
    
    $pointSup = new stdClass();
    $pointSup->lon=$lonmax;
    $pointSup->lat=$latmax;
    $pointInf= new stdClass();
    $pointInf->lon=$lonmin;
    $pointInf->lat=$latmin;
    // echo("geo_utils.php :: Delta Lat : ".$delta_lat." Delta_lon : ".$delta_lon."\n");
    // echo("PointSup:: Lon:".$pointSup->lon." Lat:".$pointSup->lat. "\nPointInf :: Lon:".$pointInf->lon." Lat:".$pointInf->lat."\n");
}      
    
// Distances 
// On applique la formule de la distance selon un grand cercle 
// seulement valable à l'équateur pour les longitudes sur la projection Mercator
// Distance (km) = Rayon terreste(6400 km) * angle (°)  *  PI / 180

// Distance entre deux poits de l'écran
// --------------------------------------
function distancePointsEcran($x1,$y1,$x2,$y2){
    return sqrt(($x1-$x2) * ($x1-$x2) + ($y1-$y2) * ($y1-$y2)); 
}



// Distance entre deux points du plan d'eau
//---------------------------------------
function distanceGeodesique($lon1,$lat1,$lon2,$lat2){
    $anglelon=abs($lon2 - $lon1);
    $anglelat=abs($lat2 - $lat1);
    $dlon =  6378137.0 * $anglelon * M_PI / 180.0;
    $dlat =  6356752.0 * $anglelat * M_PI / 180.0;  
    $distance = sqrt($dlon * $dlon + $dlat * $dlat);
    return (round($distance * 100.0) / 100.0);
}
            
// --------------------------------------
function distanceEcran2Earth($x1,$y1,$x2,$y2){
    $gcoord1=fromScreenToGeoCoord($x1, $y1);
    $gcoord2=fromScreenToGeoCoord($x2, $y2);
    return(distanceGeodesique($gcoord1->lon,$gcoord1->lat,$gcoord1->lon,$gcoord2->lat));        
}

// --------------------------------------
function distanceHorizontalePixels($x,$y,$npixels){
    return distanceEcran2Earth($x, $y, $x+$npixels, $y);
}

// --------------------------------------
function distanceVerticalePixels($x,$y,$npixels){
    return distanceEcran2Earth($x, $y, $x, $y+$npixels);
}


// Secteurs du vent 
// --------------------------------------
function secteur_vent($twd){
    return windsector[round(($twd % 360) / 22.5)];
}


 /****************************************
  *     De la Terre à l'Ecran
  ****************************************/
  

// Conversion coordonnées géographique lon, lat en coordonnées écran du canvas
// $cavasw est défini plus haut

// Origine coin supérieur gauche, sens indirect
function get_Xecran_lon2($lon){
    global $lonmax;
    global $lonmin;
    global $canvasw;
    return round(($lon-$lonmax) * $canvasw / ($lonmin-$lonmax));
}  

function get_Yecran_lat2($lat){  
    global $latmax;
    global $latmin;
    global $canvash;    
    return round(($lat-$latmax) * $canvash / ($latmin-$latmax));
}    


   
// Centré sur 0,0 en milieu d'écran, repère normal, sens direct
function get_Xecran_lon($lon){
    global $lonmax;
    global $lonmin;
    global $canvasw;
    return round(($lon-(($lonmax+$lonmin)/2.0)) * $canvasw / ($lonmin-$lonmax));
}  

function get_Yecran_lat($lat){  
    global $latmax;
    global $latmin;
    global $canvash;    
    return round(($lat-(($latmax+$latmin)/2.0)) * $canvash / ($latmax-$latmin));
}    



// Conversion des coordonnées géographique en coordonnées "écran"
function set_X_Ecran_polygone_navigation(){
    global $zonenav_lon;
    global $poly_xecran;
    $poly_xecran=array();
    $index=0;
    while ($index<count($zonenav_lon)){
        $poly_xecran[$index]=get_Xecran_lon($zonenav_lon[$index]);
        $index++;
    }  
}

function set_Y_Ecran_polygone_navigation(){
    global $zonenav_lat;
    global $poly_yecran;
    $poly_yecran=array();
    $index=0;    
    while ($index<count($zonenav_lat)){
        $poly_yecran[$index]=get_Yecran_lat($zonenav_lat[$index]);
        $index++;
    }  
}

function set_X_Ecran_ligne_concurrents(){
    global $zoneconc_lon;
    global $ligne_xecran;
    $ligne_xecran=array();
    $index=0;
    while ($index<count($zoneconc_lon)){
        $ligne_xecran[$index]=get_Xecran_lon($zoneconc_lon[$index]);
        $index++;
    }  
}

function set_Y_Ecran_ligne_concurrents(){
    global $zoneconc_lat;
    global $ligne_yecran;
    $ligne_yecran=array();
    $index=0;  
    while ($index<count($zoneconc_lat)){
        $ligne_yecran[$index]=get_Yecran_lat($zoneconc_lat[$index]);
        $index++;
    }  
}



// conversions 
// ------------------------------------------------------------
function init_ecran_ZN(){
    rectangle_englobantZN(); // Pour les fonctions de changement de repère
    set_X_Ecran_polygone_navigation();  // table des X
    set_Y_Ecran_polygone_navigation();  // Table des Y   
    set_X_Ecran_ligne_concurrents();  // table des X
    set_Y_Ecran_ligne_concurrents();  // Table des Y           
}

// -----------------------------------------------
function rotation_ecran_ZN($radian){
global $poly_xsaisie;
global $poly_ysaisie;
global $ligne_xsaisie;
global $ligne_ysaisie;
global $poly_xecran;
global $poly_yecran;
global $ligne_xecran;
global $ligne_yecran;
        
    $poly_xsaisie= array(); // Tableau des coordonnées écran de la zone de navigation après rotation face au vent
    $poly_ysaisie= array();
    $ligne_xsaisie= array(); // Tableau des coordonnées écran de la ligne de déambulation des concurrents après rotation face au vent
    $ligne_ysaisie= array();
    $index=0;
    while ($index<count($poly_xecran)){
        $poly_xsaisie[$index]= setDisplayToSaisieX($poly_xecran[$index],$poly_yecran[$index], $radian);
        $poly_ysaisie[$index]= setDisplayToSaisieY($poly_xecran[$index],$poly_yecran[$index], $radian);
        $index++;
    }
    $index=0;
    while ($index<count($ligne_xecran)){
        $ligne_xsaisie[$index]= setDisplayToSaisieX($ligne_xecran[$index],$ligne_yecran[$index], $radian);
        $ligne_ysaisie[$index]= setDisplayToSaisieY($ligne_xecran[$index],$ligne_yecran[$index], $radian);
        $index++;
    }
}




/*************************************
 * De l'écran à la Terre
 * ***********************************/
 
// Conversion des coordonnées d'un point dans le canvas en coordonnées géographiques lon, lat 
// $canvasw est défini plus haut
// Ne pas oublier que la définition sur la grille du canevas est très grossière par rapport
// à la grille du monde réel 

// Repère indirect
function get_lon_Xecran2($x){
    global $lonmax;
    global $lonmin;
    global $canvasw;
    return ($x * ($lonmin-$lonmax) / $canvasw + $lonmax*1.0);
} 
 
function get_lat_Yecran2($y){
    global $latmax;
    global $latmin;
    global $canvash;    
    return ($y * ($latmin-$latmax) / $canvash + $latmax*1.0);
} 

// Repère direct
function get_lon_Xecran($x){
    global $lonmax;
    global $lonmin;
    global $canvasw;
    return ($x * ($lonmin-$lonmax) / $canvasw + ($lonmax+$lonmin) / 2.0);
} 
 
function get_lat_Yecran($y){
    global $latmax;
    global $latmin;
    global $canvash;    
    return ($y * ($latmax-$latmin) / $canvash + ($latmax+$latmin)/2.0);
} 

// Conversion des coordonnées d'un point cliqué à la souris en coordonnées géographiques lon, lat 
// $canvasw et $canvash sont définis plus haut
// Ne pas oublier que la définition sur l'écran est très grossière par rapport
// à la grille du monde réel 

function get_lon_MouseXecran($x){
    global $lonmax;
    global $lonmin;
    global $canvasw;
    return ($lonmin*1.0 -  $x  * ($lonmin-$lonmax) / $canvasw);
} 
 
function get_lat_MouseYecran($y){
    global $latmax;
    global $latmin;
    global $canvash;
    return ($y * ($latmin-$latmax) / $canvash + $latmax*1.0); 
} 

 // Affiche les longitude Latitude correspondant au point sur l'écran
function screen2earth($xcoord, $ycoord) {   
  $lon = round(get_lon_MouseXecran($xcoord)*100000)/100000; // 5 décimales
  $lat = round(get_lat_MouseYecran($ycoord)*100000)/100000;
  echo (" lon <i>".$lon."</i>");
  echo (" lat <i>".$lat+"</i><br>\n");
}


/** **************************************************
 * Transfert des positions des bouées de l'écran d'affichage 
 * vers l'écran de saisie
 * ***************************************************/

// Passer du canavas d'affichage vers le canvas de saisie
/*
    ctx.translate(-canvasw/2, -($canvash/2+20);// T1
    ctx.rotate(PI / 2 - $twd_radian);   // R
    ctx.translate(canvasw/2, canvash/2-20);       // T2


    M = [sin(𝛼)   cos(𝛼)] 
       [-cos(𝛼)  sin(𝛼)]
       
    ctx.translate(+canvasw/2, ($canvash/2+20);   
*/

//---------------------------
function fromDisplayToSaisieCoordLonLat($x, $y){
    // Appliquer les transformations inverses (T) ° (-R) ° (-T)
global $twd_radian;
    $cx=setDisplayToSaisieX($x,$y,$twd_radian); 
    $cy=setDisplayToSaisieY($x,$y,$twd_radian);
    return '{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}';
}

// -----------------------
function setDisplayToSaisieX($x,$y, $radian){
// On applique une translation T1, une rotation R  de ($twd_radian - PI/2) et une translation -T1   
global $canvasw;
global $canvash;
    $x0 = $x - ($canvasw/2); // Translation T1
    $y0 = $y - ($canvash/2);
    $x1 = $x0 * sin($radian) + $y0 * cos($radian); // Rotation R
    return round($x1 + $canvasw/2.0); // Translation -T1
}
 
// -----------------------
function setDisplayToSaisieY($x,$y,$radian){
// On applique une translation T, une rotation R  et une translation -T 
global $canvasw;
global $canvash;  
    $x0 = $x - ($canvasw/2); // Translation T1
    $y0 = $y - ($canvash/2);
    $y1 = $y0 * sin($radian) - $x0 * cos($radian);
    return round($y1 + $canvash/2.0); // Translation -T1
}
 


/** **************************************************
 * Transfert des positions des bouées vers la carte  *
 * ***************************************************/

// Repasser dans le repère du canavas d'origine 
/*
    ctx.translate(-canvasw/2, -canvash/2+20); 
    ctx.rotate($twd_radian - PI / 2); --> PI / 2 - $twd_radian
    M = [sin(𝛼)   -cos(𝛼)] 
       [cos(𝛼)  sin(𝛼)]
    ctx.translate(canvasw/2, canvash/2-20);   
*/

//---------------------------
function fromScreenToGeoCoord($x, $y){
// Ne pas oublier d'appliquer les transformations inverses (-T) ° TR ° (T)
global $twd_radian; 
    $cx=setSaisieToDisplayX($x,$y,$twd_radian); 
    $cy=setSaisieToDisplayY($x,$y,$twd_radian);
    return json_decode('{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}',false);
}

// -----------------------
function setSaisieToDisplayX($x, $y, $radian){
// On applique une translation -T, une rotation inverse R  et une translation T   
global $canvasw;
global $canvash; 
    $x0 = $x - ($canvasw/2.0); // Translation T1
    $y0 = $y - ($canvash/2.0);
    $x1 = $x0 * sin($radian) - $y0 * cos($radian); // Rotation R
    return round($x1 + ($canvasw/2.0)); // Translation T2
}
 
 // -----------------------
function setSaisieToDisplayY($x, $y,$radian){
// On applique une translation T1, une rotation R  et une translation T2
global $canvasw;
global $canvash;  
    $x0 = $x - ($canvasw/2.0); // Translation T1
    $y0 = $y - ($canvash/2.0);
    $y1 = $x0 * cos($radian) + $y0 * sin($radian);
    return round($y1 + ($canvash/2.0));
}
 

// -----------------------
function init_ecran_bouees_fixes(){  
    // Les balises sont des bouées fixes stockées dans une table initialisée dans le script sitenavigation.js 
    global $balises_name;
    global $balises_lon;
    global $balises_lat;
    global $balisesEcran;
    if (($balises_name!==null) && (count($balises_name)>0)){
        $balisesEcran=array();
        for ($index=0; $index<count($balises_name); $index++) {
            // retourn un tableau d'objets                                 
            $balisesEcran[$index]=(json_decode('{"id":'.$index.',"x":'.get_Xecran_lon($balises_lon[$index]).',"y":'.get_Yecran_lat($balises_lat[$index]).',"name":"'.$balises_name[$index]+'"}', false));
        }     
    }   
}


