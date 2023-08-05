<?php
// Lecture de la liste des plans d'eau disponibles pour la régate radiocommandée 
// Utilise simpleXml

define("DATAPATH", "../json/");

$debug = true;
$reponse_ok = '{"ok":1}';
$reponse_not_ok = '{"ok":0}';
$file = ""; 
$reponse=$reponse_not_ok;

// Get the JSON contents
if (isset($_SERVER['REQUEST_METHOD']) && (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')) {
  throw new Exception('Only GET requests are allowed');
}
if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false)) {
  throw new Exception('Content-Type must be application/json');
}
if (!empty($_GET['file'])) {
    $file = $_GET['file'];  
}

if (!empty($file))
{
    // Charger le fichier pour récupérer l'info demandée
    //$reponse = json_encode(getjson($file));
    $reponse = getjson($file);
}
echo $reponse;

//--------------------------
function getjson($file){ 
    if (file_exists(DATAPATH.$file)){
        if ($data=file_get_contents(DATAPATH.$file)){
            return $data;
        }
    } 
    return $reponse_not_ok;
}


?>


