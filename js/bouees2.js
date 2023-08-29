// JavaScript Document
// Version sans rectangle ni calcul d'intériorité
// Trace des Bouees mobiles pour les déplacer


let oksaisieSommet=0;
let rectangleEnglobant=[];

 
/**********************************************
 *  Réticule
 * ********************************************/
 
 // Affiche les coordonnées quand on clique dans le Canvas3
function show_reticule2(x,y) {
  // On veut les coordonnées relatives au canvas
    //ctx7.save(); // save state  
    //ctx7.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v 

    ctx7.clearRect(0, 0, canvas7.width, canvas7.height); 
    ctx7.beginPath();
    ctx7.moveTo(x, 0);
    ctx7.lineTo(x, canvas7.height);
    ctx7.moveTo(0,y);
    ctx7.lineTo(canvas7.width,y);
    ctx7.lineWidth = 0.5;
    ctx7.strokeStyle = "pink";
    ctx7.stroke(); 
    
        
        if (rectangleEnglobant.length==1){
            console.debug("Cible 2");
            console.debug("RSommet1:"+ rectangleEnglobant);
            console.debug("RSommet1:"+ rectangleEnglobant[0].x+","+rectangleEnglobant[0].y);
            drawCible2(rectangleEnglobant[0].x,rectangleEnglobant[0].y);
        }
        else if (rectangleEnglobant.length==2){
            console.debug("Rectangle");
            console.debug("RSommet1:"+ rectangleEnglobant[0].x+","+rectangleEnglobant[0].y);
            console.debug("RSommet2:"+ rectangleEnglobant[1].x+","+rectangleEnglobant[1].y);
            drawRectangle2(rectangleEnglobant[0].x,rectangleEnglobant[0].y, rectangleEnglobant[1].x,rectangleEnglobant[1].y);
        }
    
    //ctx7.restore(); // restore to original stat 
}

// Appelée qd la souris passe sur le canvas 3
async function myMoveFunction2(){
// Affiche un réticule quand la souris se déplace
    xcoord= event.offsetX;
    ycoord= event.offsetY;
    var x = setMouseXPos(xcoord);
    var y = setMouseYPos(ycoord);
    show_reticule2(x,y);
    document.getElementById("coordx2").innerHTML = x;
    document.getElementById("coordy2").innerHTML = y;
    // Calcul de distance
    
    gcoord2=fromScreenToGeoCoord(x, y);
    document.getElementById("lon2").innerHTML = Math.round(gcoord2.lon * 100000) / 100000;
    document.getElementById("lat2").innerHTML = Math.round(gcoord2.lat * 100000) / 100000;    
    
    if ((balisesMobilesEcran !== undefined) && (balisesMobilesEcran.length>=1)){
        var distance = distanceGeodesique(balisesMobilesEcran[balisesMobilesEcran.length-1].x, balisesMobilesEcran[balisesMobilesEcran.length-1].y, x, y);
        /*
        gcoord1=fromScreenToGeoCoord(balisesMobilesEcran[balisesMobilesEcran.length-1].x, balisesMobilesEcran[balisesMobilesEcran.length-1].y);

        // On applique la formule de la distance selon un grand cercle 
        // seulement valable à l'équateur pour les longitudes sur la projection Mercator
        // Distance (km) = Rayon terreste(6400 km) * angle (°)  *  Math.PI / 180
        // Sur les latitudes la formule 
        var anglelon=gcoord2.lon-gcoord1.lon;
        var anglelat=gcoord2.lat-gcoord1.lat;
        var dlon =  6378137 * anglelon * Math.PI / 180.0;
        var dlat =  6356752 * anglelat * Math.PI / 180.0;  
        distance = Math.sqrt(dlon * dlon + dlat * dlat);
        */
        document.getElementById("distance2").innerHTML = "<span class=\"surligne\">"+ distance +"</span>";
    }    
    await sleep(200);
    // Let's go reticule  
}


function drawReticule2(event){
// Fonction destinée à récupérer les évènement souris sur le canvas3
// Elle ne fait rien mais est indispensable !:>))
    myMoveFunction2();
}

 
  /*******************************************
  *     Saisie des emplacements de bouées    *
  ********************************************/
