<?php
// Placement automatique de bouées de régate sur un site en fonction de la direction du vent
// Ce code est pratiquement complet... et assez peu efficient !

include ("./include/config.php");
include ("./include/saisie.php");
include ("./include/geo_utils.php");
include ("./include/initial.php");
include ("./include/algo.php");

$debug = false;
$debug1 = true;
$debug2 = true;
$debug3 = true;

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
$twd_radian=0.0;
$twd_degre=90;

// Coordonnées relatives du rectangle où placer les bouées
$xouest=0;
$xest=$canvasw;
$ysud=$canvash;
$ynord=0; 


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
 
$succes=traitement_initial($dataObject, $twd_radian);

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
