<?php
// Placement automatique de bouées de régate sur un site en fonction de la direction du vent
// Ce code est pratiquement complet... et assez peu efficient !

include ("./include/config.php");
include ("./include/saisie.php");
include ("./include/geo_utils.php");
include ("./include/algo.php");

$debug = false;
$debug1 = false;
$debug2 = false;
$debug3 = false;

$nomSite=''; // Pour les données sauvegardées
$nomSite2=''; // Pour le nom de fichier des données saugegardees 
$site='';   // pour le nom de fichier de données importées concernant les données du site
$nbouees=6; // nombre max de bouées mobiles à placer.
$ecartBoueesXmetres=10;
$ecartBoueesYmetres=50;

$reponse_ok = '{"ok":1}';
$reponse_not_ok = '{"ok":0}';

$data=null;
$dataObject=null;

$reponse=$reponse_not_ok;
$xPasse1=0; // les abscisses recherchées pour le placement des bouées
$xPasse2=0;
$yMaxPasse1=array();    // Les ordonnées des droites déterminant le rectangle de placement des bouées 
$yMaxPasse2=array();
$twd_radian=0.0;
$twd_degre=90;

// Coordonnées relatives du rectangle où placer les bouées
$xouest=0;
$xest=$canvasw;
$ysud=$canvash;
$ynord=0; 
$x1Lon=0.0;
$y1Lat=0.0;
$x2Lon=0.0;
$y2Lat=0.0;

// Get the JSON contents
if (isset($_SERVER['REQUEST_METHOD']) && (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')) {
  throw new Exception('Only GET requests are allowed');
}
if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false)) {
  throw new Exception('Content-Type must be application/json');
}

if (isset($_GET) && !empty($_GET)){
    if ($debug){
        echo '$_GET '."\n";
        print_r($_GET);  
    }
    if (isset($_GET['twd'])){
        if (is_numeric($_GET['twd']))
        {
            $twd=intval($_GET['twd']);
            if ($twd>=0 && $twd<=360){
                $twd_degre=$twd;          
            }
        }        
    }
    if (!empty($_GET['site'])){
        $nomSite=urldecode($_GET['site']);
        $nomSite2=str_replace(' ','',$nomSite);
        $nomSite2=str_replace("'",'',$nomSite2);
        $site=strtolower($nomSite2);
    }
    if (!empty($_GET['nbouees'])){
        $nbouees=$_GET['nbouees'];
    }
    if (!empty($_GET['ecartBoueesX'])){
        $ecartBoueesXmetres=$_GET['ecartBoueesX'];
    }
    if (!empty($_GET['ecartBoueesY'])){
        $ecartBoueesYmetres=$_GET['ecartBoueesY'];
    }
}


// Le calcul commence
/*
B° → A radian : A = ( [(450 - B) MODULO 2PI] * PI / 90.0)
*/
if ($debug || $debug1 || $debug2 || $debug3){
    echo ("<html><head></head><body><h3>Placer_bouees.php</h3><p>Placement automatique des bouées de régate mobiles<br>(cc)jean.fruitet@free.fr</p>");
}

$twd_radian = get_radian_repere_direct($twd_degre);

if ($debug || $debug1 || $debug2 || $debug3){
    $msg=sprintf("Site:%s TWD°:%d, TWD radian:%f<br>\n",$site,$twd_degre, $twd_radian);
    echo "<br />$msg\n";
    if ($debug) {
        file_put_contents("debug_test.txt", $msg);
    }        
}

// Charger les information de site
// Elles sont dans un fichier DATAPATH_INPUT."nomsite.json"

$filename_input=$site.".json";
if ($debug){
    echo ("Fichier Input: ".DATAPATH_INPUT.$filename_input."<br>\n");
    file_put_contents("debug_test.txt", $data, FILE_APPEND);    
}

if (file_exists(DATAPATH_INPUT.$filename_input)){
    if ($data=file_get_contents(DATAPATH_INPUT.$filename_input)){
        $dataObject=json_decode($data,false);
        if ($debug){
            file_put_contents("debug_test.txt", $data, FILE_APPEND);
        }        
        if ($debug){
            file_put_contents("debug_test.txt", $dataObject, FILE_APPEND);
        }
    }
} 
else {
    echo $reponse_not_ok;
    exit; 
}

