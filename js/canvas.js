// JavaScript Document
// Zone de dessin

 let rectangle = {x1:0,y1:0,x2:0,y2:0}; // le rectangle à tracer
 let oksaisierectangle=0; // état de la saisie 0: aucun sommet; 1 : un sommet; 2: deux sommets 
 let saisir_encore=true; 
 
  // Saisie des emplacements de bouees de départ et des portes au vent et sous le vent
 let bouees = []; // [{"id":0,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"green","flag":"green"}, ... {"id":5,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"yellow","flag":"green"}]
 let nbouees=0; // indice dans le tableau bouees 
 let MAXBOUEE=10; // A priori par paires perpendiculaires à la direction du vent

let babord=false;
let depart=false;
let arrivee=false;
let porte=false;

const canvas = document.getElementById("canvas1");
const ctx = canvas.getContext("2d");
canvas.height=canvas.width;
canvasw=canvas.width;
canvash=canvas.height;
// console.debug("Zoom : "+zoom+" canvasw : "+canvasw+" canvash : "+canvash);

const canvas2 = document.getElementById("canvas2");
const ctx2 = canvas2.getContext("2d");
canvas2.height=canvas2.width;

const canvas3 = document.getElementById("canvas3");
const ctx3 = canvas3.getContext("2d");
canvas3.height=canvas3.width;

 /*   
ctx.save(); // save state
ctx.transform(1, 0, 0, -1, 0, canvasw); // scale h, skew h, skew v, scale v, move h, move v  

const xArray = [50,60,70,80,90,100,110,120,130,140,150];
const yArray = [7,8,8,9,9,9,10,11,14,14,15];

ctx.fillStyle = "red";


for (let i = 0; i < xArray.length-1; i++) {
  let x = xArray[i]*canvasw/150;
  let y = yArray[i]*canvasw/15;
  ctx.beginPath();
  ctx.ellipse(x, y, 3, 3, 0, 0, Math.PI * 2);
  ctx.fill();
}

ctx.restore(); // restore to original state
*/

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
        ctx2.translate(canvas2.width/2, 120);        
    }
    else if ((twd>135) && (twd<=225)){
        // console.debug("twd bas "+twd);
        ctx2.translate(canvas2.width/2, 140);        
    }
    else{
        // console.debug("twd haut "+twd);
        ctx2.translate(canvas2.width/2, 100);
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

    ctx2.font = "32pt Calibri";
    ctx2.fillStyle = "green";
    var msg="TWD "+ twd +"° : "+secteur_vent(twd);
    ctx2.fillText(msg, 10, 50); 
    ctx2.font = "20pt Calibri";
    ctx2.fillStyle = "black";
    ctx2.fillText("Direction d'où vient le vent", 5, 80);     
    // ctx.drawImage(canvas2, canvas.width-100, 5);
    
    // ctx2.resetTransform();
}


function setMouseXPos(xmousecoord){  // retourne ue position dans le canvas en fonction de la position de la souris 
    return Math.round(xmousecoord * (canvasw*zoom) / cw);
}

function setMouseYPos(ymousecoord){
    return Math.round(ymousecoord * (canvash*zoom) / ch);
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
    drawAll();
}

function zoomIn() {
  zoom+=0.1;
  zoomfactor++;
  drawAll();
}

function zoomReset() {
  zoom=1;
  zoomfactor=1;
  drawAll();
}
  
// Conversion coordonnées géographique lon, lat en coordonnées écran du canvas
// cavasw est défini plus haut

function get_Xecran_lon(lon){
    return Math.round(zoom * canvasw * (1 - (lon-lonmax) / (lonmin-lonmax)));
}  
   
