// JavaScript Document



/******************************************
 *  Mouse To Canvas
 * ****************************************/
function setMouseXPos(xmousecoord){  // retourne une position dans le canvas en fonction de la position de la souris 
    //return Math.round(xmousecoord * (canvasw*zoom) / cw);
    return Math.round(xmousecoord * (canvasw) / cw);
}

function setMouseYPos(ymousecoord){
    //return Math.round(ymousecoord * (canvash*zoom) / ch);
    return Math.round(ymousecoord * (canvash) / ch);
}

// Conversion coordonnées géographique lon, lat en coordonnées écran du canvas
// cavasw est défini plus haut

function get_Xecran_lon(lon){
    // return Math.round(zoom * canvasw * (1 - (lon-lonmax) / (lonmin-lonmax)));
    return Math.round((lon-lonmax) * (cw*zoom) / (lonmin-lonmax));
}  
   
function get_Yecran_lat(lat){  
    //return Math.round((lat-latmax) * (zoom*canvash) / (latmin-latmax)); 
    return Math.round((lat-latmax) * (zoom*ch) / (latmin-latmax));
}    



// et hop!
function set_X_Ecran_polygone_navigation(){
  var index=0;
  poly_xecran.length=0;
  while (index<zonenav_lon.length){
    poly_xecran[index]=get_Xecran_lon(zonenav_lon[index]);
    index++;
  }  
}

function set_Y_Ecran_polygone_navigation(){
  var index=0;
  poly_yecran.length=0;
  while (index<zonenav_lat.length){
    poly_yecran[index]=get_Yecran_lat(zonenav_lat[index]);
    index++;
  }  
}

function set_X_Ecran_ligne_concurrents(){
  var index=0;
  ligne_xecran.length=0;
  while (index<zoneconc_lon.length){
    ligne_xecran[index]=get_Xecran_lon(zoneconc_lon[index]);
    index++;
  }  
}

function set_Y_Ecran_ligne_concurrents(){
  var index=0;
  ligne_yecran.length=0;
  while (index<zonenav_lat.length){
    ligne_yecran[index]=get_Yecran_lat(zoneconc_lat[index]);
    index++;
  }  
}


/*************************************
 * De l'écran à la Terre
 * ***********************************/
 
// Conversion des coordonnées d'un point dans le canvas en coordonnées géographiques lon, lat 
// canvasw et zoom sont définis plus haut
// Ne pas oublier que la définition sur la grille du canevas est très grossière par rapport
// à la grille du monde réel 
function get_lon_Xecran(x){
    return (x * (lonmin-lonmax) / (zoom*canvasw) + lonmax*1.0);
} 
 
function get_lat_Yecran(y){
    return (y * (latmin-latmax) / (zoom*canvash) + latmax*1.0);
} 



// Conversion des coordonnées d'un point cliqué à la souris en coordonnées géographiques lon, lat 
// cw et ch sont définis plus haut
// Ne pas oublier que la définition sur l'écran est très grossière par rapport
// à la grille du monde réel 

function get_lon_MouseXecran(x){
    return (lonmin -  x  * (lonmin-lonmax) / cw);
} 
 
function get_lat_MouseYecran(y){
    return (y * (latmin-latmax) / ch + latmax);
} 

 // Affiche les longitude Latitude correspondant au point sur l'écran
function screen2earth() {   
  var lon = Math.round(get_lon_MouseXecran(xcoord)*100000)/100000; // 5 décimales
  var lat = Math.round(get_lat_MouseYecran(ycoord)*100000)/100000;
  document.getElementById("lon").innerHTML = "<i>"+lon+"</i>";
  document.getElementById("lat").innerHTML = "<i>"+lat+"</i>";
}


 
/** **************************************************
 * Transfert des positions des bouées de l'écran d'affichage 
 * vers l'écran de saisie
 * ***************************************************/

// Passer du canavas d'affichage vers le canvas de saisie
/*
    ctx.translate(-canvasw/2, -(canvash/2+20);// T1
    ctx.rotate(Math.PI / 2 - twd_radian);   // R
    ctx.translate(canvasw/2, canvash/2-20);       // T2


    M = [sin(𝛼)   cos(𝛼)] 
       [-cos(𝛼)  sin(𝛼)]
       
    ctx.translate(+canvasw/2, (canvash/2+20);   
*/

//---------------------------
function fromDisplayToSaisieCoordLonLat(x, y){
    // Appliquer les transformations inverses (T) ° (-R) ° (-T)
    var cx=setDisplayToSaisieX(x,y,twd_radian); 
    var cy=setDisplayToSaisieY(x,y,twd_radian);
    return {"lon":get_lon_Xecran(cx),"lat":get_lat_Yecran(cy)};
}


function setDisplayToSaisieX(x,y, radian){
// On applique une translation T1, une rotation R  de (twd_radian - PI/2) et une translation -T1   
    var x0 = x - canvasw/2; // Translation T1
    var y0 = y - (canvash/2+20);
    var x1 = x0*Math.sin(radian) + y0*Math.cos(radian); // Rotation R
    return Math.round(x1) + canvasw/2; // Translation -T1
}
 

function setDisplayToSaisieY(x,y,radian){
// On applique une translation T, une rotation R  et une translation -T   
    var x0 = x - canvasw/2; // Translation T1
    var y0 = y - (canvash/2+20);
    var y1 = - x0 * Math.cos(radian) + y0*Math.sin(radian);
    return Math.round(y1) + (canvash/2+20);
}
 


/** **************************************************
 * Transfert des positions des bouées vers la carte  *
 * ***************************************************/

// Repasser dans le repère du canavas d'origine 
/*
    ctx.translate(-canvasw/2, -canvash/2+20); 
    ctx.rotate(twd_radian - Math.PI / 2); --> Math.PI / 2 - twd_radian
    M = [sin(𝛼)   -cos(𝛼)] 
       [cos(𝛼)  sin(𝛼)]
    ctx.translate(canvasw/2, canvash/2-20);   
*/

//---------------------------
function fromScreenToGeoCoord(x, y){
    // Ne pas oublier d'appliquer les transformations inverses (-T) ° TR ° (T)
    var cx=setSaisieToDisplayX(x,y,twd_radian); 
    var cy=setSaisieToDisplayY(x,y,twd_radian);
    return {"lon":get_lon_Xecran(cx),"lat":get_lat_Yecran(cy)};
}


function setSaisieToDisplayX(x,y, radian){
// On applique une translation -T, une rotation inverse R  et une translation T   
    var x0 = x - canvasw/2; // Translation T1
    var y0 = y - (canvash/2+20);
    var x1 = x0*Math.sin(radian) - y0*Math.cos(radian); // Rotation R
    return Math.round(x1) + canvasw/2; // Translation T2
}
 
function setSaisieToDisplayY(x,y,radian){
// On applique une translation T1, une rotation R  et une translation T2   
    var x0 = x - canvasw/2; // Translation T1
    var y0 = y - (canvash/2+20);
    var y1 = x0 * Math.cos(radian) + y0*Math.sin(radian);
    return Math.round(y1) + (canvash/2+20);
}
 

