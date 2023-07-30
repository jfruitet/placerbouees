// JavaScript Document
// Zone de dessin
// Version sans rectangle ni calcule d'intériorité

// let rectangle = {x1:0,y1:0,x2:0,y2:0}; // le rectangle à tracer
// let oksaisierectangle=0; // état de la saisie 0: aucun sommet; 1 : un sommet; 2: deux sommets 


let saisir_encore=true; 
let compteur=0; // Compteur de bouées, permet de d'activer babord ou tribord automatiquement 
// Balies : bouées fixes du plan d'eau
let balisesEcran = []; // [{"x", "y", id":0,"name":"ARBL0","color":"green","fillcolor":"green"},...]

// Saisie des emplacements de bouees de départ et des portes au vent et sous le vent
let bouees = []; // [{"id":0,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"green","flag":"green"}, ... {"id":5,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"yellow","flag":"green"}]
let nbouees=0; // indice dans le tableau bouees 
let MAXBOUEE=20; // A priori par paires perpendiculaires à la direction du vent

let babord=false;
let depart=false;
let arrivee=false;
let porte=false;

const canvas = document.getElementById("canvas1");
const ctx = canvas.getContext("2d");
canvas.height=canvas.width;
canvasw=canvas.width;
canvash=canvas.height;
//console.debug("Zoom : "+zoom+" canvasw : "+canvasw+" canvash : "+canvash);

const canvas2 = document.getElementById("canvas2");
const ctx2 = canvas2.getContext("2d");
canvas2.height=canvas2.width;

const canvas3 = document.getElementById("canvas3");
const ctx3 = canvas3.getContext("2d");
canvas3.height=canvas3.width;

const canvas4 = document.getElementById("canvas4");
const ctx4 = canvas4.getContext("2d");


// Secteurs du vent 
function secteur_vent(twd){
    return windsector[Math.round ((twd % 360) / 22.5)];
}
 
// Affiche un segment fléché dans le canvas1 dans la direction opposée à celle du vent
function affiche_fleche_TWD(){
    //console.log('TWD radian '+twd_radian);
    ctx2.clearRect(0, 0, canvas2.width, canvas2.height);
    ctx2.save(); // save state  
    ctx2.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v  
    // console.debug("twd "+twd);
    if (((twd>90) && (twd<=135)) || ((twd>225) && (twd<=270))){
        // console.debug("twd bas "+twd);
        ctx2.translate(canvas2.width/2, 100);        
    }
    else if ((twd>135) && (twd<=225)){
        // console.debug("twd bas "+twd);
        ctx2.translate(canvas2.width/2, 120);        
    }
    else{
        // console.debug("twd haut "+twd);
        ctx2.translate(canvas2.width/2, 90);
    }
    ctx2.rotate(Math.PI-twd_radian); // PI - twd_radian parce que j'ai construit la flèche horizontalement !:>((
    //ctx2.rotate(-twd_radian);
    ctx2.beginPath();
    ctx2.moveTo(0,0);
    ctx2.lineTo(30,0);
    ctx2.lineTo(30,-5);
    ctx2.lineTo(40,0);
    ctx2.lineTo(30,5);
    ctx2.lineTo(30,0);
    //ctx2.closePath();   
    ctx2.lineWidth = 10;
    ctx2.strokeStyle = "green";
    ctx2.stroke();  
    ctx2.restore(); // restore to original stat  

    ctx2.font = "16pt Calibri";
    ctx2.fillStyle = "green";
    var msg="TWD "+ twd +"° : "+secteur_vent(twd);
    ctx2.fillText(msg, 14, 24); 
    ctx2.font = "12pt Calibri";
    ctx2.fillStyle = "black";
    ctx2.fillText("Direction d'où vient", 14, 48); 
    ctx2.fillText("le vent", 14, 64);     
}

