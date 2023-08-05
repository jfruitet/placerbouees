// JavaScript Document
// Gestion de la cartographie
// Carte de l'étang du Plessis           
var osm;
var osmHOT;
var baseMaps;
var overlayMaps;
var targetSite;
var map;

var geojsonBouees='';
var layerControl;
var tmarkers=[]; // tableau des markers de bouees
var mesboues=null;// La couche des markers de bouées

var legend;
var zonenav;
var zoneconcurrents;
var balises;
var mesbouees;
var baliseSite;

    
const mySvgIconBalises = L.divIcon({
  html: `            
<svg width="60" height="60" viewBox="0 0 100 100"
  version="1.1"
  <!-- preserveAspectRatio="none" -->
  fill="none" 
  xmlns="http://www.w3.org/2000/svg">
<path d="M46.0859 36.5517C46.0859 44.8804 38.9758 51.7931 30.0016 51.7931C21.0274 51.7931 13.9172 44.8804 13.9172 36.5517C13.9172 28.223 21.0274 21.3103 30.0016 21.3103C38.9758 21.3103 46.0859 28.223 46.0859 36.5517Z" fill="#F5FA1A" stroke="#430A0A" stroke-width="4"/>
<ellipse cx="30.0016" cy="37.931" rx="18.0844" ry="15.8621" fill="#430A0A"/>
<mask id="path-3-inside-1_0_1" fill="white">
<path fill-rule="evenodd" clip-rule="evenodd" d="M13.6673 35.5172C13.6673 28.8885 18.288 23.2757 24.6626 21.3775C25.6635 22.6658 26.6275 24.0714 27.5211 25.5514C26.3595 26.6144 25.3531 27.8212 24.4948 29.1375C21.1263 34.3034 20.0537 41.102 20.4955 47.7378C16.3711 45.0641 13.6673 40.5879 13.6673 35.5172ZM34.7628 21.5651C33.3745 21.0937 31.8957 20.8008 30.3575 20.7156C30.8038 21.372 31.2395 22.0473 31.662 22.7384C32.0695 22.5402 32.4891 22.3554 32.921 22.1847C33.5159 21.9495 34.1298 21.7428 34.7628 21.5651ZM34.0577 27.1356C34.2864 27.0294 34.5201 26.929 34.7591 26.8346C36.5507 26.1264 38.7075 25.7239 41.2899 25.7722C43.7057 28.3781 45.1691 31.7868 45.1691 35.5172C45.1691 41.2588 41.7026 46.2381 36.6323 48.7017C37.9744 45.4806 38.1385 41.8374 37.619 38.2591C37.0829 34.5671 35.7916 30.7401 34.0577 27.1356ZM29.9643 30.186C31.2847 33.0985 32.2556 36.1173 32.6709 38.9775C33.1827 42.5027 32.815 45.5244 31.515 47.8022C30.9881 48.7254 30.2724 49.5889 29.3091 50.3445C28.0793 50.3366 26.883 50.1961 25.7355 49.9373C24.8354 43.0981 25.7236 36.4071 28.683 31.8686C29.0733 31.2701 29.4997 30.7081 29.9643 30.186Z"/>
</mask>
<path fill-rule="evenodd" clip-rule="evenodd" d="M13.6673 35.5172C13.6673 28.8885 18.288 23.2757 24.6626 21.3775C25.6635 22.6658 26.6275 24.0714 27.5211 25.5514C26.3595 26.6144 25.3531 27.8212 24.4948 29.1375C21.1263 34.3034 20.0537 41.102 20.4955 47.7378C16.3711 45.0641 13.6673 40.5879 13.6673 35.5172ZM34.7628 21.5651C33.3745 21.0937 31.8957 20.8008 30.3575 20.7156C30.8038 21.372 31.2395 22.0473 31.662 22.7384C32.0695 22.5402 32.4891 22.3554 32.921 22.1847C33.5159 21.9495 34.1298 21.7428 34.7628 21.5651ZM34.0577 27.1356C34.2864 27.0294 34.5201 26.929 34.7591 26.8346C36.5507 26.1264 38.7075 25.7239 41.2899 25.7722C43.7057 28.3781 45.1691 31.7868 45.1691 35.5172C45.1691 41.2588 41.7026 46.2381 36.6323 48.7017C37.9744 45.4806 38.1385 41.8374 37.619 38.2591C37.0829 34.5671 35.7916 30.7401 34.0577 27.1356ZM29.9643 30.186C31.2847 33.0985 32.2556 36.1173 32.6709 38.9775C33.1827 42.5027 32.815 45.5244 31.515 47.8022C30.9881 48.7254 30.2724 49.5889 29.3091 50.3445C28.0793 50.3366 26.883 50.1961 25.7355 49.9373C24.8354 43.0981 25.7236 36.4071 28.683 31.8686C29.0733 31.2701 29.4997 30.7081 29.9643 30.186Z" fill="#F5FA1A"/>
<path d="M24.6626 21.3775L25.4523 20.764L25.0327 20.2239L24.3772 20.4191L24.6626 21.3775ZM27.5211 25.5514L28.1962 26.2892L28.8007 25.736L28.3772 25.0345L27.5211 25.5514ZM24.4948 29.1375L25.3324 29.6837L25.3324 29.6837L24.4948 29.1375ZM20.4955 47.7378L19.9515 48.5769L21.6258 49.6624L21.4933 47.6714L20.4955 47.7378ZM34.7628 21.5651L35.0331 22.5278L38.1398 21.6556L35.0843 20.6181L34.7628 21.5651ZM30.3575 20.7156L30.4128 19.7171L28.3936 19.6053L29.5305 21.2778L30.3575 20.7156ZM31.662 22.7384L30.8088 23.26L31.2825 24.035L32.0994 23.6377L31.662 22.7384ZM32.921 22.1847L33.2886 23.1147L33.2886 23.1147L32.921 22.1847ZM34.0577 27.1356L33.6365 26.2287L32.7171 26.6557L33.1566 27.5691L34.0577 27.1356ZM34.7591 26.8346L35.1267 27.7646L35.1267 27.7646L34.7591 26.8346ZM41.2899 25.7722L42.0232 25.0923L41.7339 24.7803L41.3085 24.7723L41.2899 25.7722ZM36.6323 48.7017L35.7092 48.3171L34.6932 50.7556L37.0693 49.6011L36.6323 48.7017ZM37.619 38.2591L38.6086 38.1154L38.6086 38.1154L37.619 38.2591ZM32.6709 38.9775L33.6605 38.8338L33.6605 38.8338L32.6709 38.9775ZM29.9643 30.186L30.875 29.7731L30.2398 28.372L29.2172 29.5213L29.9643 30.186ZM31.515 47.8022L32.3835 48.2979L32.3835 48.2979L31.515 47.8022ZM29.3091 50.3445L29.3027 51.3445L29.6517 51.3467L29.9263 51.1313L29.3091 50.3445ZM25.7355 49.9373L24.744 50.0678L24.8351 50.7594L25.5155 50.9128L25.7355 49.9373ZM28.683 31.8686L27.8454 31.3224L27.8454 31.3224L28.683 31.8686ZM24.3772 20.4191C17.6245 22.4299 12.6673 28.4 12.6673 35.5172H14.6673C14.6673 29.377 18.9514 24.1215 24.948 22.3359L24.3772 20.4191ZM28.3772 25.0345C27.4642 23.5224 26.4782 22.0845 25.4523 20.764L23.8729 21.991C24.8489 23.2472 25.7908 24.6204 26.6651 26.0683L28.3772 25.0345ZM26.8461 24.8137C25.6182 25.9372 24.5581 27.2095 23.6571 28.5913L25.3324 29.6837C26.148 28.4329 27.1008 27.2915 28.1962 26.2892L26.8461 24.8137ZM23.6571 28.5913C20.1293 34.0015 19.0475 41.0416 19.4977 47.8043L21.4933 47.6714C21.06 41.1624 22.1233 34.6052 25.3324 29.6837L23.6571 28.5913ZM12.6673 35.5172C12.6673 40.9579 15.5699 45.7364 19.9515 48.5769L21.0394 46.8987C17.1723 44.3917 14.6673 40.2179 14.6673 35.5172H12.6673ZM35.0843 20.6181C33.6106 20.1178 32.0423 19.8073 30.4128 19.7171L30.3023 21.7141C31.7491 21.7942 33.1384 22.0696 34.4413 22.512L35.0843 20.6181ZM29.5305 21.2778C29.9676 21.9208 30.3946 22.5825 30.8088 23.26L32.5152 22.2169C32.0843 21.512 31.6399 20.8232 31.1846 20.1534L29.5305 21.2778ZM32.5534 21.2547C32.098 21.4347 31.655 21.6298 31.2246 21.8392L32.0994 23.6377C32.4839 23.4507 32.8803 23.2761 33.2886 23.1147L32.5534 21.2547ZM34.4925 20.6023C33.8271 20.7891 33.1807 21.0068 32.5534 21.2547L33.2886 23.1147C33.8511 22.8923 34.4325 22.6965 35.0331 22.5278L34.4925 20.6023ZM34.4789 28.0426C34.6898 27.9446 34.9057 27.8519 35.1267 27.7646L34.3915 25.9046C34.1346 26.0062 33.8829 26.1142 33.6365 26.2287L34.4789 28.0426ZM35.1267 27.7646C36.7809 27.1107 38.8044 26.7259 41.2712 26.772L41.3085 24.7723C38.6106 24.7219 36.3205 25.1421 34.3915 25.9046L35.1267 27.7646ZM46.1691 35.5172C46.1691 31.5177 44.5986 27.8703 42.0232 25.0923L40.5565 26.452C42.8129 28.8859 44.1691 32.0559 44.1691 35.5172H46.1691ZM37.0693 49.6011C42.4483 46.9876 46.1691 41.6797 46.1691 35.5172H44.1691C44.1691 40.8378 40.9568 45.4887 36.1953 47.8022L37.0693 49.6011ZM36.6294 38.4028C37.1323 41.8669 36.9603 45.3143 35.7092 48.3171L37.5554 49.0863C38.9884 45.6469 39.1448 41.808 38.6086 38.1154L36.6294 38.4028ZM33.1566 27.5691C34.8573 31.1046 36.1111 34.8334 36.6294 38.4028L38.6086 38.1154C38.0548 34.3008 36.726 30.3756 34.9589 26.7021L33.1566 27.5691ZM33.6605 38.8338C33.2286 35.8594 32.2243 32.7492 30.875 29.7731L29.0535 30.5989C30.3451 33.4479 31.2826 36.3753 31.6813 39.1212L33.6605 38.8338ZM32.3835 48.2979C33.8319 45.7601 34.1913 42.4899 33.6605 38.8338L31.6813 39.1212C32.1741 42.5156 31.7981 45.2887 30.6465 47.3065L32.3835 48.2979ZM29.9263 51.1313C30.9947 50.2932 31.7946 49.3297 32.3835 48.2979L30.6465 47.3065C30.1816 48.121 29.5501 48.8845 28.6919 49.5576L29.9263 51.1313ZM25.5155 50.9128C26.7328 51.1873 28.0007 51.3361 29.3027 51.3445L29.3155 49.3445C28.1579 49.3371 27.0332 49.2048 25.9555 48.9618L25.5155 50.9128ZM26.7269 49.8069C25.8439 43.0967 26.7406 36.6781 29.5207 32.4148L27.8454 31.3224C24.7065 36.1361 23.827 43.0994 24.744 50.0678L26.7269 49.8069ZM29.5207 32.4148C29.8853 31.8556 30.2817 31.3336 30.7113 30.8508L29.2172 29.5213C28.7177 30.0826 28.2612 30.6846 27.8454 31.3224L29.5207 32.4148Z" fill="#430A0A" mask="url(#path-3-inside-1_0_1)"/>
<path d="M61.3483 11.0218L34.0852 14.3475L40.2135 0.56667L61.3483 11.0218Z" fill="#D72D4B"/>
<path d="M31.46 23.7931L40.7939 1.72414" stroke="#430A0A" stroke-width="3"/>
<path d="M2 47.0671C5.13793 44.9682 14.9157 42.0298 28.9234 47.0671C42.9312 52.1044 53.211 49.5287 56.6 47.6112" stroke="#5293F4" stroke-width="5"/>
<path d="M4.4 53.4957C7.53793 51.3968 17.3157 48.4583 31.3234 53.4957C45.3312 58.533 55.611 55.9573 59 54.0398" stroke="#5293F4" stroke-width="5"/>
`,
  className: "",
  iconSize: [24, 40],
  iconAnchor: [12, 40],
});   

