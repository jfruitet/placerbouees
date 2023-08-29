// JavaScript Document

let masque=true;

let twd_radian = 0.0;
let twd=0;
let xcoord=0;   // Valeur du event.offsetX de la souris
let ycoord=0;

let xcoordold=0; // Pour le calcul de distance entre deux points successifs du canvas
let ycoordold=0;

let canvasw= 0; // valeur de canvas.width
let canvash= 0;
let zoom=1;   // Valeur multiplicative appliquée au calcul des positions sur le canvas
let zoomfactor=1; // compteur pour l'affichage 

let cw = 520; // Dimension du viewport affecté au canvas ; un event.offsetX vaut entre 0 et cw 
let ch = 520; // A vrai dire on devrait pouvoir identifier ça à la largeur du canvas

const windsector=["N","NNE","NE","ENE","E","ESE","SE","SSE","S","SSW","SW","WSW","W","WNW","NW","NNW","N"];


let nomDuSite='Le Plessis';
let longitudeDuSite=-1.47402;
let latitudeDuSite=47.24338;
let fichierACharger='leplessis.json';        
let infoSite='Plan d\'eau du Plessis, 44980 Sainte-Luce/Loire.<br>Club de radiomodélisme <a target="_blank" href="https://arbl.fr/">ARBL</a>.';


function masqueTexte() {
    masque=!masque;
    var x = document.getElementsByClassName("corpstexte");
    if (masque==true){
        for (var i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        document.getElementById("masquer").innerHTML="Sites";
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

// Non utilisé car aucun formulaire dans cette page
function myFunction() {
  document.getElementById("frm1").submit();
}

// Positonne la direction du vent en degre
function setTWD() {
    if (document.getElementById("twd").value.length>0){
        twd=document.getElementById("twd").value;        
    }
    if (twd>=0 && twd<=360){
        document.getElementById("twddeg").innerHTML='TWD°: <i>'+twd+'</i>';
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
    //console.debug("Event Offset X:"+event.offsetX+" Y:"+event.offsetY);
    //console.debug("Xcoord:"+xcoord+" Ycoord:"+ycoord);   
    document.getElementById("coordx").innerHTML = ycoord;
    document.getElementById("coordy").innerHTML = ycoord;
    // calcule la distance au point cliqué précedemment
    var distance = distanceGeodesique(xcoord,ycoord,xcoordold,ycoordold);
    document.getElementById("distance").innerHTML = distance;
    xcoordold=xcoord;
    ycoordold=ycoord;  
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

/************************
 * Cookies
 * **********************/
 
function setCookie(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  let expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + "; SameSite=Strict; " + expires + ";path=/";
}

 
// retourne la valeur du cookie spécifié
function getCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for(let i = 0; i <ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}
/*

let nomDuSite='Le Plessis';
let longitudeDuSite=-1.47402;
let latitudeDuSite=47.24338;
let fichierACharger='leplessis.json';        
let infoSite='Plan d\'eau du Plessis, 44980 Sainte-Luce/Loire.<br>Club de radiomodélisme <a target="_blank" href="https://arbl.fr/">ARBL</a>.';

*/

// verifie les cookies utiles
function checkCookies() {
  let stwd = getCookie("stwd");
  if (stwd!="" && stwd!=null) {
    var itwd=parseInt(stwd);    
    if (!isNaN(itwd) && itwd>=0 && itwd<=360){
        twd=itwd;
    }    
  } 
  let nomsite = getCookie("nomsite");
  if (nomsite != "" && nomsite !=null) {
        nomDuSite=nomsite;
  } 
  let londusite = getCookie("londusite");
  if (londusite != "" && londusite !=null) {
        longitudeDuSite=londusite;
  } 
  let latdusite = getCookie("latdusite");
  if (latdusite != "" && latdusite !=null) {
        latitudeDuSite=latdusite;
  } 
  let fichieracharger= getCookie("fichieracharger");
  if (fichieracharger != "" && fichieracharger !=null) {
        fichierACharger=fichieracharger;
  } 
}
  
// positionne les cookies utiles
function setCookies(){  
    
    if (twd>=0 && twd<=360) {
        setCookie("stwd", twd, 30); // 30 jours
    }   
    if (nomDuSite != "" && nomDuSite != null) {
        setCookie("nomsite", nomDuSite, 30); // 30 jours
    }
    if (longitudeDuSite != "" && longitudeDuSite != null) {
        setCookie("londusite", longitudeDuSite, 30); // 30 jours
    }
    if (latitudeDuSite != "" && latitudeDuSite != null) {
        setCookie("latdusite", latitudeDuSite, 30); // 30 jours
    }
    if (fichierACharger != "" && fichierACharger != null) {
        setCookie("fichieracharger", fichierACharger, 30); // 30 jours
    }
} 

 // ----------------------- 
function listePlansEauDisponibles(){
    getListePlansEau(); // Récupère la liste des plans d'eau
 }