//
function drawPetitDrapeau(x, y, flag)   { 
    // Drapeau de la balise mobile 
    ctx4.beginPath();    
    ctx4.strokeStyle = flag;
    ctx4.fillStyle = flag;
    ctx4.moveTo(x, y-6);
    ctx4.lineTo(x, y-24);
    ctx4.lineTo(x+10, y-18);
    ctx4.lineTo(x, y-16);
    ctx4.fill();
    ctx4.lineWidth = 2;
    ctx4.stroke();       
}

//
function drawPetiteBalise(x, y, fillcolor, flag){
    // Corps de la balise mobile        
 
    ctx4.beginPath();
    ctx4.fillStyle = fillcolor; 
    ctx4.strokeStyle = "black"; 
    ctx4.ellipse(x, y, 8, 6, 0, 0, Math.PI * 2);
    ctx4.fill();  
    ctx4.stroke(); 
    drawPetitDrapeau(x, y, flag);
}

//
function drawPetiteBaliseAncree(x, y, fillcolor, flag){
    // Corps de la balise ancrée        
    ctx4.beginPath();
    ctx4.fillStyle = fillcolor; 
    ctx4.strokeStyle = "black"; 
    ctx4.rect(x-5, y-5, 10, 10);
    ctx4.fill();  
    ctx4.stroke(); 
    drawPetitDrapeau(x, y, flag);
}

// Affiche la légende dans le canvas4
function affiche_legende(){
    ctx4.clearRect(0, 0, canvas4.width, canvas4.height);
    ctx4.font = "16pt Calibri";
    ctx4.beginPath(); 
    ctx4.fillStyle = "#0033aa";
    ctx4.fillText("Légende", 14, 24); 
    ctx4.font = "12pt Calibri";
    ctx4.fillText("Bouées fixes", 14, 48);
    ctx4.fillText("Balises mobiles", 14, 128);     
    ctx4.font = "10pt Calibri";
    ctx4.fillText("Balises ancrées", 14, 90);     
    ctx4.fillText("Départ tribord", 14, 166); 
    ctx4.fillText("Arrivée bâbord", 14, 204);
    ctx4.fillText("Dog leg tribord", 14, 240);
    ctx4.fillText("Porte bâbord", 14, 274);
       
    ctx4.stroke(); 
    // dot     
    var x=120;
    var y=42;  
    ctx4.beginPath();    
    ctx4.fillStyle = "#0033aa";   
    ctx4.ellipse(x, y, 6, 6, 0, 0, Math.PI * 2);
    ctx4.fill();   
    ctx4.stroke();  
    drawPetiteBaliseAncree(120, 88, "yellow", "red");    
    drawPetiteBalise(120, 164, "yellow", "green");
    drawPetiteBalise(120, 202, "blue", "red");
    drawPetiteBalise(120, 238, "black", "green");
    drawPetiteBalise(120, 272, "purple", "red");    
}

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

function affiche_dot(){
    // Trace un point à la position du curseur de la souris xcoord, ycoord
    ctx.save(); // save state  
    ctx.transform(1, 0, 0, 1, 0, 0); // scale h, skew h, skew v, scale v, move h, move v  
    ctx.beginPath();
    ctx.translate(setMouseXPos(xcoord)-3, setMouseYPos(ycoord)-3);
    ctx.scale(3,3);
    ctx.arc(1,1,1,0, Math.PI * 2, false);
    ctx.restore(); // restore to original state
    // context.stroke();
    ctx.fillStyle = "red";
    ctx.fill();
}


function clearCanvas(){
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}


 /****************************************
  *     De la Terre à l'Ecran
  ****************************************/
  
function zoomOut() {
    zoom-=0.1;
    zoomfactor--;
    nbouees=0;
    bouees.lenth=0;
    drawAll();
}

function zoomIn() {
  zoom+=0.1;
  zoomfactor++;
  nbouees=0;
  bouees.lenth=0;
  drawAll();
}

function zoomReset() {
  zoom=1;
  zoomfactor=1;
  nbouees=0;
  bouees.lenth=0;  
  drawAll();
}
  
