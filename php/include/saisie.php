<?php

// Fonctions de conversion du monde vers l'écran, de l'écran vers la saisie, et retour

/******************************************
 *  Mouse To Canvas
 * ****************************************/
function setMouseXPos($xmousecoord){  // retourne une position dans le canvas en fonction de la position de la souris 
    //return round(xmousecoord * (canvasw*zoom) / cw);
    global $cw;
    global $canvasw;  
    return round($xmousecoord * ($canvasw) / $cw);
}

function setMouseYPos($ymousecoord){
    //return round(ymousecoord * (canvash*zoom) / ch);
    global $ch;
    global $canvash; 
    return round($ymousecoord * ($canvash) / $ch);
}

// Conversion coordonnées géographique lon, lat en coordonnées écran du canvas
// cavasw est défini plus haut

function get_Xecran_lon($lon){
    global $cw;
    global $canvasw; 
    global $zoom;
    global $lonmax;
    global $lonmin;
    // return round(zoom * canvasw * (1 - (lon-lonmax) / (lonmin-lonmax)));
    return round(($lon-$lonmax) * ($cw*$zoom) / ($lonmin-$lonmax));
}  
   
function get_Yecran_lat($lat){  
    global $ch;
    global $canvash; 
    global $zoom;
    global $latmax;
    global $latmin;
    //return round((lat-latmax) * (zoom*canvash) / (latmin-latmax)); 
    return round(($lat-$latmax) * ($zoom*$ch) / ($latmin-$latmax));
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
    global $zoom;
    global $lonmax;
    global $lonmin;
    return ($x * ($lonmin-$lonmax) / ($zoom*$canvasw) + $lonmax*1.0);
} 
 
function get_lat_Yecran($y){
    global $canvash; 
    global $zoom;
    global $latmax;
    global $latmin;
    return ($y * ($latmin-$latmax) / ($zoom*$canvash) + $latmax*1.0);
} 



// Conversion des coordonnées d'un point cliqué à la souris en coordonnées géographiques lon, lat 
// cw et ch sont définis plus haut
// Ne pas oublier que la définition sur l'écran est très grossière par rapport
// à la grille du monde réel 

function get_lon_MouseXecran($x){
    global $cw;
    global $lonmax;
    global $lonmin;
    return ($lonmin -  $x  * ($lonmin-$lonmax) / $cw);
} 
 
function get_lat_MouseYecran($y){
    global $ch;
    global $latmax;
    global $latmin;
    return ($y * ($latmin-$latmax) / $ch + $latmax);
} 

 // Affiche les longitude Latitude correspondant au point sur l'écran
function screen2earth($xcoord, $ycoord) {   
  $lon=round(get_lon_MouseXecran($xcoord)*100000.0/100000.0); // 5 décimales
  $lat= round(get_lat_MouseYecran($ycoord)*100000.0/100000.0);
  echo "<br>Lon:".$lon.", Lat:".$lat."\n";
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
function fromDisplayToSaisieCoordLonLat($x, $y){
    // Appliquer les transformations inverses (T) ° (-R) ° (-T)
    global $twd_radian;
    $cx=setDisplayToSaisieX($x,$y,$twd_radian); 
    $cy=setDisplayToSaisieY($x,$y,$twd_radian);
    return json_decode('{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}');
}


function setDisplayToSaisieX($x,$y, $radian){
// On applique une translation T1, une rotation R  de (twd_radian - PI/2) et une translation -T1   
    global $canvasw;
    global $canvash;
    $x0 = $x - $canvasw/2; // Translation T1
    $y0 = $y - ($canvash/2+20);
    $x1 = $x0*sin($radian) + $y0*cos($radian); // Rotation R
    return round($x1 + $canvasw/2); // Translation -T1
}
 

function setDisplayToSaisieY($x,$y,$radian){
// On applique une translation T, une rotation R  et une translation -T   
global $canvasw;
global $canvash;
    $x0 = $x - $canvasw/2; // Translation T1
    $y0 = $y - ($canvash/2+20);
    $y1 = - $x0 * cos($radian) + $y0*sin($radian);
    return round($y1 + ($canvash/2+20));
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
function fromScreenToGeoCoord($x, $y){
    // Ne pas oublier d'appliquer les transformations inverses (-T) ° TR ° (T)
    global $twd_radian;
    $cx=setSaisieToDisplayX($x,$y,$twd_radian); 
    $cy=setSaisieToDisplayY($x,$y,$twd_radian);
    return json_decode('{"lon":'.get_lon_Xecran($cx).',"lat":'.get_lat_Yecran($cy).'}');
}


function setSaisieToDisplayX($x,$y, $radian){
// On applique une translation -T, une rotation inverse R  et une translation T   
global $canvasw;
global $canvash;
    $x0 = $x - $canvasw/2; // Translation T1
    $y0 = $y - ($canvash/2+20);
    $x1 = $x0*sin($radian) - $y0*cos($radian); // Rotation R
    return round($x1 + $canvasw/2); // Translation T2
}
 
function setSaisieToDisplayY($x,$y,$radian){
// On applique une translation T1, une rotation R  et une translation T2
   global $canvasw;
    global $canvash;
    $x0 = $x - $canvasw/2; // Translation T1
    $y0 = $y - ($canvash/2+20);
    $y1 = $x0 * cos($radian) + $y0*sin($radian);
    return round($y1 + $canvash/2+20);
}
 
?>
