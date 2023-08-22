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
    //return ((M_PI / 180.0) * ((450 - $angle) % 360));
    //return (5.0 * M_PI - $angle * M_PI / 90.0);
    //return ((450.0 - $angle) * M_PI / 90.0);
    return (((450 - $angle) % 360) * M_PI / 90.0);
}

// angle est exprimé en radian dans le repère orthonormé direct
// retourne la valeur de l'angle  en degré dans le repère géographique
function get_degre_repere_geographique($angle){
    return ((180.0 * M_1_PI * (5 * M_PI_2 - $angle)) % 360);
}


// Conversion coordonnées géographique lon, lat en coordonnées écran du canvas
// cavasw et canvsh sont définis plus haut

function get_Xecran_lon2($lon){
    global $canvasw; 
    global $lonmax;
    global $lonmin;
    return round(($lon - ($lonmax+$lonmin) / 2.0) * $canvasw / ($lonmax - $lonmin));
}  
   
function get_Yecran_lat2($lat){  
    global $canvash; 
    global $latmax;
    global $latmin;
    return round(($lat - ($latmax+$latmin) / 2.0) * $canvash / ($latmin-$latmax));
}    


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
    return (($lat - ($latmax+$latmin) / 2.0) * $canvash / ($latmin-$latmax));
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
    // return ($x * ($lonmin-$lonmax) / ($zoom*$canvasw) + $lonmax*1.0);
    return ( $x * ($lonmax-$lonmin) / $canvasw + ($lonmax+$lonmin) / 2.0);
} 
 
function get_lat_Yecran($y){
    global $canvash; 
    //global $zoom;
    global $latmax;
    global $latmin;
    //return ($y * ($latmin-$latmax) / ($zoom*$canvash) + $latmax*1.0);
    return ( $y * ($latmin-$latmax) / $canvash + ($latmax+$latmin) / 2.0);
} 





 
/** **************************************************
 * Transfert des positions des bouées de l'écran d'affichage 
 * vers l'écran de saisie
 * ***************************************************/

// Passer du canavas d'affichage vers le canvas de saisie
/*
    ctx.translate(-canvasw/2, -(canvash/2+20);// T1
    ctx.rotate(PI / 2 - twd_radian);   // R
    ctx.translate(canvasw/2, canvash/2-20);       // T2


    M = [sin(𝛼)   cos(𝛼)] 
       [-cos(𝛼)  sin(𝛼)]
       
    ctx.translate(+canvasw/2, (canvash/2+20);   
*/

//---------------------------
function fromDisplayToSaisieCoordLonLat2($x, $y){
    // Appliquer les transformations inverses (T) ° (-R) ° (-T)
    global $twd_radian;
    $cx=setDisplayToSaisieX2($x,$y,$twd_radian); 
    $cy=setDisplayToSaisieY2($x,$y,$twd_radian);
    return json_decode('{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}');
}


function setDisplayToSaisieX2($x,$y, $radian){
// On applique une translation T1, une rotation R  de (twd_radian - PI/2) et une translation -T1   
    global $canvasw;
    global $canvash;
    $x0 = $x - $canvasw/2.0; // Translation T1
    $y0 = $y - ($canvash/2.0);
    $x1 = $x0*sin($radian) + $y0*cos($radian); // Rotation R
    return ($x1 + $canvasw/2.0); // Translation -T1
}
 

function setDisplayToSaisieY2($x,$y,$radian){
// On applique une translation T, une rotation R  et une translation -T   
global $canvasw;
global $canvash;
    $x0 = $x - $canvasw/2.0; // Translation T1
    $y0 = $y - ($canvash/2.0);
    $y1 = - $x0 * cos($radian) + $y0*sin($radian);
    return ($y1 + ($canvash/2.0));
}
 

//---------------------------
function fromDisplayToSaisieCoordLonLat($x, $y){
    // Appliquer les transformations inverses (T) ° (-R) ° (-T)
    global $twd_radian;
    $cx=setDisplayToSaisieX($x,$y,$twd_radian); 
    $cy=setDisplayToSaisieY($x,$y,$twd_radian);
    return json_decode('{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}');
}


function setDisplayToSaisieX($x,$y, $radian){
// On applique une rotation R  de (twd_radian - PI/2)    
    return ($x * sin($radian) + $y * cos($radian)); // Rotation R
}
 

function setDisplayToSaisieY($x,$y,$radian){
// On applique  une rotation R     
    return (($y * sin($radian)- $x * cos($radian)));
}
 

/** **************************************************
 * Transfert des positions des bouées vers la carte  *
 * ***************************************************/

// Repasser dans le repère du canavas d'origine 
/*
    ctx.translate(-canvasw/2, -canvash/2+20); 
    ctx.rotate(twd_radian - PI / 2); --> PI / 2 - twd_radian
    M = [sin(𝛼)   -cos(𝛼)] 
       [cos(𝛼)  sin(𝛼)]
    ctx.translate(canvasw/2, canvash/2-20);   
*/

//---------------------------
function fromScreenToGeoCoord2($x, $y){
    // Ne pas oublier d'appliquer les transformations inverses (-T) ° TR ° (T)
    global $twd_radian;
    $cx=setSaisieToDisplayX2($x,$y,$twd_radian); 
    $cy=setSaisieToDisplayY2($x,$y,$twd_radian);
    return json_decode('{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}');
}


function setSaisieToDisplayX2($x,$y, $radian){
// On applique une translation -T, une rotation inverse R  et une translation T   
global $canvasw;
global $canvash;
    $x0 = $x - $canvasw/2.0; // Translation T1
    $y0 = $y - ($canvash/2.0);
    $x1 = $x0*sin($radian) - $y0*cos($radian); // Rotation R
    return round($x1 + $canvasw/2.0); // Translation T2
}
 
function setSaisieToDisplayY2($x,$y,$radian){
// On applique une translation T1, une rotation R  et une translation T2
   global $canvasw;
    global $canvash;
    $x0 = $x - $canvasw/2.0; // Translation T1
    $y0 = $y - ($canvash/2.0);
    $y1 = $x0 * cos($radian) + $y0*sin($radian);
    return round($y1 + $canvash/2.0);
}
 

//---------------------------
function fromScreenToGeoCoord($x, $y){
    // Ne pas oublier d'appliquer les transformations inverses (-T) ° TR ° (T)
    global $twd_radian;
    $cx=setSaisieToDisplayX($x,$y,$twd_radian); 
    $cy=setSaisieToDisplayY($x,$y,$twd_radian);
    return json_decode('{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}');
}


function setSaisieToDisplayX($x,$y, $radian){
// On applique une translation -T, une rotation inverse R  et une translation T   
    return ($x*sin($radian) - $y*cos($radian)); // Rotation R
}
 
function setSaisieToDisplayY($x,$y,$radian){
// On applique une translation T1, une rotation R  et une translation T2
    return ($x * cos($radian) + $y*sin($radian));
}
?>
