// JavaScript Document
// Edition / Déplacement des bouées 
// Affichage et déplacement global de bouées chargées depuis un fichier de positionnement automatique 
let balisesMobilesEcran = []; // [{"id":0,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"green","flag":"green"}, ... {"id":5,"x":x,"y":y,"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"yellow","flag":"green"}]

const canvas5 = document.getElementById("canvas5");
const ctx5 = canvas5.getContext("2d");
canvas5.height=canvas5.width;
canvasw=canvas5.width;
canvash=canvas5.height;
console.debug("canvas2.js :: Zoom : "+zoom+" canvasw : "+canvasw+" canvash : "+canvash);

const canvas6 = document.getElementById("canvas6");
const ctx6 = canvas6.getContext("2d");
canvas6.height=canvas6.width;

const canvas7 = document.getElementById("canvas7");
const ctx7 = canvas7.getContext("2d");
canvas7.height=canvas7.width;

const canvas8 = document.getElementById("canvas8");
const ctx8 = canvas8.getContext("2d");


  
function zoomOut2() {
    zoom-=0.1;
    zoomfactor--;
    drawAll2();
}

function zoomIn2() {
  zoom+=0.1;
  zoomfactor++;
  drawAll2();
}

function zoomReset2() {
  zoom=1;
  zoomfactor=1;
  drawAll2();
}

function draw_scale2(){
// calcule et affiche l'échelle en mètres
// On applique la formule de la distance selon un grand cercle 
// seulement valable à l'équateur pour les longitudes sur la projection Mercator
// Distance (km) = Rayon terreste(6400 km) * angle (°)  *  Math.PI / 180
// Sur les latitudes la formule 
console.debug("canvas2.js :: draw_scale2()");
    var anglelon=lonmax-lonmin;
    var anglelat=latmax-latmin;
    var dlon =  Math.abs(6378137 * anglelon * Math.PI / 180.0);
    var dlat =  Math.abs(6356752 * anglelat * Math.PI / 180.0);  
    var echellex=canvasw * zoom / dlon;
    var echelley=canvash * zoom / dlat;
    // console.debug("Zoom : "+zoom+" canvasw : "+canvasw+" canvash : "+canvash);
    // console.debug("dlon : "+dlon+" dlat : "+dlat);
    // console.debug("echellex : "+echellex+" echelley : "+echelley);
    
    ctx5.save(); // save state  
    ctx5.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v  
    ctx5.beginPath();   
    // vertical
    var dh = canvash * zoom / 10;
    var dw = canvasw * zoom / 10;
    var nbtick = 10;
    var maxh = Math.min(canvash*zoom, canvash);
    var maxw = Math.min(canvasw*zoom, canvasw);
    // Vertical
    ctx5.moveTo(1, 0);
    ctx5.lineTo(1,maxh-1);
    for (var index=0; index<nbtick; index++){
        ctx5.moveTo(1,index*dh);
        ctx5.lineTo(5,index*dh);
    }
    // horizontal
    ctx5.moveTo(1,maxh);
    ctx5.lineTo(maxw,maxh-1);
    for (var index=0; index<nbtick; index++){
        ctx5.moveTo(index*dw, maxh-1);
        ctx5.lineTo(index*dw,maxh-5);
    }
    ctx5.lineWidth = 0.5;
    ctx5.strokeStyle = "Black";
    ctx5.stroke(); 
    ctx5.font = "9pt Calibri";
    ctx5.fillStyle = "black";
    ctx5.fillText("Hauteur : "+Math.floor(dlat)+"m  Largeur : "+Math.floor(dlon)+"m", 5, canvash-10);     
    
    ctx5.restore(); // restore to original stat 
}

  
// ---------------------------------------------------  
function clearCanvas5(){
    ctx5.clearRect(0, 0, canvas.width, canvas.height);
}

// ----------------------------------------------------
// Comme son nom l'indique trace le contenu du canvas 5 pour le déplacement
function drawAll2(){    
    //console.debug("canvas2.js :: drawAll2()"); 
    ctx7.clearRect(0, 0, canvas7.width, canvas7.height); // Pour le cas où il y aurait des scories
    document.getElementById("zoomv2").innerHTML=zoomfactor;

    // Passer la main au canvas
    document.getElementById("canvas5").style.zIndex=2;   
    document.getElementById("canvas7").hidden=true;
    
    init_ecran_ZN(); // tenir compte du zoom    
    init_ecran_bouees_fixes(); // Toutes les bouées fixes sont placées dans un tableau
    clearCanvas5();    
    draw_scale2(); 
    
    draw_Ecran_poly_navigation2(); 
    draw_Ecran_ligne_concurrents2();
    //console.debug("canvas2.js :: draw_Ecran_bouees_fixes2()"); 
    draw_Ecran_bouees_fixes2();
    
    // Bouées mobiles
    init_ecran_bouees_mobiles(); // Toutes les bouées mobiles sont placées dans un tableau   
    if ((balisesMobilesEcran !== undefined) && (balisesMobilesEcran.length>0)){
        //console.debug("Affichage des bouées mobiles dans le canvas 5");
        drawBoueesMobiles();
    }   
    affiche_fleche_TWD2();
    affiche_legende2(8);
     
}


