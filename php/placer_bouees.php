<?php
// Calcul du placement de bouées de régate en fonction de la direction du vent
// Ce code est tout à fait inachevé...
// A suivre.

define("DATAPATH_INPUT", "../json/"); // Les données seront lues dans ce dossier.
define("DATAPATH_OUTPUT", "../data/"); // Les données seront sauvegardées dans ce dossier.

include ("./include/geo_utils.php");
include ("./include/algo.php");

$debug = false;
$debug1 = false;

$site='';
$reponse_ok = '{"ok":1}';
$reponse_not_ok = '{"ok":0}';
$data=null;
$reponse=$reponse_not_ok;


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
        $site=strtolower(str_replace(' ','',urldecode($_GET['site'])));
    }
}


// Le calcul commence
/*
B° → A radian : A = (PI / 180 * (270 - B)) MODULO 2PI
*/
if ($debug || $debug1){
    echo ("<html><head></head><body><h3>Placer_bouees.php</h3><p>Placement automatique des bouées de régate mobiles<br>(cc)jean.fruitet@free.fr</p>");
}
$twd_radian = (M_PI / 180.0) * ((450 - $twd_degre) % 360);

if ($debug){
    $msg=sprintf("Site:%s TWD°:%d, TWD radian:%f<br>\n",$site,$twd_degre, $twd_radian);
    echo "<br />$msg\n";
    file_put_contents("debug_test.txt", $msg);
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

/******************************************
 * Chargement des données en input 
 * ****************************************/
if (!empty($dataObject)){
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
    }    
    echo "<br>\n";
}


/*******************************************************************
 * Transformation en coordonnées "écran" pour accélerer l'algorithme 
 * *****************************************************************/
init_ecran_ZN();

if ($debug1){
    echo "<br>Polygone de navigation<br>\n<table border=\"1\">\n<tr>\n";
    foreach ($poly_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($poly_yecran as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n<br>\n";

    echo "Ligne des concurrents<br>\n<table border=\"1\">\n<tr>\n";
    foreach ($ligne_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
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

if ($debug1){
    echo "<br>Polygone de navigation  APRES rotation <br>\n<table border=\"1\">\n<tr>\n";
    foreach ($poly_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($poly_ysaisie as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table><br>\n";

    echo "Ligne des concurrents   APRES rotation <br>\n<table border=\"1\">\n<tr>\n";
    foreach ($ligne_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($ligne_ysaisie as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n";
}
 
// Vérification
if ($debug1){
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
}    

if ($debug1){
    echo "<br>Données chargées avec succès. Transformations vérifiées. Début de l'algorithme calcul<br>\n";
}

   
   
/******************************************
 * Début de l'algorithme de positionnement
 * ****************************************/
 calcule_rectangle_bouees($debug||$debug1);
 
/******************************************
 * Sauvegarder les position
 * ****************************************/ 


// Sauvegarder les information de site
// Elles sont placer dans un fichier DATAPATH_OUTPUT.

/*    
$filename_output="robonav_".$site."_".$twd_deg."_".date("Ymd").".json";
if ($handle = fopen(DATAPATH.$filename, "w")){
    fwrite($handle, $data);
    fclose($handle);
}

*/

if ($debug || $debug1){
echo ("</body></head></html>");
}
?>
