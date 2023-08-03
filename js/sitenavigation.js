// JavaScript Document
// Zone de Navigation et Bouees Fixes
// variables suivantes sont initialisées lors de la sélection d'un site par un apple Ajax du fichier ajax.js

var tid=[];
var strproperty="";
var tproperties=[];
var tcoordinates=[];

let zonenav_lat=[];     // latitudes du polyline de navigation
let zonenav_lon=[];     // longitudes du polyline de navigation

let zoneconc_lon= [];   // longitudes de la ligne des concurrents
let zoneconc_lat = [];  // latitudes de la ligne des concurrents

// Objet Javascript et non pas string geojson 
let geojsonZoneConcurrents="";
/*
{"type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",    
      "properties": {
        "name":"ZC",
        "title": "Zone Concurrents",
        "description": "Zone de déplacement des concurrents",
        "infobox": true,
        "color": "#aaaa33",
        "fill": "#ffffee"              
      },     
      "geometry": {
        "coordinates": [
          [
            -1.4747445028388313,
            47.2439195874486
          ],
          [
            -1.4744189545636743,
            47.24402752137087
          ],
          [
            -1.4741350461847276,
            47.2440943374981
          ],
          [
            -1.4739079194809506,
            47.244138024920545
          ],
          [
            -1.4736921491121393,
            47.24416372338749
          ],
          [
            -1.4734763787433565,
            47.24415601384845
          ],
          [
            -1.4733401027220054,
            47.24410975659259
          ]
        ],
        "type": "LineString"
      }
    }
  ]
}
*/
// Objet Javascript et non pas string geojson
let geojsonZoneNav="";
/*
{"type":"FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "name":"ZN",      
        "title": "ZN",
        "description": "Zone de navigation réservée à l'ARBL",
        "infobox": true,
        "fill": "#ffffee",
        "fill-opacity": "0.2",
        "color": "#0000aa"       
      },
      "geometry": {
        "coordinates": [
          [
            [
              -1.4746555619712751,
              47.243830107759834
            ],
            [
              -1.4733794850961033,
              47.24306984068747
            ],
            [
              -1.4732841764495106,
              47.24323699253995
            ],
            [
              -1.4733636003215622,
              47.24364139000113
            ],
            [
              -1.4737871943051744,
              47.24402062214875
            ],
            [
              -1.4743378664837508,
              47.24396131113892
            ],
            [
              -1.4746555619712751,
              47.243830107759834
            ]
          ]
        ],
        "type": "Polygon"
      }
     }
    ]
}
*/
// Repris "à la main" du fichier geojsonBalises
// Le chargement dynamique du fichier des balises geojson dans la partie dessin est problématique pour des motifs que je n'arrive pas à cerner;
// Un premier chargement fonctionne mais pas les rechargements suivants.
let balisesTable = [];
/*
[{"id":1, "name":"Ponton", "lon":-1.4745214367317487, "lat":47.24395956032603, "color":"#000000", "fillcolor":"#000000"},
{"id":2, "name":"ARBL01", "lon":-1.4743960142401136, "lat":47.243888635331786, "color":"#0000aa", "fillcolor":"#0033aa"},
{"id":3, "name":"ARBL02", "lon":-1.4745009820620112, "lat":47.24378322913367, "color":"#0000ff", "fillcolor":"#00aaff"},
{"id":4, "name":"ARBL03", "lon":-1.4743325961802896, "lat":47.24381885942171, "color":"#00aa00", "fillcolor":"#00aa33"},
{"id":5, "name":"ARBL04", "lon":-1.473437527604915, "lat":47.24324818948409, "color":"#00ff00", "fillcolor":"#aaff00"},
{"id":6, "name":"ARBL05", "lon":-1.473739759025733, "lat":47.24383827416719, "color":"#0000ff", "fillcolor":"#aa0000"},
{"id":7, "name":"ARBL06", "lon":-1.4739887654452843, "lat":47.24395058704869, "color":"#ff0000", "fillcolor":"#ffaa00"},
{"id":8, "name":"ARBL07", "lon":-1.4739376244419304, "lat":47.243720925304075, "color":"#ccaa00", "fillcolor":"#aaff00"},
{"id":9, "name":"ARBL08", "lon":-1.4740416993153929, "lat":47.24356182462, "color":"#ffff00", "fillcolor":"#ffffaa"},
{"id":10, "name":"ARBL09", "lon":-1.4736141033273782, "lat":47.24355121140542, "color":"#00aaaa", "fillcolor":"#00ffcc"}];
*/