function get_Yecran_lat(lat){  
    return Math.round((lat-latmax) * (zoom*canvash) / (latmin-latmax)); 
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
    init_ecran_ZN(); 
    clearCanvas();
    draw_scale(); 
    draw_Ecran_poly_navigation(); 
    draw_Ecran_ligne_concurrents();
    document.getElementById("zoomv").innerHTML=zoomfactor;
    document.getElementById("consigne").innerHTML="Entrez la direction <b><i>d'où souffle le vent</i></b> en degré puis cliquez  \"Soumettre\"";
    // Passer la main au canvas
    document.getElementById("canvas1").style.zIndex=2;
    document.getElementById("canvas3").style.zIndex=0;    
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
    //lon = lonmin -  x  * (lonmin-lonmax) / cw
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

/*************************************************
 * Saisie d'un rectangle de "parcours de régate"
 * ***********************************************/
 

 
 // storeRect() enregistre le coordonnées cliquées dans une structure
function storeRect(){
    xcoord= event.offsetX;
    ycoord= event.offsetY;
    var x = setMouseXPos(xcoord);
    var y = setMouseYPos(ycoord);
    // console.debug("StoreRect() :: X:"+x+" Y:"+y+"\n");
    if (oksaisierectangle==0) {
        rectangle.x1=x;
        rectangle.y1=y;
        drawCible(x,y);
        oksaisierectangle=1;
    }
    else if (oksaisierectangle==1) {
        rectangle.x2=x;
        rectangle.y2=y;
        drawCible(rectangle.x1,rectangle.y1);
        drawCible(x,y);
        oksaisierectangle=2;      
    }
    if (oksaisierectangle==2){
        // Tracer le rectangle
        drawRectangle();  
        // await sleep(5000);
        //console.debug("et la suite... placer les bouees");
        placerBouees();             
    }
 }
 
 
  /*******************************************
  *     Saisie des emplacements de bouées    *
  ********************************************/
 
 function boueesReset(){ // raz bouees[]
    nbouees=0;
    bouees.length=0;
    saisir_encore=true;
    drawAll();
    document.getElementById("bvalider").style.visibility="hidden";
    document.getElementById("transfert").style.visibility="hidden";
    document.getElementById("breset").style.visibility="hidden";
}

// arrête la saisie des bouées
 function boueesValider(){
    saisir_encore=false;
    drawBouees();
    document.getElementById("transfert").style.visibility="visible"; 
    removeEvent(canvas3,"dblclick");
    removeEvent(canvas3,"mouseover");      
 }


 // saisie des 6 boues du parcours
 function placerBouees(){
    document.getElementById("consigne").innerHTML="Double clic pour placer une à une les 6 bouées dans le rectangle."; 
    // document.getElementById("canvas3").addEventListener("dblclick", nouvelleBouee((nbouees>=0) && (nbouees<MAXBOUEE)));
    removeEvent(canvas3,"click");
    removeEvent(canvas3,"mouseover");
    addEvent(canvas3,"dblclick",nouvelleBouee());   
}


// ---------------------
function nouvelleBouee() {
    if ((nbouees>=0) && (nbouees<MAXBOUEE) && saisir_encore==true)
    {
        document.getElementById("consigne").innerHTML = document.getElementById("consigne").innerHTML  + "Bouée N°:"+nbouees;
        babord=document.getElementById("babord").checked;
        var flag = "green";
        if (babord) { flag="red";} else {flag="green";}
        
        depart=document.getElementById('depart').checked;
        porte=document.getElementById('porte').checked;
        arrivee=document.getElementById('arrivee').checked;
        
        xcoord= event.offsetX;
        ycoord= event.offsetY;
        var x = setMouseXPos(xcoord);
        var y = setMouseYPos(ycoord);
        //console.debug("nouvelleBouee() :: N°:"+nbouees+" X:"+x+" Y:"+y+"\n");
        if ((x>=rectangle.x1) && (x<=rectangle.x2) && (y>=rectangle.x1) && (y<=rectangle.y2))
        {
            //if (nbouees % 2)
            if (depart)
            { 
                //bouees[nbouees]='{"id":'+nbouees+',"x":'+x+',"y":'+y+',"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"red"}';
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"yellow","flag":flag};
                drawBoueeColor(x,y,"yellow",flag, ctx3); 
            }
            else if (arrivee)
            { 
                //bouees[nbouees]='{"id":'+nbouees+',"x":'+x+',"y":'+y+',"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"green"}';
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"blue","flag":flag};
                drawBoueeColor(x,y,"blue",flag, ctx3); 
            }
            else if(porte)
            {
                //bouees[nbouees]='{"id":'+nbouees+',"x":'+x+',"y":'+y+',"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"green"}';
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"purple","flag":flag};
                drawBoueeColor(x,y,"purple",flag, ctx3); 
            } 
            else
            {
                //bouees[nbouees]='{"id":'+nbouees+',"x":'+x+',"y":'+y+',"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"green"}';
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"black","flag":flag};
                drawBoueeColor(x,y,"black",flag, ctx3);  
            }
           nbouees++;            
        }
    }
    else if (nbouees>0){ // afficher les bouées
        /*
        var msg="";
        document.getElementById("consigne").innerHTML = "Bouées ";
        for (var index=0; index<bouees.length; index++){
            msg= msg + bouees[index] + " ";
        }
        */        
        document.getElementById("consigne").innerHTML = "Les bouées sont placées. Cliquer sur \"Transférer\" ";
        boueesValider();       
    }
    // Dessiner les bouées... 
    drawBouees(ctx3);
}