/* 
 function boueesAnnuler(){ // remets à zéro toute saisie
    nbouees=0;
    bouees.length=0;
    saisir_encore=false;
    compteur = 0;

    drawAll();
    
    document.getElementById("bdelete").style.visibility="hidden";
    document.getElementById("transfert").style.visibility="hidden";
    document.getElementById("breset").style.visibility="hidden";
    document.getElementById("bvalider").style.visibility="hidden";
    document.getElementById("bannuler").style.visibility="hidden";
    document.getElementById("consigne").innerHTML="Entrez la direction <b><i>d'où souffle le vent</i></b> en degré puis cliquez  \"Soumettre\"  ";
    
 }
 // retire la dernière bouée du tableau
 function boueesDelete(){ // raz bouees[]
    if (bouees.length>0) {
        document.getElementById("consigne").innerHTML="<span class=\"surligne\">Bouée "+ nbouees + " retirée. </span> ";
        bouees.length--;
        nbouees=bouees.length;
        compteur = nbouees;
        ctx3.clearRect(0, 0, canvas3.width, canvas3.height);         
        drawBouees(ctx3);
    }        
}


// Vide le tableau des bouées
 function boueesReset(){ // raz bouees[]
    nbouees=0;
    bouees.length=0;
    saisir_encore=true;
    compteur = 0;

    //drawAll();

}

// affiche les bouées sur la map, arrête la saisie des bouées
 function boueesValider(){
    if ((bouees !== undefined) && (bouees.length>0)){
        var x;
        var y;
        for (var index=0; index<bouees.length; index++){
            // Passer dans le repère d'origine du canvas
            cx=setSaisieToDisplayX(bouees[index].x,bouees[index].y,twd_radian); // Passer dans le repère d'origine du canvas
            cy=setSaisieToDisplayY(bouees[index].x,bouees[index].y,twd_radian);
            bouees[index].cx=cx;
            bouees[index].cy=cy;           
            bouees[index].lon=get_lon_Xecran(cx); // Attention de ne pas inverser l'ordre des changements de repères
            bouees[index].lat=get_lat_Yecran(cy);
            //console.debug("Index:"+index+" X:"+bouees[index].x+" Y:"+bouees[index].y+"  --> Cx:"+cx+" Cy:"+cy+"  --> Lon:"+bouees[index].lon+" Lat:"+bouees[index].lat+"\n");
        }    
        
        // Imprimer les coordonnées

        addBouees2Map();
        saisir_encore=false;
        document.getElementById("bdelete").style.visibility="hidden";
        document.getElementById("transfert").style.visibility="hidden";
        document.getElementById("breset").style.visibility="hidden";
        document.getElementById("bvalider").style.visibility="hidden";
        document.getElementById("bannuler").style.visibility="hidden";
        document.getElementById("consigne").innerHTML="Entrez la direction <b><i>d'où souffle le vent</i></b> en degré puis cliquez  \"Soumettre\"  ";        
    }
 

    //drawBouees();
    removeEvent(canvas3,"dblclick");
    removeEvent(canvas3,"mouseover");  
    drawAll();    
    document.getElementById("transfert").style.visibility="visible";
    document.getElementById("consigne").innerHTML="Tranférez les bouées vers le serveur. ";      
 }

*/
 // saisie des bouées du parcours (MAXBOUEES au plus)
 function placerRectangle(){
    document.getElementById("consigne").innerHTML="Double clic pour placer un sommet du rectangle englobant. "; 
    removeEvent(canvas7,"click");
    // removeEvent(canvas3,"mouseover");
    addEvent(canvas7,"dblclick",nouveauSommet());  
}



// ---------------------
function nouveauSommet() {
    xcoord= event.offsetX;  // Position de la souris
    ycoord= event.offsetY;
    var x = setMouseXPos(xcoord);  // retourne une position dans le canvas en fonction de la position de la souris 
    var y = setMouseYPos(ycoord);   // c'est une homothétie xcoord * (canvasw) / cw);
        // Ne pas oublier d'appliquer les transformations inverses (-T') ° TR ° (-T) pour récupérer les coordonnées réelle de l'objet
        // dans le repère d'affichage
    var cx=setSaisieToDisplayX(x,y,twd_radian); 
    var cy=setSaisieToDisplayY(x,y,twd_radian);
        
    console.debug("nouveau sommet() :: MouseX:"+xcoord+" MouseY:"+ycoord+" Zoom:"+zoom+"\nCoordonnées écran de saisie X:"+x+" Y:"+y+"\n");
    console.debug("Coordonnées écran d'affichage CX:"+cx+" CY:"+cy+"\n");
        
    if (rectangleEnglobant!==undefined){
        if (oksaisieSommet<2){
            oksaisieSommet++;       
        }
        else{
            oksaisieSommet=0;
            rectangleEnglobant.length=0;
        }
        rectangleEnglobant.push(JSON.parse('{"x":'+x+',"y":'+y+'}'));        
    }
    //drawBoueesMobiles2();
}

 
 
