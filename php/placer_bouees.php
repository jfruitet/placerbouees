<?php
// Calcul du placement de bouées de régate en fonction de la direction du vent
// Ce code est tout à fait inachevé...
// A suivre.

define("DATAPATH_INPUT", "../json/"); // Les données seront lues dans ce dossier.
define("DATAPATH_OUTPUT", "../data/"); // Les données seront sauvegardées dans ce dossier.

$debug = true;
$twd_degre=0;
$twd_radian=0.0;
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
        //print_r($dataObject);
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
    $ZC_lon=array();
    $ZC_lat=array();

    foreach ($dataObject->geojsonZoneConcurrents->features[0]->geometry->coordinates as $key => list($lon,$lat)){
        $ZC_lon[$key]=$lon;
        $ZC_lat[$key]=$lat;
    }
    
    // Zone de navigation
    $ZN_lon=array();
    $ZN_lat=array();
    
    foreach ($dataObject->geojsonZoneNav->features[0]->geometry->coordinates[0] as $key => list($lon,$lat)){
        $ZN_lon[$key]=$lon;
        $ZN_lat[$key]=$lat;
    }

    // Bouées fixes
    
    $Balises_name=array();
    $Balises_lon=array();
    $Balises_lat=array();
    $index=0;
    foreach ($dataObject->geojsonBalises->features as $feature){
        $Balises_name[$index]=$feature->properties->name;
        $index++;
    }

    $index=0;
    foreach ($dataObject->geojsonBalises->features as $feature){
        $Balises_lon[$index]=$feature->geometry->coordinates[0];
        $Balises_lat[$index]=$feature->geometry->coordinates[1];
        $index++;
    }
    
    if ($debug){    
    echo "<br>Zone Concurrents<br>\n";    
    echo "<br>ZC_lon<br>\n";
    print_r($ZC_lon);
    echo "<br>ZC_lat<br>\n";
    print_r($ZC_lat);
    echo "<br>Zone Navigation<br>\n";
    echo "<br>ZN_lon<br>\n";
    print_r($ZN_lon);
    echo "<br>ZN_lat<br>\n";
    print_r($ZN_lat);

    echo "<br>Balises fixes<br>\n";   
    echo "<br>Balises_name<br>\n";
    print_r($Balises_name);
               
    echo "<br>Balises_lon<br>\n";
    print_r($Balises_lon);
    echo "<br>Balises_lat<br>\n";
    print_r($Balises_lat);
    }    
}

/******************************************
 * Début de l'algorithme de positionnement
 * ****************************************/
 
 
 

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