// Conversion coordonnées géographique lon, lat en coordonnées écran du canvas
// cavasw est défini plus haut

function get_Xecran_lon(lon){
    // return Math.round(zoom * canvasw * (1 - (lon-lonmax) / (lonmin-lonmax)));
    return ((lon-lonmax) * (cw*zoom) / (lonmin-lonmax));
}  
   
function get_Yecran_lat(lat){  
    //return Math.round((lat-latmax) * (zoom*canvash) / (latmin-latmax)); 
    return Math.round((lat-latmax) * (zoom*ch) / (latmin-latmax));
}    



// et hop!
function set_X_Ecran_polygone_navigation(){
  var index=0;
  while (index<zonenav_lon.length){
    poly_xecran[index]=get_Xecran_lon(zonenav_lon[index]);
    index++;
  }  
}

function set_Y_Ecran_polygone_navigation(){
  var index=0;
  while (index<zonenav_lat.length){
    poly_yecran[index]=get_Yecran_lat(zonenav_lat[index]);
    index++;
  }  
}

function set_X_Ecran_ligne_concurrents(){
  var index=0;
  while (index<zoneconc_lon.length){
    ligne_xecran[index]=get_Xecran_lon(zoneconc_lon[index]);
    index++;
  }  
}

function set_Y_Ecran_ligne_concurrents(){
  var index=0;
  while (index<zonenav_lat.length){
    ligne_yecran[index]=get_Yecran_lat(zoneconc_lat[index]);
    index++;
  }  
}

// Comme son nom l'indique trace le contenu du canvas
function drawAll(){    
    ctx3.clearRect(0, 0, canvas3.width, canvas3.height); // Pour le cas où il y aurait des scories
    document.getElementById("typebouee").style.visibility="hidden";
    document.getElementById("bdelete").style.visibility="hidden";
    document.getElementById("transfert").style.visibility="hidden";
    document.getElementById("breset").style.visibility="hidden";
    document.getElementById("bvalider").style.visibility="hidden";
    document.getElementById("zoomv").innerHTML=zoomfactor;
    //document.getElementById("consigne").innerHTML="Entrez la direction <b><i>d'où souffle le vent</i></b> en degré puis cliquez  \"Soumettre\"";
    // Passer la main au canvas
    document.getElementById("canvas1").style.zIndex=2;
    //document.getElementById("canvas3").style.zIndex=0;    
    document.getElementById("canvas3").hidden=true;
    init_ecran_ZN(); // tenir compte du zoom
    init_ecran_bouees(); // Toutes les bouées fixes sont placées dans un tableau

    clearCanvas();
    draw_scale(); 
    draw_Ecran_poly_navigation(); 
    draw_Ecran_ligne_concurrents();
    
    if ((bouees !== undefined) && (bouees.length>0)){
        drawBoueesContexte1();
    }
    console.debug("draw_Ecran_bouees_fixes()"); 
    draw_Ecran_bouees_fixes();
    
}

function draw_Ecran_poly_navigation(){
    if ((poly_xecran.length>0) && (poly_yecran.length>0)){       
        ctx.save(); // save state  
        ctx.transform(1, 0, 0, 1, 0, 0); // scale h, skew h, skew v, scale v, move h, move v  
        ctx.beginPath();    
        ctx.moveTo(poly_xecran[0],poly_yecran[0]);
        for (index=0; index<poly_xecran.length; index++){
            ctx.lineTo(poly_xecran[index],poly_yecran[index]);
        }
        ctx.closePath();   
        ctx.lineWidth = 1;
        ctx.strokeStyle = "navy";
        ctx.stroke();  
        ctx.restore(); // restore to original stat  
    } 
}