// Trace  une ellipse surmontée d'un drapeau
function drawBoueeColor2(x,y,color,flag){   
    ctx7.fillStyle=color;
    ctx7.strokeStyle = "black";
    ctx7.beginPath();
    ctx7.ellipse(x, y, 5, 4, 0, 0, Math.PI * 2);
    ctx7.fill();   
    ctx7.stroke();     
        
    // Flag
    ctx7.beginPath();
    ctx7.strokeStyle = flag;
    ctx7.beginPath();   
    ctx7.moveTo(x, y-4); 
    ctx7.lineTo(x, y-13);
    ctx7.lineTo(x+5, y-10);
    ctx7.lineTo(x, y-7);            
    ctx7.fillStyle=flag;
    ctx7.fill();
    ctx7.lineWidth = 0.5;
    ctx7.stroke();      
}

 // Trace une petite cible
function drawCible2(x,y){
    //ctx7.clearRect(0, 0, canvas7.width, canvas7.height); 
    ctx7.beginPath();
    ctx7.moveTo(x+5, y+5);
    ctx7.lineTo(x-5, y-5);
    ctx7.moveTo(x+5, y-5);
    ctx7.lineTo(x-5, y+5);
    ctx7.lineWidth = 0.5;
    ctx7.strokeStyle = "purple";
    ctx7.stroke();   
}

 // Trace une petite cible
function drawRectangle2(x1,y1,x2,y2){
    var large= Math.abs(x2-x1);
    var haut= Math.abs(y2-y1);
    var x=Math.min(x1,x2);
    var y=Math.min(y1,y2);
    
    //ctx7.clearRect(0, 0, canvas7.width, canvas7.height); 
    ctx7.lineWidth = 0.5;
    ctx7.strokeStyle = "green";
    ctx7.beginPath();
    ctx7.rect(x, y, large, haut);
    ctx7.stroke();   
}
 
 // redessine le plan d'eau avec le vent "face au nord"
 // et traite les coordonnées saisies à la souris
 function ecranSaisie(){
    //zoomReset();
    clearCanvas5();
    ctx5.save(); // save state  
    ctx5.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v  
    ctx5.translate(canvas5.width/2, canvas5.height/2+20);// T1
    ctx5.rotate(twd_radian - Math.PI / 2);   // R
    ctx5.translate(-canvas5.width/2, -canvas5.height/2-20);       // T2
    init_ecran_ZN(); 
    init_ecran_bouees_fixes();
    init_ecran_bouees_mobiles();
    draw_Ecran_poly_navigation2(); 
    draw_Ecran_ligne_concurrents2();   
    draw_Ecran_bouees_fixes2();
    drawBoueesMobiles();
    ctx5.restore(); // restore to original stat    
    // Affiche la flèche des vents     
    ctx5.beginPath();
    ctx5.moveTo(canvas5.width/2, 0);
    ctx5.lineTo(canvas5.width/2,20);
    ctx5.lineTo(canvas5.width/2-3,20);
    ctx5.lineTo(canvas5.width/2,25);
    ctx5.lineTo(canvas5.width/2+3,20);
    ctx5.lineTo(canvas5.width/2,20);
    //ctx5.closePath();   
    ctx5.lineWidth = 3;
    ctx5.strokeStyle = "red";
    ctx5.stroke(); 
    
    document.getElementById("zoomv2").innerHTML=zoomfactor;
    // document.getElementById("consigne").innerHTML="La figure globale est ramenée face au vent. <span class=\"surligne\">Positionnez une diagonale du rectangle de la régate</span>.";
    document.getElementById("consigne2").innerHTML="La figure globale est ramenée face au vent. <span class=\"surligne\">Positionnez les bouées une à une.</span>.";

    document.getElementById('bmove').style.visibility="visible";
    document.getElementById('breset2').style.visibility="visible";
    document.getElementById('bdisplay').style.visibility="visible";  
    document.getElementById('bsave').style.visibility="visible";   

    // Placer le canvas7 au dessus des autres
    document.getElementById("canvas6").style.zIndex=0;
    document.getElementById("canvas7").style.zIndex=2;
    document.getElementById("canvas7").hidden=false;
    
    //saisir_encore=true; 
    document.getElementById("canvas7").onclick = function() {
        placerRectangle()
    };
 }
 