// Dessine toutes les bouées placées sur le canvas
function drawBouees(){
    if ((bouees !== undefined) && (bouees.length>0)){
        for (var index=0; index<bouees.length; index++){
            drawBoueeColor(bouees[index].x,bouees[index].y,bouees[index].color,bouees[index].flag,ctx3);
        }    
    }
}
 
// Trace une petitebouee circulaire
function drawBoueeColor(x,y,color,flag ,contxt){   
    contxt.fillStyle=color;
    contxt.strokeStyle = color;
    contxt.beginPath();
    contxt.ellipse(x, y, 4, 3, 0, 0, Math.PI * 2);
    contxt.fill();   
    contxt.strokeStyle = flag;
    contxt.beginPath();
    contxt.moveTo(x+5, y+5);
    contxt.lineTo(x-5, y-5);
    contxt.moveTo(x+5, y-5);
    contxt.lineTo(x-5, y+5);
    contxt.lineWidth = 0.5;
    contxt.stroke();   
     
}

 // Trace une petite cible
function drawCible(x,y){
    ctx3.clearRect(0, 0, canvas3.width, canvas3.height); 
    ctx3.beginPath();
    ctx3.moveTo(x+5, y+5);
    ctx3.lineTo(x-5, y-5);
    ctx3.moveTo(x+5, y-5);
    ctx3.lineTo(x-5, y+5);
    ctx3.lineWidth = 0.5;
    ctx3.strokeStyle = "orange";
    ctx3.stroke();   
}

 
 // dessine un rectangle de diagonale rectangle.x1, rectangle.y1, rectangle.x2, rectangle.y2
function drawRectangle(){
    if (rectangle.x2<rectangle.x1){
        var aux=rectangle.x1;
        rectangle.x1=rectangle.x2;
        rectangle.x2=aux;
    }
    if (rectangle.y2<rectangle.y1){
        var aux=rectangle.y1;
        rectangle.y1=rectangle.y2;
        rectangle.y2=aux;
    }
        
    ctx3.rect(rectangle.x1, rectangle.y1, rectangle.x2-rectangle.x1, rectangle.y2-rectangle.y1);
    ctx3.lineWidth = 0.5;
    ctx3.strokeStyle = "pink";
    ctx3.stroke(); 
    //ctx3.restore(); // restore to original stat 
}



 
 // redessine le plan d'eau avec le vent "face au nord"
 // et traite les coordonnées saisies à la souris
 function ajouterBouees(){
    zoomReset();
    clearCanvas();
    ctx.save(); // save state  
    ctx.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v  
    ctx.translate(canvasw/2, canvash/2+20);// T1
    //ctx.rotate(Math.PI - twd_radian + Math.PI / 2.0); // PI - twd_radian parce que j'ai construit la flèche horizontalement !:>((
    ctx.rotate(twd_radian - Math.PI / 2);   // R
    ctx.translate(-canvasw/2, -canvash/2-20);       // T2
    init_ecran_ZN(); 

    draw_Ecran_poly_navigation(); 
    draw_Ecran_ligne_concurrents();
    ctx.restore(); // restore to original stat        
    ctx.beginPath();
    ctx.moveTo(canvasw/2, 0);
    ctx.lineTo(canvasw/2,20);
    ctx.lineTo(canvasw/2-3,20);
    ctx.lineTo(canvasw/2,25);
    ctx.lineTo(canvasw/2+3,20);
    ctx.lineTo(canvasw/2,20);
    //ctx.closePath();   
    ctx.lineWidth = 3;
    ctx.strokeStyle = "red";
    ctx.stroke(); 
    
    document.getElementById("zoomv").innerHTML=zoomfactor;
    document.getElementById("consigne").innerHTML="La figure globale est ramenée face au vent. Positionnez le rectangle de la régate.";
    document.getElementById("typebouee").style.visibility="visible";
    document.getElementById("breset").style.visibility="visible";
    document.getElementById("bvalider").style.visibility="visible";
    
    // Placerle canvas3 au dessus des autres
    document.getElementById("canvas1").style.zIndex=0;
    document.getElementById("canvas3").style.zIndex=2;
    
    oksaisierectangle=0; 
    document.getElementById("canvas3").onclick = function() {storeRect()};
    
 }
 
 
 // Affiche les coordonnées quand on clique dans le Canvas
