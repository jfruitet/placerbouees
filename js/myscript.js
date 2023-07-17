// JavaScript Document

let masque=true;

let twd_radian = 0.0;
let twd=0;
let xcoord=0;   // Valeur du event.offsetX de la souris
let ycoord=0;

let canvasw= 0; // valeur de canvas.width
let canvash= 0;
let zoom=1;   // Valeur multiplicative appliquée au calcul des positions sur le canvas
let zoomfactor=1; // compteur pour l'affichage 

let cw = 500; // Dimension du viewport affecté au canvas ; un event.offsetX vaut entre 0 et cw 
let ch = 500; 

const windsector=["N","NNE","NE","ENE","E","ESE","SE","SSE","S","SSW","SW","WSW","W","WNW","NW","NNW","N"];

function masqueTexte() {
    masque=!masque;
    var x = document.getElementsByClassName("corpstexte");
    if (masque==true){
        for (var i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        document.getElementById("masquer").innerHTML="Informations";
    }
    else{
        for (var i = 0; i < x.length; i++) {
            x[i].style.display = "block";  
        }
        document.getElementById("masquer").innerHTML="Masquer";   
    }
}

function afficheTexte() {
  var x = document.getElementsByClassName("corpstexte");
  for (var i = 0; i < x.length; i++) {
    x[i].style.display = "block";
  }
}


function myFunction() {
  document.getElementById("frm1").submit();
}

// Positonne la direction du vent en degre
function setTWD() {
    if (document.getElementById("twd").value.length>0){
        twd=document.getElementById("twd").value;        
    }
    document.getElementById("twd").innerHTML=""; // Efface le contenu de la saisie
    document.getElementById("twddeg").innerHTML='TWD°: <i>'+twd+'</i>';
    twd_radian=get_radian_repere_direct(twd);// Math.round(number*100)/100
    var twd_radrounded=Math.round(twd_radian*1000)/1000;
    document.getElementById("twdrad").innerHTML='TWD radian: <i>'+twd_radrounded+'</i>';
}

// Affiche les coordonnées quand on clique dans le Canvas
function show_coords(event) {
  //xcoord= document.getElementById("coordx").innerHTML = event.clientX;
  //ycoord= document.getElementById("coordy").innerHTML = event.clientY;
  // On veut les coordonnées relatives au canvas
  xcoord= event.offsetX;
  ycoord= event.offsetY;
  document.getElementById("coordx").innerHTML = xcoord;
  document.getElementById("coordy").innerHTML = ycoord;
}

// A sleep function from https://www.sitepoint.com/delay-sleep-pause-wait/
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}


// Bon on va essayer de faire ça propre
// Il FAUT que le troisième paramètre soit le même 
function addEvent(obj,event,fct){
     if(obj.attachEvent)
        obj.attachEvent('on' + event,fct);
     else
        obj.addEventListener(event,fct,true);
}
 
function removeEvent(obj,event,fct){
    if (obj.detachEvent) {
        obj.detachEvent("on" + event, fct);
    }
    else {
        obj.removeEventListener(event, fct, true);
    }
}
 
/* Pour ajouter la fonction à l'événement */
// addEvent(composant,"mouseout",function(){... code de la fonction à ajouter... );});
/* Pour supprimer la fonction à l'événement */
// removeEvent(composant,"mouseout",function(){... à retirer... ;});


