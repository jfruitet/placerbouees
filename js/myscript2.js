// JavaScript Document
// myscript2.js
// Positonne la direction du vent en degre pour l'editeur 2
// -----------------------
function setTWD2() {
    if (twd>=0 && twd<=360){
        document.getElementById("twddeg2").innerHTML='TWD°: <i>'+twd+'</i>';
    }    
    twd_radian=get_radian_repere_direct(twd);// Math.round(number*100)/100
    var twd_radrounded=Math.round(twd_radian*1000)/1000;
    document.getElementById("twdrad2").innerHTML='TWD radian: <i>'+twd_radrounded+'</i>';
    document.getElementById("zoomv2").innerHTML=zoomfactor;    
}

// Affiche les coordonnées pour l'éditeur 2 quand on clique dans le Canvas
// --------------------------------
function show_coords2(event) {
    //xcoord= document.getElementById("coordx").innerHTML = event.clientX;
    //ycoord= document.getElementById("coordy").innerHTML = event.clientY;
    // On veut les coordonnées relatives au canvas
    xcoord= event.offsetX;
    ycoord= event.offsetY;
    //console.debug("Event Offset X:"+event.offsetX+" Y:"+event.offsetY);
    //console.debug("Xcoord:"+xcoord+" Ycoord:"+ycoord);   
    document.getElementById("coordx2").innerHTML = ycoord;
    document.getElementById("coordy2").innerHTML = ycoord;
    // calcule la distance au point cliqué précedemment
    var distance = distanceGeodesique(xcoord,ycoord,xcoordold,ycoordold);
    document.getElementById("distance2").innerHTML = distance;
    xcoordold=xcoord;
    ycoordold=ycoord;  
}


/***********************************************
 *  Edition / Deplacement
 * ********************************************/

// ---------------------------
function editionBouees(){
    console.debug("Editer des bouées");
    // Affichage du canvas d'édition
    document.getElementById("ajout").style.display="block";
    document.getElementById("fileedit").style.display="none";
}

// ---------------------------
function choixFichierBouees(){
    console.debug("Déplacer des bouées");
    document.getElementById("ajout").style.display="none";
    document.getElementById("fileedit").style.display="block";  
    document.getElementById("fileslist").style.display="block"; 
    document.getElementById("moveedit").style.display="none";      
}

// ---------------------------
function deplacementBouees(){
    document.getElementById("moveedit").style.display="block";  

    document.getElementById('bmove').style.visibility="visible";
    document.getElementById('breset2').style.visibility="visible";
    document.getElementById('bdisplay').style.visibility="visible";  
    document.getElementById('bsave').style.visibility="visible";   
    setTWD2(); 
}


