// JavaScript Document
// Fonctions de conversion de données d'un repère à l'autre

// angle est exprimé en degré dans le repère géographique
// retourne la valeur de l'angle en radian dans le repère orthonormé direct
function get_radian_repere_direct(angle){
    return ((Math.PI / 180.0) * ((450 - angle) % 360));
}

// angle est exprimé en radian dans le repère orthonormé direct
// retourne la valeur de l'angle  en degré dans le repère géographique
function get_degre_repere_geographique(angle){
    return ((180.0 / Math.PI * (5 * Math.PI / 2.0 - angle)) % 360);
}



// Conversion d'un polygone géographique en polygone écran
// Il faut calculer le rectangle englobant puis se ramener par homothétie et translation aux coordonnées du canvas
// Attention : le repère géographique croit d'EST en OUEST (longitude) et de SUD au Nord (latitude)

let lonmin=180; // en degré géographique
let latmin=90;
let lonmax=-179; // en degré géographique EST ligne de changement d'horaire
let latmax=-90;

let poly_xecran = []; // Tableau des coordonnées écran de la zone de navigation
let poly_yecran = [];
let ligne_xecran = []; // Tableau des coordonnées écran de la ligne de déambulation des concurrents
let ligne_yecran = [];


// Boite englobante de la zone de navigation + zone d'évolution des concurrents
function rectangle_englobantZN(){
  var index=0;
  if ((zonenav_lat != undefined) && (zonenav_lat.length>0)){
    while (index<zonenav_lat.length){
        if (zonenav_lat[index]<=latmin){ latmin=zonenav_lat[index];}  
        if (zonenav_lat[index]>latmax){ latmax=zonenav_lat[index];}
        if (zonenav_lon[index]<=lonmin){ lonmin=zonenav_lon[index];}  
        if (zonenav_lon[index]>lonmax){ lonmax=zonenav_lon[index];}    
        index++;
    }
  }
  else {
    console.debug("geo_utils.js :: 48 :: zonenav_lat vide\n");  
  }  
  if ((zoneconc_lat != undefined) && (zoneconc_lat.length>0)){
    index=0;
    while (index<zoneconc_lat.length){
        if (zoneconc_lat[index]<=latmin){ latmin=zoneconc_lat[index];}  
        if (zoneconc_lat[index]>latmax){ latmax=zoneconc_lat[index];}
        if (zoneconc_lon[index]<=lonmin){ lonmin=zoneconc_lon[index];}  
        if (zoneconc_lon[index]>lonmax){ lonmax=zoneconc_lon[index];}    
        index++;
    }
  }  
  else{
    console.debug("geo_utils.js :: 61 :: zoneconc_lat vide\n");
  }    
  
  delta_lat=latmax-latmin; 
  delta_lon=lonmax-lonmin;
 
  milieu_lon= (lonmin + lonmax) /2.0;
  milieu_lat= (latmin + latmax) /2.0;
  pointsup={"lon":lonmax, "lat":latmax};
  pointinf={"lon":lonmin, "lat":latmin};
  // console.debug("geo_utils.js :: Delta Lat : "+delta_lat+" Delta_lon : "+delta_lon+"\n");
  // console.debug("PointSup:: Lon:"+pointsup.lon+" Lat:"+pointsup.lat+ "\nPointInf :: Lon:"+pointinf.lon+" Lat:"+pointinf.lat+"\n");
}      
    

