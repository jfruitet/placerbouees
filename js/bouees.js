// JavaScript Document
// Version sans rectangle ni calcul d'intériorité
// Trace des Bouees

 
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

// arrête la saisie des bouées
 function boueesValider(){
    saisir_encore=false;
    drawBouees();
    document.getElementById("transfert").style.visibility="visible"; 
    removeEvent(canvas3,"dblclick");
    removeEvent(canvas3,"mouseover");      
 }


 // saisie des bouées du parcours (MAXBOUEES au plus)
 function placerBouees(){
    document.getElementById("consigne").innerHTML="Double clic pour placer une à une les bouées dans le rectangle (20 au plus). "; 

    removeEvent(canvas3,"click");
    removeEvent(canvas3,"mouseover");
    addEvent(canvas3,"dblclick",nouvelleBouee());  
}

// Recherche une bouée fixe dans le voisinage
// En entrée une coordonnée écran dans le repère lié au canvas 
function getBoueeFixeDansVoisinage(cx, cy){
    var index=0; 
    //console.debug ("getBoueeFixe :: X:"+cx+" Y:"+cy);
    while (index<balisesEcran.length){
        var xfixe= balisesEcran[index].x;
        var yfixe= balisesEcran[index].y;
        //console.debug ("Balise Index="+index+" Xfixe:"+xfixe+" Yfixe:"+yfixe);
        if ((cx>=xfixe-3) && (cx<=xfixe+3) && (cy>=yfixe-2) && (cy<=yfixe+2)){
            //console.debug ("Got ONE Index="+index+" -> ID:"+balisesEcran[index].id+" "+balisesEcran[index].name+"\n");      
            return balisesEcran[index].id;
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
        // Ne pas oublier d'appliquer les transformations inverses (-T') ° TR ° (-T)
        var cx=setCanvasX(x,y,twd_radian); 
        var cy=setCanvasY(x,y,twd_radian);
        
        //console.debug("nouvelleBouee() :: N°:"+nbouees+" MX:"+xcoord+" MY:"+ycoord+" Zoom:"+zoom+" X:"+x+" Y:"+y+"\n");
        // Verifier si on pointe la souris sur une bouée fixe
        var idfixe=getBoueeFixeDansVoisinage(cx, cy);

            if (fdepart)
            { 
                //bouees[nbouees]='{"id":'+nbouees+',"x":'+x+',"y":'+y+',"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"red"}';
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":cx,"cy":cy,"lon":0.0,"lat":0.0,"color":"yellow","flag":flag,"idfixe":idfixe};
                drawBoueeColor(x,y,"yellow",flag,idfixe, ctx3); 
                compteur++;
            }
            else if (farrivee)
            { 
                //bouees[nbouees]='{"id":'+nbouees+',"x":'+x+',"y":'+y+',"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"green"}';
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":cx,"cy":cy,"lon":0.0,"lat":0.0,"color":"blue","flag":flag,"idfixe":idfixe};
                drawBoueeColor(x,y,"blue",flag,idfixe, ctx3); 
                compteur++;
            }
            else if (fporte)
            {
                //bouees[nbouees]='{"id":'+nbouees+',"x":'+x+',"y":'+y+',"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"green"}';
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":cx,"cy":cy,"lon":0.0,"lat":0.0,"color":"purple","flag":flag,"idfixe":idfixe};
                drawBoueeColor(x,y,"purple",flag,idfixe, ctx3); 
                compteur++;
            } 
            else // fdogleg
            {
                //bouees[nbouees]='{"id":'+nbouees+',"x":'+x+',"y":'+y+',"cx":0,"cy":0,"lon":0.0,"lat":0.0,"color":"green"}';
                bouees[nbouees]={"id":nbouees,"x":x,"y":y,"cx":cx,"cy":cy,"lon":0.0,"lat":0.0,"color":"black","flag":flag,"idfixe":idfixe};
                drawBoueeColor(x,y,"black",flag,idfixe, ctx3);  
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
    drawBouees(ctx3);
}

// Dessine toutes les bouées placées sur le canvas
function drawBouees(){
    if ((bouees !== undefined) && (bouees.length>0)){
        for (var index=0; index<bouees.length; index++){
            drawBoueeColor(bouees[index].x,bouees[index].y,bouees[index].color,bouees[index].flag,bouees[index].idfixe,ctx3);
        }    
    }
}
 
 
// Trace une petitebouee circulaire
function drawBoueeColor(x,y,color,flag,idfixe,contxt){   
    contxt.fillStyle=color;
    contxt.strokeStyle = color;
    contxt.beginPath();
    if (idfixe>=0){
        contxt.rect(x-3, y-3, 6, 6);   
        contxt.fill(); 
    }
    else{
        contxt.ellipse(x, y, 4, 3, 0, 0, Math.PI * 2);
        contxt.fill();   
    }
    contxt.strokeStyle = flag;
    contxt.beginPath();
    contxt.moveTo(x, y-3);
    contxt.lineTo(x, y-13);
    contxt.lineTo(x+5, y-10);
    contxt.lineTo(x, y-7);
    contxt.fillStyle=flag;
    contxt.fill();
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

 
 // redessine le plan d'eau avec le vent "face au nord"
 // et traite les coordonnées saisies à la souris
 function ajouterBouees(){
    //zoomReset();
    clearCanvas();
    ctx.save(); // save state  
    ctx.transform(1, 0, 0, 1, 0, 0); // Réinitialisation : scale h, skew h, skew v, scale v, move h, move v  
    ctx.translate(canvasw/2, canvash/2+20);// T1
    //ctx.rotate(Math.PI - twd_radian + Math.PI / 2.0); // PI - twd_radian parce que j'ai construit la flèche horizontalement !:>((
    ctx.rotate(twd_radian - Math.PI / 2);   // R
    ctx.translate(-canvasw/2, -canvash/2-20);       // T2
    init_ecran_ZN(); 
    init_ecran_Balises();
    draw_Ecran_poly_navigation(); 
    draw_Ecran_ligne_concurrents();   
    draw_Ecran_balises(ctx);
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
 