// Objet Javascript et non pas string geojson
let geojsonBalises="";
/*
{
  "type": "FeatureCollection",
  "features": [
    {
      "geometry": {
        "coordinates": [
          -1.4745214367317487,
          47.24395956032603
        ],
        "type": "Point"
      },
      "id": 1,
      "properties": {
        "description": "Ponton réservé au radio modélisme",
        "title": "Ponton",
        "name": "Ponton",
        "color": "#000000",
        "fillColor": "#000000",
        "fillOpacity": 0.8
      },
      "type": "Feature"
    },
    {
      "geometry": {
        "coordinates": [
          -1.4743960142401136,
          47.243888635331786
        ],
        "type": "Point"
      },
      "id": 2,
      "properties": {
        "description": "ARBL01 - Départ Nord",
        "title": "ARBL01",
        "name": "ARBL01",
        "color": "#0000aa",
        "fillColor": "#0033aa",
        "fillOpacity": 0.8
      },
      "type": "Feature"
    },
    {
      "geometry": {
        "coordinates": [
          -1.4745009820620112,
          47.24378322913367
        ],
        "type": "Point"
      },
      "id": 3,
      "properties": {
        "description": "ARBL02 - Départ Ouest",
        "title": "ARBL02",
        "name": "ARBL02",
        "color": "#0000ff",
        "fillColor": "#00aaff",
        "fillOpacity": 0.8
      },
      "type": "Feature"
    },
    {
      "geometry": {
        "coordinates": [
          -1.4743325961802896,
          47.24381885942171
        ],
        "type": "Point"
      },
      "id": 4,
      "properties": {
        "description": "ARBL03 - Départ Est",
        "title": "ARBL03",
        "name": "ARBL03",
        "color": "#00aa00",
        "fillColor": "#00aa33",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8
      },
      "type": "Feature"
    },
    {
      "geometry": {
        "coordinates": [
          -1.473437527604915,
          47.24324818948409
        ],
        "type": "Point"
      },
      "id": 5,
      "properties": {
        "description": "ARBL04 - Sud Est",
        "title": "ARBL04",
        "name": "ARBL04",
        "color": "#00ff00",
        "fillColor": "#aaff00",
        "fillOpacity": 0.8
      },
      "type": "Feature"
    },
    {
      "geometry": {
        "coordinates": [
          -1.473739759025733,
          47.24383827416719
        ],
        "type": "Point"
      },
      "id": 6,
      "properties": {
        "description": "ARBL05 - Nord Est",
        "title": "ARBL05",
        "name": "ARBL05",
        "color": "#0000ff",
        "fillColor": "#aa0000",
        "fillOpacity": 0.8
      },
      "type": "Feature"
    },
    {
      "geometry": {
        "coordinates": [
          -1.4739887654452843,
          47.24395058704869
        ],
        "type": "Point"
      },
      "id": 7,
      "properties": {
        "description": "ARBL06 - Nord",
        "title": "ARBL06",
        "name": "ARBL06",
        "color": "#ff0000",
        "fillColor": "#ffaa00",
        "fillOpacity": 0.8
      },
      "type": "Feature"
    },
    {
      "geometry": {
        "coordinates": [
          -1.4739376244419304,
          47.243720925304075
        ],
        "type": "Point"
      },
      "id": 8,
      "properties": {
        "description": "ARBL07 - Centre",
        "title": "ARBL07",
        "name": "ARBL07",
        "color": "#ccaa00",
        "fillColor": "#aaff00",
        "fillOpacity": 0.8
      },
      "type": "Feature"
    },
    {
      "geometry": {
        "coordinates": [
          -1.4740416993153929,
          47.24356182462
        ],
        "type": "Point"
      },
      "id": 9,
      "properties": {
        "description": "ARBL08 - Ouest",
        "title": "ARBL08",
        "name": "ARBL08",
        "color": "#ffff00",
        "fillColor": "#ffffaa",
        "fillOpacity": 0.8
      },
      "type": "Feature"
    },
    {
      "geometry": {
        "coordinates": [
          -1.4736141033273782,
          47.24355121140542
        ],
        "type": "Point"
      },
      "id": 10,
      "properties": {
        "description": "ARBL09 - Est",
        "title": "ARBL09",
        "name": "ARBL09",
        "color": "#00aaaa",
        "fillColor": "#00ffcc",
        "fillOpacity": 0.8
      },
      "type": "Feature"
    }
  ]
}
*/