// -----------------------
function init_ecran_bouees_mobiles(){  
    // Les bouees mobiles chargées dans ajax4.js 
    //console.debug("canvas2.js :: init_ecran_bouees_mobiles()");   
    if ((boueesMobiles!==undefined) && (boueesMobiles.length>0)){
        balisesMobilesEcran.length=0;
        for (var index=0; index<boueesMobiles.length; index++) {                                
            balisesMobilesEcran[index]=(JSON.parse('{"id":'+boueesMobiles[index].id+',"x":'+get_Xecran_lon(boueesMobiles[index].lon)+',"y":'+get_Yecran_lat(boueesMobiles[index].lat)+',"name":"'+boueesMobiles[index].name+'", "color":"'+boueesMobiles[index].color+'","fillcolor":"'+boueesMobiles[index].fillcolor+'"}'));
        }     
    }  
     
}

// --------------------------------------
function draw_Ecran_poly_navigation2(){
    if ((poly_xecran.length>0) && (poly_yecran.length>0)){       
        ctx5.save(); // save state  
        ctx5.transform(1, 0, 0, 1, 0, 0); // scale h, skew h, skew v, scale v, move h, move v  
        ctx5.beginPath();    
        ctx5.moveTo(poly_xecran[0],poly_yecran[0]);
        for (index=0; index<poly_xecran.length; index++){
            ctx5.lineTo(poly_xecran[index],poly_yecran[index]);
        }
        ctx5.closePath();   
        ctx5.lineWidth = 1;
        ctx5.strokeStyle = "navy";
        ctx5.stroke();  
        ctx5.restore(); // restore to original stat  
    } 
}

// --------------------------------------
function draw_Ecran_ligne_concurrents2(){
    if ((ligne_xecran.length>0) && (ligne_yecran.length>0)){       
        ctx5.save(); // save state  
        ctx5.transform(1, 0, 0, 1, 0, 0); // scale h, skew h, skew v, scale v, move h, move v  
        ctx5.beginPath();    
        ctx5.moveTo(ligne_xecran[0],ligne_yecran[0]);
        for (index=0; index<ligne_xecran.length; index++){
            ctx5.lineTo(ligne_xecran[index],ligne_yecran[index]);
        }
        //ctx5.closePath();   // Ce n'est pas une ligne fermée
        ctx5.lineWidth = 4;
        ctx5.strokeStyle = "orange";
        ctx5.stroke();  
        ctx5.restore(); // restore to original stat  
    } 
}


// -------------------------------------

// Trace une petitebouee circulaire dans le contexte passé en argument
// Les coordonnées fournies sont celle du contexte du canvas1 
function drawBoueesFixesColor2(x,y,fillcolor){   
    ctx5.beginPath();
    ctx5.fillStyle=fillcolor;
    ctx5.strokeStyle = "black";
    ctx5.ellipse(x, y, 4, 4, 0, 0, Math.PI * 2);
    ctx5.fill();
    ctx5.lineWidth = 0.5;
    ctx5.stroke();       
}

// Dessine les balises sur le canvas ad hoc
// -----------------------
function draw_Ecran_bouees_fixes2(){
    if ((balisesEcran!==undefined) && (balisesEcran.length>0)){
        for (var index=0; index<balisesEcran.length; index++) {
            drawBoueesFixesColor2(balisesEcran[index].x,balisesEcran[index].y,balisesEcran[index].fillcolor);
        }
    }   
}


 
 // Trace une petitebouee avec un drapeau
 //--------------------------------
function drawBoueeMobile(x,y,color,flag){   
    ctx5.fillStyle=color;
    ctx5.strokeStyle = color;
    ctx5.beginPath();
    ctx5.ellipse(x, y, 5, 4, 0, 0, Math.PI * 2);
    ctx5.fill();   
    ctx5.stroke();     
        
    // Flag
    ctx5.beginPath();
    ctx5.strokeStyle = flag;
    ctx5.beginPath();   
    ctx5.moveTo(x, y-4); 
    ctx5.lineTo(x, y-13);
    ctx5.lineTo(x+5, y-10);
    ctx5.lineTo(x, y-7);            
    ctx5.fillStyle=flag;
    ctx5.fill();
    ctx5.lineWidth = 0.5;
    ctx5.stroke();      
}


// Dessine toutes les bouées placées sur le canvas1
// ----------------------------------
function drawBoueesMobiles(){
    if ((balisesMobilesEcran !== undefined) && (balisesMobilesEcran.length>0)){
        for (var index=0; index<balisesMobilesEcran.length; index++){
            //console.debug(" Index: "+index+ " -> "+balisesMobilesEcran[index].x+", "+balisesMobilesEcran[index].y+", "+balisesMobilesEcran[index].color+", "+balisesMobilesEcran[index].fillcolor);
            drawBoueeMobile(balisesMobilesEcran[index].x,balisesMobilesEcran[index].y,balisesMobilesEcran[index].color,balisesMobilesEcran[index].fillcolor);                
        }    
    }
}


