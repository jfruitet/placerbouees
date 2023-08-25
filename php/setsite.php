<?php
// Placement automatique de bouées de régate sur un site pour toutes les directions de la rose des vent
// Ce code est assez peu efficient !


include ("./include/config.php");
include ("./include/saisie.php");
include ("./include/geo_utils.php");
include ("./include/initial.php");
include ("./include/algo.php");

$debug = true;
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
$twd_radian=0.0;
$twd_degre=90;

// Coordonnées relatives du rectangle où placer les bouées
$xouest=0;
$xest=$canvasw;
$ysud=$canvash;
$ynord=0; 



$nomSite='';
$nomSite2='';
$site='';
$data=array();
// Pour la rose des vents
// $windsector=array("N","NNE","NE","ENE","E","ESE","SE","SSE","S","SSW","SW","WSW","W","WNW","NW","NNW","N");
// 
$reponse=""; // response


// Get the JSON contents
if (isset($_SERVER['REQUEST_METHOD']) && (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')) {
  throw new Exception('Only GET requests are allowed');
}
if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false)) {
  throw new Exception('Content-Type must be application/json');
}
        

if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
    $uri = 'https://';
} else {
	$uri = 'http://';
}
$uri .= $_SERVER['HTTP_HOST'];
    
if (!empty($_GET['site'])){
    $nomSite=urldecode($_GET['site']);
    $nomSite2=str_replace(' ','',$nomSite);
    $site=strtolower(str_replace("'",'',$nomSite2));
}


$twd_degre=0; 
while ($twd_degre<360){
    
    $twd_radian = get_radian_repere_direct($twd_degre);
    // Charger les information de site
    // Elles sont dans un fichier DATAPATH_INPUT."nomsite.json"

    $filename_input=$site.".json";

    if (file_exists(DATAPATH_INPUT.$filename_input)){
        if ($data=file_get_contents(DATAPATH_INPUT.$filename_input)){
            $dataObject=json_decode($data,false);
        }
    } 

    /******************************************
    * Chargement des données en input 
    * ****************************************/
    if (!empty($dataObject)){
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
            else {
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
            $reponse.=$twd_degre.',';
        }
    }
    $twd_degre+=22.5;
}
if ( !empty($reponse)){
    $reponse=substr($reponse, 0, strlen($reponse)-1);     // Retirer la dernière virgule 
    $reponse='{"site":"'.$nomSite2.'","twd":['.$reponse.'],"ok":1}';
}    
else{
    $reponse='{"site":"'.$nomSite2.'","twd":[],"ok":0}';
}   
echo $reponse;
 
?>