function popupInfo(feature, layer) {
    // does this feature have a property named description ?
    if (feature.properties && feature.properties.description) {
        layer.bindPopup(feature.properties.description);
    }
}


function setStyleColor(feature) {
    if (feature.properties && feature.properties.color && feature.properties.fillColor  && feature.properties.fillOpacity) 
        style: '{color:feature.properties.color, fillcolor:feature.properties.fillColor, opacity:feature.properties.fillOpacity}';                    
}


// -----------------------
function setDataSite(response){
    if (response !== undefined){
        resetMap(); // Supprimer les éléments actuels de la carte
        //console.debug("Chargement des données pour le site \n"+ response);
        const objSite = JSON.parse(response);
        geojsonZoneConcurrents = objSite.geojsonZoneConcurrents;
        geojsonZoneNav = objSite.geojsonZoneNav;
        geojsonBalises = objSite.geojsonBalises;
        
        //console.debug(geojsonZoneConcurrents);
        //console.debug(geojsonZoneNav);
        //console.debug(geojsonBalises);  
        
        // var geojsonZoneConcurrentsArray = Object.entries(objSite.geojsonZoneConcurrents);
        //console.debug(geojsonZoneConcurrentsArray);
        var ZCCoordinates = Object.entries(geojsonZoneConcurrents.features[0].geometry.coordinates);
        // Pas évident de parcourir ce tableau d'objet !:>((
        zoneconc_lon.length=0;
        zoneconc_lat.length=0;
        
        for (var i=0; i<ZCCoordinates.length; i++ ){
            // console.debug("index,Lon,Lat : "+ZCCoordinates[i]+"\n");  
            // index,Lon,Lat : 6,-1.4733401027220054,47.24410975659259          
            // console.debug("index:"+ZCCoordinates[i][0]+ " Lon,Lat:"+ZCCoordinates[i][1]+"\n");
            // index:6 Lon,Lat:-1.4733401027220054,47.24410975659259
            var latlon = ZCCoordinates[i][1].toString().split(',');
            // console.debug("lat:"+latlon[1]+" lon:"+latlon[0]+"\n"); 
            // lat:47.24410975659259 lon:-1.4733401027220054
            // Bon c'est ça qu'il me faut: à la position ZCCoordinates[i][0] je mets la valeur latlon[0] et latlon[1] 
            zoneconc_lon[ZCCoordinates[i][0]]=latlon[0];
            zoneconc_lat[ZCCoordinates[i][0]]=latlon[1];
        }         

        var ZNCoordinates = Object.entries(geojsonZoneNav.features[0].geometry.coordinates[0]);
        // Pas évident de parcourir ce tableau d'objet !:>((
        zonenav_lon.length=0;
        zonenav_lat.length=0;
        for (var i=0; i<ZNCoordinates.length; i++ ){
            //console.debug("index,Lon,Lat : "+ZNCoordinates[i]+"\n");  
            // index,Lon,Lat : 6,-1.4733401027220054,47.24410975659259          
            // console.debug("index:"+ZNCoordinates[i][0]+ " Lon,Lat:"+ZNCoordinates[i][1]+"\n");
            // index:6 Lon,Lat:-1.4733401027220054,47.24410975659259
            var latlon = ZNCoordinates[i][1].toString().split(',');
            //console.debug("lat:"+latlon[1]+" lon:"+latlon[0]+"\n"); 
            // lat:47.24410975659259 lon:-1.4733401027220054
            // Bon c'est ça qu'il me faut
            zonenav_lon[ZNCoordinates[i][0]]=latlon[0];
            zonenav_lat[ZNCoordinates[i][0]]=latlon[1];
        }    
        //console.debug("Zone_nav_lon : \n"+zonenav_lon.toString());
        //console.debug("Zone_nav_lat : \n"+zonenav_lat.toString());
        //console.debug(afficherProps(geojsonBalises, "features"));
        
        var BalisesInfos = Object.entries(geojsonBalises.features);
        
        //var keysBalisesInfo = Object.keys(BalisesInfos[0]);
        
        //console.debug("Keys\n"+ keysBalisesInfo + "\n"); 
        //console.debug(parcoursRecursifObjet(geojsonBalises.features));
        tcoordinates.length=0;
        tid.length=0;
        tproperties.length=0;
        strproperty="";
        for (var i=0; i<BalisesInfos.length; i++) {
            initRecursifBalise(BalisesInfos[i]);
        }
        //console.debug("Id: "+tid+"\nCoordinates:\n"+tcoordinates+"\nProperties:\n"+tproperties);
        // reconstruire un tableau d'objet à paertir d'une chaîne... Il y a peut-être plus simple ?
        balisesTable.length=0;
        for (var i=0; i<tid.length; i++) {
             balisesTable[i]=(JSON.parse('{'+tid[i]+','+tcoordinates[i]+','+tproperties[i]+'}'));    
        }                
        //console.debug(balisesTable.toString());
        
        // Calculer l'emprise de la zone à afficher sur le canvas 
        rectangle_englobantZN();               
       
        // document.getElementById("rotation").style.display="inline";
        document.getElementById("transfert").style.visibility="hidden";
        initMap();
        displayMap(); // Afficher la carte  
          
        // En cas de changement de site il faut réinitialiser la collecte des balises
        nbouees=0;
        bouees.length=0;  
        // Puisque le site est chargé on peut sauvegarder les cookies 
        setCookies(); // nomDuSite, longitudeDuSite, latitudeDuSite, fichierACharger     
        drawAll();      // Afficher le canvas
    }          
}