// Reset les markers de bouées déjà présents
function resetMarkersBouees(){
    if (tmarkers.length>0){
        if (mesboues !== undefined)
        {
            map.removeLayer(mesbouees);
            map.removeControl(layerControl);
            overlayMaps = {
                    "Info": balises,
                    "Zone Nav.":zonenav, 
                    "Concurrents": zoneconcurrents, 
                    "Balises": balises
            }
            layerControl = L.control.layers(baseMaps, overlayMaps).addTo(map); // Deux couches proposées
            //layerControl = L.control.layers(overlayMaps).addTo(map);   
        }
    }        
}

// Ajouter les bouees à la carte
// ------------------------------
function addBouees2Map(){
    
    if ((map!==undefined) && (bouees !== undefined) && (bouees.length>0)){
        
        resetMarkersBouees();
        var myIcon;
  
        for (var index=0; index<bouees.length; index++){  
            var style='';
            var description="Bouée N° "+bouees[index].id;
            var franchissement = true; // Tribord
            // Franchissement           
            if (bouees[index].flag=="green"){
                franchissement = true;
            }
            else{
                franchissement = false;   
            }             
            // Type de bouée
            if (bouees[index].color=="yellow"){
                description = description + '. Bouée de départ ';
                if (franchissement == true){
                    myIcon = myIconYellowTribord;
                }  
                else{
                    myIcon = myIconYellowBabord;
                }
            }
            else if (bouees[index].color=="blue"){
                description = description + '. Bouée d\'arrivée ';  
                if (franchissement == true){
                    myIcon = myIconBlueTribord;
                }  
                else{
                    myIcon = myIconBlueBabord;
                }          
            } 
            else if (bouees[index].color=="purple"){
                description = description + '. Porte ';  
                myIcon = myIconPurple;  
                if (franchissement == true){
                    myIcon = myIconPurpleTribord;
                }  
                else{
                    myIcon = myIconPurpleBabord;
                }                                   
            } 
            else{
                if (bouees[index].flag=="green"){
                    myIcon = myIconGreen; 
                }
                else{
                    myIcon = myIconRed; 
                }
            }   
            //console.log("maps:: 123 :: Lat:"+bouees[index].lat+" Lon: "+bouees[index].lon+"\n");
            
            //geojsonb = geojsonb + ',"weight":1,"opacity":1,"fillOpacity":0.8},"geometry":{"coordinates":['+ bouees[index].lon +','+bouees[index].lat+'],"type":"Point"}} ';
            var latlng = L.latLng(bouees[index].lat,bouees[index].lon);
            tmarkers[index] = L.marker(latlng, {title: "Bouée N°"+bouees[index].id,clickable: true,draggable: false,icon: myIcon}).bindPopup(description + " ("+bouees[index].lon+","+bouees[index].lat+")");                       
        }
    }
   //  console.log("geojsonBouees\n"+geojsonBouees);
   //  console.log("\nstringify \n:"+JSON.stringify(geojsonBouees, null, 2));
      
    if (tmarkers.length>0)
    {
        mesbouees=L.layerGroup(tmarkers);
    	setTimeout(() => {  
            // Ajout des bouees
            map.addLayer(mesbouees);
            // Ajout du controle
       	    layerControl.addOverlay(mesbouees, "Balises mobiles");
        }, 1000)
    }
}

        
// -------------------
// Construction de la carte
// ------------------------------   
function resetMap(){
    //console.debug("Reset Map\n");
    // Supprimer les éléments actuels de la carte sauf le marker du site
        if (layerControl !== undefined)  {map.removeControl(layerControl);}                   
        if (legend !== undefined) {map.removeControl(legend);}
        if (mesbouees !== undefined) { map.removeLayer(mesbouees);}
        if (zonenav !== undefined) { map.removeLayer(zonenav);}
        if (zoneconcurrents !== undefined) { map.removeLayer(zoneconcurrents);}
        if (balises !== undefined) { map.removeLayer(balises);}
        if (baliseSite !== undefined) { baliseSite.removeFrom(map);}
}



