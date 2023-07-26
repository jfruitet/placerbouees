// JavaScript Document
// Version sans rectangle ni calcul d'intériorité
// Trace des Bouees

let distance = 0;
let gcoord1={"lon":0.0, "lat":0.0};
let gcoord2={"lon":0.0, "lat":0.0};
 
/**********************************************
 *  Réticule
 * ********************************************/
 
 // Affiche les coordonnées quand on clique dans le Canvas3
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
    // On va se passer du rectangle
    // if (oksaisierectangle==1){
    //    drawCible(rectangle.x1,rectangle.y1);
    // }
    // drawRectangle();
    drawBouees();
    //ctx3.restore(); // restore to original stat 
}

// Appelée qd la souris passe sur le canvas 3
async function myMoveFunction(){
// Affiche un réticule quand la souris se déplace
    xcoord= event.offsetX;
    ycoord= event.offsetY;
    var x = setMouseXPos(xcoord);
    var y = setMouseYPos(ycoord);
    show_reticule(x,y);
    document.getElementById("coordx").innerHTML = x;
    document.getElementById("coordy").innerHTML = y;
    // Calcul de distance
    gcoord2=fromScreenToGeoCoord(x, y);
    document.getElementById("lon").innerHTML = Math.round(gcoord2.lon * 100000) / 100000;
    document.getElementById("lat").innerHTML = Math.round(gcoord2.lat * 100000) / 100000;    
    
    if ((bouees !== undefined) && (bouees.length>=1)){
        gcoord1=fromScreenToGeoCoord(bouees[bouees.length-1].x, bouees[bouees.length-1].y);

        // On applique la formule de la distance selon un grand cercle 
        // seulement valable à l'équateur pour les longitudes sur la projection Mercator
        // Distance (km) = Rayon terreste(6400 km) * angle (°)  *  Math.PI / 180
        // Sur les latitudes la formule 
        var anglelon=gcoord2.lon-gcoord1.lon;
        var anglelat=gcoord2.lat-gcoord1.lat;
        var dlon =  6378137 * anglelon * Math.PI / 180.0;
        var dlat =  6356752 * anglelat * Math.PI / 180.0;  
        distance = Math.sqrt(dlon * dlon + dlat * dlat);
        document.getElementById("distance").innerHTML = "<span class=\"surligne\">"+ (Math.round(distance * 100000) / 100000) +"</span>";
    }    
    await sleep(200);
    // Let's go reticule  
}


function drawReticule(event){
// Fonction destinée à récupérer les évènement souris sur le canvas3
// Elle ne fait rien mais est indispensable !:>))
    myMoveFunction();
}

 
  /*******************************************
  *     Saisie des emplacements de bouées    *
  ********************************************/
 
 
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
    drawAll();
    document.getElementById("bdelete").style.visibility="hidden";
    document.getElementById("transfert").style.visibility="hidden";
    document.getElementById("breset").style.visibility="hidden";
    document.getElementById("bvalider").style.visibility="hidden";
    document.getElementById("consigne").innerHTML="Entrez la direction <b><i>d'où souffle le vent</i></b> en degré puis cliquez  \"Soumettre\"  ";
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
            // console.debug("Index:"+index+" X:"+x+" Y:"+y+"  --> Cx:"+cx+" Cy:"+cy+"  --> Lon:"+bouees[index].lon+" Lat:"+bouees[index].lat+"\n");
        }    
        
        // Imprimer les coordonnées
        /* 
        for (var index=0; index<bouees.length; index++){
            let txt = '{';
            for (let elt in bouees[index]) {
                txt += '"'+elt+'":"'+bouees[index][elt] +'", '; 
            }

            console.debug(txt+"}\n");
        }     
        */
    }
 
    addBouees2Map();
    saisir_encore=false;
    //drawBouees();
    removeEvent(canvas3,"dblclick");
    removeEvent(canvas3,"mouseover");  
    drawAll();    
    document.getElementById("transfert").style.visibility="visible";
    document.getElementById("consigne").innerHTML="Tranférez les bouées vers le serveur. ";      
 }


 // saisie des bouées du parcours (MAXBOUEES au plus)
 function placerBouees(){
    document.getElementById("consigne").innerHTML="Double clic pour placer une à une les bouées en travers du vent. "; 

    removeEvent(canvas3,"click");
    // removeEvent(canvas3,"mouseover");
    addEvent(canvas3,"dblclick",nouvelleBouee());  
}