// Légende du canvas
 
// Affiche un segment fléché dans le canvas6 dans la direction opposée à celle du vent
function affiche_fleche_TWD2(){
    //console.log('TWD radian '+twd_radian);
    ctx6.clearRect(0, 0, canvas6.width, canvas6.height);
    ctx6.save(); // save state  
    ctx6.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v  
    // console.debug("twd "+twd);
    if (((twd>90) && (twd<=135)) || ((twd>225) && (twd<=270))){
        // console.debug("twd bas "+twd);
        ctx6.translate(canvas6.width/2, 100);        
    }
    else if ((twd>135) && (twd<=225)){
        // console.debug("twd bas "+twd);
        ctx6.translate(canvas6.width/2, 120);        
    }
    else{
        // console.debug("twd haut "+twd);
        ctx6.translate(canvas6.width/2, 90);
    }
    ctx6.rotate(Math.PI-twd_radian); // PI - twd_radian parce que j'ai construit la flèche horizontalement !:>((
    //ctx6.rotate(-twd_radian);
    ctx6.beginPath();
    ctx6.moveTo(0,0);
    ctx6.lineTo(30,0);
    ctx6.lineTo(30,-5);
    ctx6.lineTo(40,0);
    ctx6.lineTo(30,5);
    ctx6.lineTo(30,0);
    //ctx6.closePath();   
    ctx6.lineWidth = 10;
    ctx6.strokeStyle = "green";
    ctx6.stroke();  
    ctx6.restore(); // restore to original stat  

    ctx6.font = "16pt Calibri";
    ctx6.fillStyle = "green";
    var msg="TWD "+ twd +"° : "+secteur_vent(twd);
    ctx6.fillText(msg, 14, 24); 
    ctx6.font = "12pt Calibri";
    ctx6.fillStyle = "black";
    ctx6.fillText("Direction d'où vient", 14, 48); 
    ctx6.fillText("le vent", 14, 64);     
}

//
function drawPetitDrapeau2(x, y, flag)   { 
    // Drapeau de la balise mobile 
    ctx8.beginPath();    
    ctx8.strokeStyle = flag;
    ctx8.fillStyle = flag;
    ctx8.moveTo(x, y-6);
    ctx8.lineTo(x, y-24);
    ctx8.lineTo(x+10, y-18);
    ctx8.lineTo(x, y-16);
    ctx8.fill();
    ctx8.lineWidth = 2;
    ctx8.stroke();       
}

//
function drawPetiteBalise2(x, y, fillcolor, flag){
    // Corps de la balise mobile        
 
    ctx8.beginPath();
    ctx8.fillStyle = fillcolor; 
    ctx8.strokeStyle = "black"; 
    ctx8.ellipse(x, y, 8, 6, 0, 0, Math.PI * 2);
    ctx8.fill();  
    ctx8.stroke(); 
    drawPetitDrapeau2(x, y, flag);
}

//
function drawPetiteBaliseAncree2(x, y, fillcolor, flag){
    // Corps de la balise ancrée        
    ctx8.beginPath();
    ctx8.fillStyle = fillcolor; 
    ctx8.strokeStyle = "black"; 
    ctx8.rect(x-5, y-5, 10, 10);
    ctx8.fill();  
    ctx8.stroke(); 
    drawPetitDrapeau2(x, y, flag);
}

// Affiche la légende dans le canvas4
function affiche_legende2(x){
    ctx8.clearRect(0, 0, canvas8.width, canvas8.height);
    ctx8.font = "16pt Calibri";
    ctx8.beginPath(); 
    ctx8.fillStyle = "#0033aa";
    ctx8.fillText("Légende", x, 24); 
    ctx8.font = "12pt Calibri";
    ctx8.fillText("Bouées fixes", x, 48);
    ctx8.fillText("Bouées mobiles", x, 128);     
    ctx8.font = "10pt Calibri";
    ctx8.fillText("Balises ancrées", x, 90);     
    ctx8.fillText("Départ tribord", x, 166); 
    ctx8.fillText("Arrivée bâbord", x, 204);
    ctx8.fillText("Dog leg tribord", x, 240);
    ctx8.fillText("Porte bâbord", x, 274);
       
    ctx8.stroke(); 
    // dot     
    var x=120;
    var y=42;  
    ctx8.beginPath();    
    ctx8.fillStyle = "#0033aa";   
    ctx8.ellipse(x, y, 6, 6, 0, 0, Math.PI * 2);
    ctx8.fill();   
    ctx8.stroke();  
    drawPetiteBaliseAncree2(120, 88, "yellow", "red");    
    drawPetiteBalise2(120, 164, "yellow", "green");
    drawPetiteBalise2(120, 202, "blue", "red");
    drawPetiteBalise2(120, 238, "black", "green");
    drawPetiteBalise2(120, 272, "purple", "red");    
}