/******************************************
 * Chargement des données en input 
 * ****************************************/
if (empty($dataObject)){
    echo $reponse_not_ok;
    exit; 
}
 
    // Zone des Concurrents 
      
    $zoneconc_lon=array();
    $zoneconc_lat=array();
    
    $zonenav_lon=array();
    $zonenav_lat=array();
    
    $balises_name=array();
    $balises_lon=array();
    $balises_lat=array();    

    foreach ($dataObject->geojsonZoneConcurrents->features[0]->geometry->coordinates as $key => list($lon,$lat)){
        $zoneconc_lon[$key]=$lon;
        $zoneconc_lat[$key]=$lat;
    }
    
    // Zone de navigation
    $zonenav_lon=array();
    $zonenav_lat=array();
    
    foreach ($dataObject->geojsonZoneNav->features[0]->geometry->coordinates[0] as $key => list($lon,$lat)){
        $zonenav_lon[$key]=$lon;
        $zonenav_lat[$key]=$lat;
    }

    // Bouées fixes
    $index=0;
    foreach ($dataObject->geojsonBalises->features as $feature){
        $balises_name[$index]=$feature->properties->name;
        $index++;
    }

    $index=0;
    foreach ($dataObject->geojsonBalises->features as $feature){
        $balises_lon[$index]=$feature->geometry->coordinates[0];
        $balises_lat[$index]=$feature->geometry->coordinates[1];
        $index++;
    }
    
if ($debug1){    
    echo "<br>Zone Concurrents<br>\n";    
    echo "<br>ZC_lon<br>\n";
    print_r($zoneconc_lon);
    echo "<br>zoneconc_lat<br>\n";
    print_r($zoneconc_lat);
    echo "<br>Zone Navigation<br>\n";
    echo "<br>zonenav_lon<br>\n";
    print_r($zonenav_lon);
    echo "<br>zonenav_lat<br>\n";
    print_r($zonenav_lat);

    echo "<br>balises fixes<br>\n";   
    echo "<br>balises_name<br>\n";
    print_r($balises_name);
               
    echo "<br>balises_lon<br>\n";
    print_r($balises_lon);
    echo "<br>balises_lat<br>\n";
    print_r($balises_lat);   
    echo "<br>\n";
}


/*******************************************************************
 * Transformation en coordonnées "écran" pour accélerer l'algorithme 
 * *****************************************************************/
init_ecran_ZN();
init_ecran_bouees_fixes();