const isObject = obj => {
  return Object.prototype.toString.call(obj) === '[object Object]'
}

function initRecursifBalise(obj){
// Parcours l'objet balise
    for (const [key, value] of Object.entries(obj)) {
        if (!isObject(value)){
            if (`${key}`=="id"){
                tid.push('"id":'+`${value}`);
            }        
            else if (`${key}`=="coordinates"){
                var lonlat= value.toString().split(',');
                //console.debug("lonlat "+lonlat);
                //console.debug("lon "+lonlat[0]);
                //console.debug("lat "+lonlat[1]);
                tcoordinates.push('"lon":'+lonlat[0]+ ',"lat":'+lonlat[1]+" ");
            }
            else if (`${key}`=="name"){
                strproperty=strproperty+ ' "name":"'+`${value}`+'"';
            }
            else if (`${key}`=="color"){
                strproperty=strproperty+ ',"color":"'+`${value}`+'"';
                
            }
            else if (`${key}`=="fillColor"){
               strproperty=strproperty+ ',"fillcolor":"'+`${value}`+'" ';
               tproperties.push(strproperty);
               strproperty="";
            }
        }
        else{
            initRecursifBalise(value);
        }            
    }
}


function parcoursRecursifObjet(obj){
    var retour="";
    for (const [key, value] of Object.entries(obj)) {
        if (!isObject(value)){
            retour = retour + `${key}:`+"\t"+`${value}`+"\n";
        }
        else{
            retour = retour + `${key}:`+"\n" + parcoursRecursifObjet(value);
        }            
    }
    return retour;
}

function afficherProps(obj, nomObjet) {
  let resultat = "";
  for (let i in obj) {
    if (obj.hasOwnProperty(i)) {
        var tabobj = Object.entries(obj);
        console.debug("TabObj:"+tabobj+"\n");
        resultat += `${nomObjet}.${i} => ${obj[i]}\n`;
    }
  }
  return resultat;
}

