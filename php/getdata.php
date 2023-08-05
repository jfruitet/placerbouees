<?php
// Lecture de la liste des plans d'eau disponibles pour la régate radiocommandée 
// Utilise simpleXml

define("DATAPATH", "../data/");

$debug = true;
$reponse_ok = '{"ok":1}';
$reponse_not_ok = '{"ok":0}';
$reponse=$reponse_not_ok;

// Get the JSON contents
if (isset($_SERVER['REQUEST_METHOD']) && (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')) {
  throw new Exception('Only GET requests are allowed');
}
if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false)) {
  throw new Exception('Content-Type must be application/json');
}

if (!empty($_GET['expression'])){
    if (!empty($_GET['nottwd'])){
        echo(getdirNotWind($_GET['expression'],$_GET['nottwd']));
    }
    else{
        echo(getdir($_GET['expression']));
    }
}
else if (!empty($_GET['file'])) {
    echo(getjson($_GET['file']));
}
else{
    echo $reponse_not_ok;
}


//--------------------------
function getjson($file){ 
global $reponse_not_ok;
    if (file_exists(DATAPATH.$file)){
        if ($data=file_get_contents(DATAPATH.$file)){
            return $data;
        }
    } 
    return $reponse_not_ok;
}

//--------------------------
function getdir($expression){
// Il ne faut matcher les extensions des fihciers .json
global $reponse_not_ok;
    $dossier = opendir(DATAPATH);
    if (empty($dossier)){
        return $reponse_not_ok;
    }
    $str1='{"fichiers":[';
    $str="";
    while($fichier = readdir($dossier))
    {
        if (($fichier != '.') && ($fichier != '..') && (mb_substr($fichier,mb_strlen($fichier)-5) == ".json"))
        {
           if (preg_match('/'.$expression.'/',$fichier)){
                $str.= '"'.$fichier.'",';
           }            
        }
    }
    closedir($dossier);
    if (!empty($str)){
        $str= substr($str,0,strlen($str)-1); // Chasser la dernière virgule
        $str1.=$str.'],"ok":1}';
        return $str1;
    }
    else{
        return $reponse_not_ok;
    }
}

//--------------------------
function getdirNotWind($expression, $nottwd){
// Il ne faut matcher les fichier ne contenant pas twd avec l'extension .json
global $reponse_not_ok;
    $dossier = opendir(DATAPATH);
    if (empty($dossier)){
        return $reponse_not_ok;
    }
    $str1='{"fichiers":[';
    $str="";
    while($fichier = readdir($dossier))
    {
        if (($fichier != '.') && ($fichier != '..') && (mb_substr($fichier,mb_strlen($fichier)-5) == ".json"))
        {
           if (preg_match('/'.$expression.'/',$fichier) && !preg_match('/'.$nottwd.'/',$fichier) ){
                $str.= '"'.$fichier.'",';
           }            
        }
    }
    closedir($dossier);
    if (!empty($str)){
        $str= substr($str,0,strlen($str)-1); // Chasser la dernière virgule
        $str1.=$str.'],"ok":1}';
        return $str1;
    }
    else{
        return $reponse_not_ok;
    }
}

?>


