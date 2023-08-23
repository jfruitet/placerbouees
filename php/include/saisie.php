<?php

// Fonctions de conversion du monde vers l'écran, de l'écran vers la saisie, et retour
// ATTENTION : aucun arrondi dans les transformations car cela introduit de très légères perturbations

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


// Conversion coordonnées géographique lon, lat en coordonnées écran du canvas
// cavasw et canvsh sont définis plus haut

function get_Xecran_lon($lon){
    global $canvasw; 
    global $lonmax;
    global $lonmin;
    return (($lon - ($lonmax+$lonmin) / 2.0) * $canvasw / ($lonmax - $lonmin));
}  
   
function get_Yecran_lat($lat){  
    global $canvash; 
    global $latmax;
    global $latmin;
    return (($lat - ($latmax+$latmin) / 2.0) * $canvash / ($latmax-$latmin));
}    



/*************************************
 * De l'écran à la Terre
 * ***********************************/
 
// Conversion des coordonnées d'un point dans le canvas en coordonnées géographiques lon, lat 
// canvasw et zoom sont définis plus haut
// Ne pas oublier que la définition sur la grille du canevas est très grossière par rapport
// à la grille du monde réel 
function get_lon_Xecran($x){
    global $canvasw; 
    //global $zoom;
    global $lonmax;
    global $lonmin;
    return ($x * ($lonmax-$lonmin) / $canvasw + ($lonmin+$lonmax) / 2.0);
} 
 
function get_lat_Yecran($y){
    global $canvash; 
    //global $zoom;
    global $latmax;
    global $latmin;
    return ($y * ($latmax-$latmin) / $canvash + ($latmax+$latmin) / 2.0);
} 





 
/** **************************************************
 * Transfert des positions des bouées de l'écran d'affichage 
 * vers l'écran de saisie
 * ***************************************************/
 
 // La direction d'où soufle le vent est twd_radian
 // On applique à la figure une rotation pour la placer face au nord.
 // Angle de rotation : PI/2 - twd_radian

// Passer du canavas d'affichage vers le canvas de saisie
/*
    ctx.rotate(PI / 2 - twd_radian);   // R


    M = [sin(𝛼)   cos(𝛼)] 
       [-cos(𝛼)  sin(𝛼)]
 
*/



//---------------------------
function fromDisplayToSaisieCoordLonLat($x, $y){
    // Appliquer les transformations inverses (T) ° (-R) ° (-T)
    global $twd_radian;
    $cx=setDisplayToSaisieX($x,$y,$twd_radian); 
    $cy=setDisplayToSaisieY($x,$y,$twd_radian);
    return json_decode('{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}');
}




function setDisplayToSaisieX($x,$y, $radian){
// On applique une rotation R  de (PI/2-$radian)    
    return ($x * sin($radian) - $y * cos($radian)); // Rotation R
}
 

function setDisplayToSaisieY($x,$y,$radian){
// On applique  une rotation R  = PI/2 - twd_radian   
    return ($x * cos($radian) + $y * sin($radian));
}


 
/** **************************************************
 * Transfert des positions des bouées vers la carte  *
 * ***************************************************/

// Repasser dans le repère du canavas d'origine 
/*
 
    ctx.rotate(twd_radian - PI / 2)
    M = [sin(𝛼)   -cos(𝛼)] 
       [cos(𝛼)  sin(𝛼)]
  
*/

 

//---------------------------
function fromScreenToGeoCoord($x, $y){
    // Ne pas oublier d'appliquer les rotation inverses   (-R) 
    global $twd_radian;
    $cx=setSaisieToDisplayX($x,$y,$twd_radian); 
    $cy=setSaisieToDisplayY($x,$y,$twd_radian);
    return json_decode('{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}');
}


function setSaisieToDisplayX($x,$y, $radian){
// On applique une rotation inverse R = $twd_radian  - PI / 2 
    return ($x*sin($radian) + $y*cos($radian)); // Rotation R
}
 
function setSaisieToDisplayY($x,$y,$radian){
// On applique une rotation R  r = $twd_radian - PI/2
    return (- $x * cos($radian) + $y*sin($radian));
}



/*
// Rotation inversée : BUG
function setSaisieToDisplayY($x,$y, $radian){
// On applique une rotation inverse R = $twd_radian  - PI / 2 
    return ($x*sin($radian) + $y*cos($radian)); // Rotation R
}
 
function setSaisieToDisplayX($x,$y,$radian){
// On applique une rotation R  r = $twd_radian - PI/2
    return (- $x * cos($radian) + $y*sin($radian));
}

*/

?>