function initMap(){
    //console.debug("Init Map\n");
    //console.debug(" Lat: "+latitudeDuSite+" Lon: "+longitudeDuSite);
    // Target's GPS coordinates. Coordonnées du centre du plan d'eau
    if ((latitudeDuSite!==undefined) && (latitudeDuSite!=0) && (longitudeDuSite!==undefined) && (longitudeDuSite!=0)){   
        targetSite = L.latLng(latitudeDuSite, longitudeDuSite);
        // Add OSM tile layer to the Leaflet map.
        osm = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            center:targetSite,
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        });
        
        // Autre source de carte (ce sont des images de pixels géolocalisés)
        osmHOT = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            maxZoom: 19,
            center:targetSite,
            attribution: '© OpenStreetMap contributors, Tiles style by Humanitarian OpenStreetMap Team hosted by OpenStreetMap France'
        });

        // Deux couches de base disponibles
        baseMaps = {
            "OpenStreetMap": osm,
            "<span style='color: navy'>OpenStreetMap.HOT</span>": osmHOT
        };     
      
       // Set map's center to target with zoom 18.  
        if (map===undefined){
            map = L.map('osm-map', {
                center: targetSite,
                zoom: 18,
                layers: [osm]            
            });    
            
            layerControl = L.control.layers(baseMaps).addTo(map); // Deux couches 
        }                     
    }
}


