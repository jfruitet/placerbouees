// JavaScript Document
// Zone de Navigation et Bouees Fixes

const zonenav_lat=[47.243830107759834,47.24306984068747,47.24323699253995,47.24364139000113,47.24402062214875,47.24396131113892,47.243830107759834];
const zonenav_lon=[-1.4746555619712751,-1.4733794850961033,-1.4732841764495106,-1.4733636003215622,-1.4737871943051744,-1.4743378664837508,-1.4746555619712751];             

const zoneconc_lon= [-1.4747445028388313,-1.4744189545636743,-1.4741350461847276,-1.4739079194809506,-1.4736921491121393,-1.4734763787433565,-1.4733401027220054];
const zoneconc_lat = [47.2439195874486,47.24402752137087,47.2440943374981,47.244138024920545,47.24416372338749,47.24415601384845,47.24410975659259];
 
var geojsonZoneConcurents={
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "title": "ZC",
        "description": "Zone de déplacement des concurrents"      
      },
      "infobox": true,
      "color": "#ffffee",
      "stroke-color": "#ffffee",      
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
};

var geojsonZoneNav={
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "title": "Zone de navigation",
        "description": "Zone interdite aux pêcheurs"
      },
      "infobox": true,
      "fill": "#ffffee",
      "fill-opacity": "0.2",
      "stroke-color": "#0000aa",
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
};

var geojsonBalises={
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
        "description": "R&eacute;serv&eacute; au radio mod&eacute;lisme",
        "title": "Ponton",
        "name": "Ponton",
        "marker-color": "#000000",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-black.png",
        "size":15,      
        "color": "#000000",
        "fillColor": "#000000",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "Ponton r&eacute;serv&eacute; au radio mod&eacute;lisme !"
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
        "description": "D&eacute;part Nord",
        "title": "ARBL01",
        "name": "ARBL01",        
        "marker-color": "#0000aa",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-navy.png",    
        "size":15,      
        "color": "#0000aa",            
        "fillColor": "#0033aa",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "ARBL01 - D&eacute;part Nord"
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
        "description": "D&eacute;part Ouest",
        "title": "ARBL02",
        "name": "ARBL02",         
        "marker-color": "#0000ff",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-green.png",            
        "size":15,      
        "color": "#0000ff",
        "fillColor": "#00aaff",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "ARBL02 - D&eacute;part Ouest"
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
        "description": "D&eacute;part Est",
        "title": "ARBL03",
        "name": "ARBL03",         
        "marker-color": "#00aa00",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-red.png",            
        "size":15,      
        "color": "#00aa00",        
        "fillColor": "#00aa33",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "ARBL03 - D&eacute;part Est"
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
        "description": "Sud Est",
        "title": "ARBL04",
        "name": "ARBL04",         
        "marker-color": "#00ff00",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-blue.png",            
        "size":15,      
        "color": "#00ff00",        
        "fillColor": "#aaff00",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "ARBL04 - Sud Est"
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
        "description": "Nord Est",
        "title": "ARBL05",
        "name": "ARBL05",         
        "marker-color": "#aa0000",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-pink.png",            
        "size":15,      
        "color": "#0000ff",        
        "fillColor": "#aa0000",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "ARBL05 - Nord Est"
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
        "description": "Nord",
        "title": "ARBL06",
        "name": "ARBL06",         
        "marker-color": "#ff0000",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-yellow.png",            
        "size":15,      
        "color": "#ff0000",        
        "fillColor": "#ffaa00",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "ARBL06 - Nord"
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
        "description": "Centre",
        "title": "ARBL07",
        "name": "ARBL07",         
        "marker-color": "#aaaa00",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-orange.png",            
        "size":15,      
        "color": "#aaaa00",        
        "fillColor": "#aaff00",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "ARBL07 - Centre"
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
        "description": "Ouest",
        "title": "ARBL08",
        "name": "ARBL08",         
        "marker-color": "#ffff00",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-vertclair.png",            
        "size":15,      
        "color": "#ffff00",        
        "fillColor": "#ffffaa",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "ARBL08 - Ouest"
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
        "description": "Est",
        "title": "ARBL09",
        "name": "ARBL09",         
        "marker-color": "#00aaaa",
        "marker-size": "little",
        "marker-symbol": "../images/marker-icon-purple.png",            
        "size":15,      
        "color": "#00aaaa",        
        "fillColor": "#00ffcc",
        "weight": 1,
        "opacity": 1,
        "fillOpacity": 0.8,
        "popupContent": "ARBL10 - Est"
      },
      "type": "Feature"
    }
  ]
};

function popupInfo(feature, layer) {
    // does this feature have a property named popupContent?
    if (feature.properties && feature.properties.popupContent) {
        layer.bindPopup(feature.properties.popupContent);
    }
}

/*
function setStyleColor(feature) {
    if (feature.properties && feature.properties.color && feature.properties.fillcolor) {
        style: '{"marker-color":'+feature.properties.color+', "fillcolor": '+feature.properties.fillcolor+'}';        
    }
}
*/
// Ne fonctionne pas
function setStyleColor(feature) {
    if (feature.properties && feature.properties.color && feature.properties.fillcolor) 
        style: '{color:feature.properties.color, fillcolor:feature.properties.fillcolor}';            
}