if ($debug2){
    echo "<br>Polygone de navigation en coordonées écran<br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($poly_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
    foreach ($poly_yecran as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n<br>\n";

    echo "Ligne des concurrents<br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($ligne_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
    foreach ($ligne_yecran as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n";

}

/******************************************
 * Transformations 
 * ****************************************/
// Convertir les polygones de navigation et les lignes de déplacement des concurrents dans le repère dit "de saisie"

// Appliquer une transformation pour ramener la figure face au vent 
// La figure est tournée dans une direction apparente du vent de 0°
// Désormais il est facile de déterminer un alignement de bouées face au vent :
// Bouées en travers du vent : y=constante
// Bouées dans le sens du vent : x=constante
// Dog leg au vent : y=minimum
// Porte sous le vent : y=maximum
// Départ : minimum<y<maximum au plus proche du chemin des concurrent 
// Ecart entre les bouées de départ, d'arrivée, porte, dog leg : entre 10 m et 20m

rotation_ecran_ZN($twd_radian);
rotation_ecran_Balises($twd_radian);

if ($debug1){
    echo "<br><b>Coordonnées des Balises dans l'écran de saisie</b>\n";
    for ($index=0; $index<count($balises_xsaisie); $index++){
        echo "<br>Balise ".$index." Id:".$balisesEcran[$index]->id."  Nom: ".$balisesEcran[$index]->name." X:".$balises_xsaisie[$index]." Y:".$balises_ysaisie[$index]."\n";
    }
    echo "<br><br>\n";
}

if ($debug2){
    echo "<br>Polygone de navigation  APRES rotation <br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($poly_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
    foreach ($poly_ysaisie as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table><br>\n";

    echo "Ligne des concurrents   APRES rotation <br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($ligne_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
    foreach ($ligne_ysaisie as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n";
}
 
// Vérification
if ($debug2){
    echo "<br>VERIFICATION DES TRANSFORMATIONS<br>\n";
    echo "<br>Milieu (lon,lat) : ".$milieu_lon.", ".$milieu_lat."\n";
    
    // convertir en coordonnées écran
    $ecranX=get_Xecran_lon($milieu_lon);
    $ecranY=get_Yecran_lat($milieu_lat);
    echo "<br>Passage en coordonnées écran<br>Milieu (ecranX,ecranY) : ".$ecranX.", ".$ecranY."\n";
    // rotation
    $saisieX = setDisplayToSaisieX($ecranX,$ecranY, $twd_radian);
    $saisieY = setDisplayToSaisieY($ecranX,$ecranY, $twd_radian);    
    echo "<br>Passage en coordonnées de saisie<br>Milieu (saisieX,saisieY) : ".$saisieX.", ".$saisieY."\n";
    
    // revenir aux coordonnées écran
    $ecranX2=setSaisieToDisplayX($saisieX, $saisieY, $twd_radian);    
    $ecranY2=setSaisieToDisplayY($saisieX, $saisieY, $twd_radian);
    echo "<br>Retour en coordonnées écran<br>Milieu (saisieX,saisieY) : ".$ecranX2.", ".$ecranY2."\n";
    
    // repasser en coordonnées géographiques
    $milieu_lon2=get_lon_Xecran($ecranX2);
    $milieu_lat2=get_lat_Yecran($ecranY2);
    echo "<br>Retour en coordonnées géographiques<br>Milieu (lon,lat) : ".$milieu_lon2.", ".$milieu_lat2."\n";   

    
    $t_ecranX=array();
    $t_ecranY=array();
    for ($i=0; $i<count($poly_xsaisie); $i++){
        $t_ecranX[$i]=setSaisieToDisplayX($poly_xsaisie[$i], $poly_ysaisie[$i], $twd_radian);
        $t_ecranY[$i]=setSaisieToDisplayY($poly_xsaisie[$i], $poly_ysaisie[$i], $twd_radian);
    }
    echo "<br>Polygone de navigation  APRES rotation inverse <br>\n<table border=\"1\">\n<tr><th>X</th>\n";
    foreach ($t_ecranX as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr><th>Y</th>\n";
    foreach ($t_ecranY as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table><br>\n";


    echo "<br>Coordonnées géographiques initiales de la zone de navigation <br>\n<table border=\"1\">\n<tr><th>Lon</th>\n";
    foreach ($zonenav_lon as $x){
        echo "<td>".$x."</td>";
    }    
    echo "</tr><tr><th>Lat</th>\n";
    foreach ($zonenav_lat as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table><br>\n";
 
    echo "<br>Retour en coordonnées géographiques après transformation <br>\n<table border=\"1\">\n<tr><th>Lon</th>\n";
    foreach ($t_ecranX as $x){
        echo "<td>".get_lon_Xecran($x)."</td>";
    }
    echo "</tr><tr><th>Lat</th>\n";
    foreach ($t_ecranY as $y){
        echo "<td>".get_lat_Yecran($y)."</td>";
    }
    echo "</tr>\n</table><br>\n";
}    

if ($debug1){
    echo "<br>Données chargées avec succès. Transformations vérifiées. Début de l'algorithme calcul<br>\n";
}

      
/******************************************
 * Début de l'algorithme de positionnement
 * ****************************************/
 
// Déterminer la boîte englobante des coordonnées de saisie pour accélérer la recherche des rectangles de navigation 
$xminPoly=1000000; // en pixels
$xmaxPoly=-1000000; // en pixels
$yminPoly=1000000; // en pixels
$ymaxPoly=-1000000; // en pixels
    foreach ($poly_xsaisie as $x){
        if ($x<=$xminPoly){ $xminPoly=$x;}
        if ($x>$xmaxPoly){ $xmaxPoly=$x;}
    }
    foreach ($poly_ysaisie as $y){
        if ($y<$yminPoly){ $yminPoly=$y;}
        if ($y>$ymaxPoly){ $ymaxPoly=$y;}
    }

 
$distance_H_MillePixels=distanceHorizontalePixels(0,0,1000);
$distance_V_MillePixels=distanceVerticalePixels(0,0,1000); 

$deltaXpixelsDixMetres=howMuchXPixelsForMeters($ecartBoueesXmetres);
$deltaYpixelsCinquanteMetres=howMuchYPixelsForMeters($ecartBoueesYmetres);

$deltaXpixelsSite=min(round(abs($xmaxPoly-$xminPoly)/4.0),$deltaXpixelsDixMetres);
$deltaYpixelsSite=min(round(abs($ymaxPoly-$yminPoly)/3.0),$deltaYpixelsCinquanteMetres);

if ($debug2){
    echo "Distance horizontale pour 1000 \"pixels\": ".$distance_H_MillePixels."\n";
    echo "<br>Distance verticale pour 1000 \"pixels\": ".$distance_V_MillePixels."\n";
    echo "<br>Nombre de \"pixels\" pour une distance horizontale de 10 mètres: ".$deltaXpixelsDixMetres."\n";
    echo "<br>Nombre de \"pixels\" pour une distance verticale de 50 mètres: ".$deltaYpixelsCinquanteMetres."\n";
    echo "<br>Nombre de \"pixels\" entre les bouées de départ : ".$deltaXpixelsSite."\n";
    echo "<br>Nombre de \"pixels\" pour une distance verticale de 50 mètres: ".$deltaYpixelsCinquanteMetres."\n";
    echo "<br>Nombre de \"pixels\" entre la porte et le dog leg: ".$deltaYpixelsSite."\n";
    echo "<br>Boîte englobante du polygone : (Xmin,Ymin):(".$xminPoly.",".$yminPoly.") (Xmax, Ymax):(".$xmaxPoly.",".$ymaxPoly.")\n";
    echo "<br>Largeur : distance(Xmin,Xmax): ".abs($xminPoly-$xmaxPoly)." Hauteur : distance(Ymin,Ymax): ".abs($ymaxPoly-$yminPoly)."\n";
}

// Calcule la distance entre chaque sommet du polygone et la ligne des concurrents
calculeTableDistances(false);

if ($debug1){    
    echo "<br>Points d'intersection trouvés\n";
    for ($i=0;$i<count($tab_distances); $i++){
        echo ("<br>".$tab_distances[$i]);
    }
}

// Rechercher le sommet du polygone le plus proche de la zone concurrents
$distanceecranmin=1000000;
$sommetmin=0;
$coordonneesmin=array();
$intersectionmin=array();
$distanceterrainmin=1000000;

    for ($i=0;$i<count($tab_distances);$i++){
        $tab_d=json_decode($tab_distances[$i],false);
        $sommet_poly=$tab_d->sommet_poly;
        $coordonnees=$tab_d->coordonnees;
        $segment_ligne=$tab_d->segment_ligne;
        $intersection=$tab_d->intersection;
        $distanceecran=$tab_d->distanceecran;
        $distanceterrain=$tab_d->distanceterrain;       
        
        if ($distanceecran<$distanceecranmin){
            $distanceecranmin=$distanceecran;
            $sommetmin=$sommet_poly;
            $coordonneesmin=$coordonnees;
            $intersectionmin=$intersection;            
            $distanceterrainmin=$distanceterrain;
        }                     
    }

if ($debug1){    
    echo "<br><br><b>Distance minimale du polygone à la ligne</b><br>\n";
    echo ("Sommet du polygone ".$sommetmin.", (x0:".$coordonneesmin[0].", y0:".$coordonneesmin[1].")<br>Intersection avec la ligne : (Ix:".$intersectionmin[0].", Iy:".$intersectionmin[1]."),<br>Distance (pixels) ".$distanceecranmin.", Distance (m) ".$distanceterrainmin);
    echo "<br>\n";
}

// Balayer l'axe X par des droites x=constante pour déterminer les points d'intersection avec le polygone de navigation.
// Calculer la distance entre ces points d'intersection
// Si cette distance est supérieure au seuil enregistrer les points d'intersection.  

// Tracer des droites verticlae (x=cte)
// on va balayer tout le plan d'eau sans se préoccuper de la distance à la zone des concurrents
// <br>Boîte englobante du polygone : (Xmin,Ymin):(".$xminPoly.",".$yminPoly.") (Xmax, Ymax):(".$xmaxPoly.",".$ymaxPoly.")\n";

// Cette partie doit être améliorée

$x0=$xminPoly;
$sensprogression=1;
$incrementX=$sensprogression*INCREMENT; // Environ 1m vers l'Est ou vers l'Ouest
$xInitial=$xminPoly;
$xFinal=$xmaxPoly;
    
$encore=true;
$succes=false;
while ($encore && !$succes){
    // nouvelle recherche
    $succes= rechercher_rectangle_utile($incrementX, $xInitial, $xFinal, $sensprogression); 
    if (!$succes){
        $x0+=$sensprogression * GRAND_INCREMENT;
        if ($sensprogression==1){
            $encore= ($x0 <= $xFinal);
        }
        else{
            $encore= ($x0 >= $xFinal);
        }    
    }
}


if ($succes){    
    placer_bouees($xouest, $xest, $ysud, $ynord); // Attention à l'ordre


/******************************************
 * Sauvegarder les position
 * ****************************************/ 
    $data='{"site":"'.$nomSite.'","twd":'.$twd_degre.',"boueesfixes":[';
    //echo "<br>Bouées fixes retenues pour le parcours\n";
    if (!empty($boueesFixesParcours)){
        for ($index=0; $index<count($boueesFixesParcours)-1; $index++){        
            $data.=$boueesFixesParcours[$index].',';
        }
        $data.=$boueesFixesParcours[$index].']';
    }
    else{
        $data.=']';
    }
    //echo "<br>Bouées mobiles ajoutéesau parcours\n";
    $data.=',"boueesmobiles":[';
    if (!empty($boueesMobilesParcours)){
        for ($index=0; $index<count($boueesMobilesParcours)-1; $index++){        
            $data.=$boueesMobilesParcours[$index].',';
        }     
        $data.=$boueesMobilesParcours[$index].']';
    }
    else{
        $data.=']';
    }
    
    // Rectangle de placement des bouéés    
    // L'afficheur ./placerbouees/chargerbouees.html doit aussi être positionnée en mod debug dans le fichier de configuration ./js/config.js
    if ($debug3){
        if (!empty($exitLonLat)){
            $data.=',"rectangle":[';
            $i=0;
            while ($i<count($exitLonLat)-1){
                $data.='{"lon":'.$exitLonLat[$i]->lon.',"lat":'.$exitLonLat[$i]->lat.'},';
                $i++;      
            }
            
            $data.='{"lon":'.$exitLonLat[$i]->lon.',"lat":'.$exitLonLat[$i]->lat.'}';
            $data.=']';
        }    
    
        // Test : affichage du fantôme du parcours utilisé pour le placement des bouées

        if (!empty($poly_xsaisie) && !empty($poly_ysaisie)){
            $data.=',"fantome":[';
            $i=0;
            while ($i<count($poly_xsaisie)-1){
                $data.='{"lon":'.get_lon_Xecran($poly_xsaisie[$i]).',"lat":'.get_lat_Yecran($poly_ysaisie[$i]).'},';
                $i++;      
            }
            $data.='{"lon":'.get_lon_Xecran($poly_xsaisie[$i]).',"lat":'.get_lat_Yecran($poly_ysaisie[$i]).'}';
            $data.=']';
        }
    }
    
    $data.='}';
    
    //echo "<br>Data<br>\n";
    //echo $data;
    
    $filename_output="robonav_".$nomSite2."_".$twd_degre."_".date("Ymd")."_auto.json";
    if ($handle = fopen(DATAPATH_OUTPUT.$filename_output, "w")){
        fwrite($handle, $data);
        fclose($handle);
    }
    echo $reponse_ok;
}   
else {
    echo $reponse_not_ok;
}


if ($debug || $debug1 || $debug2 || $debug3){
        echo ("</body></head></html>");
}


?>