function draw_Ecran_ligne_concurrents(){
    if ((ligne_xecran.length>0) && (ligne_yecran.length>0)){       
        ctx.save(); // save state  
        ctx.transform(1, 0, 0, 1, 0, 0); // scale h, skew h, skew v, scale v, move h, move v  
        ctx.beginPath();    
        ctx.moveTo(ligne_xecran[0],ligne_yecran[0]);
        for (index=0; index<ligne_xecran.length; index++){
            ctx.lineTo(ligne_xecran[index],ligne_yecran[index]);
        }
        //ctx.closePath();   // Ce n'est pas une ligne fermée
        ctx.lineWidth = 4;
        ctx.strokeStyle = "orange";
        ctx.stroke();  
        ctx.restore(); // restore to original stat  
    } 
}

// conversion 
function init_ecran_ZN(){
    rectangle_englobantZN(); // Pour les fonctions de changement de repère
    set_X_Ecran_polygone_navigation();  // table des X
    set_Y_Ecran_polygone_navigation();  // Table des Y
    console.debug("canvas.js :: 341\n");
    console.debug("Poly_xecran"+poly_xecran.toString());
    console.debug("Poly_yecran"+poly_yecran.toString());
    
    set_X_Ecran_ligne_concurrents();  // table des X
    set_Y_Ecran_ligne_concurrents();  // Table des Y   
    
    
}


/*************************************
 * De l'écran à la Terre
 * ***********************************/
 
// Conversion des coordonnées d'un point dans le canvas en coordonnées géographiques lon, lat 
// canvasw et zoom sont définis plus haut
// Ne pas oublier que la définition sur la grille du canevas est très grossière par rapport
// à la grille du monde réel 
function get_lon_Xecran(x){
    //lon = lonmin -  x  * (lonmin-lonmax) / (zoom*canvasw)
    return (lonmin -  x  * (lonmin-lonmax) / (zoom*canvasw));
} 
 