function show_reticule(x,y) {
  // On veut les coordonnées relatives au canvas
    //ctx3.save(); // save state  
    //ctx3.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v 

    ctx3.clearRect(0, 0, canvas3.width, canvas3.height); 
    ctx3.beginPath();
    ctx3.moveTo(x, 0);
    ctx3.lineTo(x, canvas3.height);
    ctx3.moveTo(0,y);
    ctx3.lineTo(canvas3.width,y);
    ctx3.lineWidth = 0.5;
    ctx3.strokeStyle = "pink";
    ctx3.stroke(); 
    //ctx3.restore(); // restore to original stat 
}

// Appelée qd la souris passe sur le canvas 3
async function myMoveFunction(){
// Affiche un réticule quand la souris se déplace
  xcoord= event.offsetX;
  ycoord= event.offsetY;
  var x = xcoord; // setMouseXPos(xcoord);
  var y = ycoord // setMouseYPos(ycoord);
  show_reticule(x,y);
  document.getElementById("coordx").innerHTML = x;
  document.getElementById("coordy").innerHTML = y;
  await sleep(200);
    // Let's go reticule  
}


function drawReticule(){
// Fonction destinée à récupérer les évènement souris sur le canvas3
// Elle ne fait rien mais est indispensable !:>))
    ;
}

function draw_scale(){
// calcule et affiche l'échelle en mètres
// On applique la formule de la distance selon un grand cercle 
// seulement valable à l'équateur pour les longitudes sur la projection Mercator
// Distance (km) = Rayon terreste(6400 km) * angle (°)  *  Math.PI / 180
// Sur les latitudes la formule 
    var anglelon=lonmax-lonmin;
    var anglelat=latmax-latmin;
    var dlon =  6378137 * anglelon * Math.PI / 180.0;
    var dlat =  6356752 * anglelat * Math.PI / 180.0;  
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
 * Transfert des positions des bouées vers la carte  *
 * ***************************************************/
 
// A suivre

// Repasser dans le repère du canavas d'origine avant la rotation + Translation
/*
    ctx.rotate(twd_radian - Math.PI / 2); --> Math.PI / 2 - twd_radian
    M = [sin(𝛼)   -cos(𝛼)] 
       [cos(𝛼)  sin(𝛼)]
    ctx.translate(-canvasw/2, -canvash/2-20);   
*/

function setCanvasX(x,y, radian){
// On applique une translation -T1, une rotation inverse R  et une translation T2   
    var x0 = x - canvasw/2; // Translation T1
    var y0 = y - (canvash/2+20);
    var x1 = x0*Math.sin(radian) - y0*Math.cos(radian); // Rotation R
    return Math.round(x1) + canvasw/2; // Translation T2
}
 
function setCanvasY(x,y,radian){
// On applique une translation T1, une rotation R  et une translation T2   
    var x0 = x - canvasw/2; // Translation T1
    var y0 = y - (canvash/2+20);
    var y1 = x0 * Math.cos(radian) + y0*Math.sin(radian);
    return Math.round(y1) + (canvash/2+20);
}
 

// ----------------------- 
function tranfertBouees(){
     if ((bouees !== undefined) && (bouees.length>0)){
        var x;
        var y;
        for (var index=0; index<bouees.length; index++){
            // Passer dans le repère d'origine du canvas
            cx=setCanvasX(bouees[index].x,bouees[index].y,twd_radian); // Passer dans le repère d'origine du canvas
            cy=setCanvasY(bouees[index].x,bouees[index].y,twd_radian);
            bouees[index].cx=cx;
            bouees[index].cy=cy;           
            bouees[index].lon=get_lon_Xecran(cx); // Attention de ne pas inverser l'ordre des changements de repères
            bouees[index].lat=get_lat_Yecran(cy);
            // console.debug("Index:"+index+" X:"+x+" Y:"+y+"  --> Cx:"+cx+" Cy:"+cy+"  --> Lon:"+bouees[index].lon+" Lat:"+bouees[index].lat+"\n");
        }    
        
        // Imprimer les coordonnées
        for (var index=0; index<bouees.length; index++){
            let txt = "";
            for (let elt in bouees[index]) {
                txt += bouees[index][elt] + ", ";
            }

            console.debug("Index:"+index+" "+txt+"\n");
        }     
        // Retracer le canvas 1
        drawAll();
        drawBoueesContexte1();  // avec les bouees      
    }
 }
 
// Dessine toutes les bouées placées sur le canvas1
function drawBoueesContexte1(){
    if ((bouees !== undefined) && (bouees.length>0)){
        for (var index=0; index<bouees.length; index++){
            drawBoueeColor(bouees[index].cx,bouees[index].cy,bouees[index].color,bouees[index].flag,ctx);
        }    
    }
}

 

