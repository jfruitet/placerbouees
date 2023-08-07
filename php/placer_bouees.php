<?php
// Calcul du placement de bouées de régate en fonction de la direction du vent
// Ce code est tout à fait inachevé...
// A suivre.

define("DATAPATH_INPUT", "../json/"); // Les données seront lues dans ce dossier.
define("DATAPATH_OUTPUT", "../data/"); // Les données seront sauvegardées dans ce dossier.

include ("./include/geo_utils.php");

$debug = false;
$debug1 = true;

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
echo ("<html><head></head><body>");
$twd_radian = (M_PI / 180.0) * ((450 - $twd_degre) % 360);

if ($debug){
    $msg=sprintf("<br>Site:%s TWD°:%d, TWD radian:%f<br>\n",$site,$twd_degre, $twd_radian);
    echo "<br />$msg\n";
    // file_put_contents("debug_test.txt", $msg, FILE_APPEND);
}

// Charger les information de site
// Elles sont dans un fichier DATAPATH_INPUT."nomsite.json"

$filename_input=$site.".json";
if (file_exists(DATAPATH_INPUT.$filename_input)){
    if ($data=file_get_contents(DATAPATH_INPUT.$filename_input)){
        if ($debug){
            file_put_contents("debug_test.txt", $data, FILE_APPEND);
        }
        $dataObject=json_decode($data,false);
        if ($debug){
            print_r($dataObject);
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
    
    if ($debug){    
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
}


/******************************************
 * Début de l'algorithme de positionnement
 * ****************************************/
// Initialiser les dimensions hors tout du plan d'eau
// Convertir les polygones de navigation et les lignes de déplacement des concurrents dans le repère écran
init_ecran_ZN();


if ($debug){
    echo "Polygone de navigation<br>\n<table>\n<tr>\n";
    foreach ($poly_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($poly_yecran as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n";
}

if ($debug){
    echo "Ligne des concurrents<br>\n<table>\n<tr>\n";
    foreach ($ligne_xecran as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($ligne_yecran as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n";
}

// Appliquer une transformation pour se ramener face au vent
 
rotation_ecran_ZN($twd_radian);
if ($debug1){
    echo "Polygone de navigation  APRES rotation <br>\n<table>\n<tr>\n";
    foreach ($poly_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($poly_ysaisie as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n";

    echo "Ligne des concurrents   APRES rotation <br>\n<table>\n<tr>\n";
    foreach ($ligne_xsaisie as $x){
        echo "<td>".$x."</td>";
    }
    echo "</tr><tr>\n";
    foreach ($ligne_ysaisie as $y){
        echo "<td>".$y."</td>";
    }
    echo "</tr>\n</table>\n";
}
 

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


echo ("</body></head></html>");

?>
