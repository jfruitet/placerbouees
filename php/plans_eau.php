<?php
// Lecture de la liste des plans d'eau disponibles pour la régate radiocommandée 
// Utilise simpleXml

define("DATAPATH", "../data/");

$debug = true;
$reponse_ok = array("ok"=>1);
$reponse_not_ok = array("ok"=>0);
$all=1;
$id=0;
$file = "plans_eau_robonav.xml"; 


// Get the JSON contents
if (isset($_SERVER['REQUEST_METHOD']) && (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')) {
  throw new Exception('Only GET requests are allowed');
}
if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false)) {
  throw new Exception('Content-Type must be application/json');
}
if (!empty($_GET['all'])) {
    $all = $_GET['all'];  
}

if (!empty($_GET['id'])) {
    $id = $_GET['id'];  
}

if ($id>0)
{
    // Charger le fichier pour récupérer l'info demandée
    //$reponse = json_encode(get_plans_eau($id));
    $reponse = (get_plans_eau($id));
}
else if ($all != 0){
    // Afficher la liste des sites disponibles
    $reponse = json_encode(get_all_plans_eau());
}
else{
    $reponse = json_encode(array("Usage"=>"./php/plans_eau.php?all=[0|1]&id=[numéro du plan d'eau]","ok"=>0)); // Chasser la première accolade
}
echo $reponse;

 
 /*
	<site>
		<id>1</id>
		<name>Le Plessis</name>
		<city>Sainte-Luce-sur-Loire</city>
		<zipcode>44980</zipcode>
		<country>France</country>
		<lon>-1.47402</lon>
		<lat>47.24338</lat>
		<club>ARBL</club>
		<url>https://www.arbl.fr</url>
		<json>leplessis.json</json>
	</site>
 */
//--------------------------
function  get_plans_eau($index=0){
    global $file;
    global $data;    
    if (($index>0) && file_exists(DATAPATH.$file)){
        $index--; // index = id -1 
        // afficher_selectionner($data);
        if ($data=xml2array(DATAPATH.$file, $arr = array())){
            return '{"id":'.$data['site'][$index]['id'].',"nom":"'.$data['site'][$index]['name'].'","ville":"'.$data['site'][$index]['city'].'","zipcode":"'.$data['site'][$index]['zipcode'].'","pays":"'.$data['site'][$index]['country'].'","lon":'.$data['site'][$index]['lon'].',"lat":'.$data['site'][$index]['lat'].',"jsonfile":"'.$data['site'][$index]['json'].'"}';            
        }
    } 
}


//-----------------------------------
function get_all_plans_eau(){
    global $file;
    global $data;    

    if (file_exists(DATAPATH.$file)){
        // afficher_selectionner($data);
        return xml2array(DATAPATH.$file, $arr = array());
    } 
}

//----------------------------------
function xml2array($element, $arr = array()){
// https://www.php.net/manual/fr/example.xml-structure.php
    if (is_string($element))
    {
        $element = (strlen($element) > 5 && substr($element, -4) === '.xml') 
            ? simplexml_load_file(DATAPATH.$element)
            : simplexml_load_string($element);
    }
    $iter = 0;
    foreach($element->children() as $b)
    {
        $a = $b->getName();
        if (!$b->children()){
            $arr[$a] = trim($b[0]);
        }
        else{
            $arr[$a][$iter] = array();
            $arr[$a][$iter] = xml2array($b,$arr[$a][$iter]);
        }
        $iter++;
    }
    return $arr;
}

//----------------------------------------
function  afficher_selectionner($data){
    if (!empty($data)){
        echo("<html>
<!DOCTYPE html>
<html lang=\"fr\">\n<meta charset=\"UTF-8\">  
<head>
</head>
<body>
<h3>Début du traitement</h3>
<pre>");       
        for ($i=0; $i<count($data['site']); $i++){
            echo '"site":"{Site N°"'.$data['site'][$i]['id'].", ".$data['site'][$i]['name'].", ".$data['site'][$i]['city'].", ".$data['site'][$i]['zipcode'].", <i>".$data['site'][$i]['json'].'"</i>"},'."\n";
        }
        echo("</pre>\nFin du traitement\n</body>\n</html>");   
    }
}


?>


