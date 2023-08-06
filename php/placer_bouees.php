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
$file = ''; 
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
        $site=$_GET['site']));
    }
}

   
if ($debug && !empty($site)){
    file_put_contents("debug_test.txt", "Site:".$site." TWD:".$twd_degré."\n");
}



// Le calcul commence
/*
B° → A radian : A = (PI / 180 * (270 - B)) MODULO 2PI
*/

$twd_radian = (M_PI / 180.0) * ((450 - $twd_degre) % 360);

if ($debug){
    $msg=sprintf("\nTWD°:%d, TWD radian:%f\n",$twd_degre, $twd_radian);
    echo "<br />$msg\n";
    file_put_contents("debug_test.txt", $msg, FILE_APPEND);
}

// Charger les information de site
// Elles sont dans un fichier DATAPATH_INPUT."nomsite.json"



?>