function displayMap(){

    if (map!==undefined){
        // Ajout de la zone de navigation
        if (geojsonZoneNav !== undefined){
            //zonenav = L.geoJSON(JSON.stringify(geojsonZoneNav), {
            zonenav = L.geoJSON(geojsonZoneNav, {
                style: function (feature) {
                    return {color: feature.properties.color};
                }
            }).bindPopup(function (layer) {
                return layer.feature.properties.description;
            });
            map.addLayer(zonenav);
        }
        
        // Ajout de la zone de deambulation
        if (geojsonZoneConcurrents !== undefined){
            //zoneconcurrents = L.geoJSON(JSON.stringify(geojsonZoneConcurrents), {
            zoneconcurrents = L.geoJSON(geojsonZoneConcurrents, {
                style: function (feature) {
                    return {color: feature.properties.color};
                }
            }).bindPopup(function (layer) {
                return layer.feature.properties.description;
            });
            map.addLayer(zoneconcurrents);
        }
        // Ajout des balises
        // mySvgIconBalises
        
        var geojsonMarkerOptions = {
            radius: 6,
            weight: 1,
            opacity: 1,
            fillOpacity: 0.8
        };
        
        if (geojsonBalises !== undefined){
            //balises = L.geoJSON(JSON.stringify(geojsonBalises), {
            balises = L.geoJSON(geojsonBalises, {
                pointToLayer: function (feature, latlng) {
                    return L.circleMarker(latlng, geojsonMarkerOptions);
                },
                style: function (feature) {
                    return {color: feature.properties.color};
                }
            }).bindPopup(function (layer) {
                return layer.feature.properties.description;
            });
            map.addLayer(balises);
        }
         


        // Place a marker on the same location.
        // Target's GPS coordinates. Coordonnées du centre de l'écran du Plessis
        // petite image d'une bouée joliment dessinée dont je suis très content :>))
        const svgIcon = L.divIcon({
  html: `            
<svg width="40" height="40" viewBox="0 0 60 60"
  version="1.1"
  preserveAspectRatio="none"
  fill="none" 
  xmlns="http://www.w3.org/2000/svg">
<path d="M0 4C0 1.79086 1.79086 0 4 0H56C58.2091 0 60 1.79086 60 4V56C60 58.2091 58.2091 60 56 60H4C1.79086 60 0 58.2091 0 56V4Z" fill="#72A6F5"/>
<path d="M18.6 27V38.4" stroke="#916740" stroke-width="4"/>
<path d="M38.6356 8.17232C39.1235 7.47099 40.003 7.157 40.8247 7.39079L44.3568 8.39568C46.2843 8.94408 46.2971 11.6714 44.3747 12.2378L39.2183 13.7571C37.5715 14.2423 36.119 12.5639 36.8355 11.0039L37.0912 10.4472C37.1406 10.3397 37.1994 10.2369 37.2669 10.1399L38.6356 8.17232Z" fill="#F71674"/>
<path d="M42.9 37.418C42.9 37.4035 42.8992 37.4056 42.8927 37.424C42.8688 37.4911 42.7675 37.7757 42.3429 38.2582C41.8584 38.8086 41.0958 39.4488 40.0632 40.059C38.0053 41.2751 34.9962 42.3 31.3138 42.3C27.6212 42.3 24.2251 41.2692 21.77 40.0147C20.5408 39.3866 19.5925 38.7252 18.9765 38.1515C18.6674 37.8637 18.4735 37.6272 18.3667 37.4582C18.3313 37.4023 18.3111 37.3628 18.3002 37.339C18.3023 36.8404 18.3248 36.729 18.3637 36.6499C18.3637 36.6499 18.3638 36.6497 18.3639 36.6495C18.3688 36.6383 18.4602 36.4296 19.1376 36.1643C20.6426 35.575 23.9939 35.1 31.3138 35.1C36.2868 35.1 39.3562 35.56 41.1303 36.1829C42.9254 36.8131 42.901 37.3939 42.9 37.4173C42.9 37.4177 42.9 37.4179 42.9 37.418Z" fill="#F8EF25"/>
<path d="M42.9 37.418C42.9 37.4035 42.8992 37.4056 42.8927 37.424C42.8688 37.4911 42.7675 37.7757 42.3429 38.2582C41.8584 38.8086 41.0958 39.4488 40.0632 40.059C38.0053 41.2751 34.9962 42.3 31.3138 42.3C27.6212 42.3 24.2251 41.2692 21.77 40.0147C20.5408 39.3866 19.5925 38.7252 18.9765 38.1515C18.6674 37.8637 18.4735 37.6272 18.3667 37.4582C18.3313 37.4023 18.3111 37.3628 18.3002 37.339C18.3023 36.8404 18.3248 36.729 18.3637 36.6499C18.3637 36.6499 18.3638 36.6497 18.3639 36.6495C18.3688 36.6383 18.4602 36.4296 19.1376 36.1643C20.6426 35.575 23.9939 35.1 31.3138 35.1C36.2868 35.1 39.3562 35.56 41.1303 36.1829C42.9254 36.8131 42.901 37.3939 42.9 37.4173C42.9 37.4177 42.9 37.4179 42.9 37.418Z" fill="#916740"/>
<path d="M42.9 37.418C42.9 37.4035 42.8992 37.4056 42.8927 37.424C42.8688 37.4911 42.7675 37.7757 42.3429 38.2582C41.8584 38.8086 41.0958 39.4488 40.0632 40.059C38.0053 41.2751 34.9962 42.3 31.3138 42.3C27.6212 42.3 24.2251 41.2692 21.77 40.0147C20.5408 39.3866 19.5925 38.7252 18.9765 38.1515C18.6674 37.8637 18.4735 37.6272 18.3667 37.4582C18.3313 37.4023 18.3111 37.3628 18.3002 37.339C18.3023 36.8404 18.3248 36.729 18.3637 36.6499C18.3637 36.6499 18.3638 36.6497 18.3639 36.6495C18.3688 36.6383 18.4602 36.4296 19.1376 36.1643C20.6426 35.575 23.9939 35.1 31.3138 35.1C36.2868 35.1 39.3562 35.56 41.1303 36.1829C42.9254 36.8131 42.901 37.3939 42.9 37.4173C42.9 37.4177 42.9 37.4179 42.9 37.418Z" fill="black" fill-opacity="0.2"/>
<path d="M42.9 37.418C42.9 37.4035 42.8992 37.4056 42.8927 37.424C42.8688 37.4911 42.7675 37.7757 42.3429 38.2582C41.8584 38.8086 41.0958 39.4488 40.0632 40.059C38.0053 41.2751 34.9962 42.3 31.3138 42.3C27.6212 42.3 24.2251 41.2692 21.77 40.0147C20.5408 39.3866 19.5925 38.7252 18.9765 38.1515C18.6674 37.8637 18.4735 37.6272 18.3667 37.4582C18.3313 37.4023 18.3111 37.3628 18.3002 37.339C18.3023 36.8404 18.3248 36.729 18.3637 36.6499C18.3637 36.6499 18.3638 36.6497 18.3639 36.6495C18.3688 36.6383 18.4602 36.4296 19.1376 36.1643C20.6426 35.575 23.9939 35.1 31.3138 35.1C36.2868 35.1 39.3562 35.56 41.1303 36.1829C42.9254 36.8131 42.901 37.3939 42.9 37.4173C42.9 37.4177 42.9 37.4179 42.9 37.418Z" stroke="#C41C1C" stroke-width="3"/>
<path d="M44 37.1617C44 37.5587 43.7757 38.1544 43.1386 38.8843C42.5173 39.5961 41.5822 40.3415 40.3789 41.0203C37.9744 42.3768 34.6182 43.4 30.9 43.4C27.1818 43.4 23.8256 42.3768 21.4211 41.0203C20.2178 40.3415 19.2827 39.5961 18.6614 38.8843C18.0243 38.1544 17.8 37.5587 17.8 37.1617C17.8 36.8046 17.9866 36.3488 18.5766 35.8183C19.1663 35.2882 20.0759 34.765 21.2852 34.3051C23.6969 33.3881 27.0959 32.8 30.9 32.8C34.7041 32.8 38.1032 33.3881 40.5148 34.3051C41.7241 34.765 42.6337 35.2882 43.2234 35.8183C43.8134 36.3488 44 36.8046 44 37.1617Z" fill="#F8EF25" stroke="#916740" stroke-width="2"/>
<path d="M44.4 25.8V37.2" stroke="#916740" stroke-width="2"/>
<path d="M17.4 25.2V36.6" stroke="#916740" stroke-width="2"/>
<path d="M6 47.6428C24.0867 54.2382 28.9355 43.3528 53.4 46.0341" stroke="#474091" stroke-opacity="0.7" stroke-width="2"/>
<path d="M6 43.1347C24.0867 50.6722 28.9355 38.2318 53.4 41.2961" stroke="#474091" stroke-opacity="0.7" stroke-width="2"/>
<rect x="18.6" y="26.4" width="24.6" height="10.8" fill="#F8EF25"/>
<path d="M44 26C44 26.0414 43.9617 26.3061 43.3349 26.7265C42.7445 27.1225 41.8242 27.5183 40.5973 27.8664C38.1572 28.5586 34.7283 29 30.9 29C27.0717 29 23.6428 28.5586 21.2027 27.8664C19.9758 27.5183 19.0555 27.1225 18.4651 26.7265C17.8383 26.3061 17.8 26.0414 17.8 26C17.8 25.8961 17.9098 25.5242 18.5691 24.9434C19.1827 24.4028 20.1183 23.8217 21.3329 23.2867C23.7556 22.2196 27.1431 21.4 30.9 21.4C34.6569 21.4 38.0444 22.2196 40.4671 23.2867C41.6817 23.8217 42.6173 24.4028 43.2309 24.9434C43.8902 25.5242 44 25.8961 44 26Z" fill="#F8EF25" stroke="#916740" stroke-width="2"/>
<path d="M31.2 25.2C32.7306 20.0867 33.7484 17.6295 35.1217 14.4397M41.0741 4.84217C40.3508 4.82244 40.9298 4.40652 39.2942 6.67352M39.2942 6.67352L49.8 9.89455L35.1217 14.4397M39.2942 6.67352C38.0821 8.35346 36.6675 10.8493 35.1217 14.4397" stroke="#916740" stroke-width="1.5"/>
</svg> `,
  className: "",
  iconSize: [40, 40],
  iconAnchor: [20, 0],
});     
    
        baliseSite = L.marker(targetSite, { icon: svgIcon }).addTo(map)
            .bindPopup(infoSite);

        // Couches qui se superposent avec les objets
        overlayMaps = {
            "Info": baliseSite,
            "Zone Nav.":zonenav, 
            "Concurrents": zoneconcurrents, 
            "Bouées ancrées": balises
        }      
             
        map.removeControl(layerControl);    // L'ancien contrôle est remplacé
        layerControl = L.control.layers(baseMaps, overlayMaps).addTo(map);  


        // Légende 
        if (legend !== undefined){map.removeControl(legend);} 
        legend = L.control({ position: "bottomleft" });

        legend.onAdd = function(map) {
            var div = L.DomUtil.create("div", "legend");
            div.innerHTML += "<h4>Légende</h4>";
            div.innerHTML += '<i style="background: #0000aa"></i><span>ZN</span><br>';
            div.innerHTML += '<i style="background: #aaaa33"></i><span>ZC</span><br>';
            div.innerHTML += '<i class="icon" style="background-image: url(./images/dot.png);background-repeat: no-repeat;"></i><span>Bouées</span><br>'; 
            div.innerHTML += '<i class="icon2" style="background-image: url(./images/marker-icon-small3.png);background-repeat: no-repeat;"></i><span>Balises</span><br>'; 
            return div;
        };

        legend.addTo(map);  
        map.flyTo(targetSite);  
    }        
}
    

        