// Recherche une bouée fixe dans le voisinage
// En entrée une coordonnée dans le repère lié au canvas d'affichage
// Correspondant à un changement de coordonnées lon, lat vers x,y du canvas d'affichage
function getBoueeFixeDansVoisinage(x, y){
    var index=0; 
    //console.debug ("getBoueeFixe :: X:"+x+" Y:"+y);
    while (index<balisesEcran.length){
        var xfixe= balisesEcran[index].x;
        var yfixe= balisesEcran[index].y;
        //console.debug ("Balise Index="+index+" Xfixe:"+xfixe+" Yfixe:"+yfixe);
        
        if ((x>=xfixe-4) && (x<=xfixe+4) && (y>=yfixe-4) && (y<=yfixe+4)){
            //console.debug ("Got ONE Index="+index+" -> ID:"+balisesEcran[index].id+" "+balisesEcran[index].name+"\n");      
            return index;
        }  
        index++;
    }        
    return -1;        
}

// ---------------------
function nouvelleBouee() {
    if ((nbouees>=0) && (nbouees<MAXBOUEE) && saisir_encore==true)
    {
        var msgnumero=nbouees+1;
        document.getElementById("consigne").innerHTML = document.getElementById("consigne").innerHTML  + " <span class=\"surligne\">Bouée N° "+msgnumero+"</span>";
        var ftribord=document.getElementById("tribord").checked;
        var fbabord=!ftribord;
        var flag;
        if (fbabord) { 
            flag="red";
        } else {
            flag="green";
        }
        
        var fdepart=document.getElementById('depart').checked==true;
        var fporte=document.getElementById('porte').checked==true;
        var farrivee=document.getElementById('arrivee').checked==true;
        var fdogleg=document.getElementById('dogleg').checked==true;
        //console.debug ("ENTREE :: Compteur :"+ compteur+ " Flag :"+flag+" Tribord:"+ftribord+" Bâbord:"+fbabord+" Depart:"+fdepart+" Arrivée:"+farrivee+" Porte:"+fporte+" Dogleg:"+fdogleg+"\n");

        xcoord= event.offsetX;  // Position de la souris
        ycoord= event.offsetY;
        var x = setMouseXPos(xcoord);  // retourne une position dans le canvas en fonction de la position de la souris 
        var y = setMouseYPos(ycoord);   // c'est une homothétie xcoord * (canvasw) / cw);
        // Ne pas oublier d'appliquer les transformations inverses (-T') ° TR ° (-T) pour récupérer les coordonnées réelle de l'objet
        // dans le repère d'affichage
        var cx=setSaisieToDisplayX(x,y,twd_radian); 
        var cy=setSaisieToDisplayY(x,y,twd_radian);
        
        //console.debug("nouvelleBouee() :: N°:"+nbouees+" MouseX:"+xcoord+" MouseY:"+ycoord+" Zoom:"+zoom+"\nCoordonnées écran de saisie X:"+x+" Y:"+y+"\n");
        //console.debug("Coordonnées écran d'affichage CX:"+cx+" CY:"+cy+"\n");
        
        // Verifier si on pointe la souris sur une bouée fixe
        idfixe=-1;
        var indexfixe=getBoueeFixeDansVoisinage(cx, cy);
        if (indexfixe>-1) { // C'est une bouée fixe ; on amène ses coordonnées dans le canvas de saisie
            //console.debug ("Bouée fixe dans le canvas d'affichage\nX:"+balisesEcran[indexfixe].x+" Y:"+balisesEcran[indexfixe].y+" idfixe:"+balisesEcran[indexfixe].id+"\n");
            // On repasse dans le repère de saisie par transformation Display --> Saisie
            var newx = setDisplayToSaisieX(balisesEcran[indexfixe].x,balisesEcran[indexfixe].y, twd_radian);
            var newy = setDisplayToSaisieY(balisesEcran[indexfixe].x,balisesEcran[indexfixe].y, twd_radian);
            idfixe=balisesEcran[indexfixe].id;
            cx=balisesEcran[indexfixe].x;
            cy=balisesEcran[indexfixe].y;
            x=newx;
            y=newy;
            //console.debug ("Bouée fixe dans le canvas de saisie X:"+x+" Y:"+y+" cx:"+cx+" cy:"+cy+" idfixe:"+idfixe+"\n");            
        }

            if (fdepart)
            { 
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":cx,"cy":cy,"lon":0.0,"lat":0.0,"color":"yellow","flag":flag,"idfixe":idfixe};
                compteur++;
            }
            else if (farrivee)
            { 
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":cx,"cy":cy,"lon":0.0,"lat":0.0,"color":"blue","flag":flag,"idfixe":idfixe};
                compteur++;
            }
            else if (fporte)
            {
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":cx,"cy":cy,"lon":0.0,"lat":0.0,"color":"purple","flag":flag,"idfixe":idfixe};
                compteur++;
            } 
            else // fdogleg
            {
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":cx,"cy":cy,"lon":0.0,"lat":0.0,"color":"black","flag":flag,"idfixe":idfixe};
            }
            nbouees++; 
            //console.debug ("AVANT :: Compteur :"+ compteur+ " Flag :"+flag+" Tribord:"+ftribord+" Bâbord:"+fbabord+" Depart:"+fdepart+" Arrivée:"+farrivee+" Porte:"+fporte+" Dogleg:"+fdogleg+" idfixe:"+idfixe+"\n");
            if ((compteur % 2) == 1){
                //console.debug ("Changer de franchissement Tribord\n");                                         
                document.getElementById("babord").checked=true;                      
                ftribord=document.getElementById("tribord").checked;
                fbabord=document.getElementById("babord").checked;
            }
            else{
                //console.debug ("Changer de franchissement Bâbord\n");                                         
                document.getElementById("tribord").checked=true;                      
                ftribord=document.getElementById("tribord").checked;
                fbabord=document.getElementById("babord").checked;         
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
    drawBouees();
}

// Dessine toutes les bouées placées sur le canvas
function drawBouees(){
    if ((bouees !== undefined) && (bouees.length>0)){
        for (var index=0; index<bouees.length; index++){
            drawBoueeColor(bouees[index].x,bouees[index].y,bouees[index].color,bouees[index].flag,bouees[index].idfixe);
        }    
    }
}
 
 
// Trace une un rectangle ou une ellipse surmontée d'un drapeau
function drawBoueeColor(x,y,color,flag,idfixe){   
    ctx3.fillStyle=color;
    ctx3.strokeStyle = "black";
    ctx3.beginPath();
    if (idfixe>=0){
        ctx3.rect(x-4, y-4, 8, 8);   
        ctx3.fill(); 
        ctx3.stroke();
    }
    else{
        ctx3.ellipse(x, y, 5, 4, 0, 0, Math.PI * 2);
        ctx3.fill();   
        ctx3.stroke();     
    }   
        
    // Flag
    ctx3.beginPath();
    ctx3.strokeStyle = flag;
    ctx3.beginPath();   
    ctx3.moveTo(x, y-4); 
    ctx3.lineTo(x, y-13);
    ctx3.lineTo(x+5, y-10);
    ctx3.lineTo(x, y-7);            
    ctx3.fillStyle=flag;
    ctx3.fill();
    ctx3.lineWidth = 0.5;
    ctx3.stroke();      
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

 
 // redessine le plan d'eau avec le vent "face au nord"
 // et traite les coordonnées saisies à la souris
 function ajouterBouees(){
    //zoomReset();
    clearCanvas();
    ctx.save(); // save state  
    ctx.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v  
    ctx.translate(canvasw/2, canvash/2+20);// T1
    ctx.rotate(twd_radian - Math.PI / 2);   // R
    ctx.translate(-canvasw/2, -canvash/2-20);       // T2
    init_ecran_ZN(); 
    init_ecran_bouees();
    draw_Ecran_poly_navigation(); 
    draw_Ecran_ligne_concurrents();   
    draw_Ecran_bouees_fixes(ctx);
    ctx.restore(); // restore to original stat    
    // Affiche la flèche des vents     
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
    // document.getElementById("consigne").innerHTML="La figure globale est ramenée face au vent. <span class=\"surligne\">Positionnez une diagonale du rectangle de la régate</span>.";
    document.getElementById("consigne").innerHTML="La figure globale est ramenée face au vent. <span class=\"surligne\">Positionnez les bouées une à une.</span>.";

    document.getElementById("typebouee").style.visibility="visible";
    document.getElementById("breset").style.visibility="visible";
    document.getElementById("bvalider").style.visibility="visible";
    document.getElementById("bdelete").style.visibility="visible";

    // Placerle canvas3 au dessus des autres
    document.getElementById("canvas1").style.zIndex=0;
    document.getElementById("canvas3").style.zIndex=2;
    document.getElementById("canvas3").hidden=false;
    
    // On passe directement au placement des bouées
    document.getElementById("babord").checked=false;
    document.getElementById("tribord").checked=true;
    document.getElementById("depart").checked=true; 
    
    saisir_encore=true; 
    document.getElementById("canvas3").onclick = function() {placerBouees()};
 }
 