function get_lat_Yecran(y){
    return (y * (latmin-latmax) / (zoom * canvash) + latmax);
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


function draw_scale(){
// calcule et affiche l'échelle en mètres
// On applique la formule de la distance selon un grand cercle 
// seulement valable à l'équateur pour les longitudes sur la projection Mercator
// Distance (km) = Rayon terreste(6400 km) * angle (°)  *  Math.PI / 180
// Sur les latitudes la formule 
    var anglelon=lonmax-lonmin;
    var anglelat=latmax-latmin;
    var dlon =  Math.abs(6378137 * anglelon * Math.PI / 180.0);
    var dlat =  Math.abs(6356752 * anglelat * Math.PI / 180.0);  
    var echellex=canvasw * zoom / dlon;
    var echelley=canvash * zoom / dlat;
    // console.debug("Zoom : "+zoom+" canvasw : "+canvasw+" canvash : "+canvash);
    // console.debug("dlon : "+dlon+" dlat : "+dlat);
    // console.debug("echellex : "+echellex+" echelley : "+echelley);
    
    ctx.save(); // save state  
    ctx.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v  
    ctx.beginPath();   
    // vertical
    var dh = canvash * zoom / 10;
    var dw = canvasw * zoom / 10;
    var nbtick = 10;
    var maxh = Math.min(canvash*zoom, canvash);
    var maxw = Math.min(canvasw*zoom, canvasw);
    // Vertical
    ctx.moveTo(1, 0);
    ctx.lineTo(1,maxh-1);
    for (var index=0; index<nbtick; index++){
        ctx.moveTo(1,index*dh);
        ctx.lineTo(5,index*dh);
    }
    // horizontal
    ctx.moveTo(1,maxh);
    ctx.lineTo(maxw,maxh-1);
    for (var index=0; index<nbtick; index++){
        ctx.moveTo(index*dw, maxh-1);
        ctx.lineTo(index*dw,maxh-5);
    }
    ctx.lineWidth = 0.3;
    ctx.strokeStyle = "black";
    ctx.stroke(); 
    ctx.font = "9pt Calibri";
    ctx.fillStyle = "black";
    ctx.fillText("Hauteur : "+Math.floor(dlat)+"m  Largeur : "+Math.floor(dlon)+"m", 5, canvash-10);     
    
    ctx.restore(); // restore to original stat 
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
 
 
 
// -----------------------
function init_ecran_bouees(){  
    // Les balises sont des bouées fixes stockées dans une table initialisée dans le script sitenavigation.js 
    if ((balisesTable!==undefined) && (balisesTable.length>0)){
        
        for (var index=0; index<balisesTable.length; index++) {
            // console.debug(balisesObj.features[item]);                                 
            balisesEcran[index]=(JSON.parse('{"id":'+balisesTable[index].id+',"x":'+get_Xecran_lon(balisesTable[index].lon)+',"y":'+get_Yecran_lat(balisesTable[index].lat)+',"name":"'+balisesTable[index].name+'", "color":"'+balisesTable[index].color+'","fillcolor":"'+balisesTable[index].fillcolor+'"}'));
        }     
    }   
}

// Trace une petitebouee circulaire dans le contexte passé en argument
// Les coordonnées fournies sont celle du contexte du canvas1 
function drawBoueesFixesColor(x,y,fillcolor){   
    ctx.beginPath();
    ctx.fillStyle=fillcolor;
    ctx.strokeStyle = "black";
    ctx.ellipse(x, y, 4, 4, 0, 0, Math.PI * 2);
    ctx.fill();
    ctx.stroke();       
}

// Dessine les balises sur le canvas ad hoc
// -----------------------
function draw_Ecran_bouees_fixes(){
    if ((balisesEcran!==undefined) && (balisesEcran.length>0)){
        for (var index=0; index<balisesEcran.length; index++) {
            drawBoueesFixesColor(balisesEcran[index].x,balisesEcran[index].y,balisesEcran[index].fillcolor);
        }
    }   
}

// ----------------------- 
function tranfertBouees(){
    sauveBouees(); // Envoie la liste des bouées vers le serveur PHP pour le stokage
    document.getElementById("transfert").style.visibility="hidden";
    document.getElementById("consigne").innerHTML="Transfert vers le serveur <span class=\"surligne\"><i>"+url_serveur+"</i></span> effectué. ";
    document.getElementById("liste").style.visibility="true";
    document.getElementById("consigne").innerHTML="Liste des plans d'eau : cliquez sur <b>\"Liste\"</b>. "; 
     
 }

// ----------------------- 
function listePlansEau(){
    getListePlansEau(); // Récupère la liste des plans d'eau
    document.getElementById("transfert").style.visibility="hidden";
    document.getElementById("consigne").innerHTML="Liste des plans d'eau : cliquez sur <b>\"Liste\"</b>. "; 
 }
 
 
 // Trace une petitebouee circulaire
 //--------------------------------
function drawBoueeColorContexte1(x,y,color,flag,idfixe){   
    ctx.fillStyle=color;
    ctx.strokeStyle = color;
    ctx.beginPath();
    if (idfixe>=0){
        ctx.rect(x-4, y-4, 8, 8);   
        ctx.fill(); 
        ctx.stroke();
    }
    else{
        ctx.ellipse(x, y, 5, 4, 0, 0, Math.PI * 2);
        ctx.fill();   
        ctx.stroke();     
    }   
        
    // Flag
    ctx.beginPath();
    ctx.strokeStyle = flag;
    ctx.beginPath();   
    ctx.moveTo(x, y-4); 
    ctx.lineTo(x, y-13);
    ctx.lineTo(x+5, y-10);
    ctx.lineTo(x, y-7);            
    ctx.fillStyle=flag;
    ctx.fill();
    ctx.lineWidth = 0.5;
    ctx.stroke();      
}

// Dessine toutes les bouées placées sur le canvas1
// ----------------------------------
function drawBoueesContexte1(){
    if ((bouees !== undefined) && (bouees.length>0)){
        for (var index=0; index<bouees.length; index++){
            drawBoueeColorContexte1(bouees[index].cx,bouees[index].cy,bouees[index].color,bouees[index].flag,bouees[index].idfixe);                
        }    
    }
}

 

