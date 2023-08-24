<?php
// Placement automatique de bouées de régate sur un site pour toutes les directions de la rose des vent
// Ce code est assez peu efficient !
// Lecture de la liste des plans d'eau disponibles pour la régate radiocommandée 
// Utilise simpleXml

include ("./include/config.php");
include ("./include/saisie.php");

$debug=false;
$nomSite='';
$site='';
$file = "plans_eau_robonav.xml"; 
$data=array();


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
    

    if (!empty($_POST['site'])){
        $nomSite=urldecode($_POST['site']);
        $nomSite2=str_replace(' ','',$nomSite);
        $site=strtolower(str_replace("'",'',$nomSite2));               
    }
    


entete();

if (!empty($nomSite)){
    echo "<br>Site recherché ".$nomSite;  
    echo "<br>Fichier à traiter ".$site;
    echo "\n";
}

get_plans_eau($debug);

    if (!empty($data)){

    echo '<p>Sites</p> 
    <form action-xhr="'.htmlspecialchars($uri.$_SERVER["PHP_SELF"]).'"
                   target=="_top"
                    method="POST">
      <select id="site" name="site">';
      
    for ($index=0; $index<count($data['site']); $index++){
        echo '<option value = "'.$data['site'][$index]['name'].'"';
        if ($nomSite==$data['site'][$index]['name']){            
            echo ' selected';
        }
        echo '>'.$data['site'][$index]['name'].'</option>'."\n";
    }        
    echo '</select>
      <input type = "submit" value = "confirmer">
    </form>
';
}    

traitement();

enqueue();

// -------------------------
function traitement(){
    // Appelle placer_bouees.php
    global $site;
    if (!empty($site)){
        echo "<br><b>Traitement de</b> <i>".$site."</i>\n";
        
    }        
}

//--------------------------
function  get_plans_eau($debug){
    global $file;
    global $data;  
    global $nomSite;

    if (file_exists(DATAPATH_INPUT.$file)){
        // afficher_selectionner($data);
        $data=xml2array(DATAPATH_INPUT.$file, $arr = array());
        if ($debug){
        for ($index=0; $index<count($data['site']); $index++){
            if (!empty($nomSite) && $nomSite==$data['site'][$index]['name']){            
                echo "<br><b>Site trouvé</b> <br>".'{"id":'.$data['site'][$index]['id'].',"nom":"'.$data['site'][$index]['name'].'","ville":"'.$data['site'][$index]['city'].'","zipcode":"'.$data['site'][$index]['zipcode'].'","pays":"'.$data['site'][$index]['country'].'","lon":'.$data['site'][$index]['lon'].',"lat":'.$data['site'][$index]['lat'].',"club":"'.$data['site'][$index]['club'].'","url":"'.$data['site'][$index]['url'].'","jsonfile":"'.$data['site'][$index]['json'].'"}'."\n";                        
            }
            else{
                echo "<br>".'{"id":'.$data['site'][$index]['id'].',"nom":"'.$data['site'][$index]['name'].'","ville":"'.$data['site'][$index]['city'].'","zipcode":"'.$data['site'][$index]['zipcode'].'","pays":"'.$data['site'][$index]['country'].'","lon":'.$data['site'][$index]['lon'].',"lat":'.$data['site'][$index]['lat'].',"club":"'.$data['site'][$index]['club'].'","url":"'.$data['site'][$index]['url'].'","jsonfile":"'.$data['site'][$index]['json'].'"}'."\n";                                
            }
        }
        }        
    }
}


//----------------------------------
function xml2array($element, $arr = array()){
// https://www.php.net/manual/fr/example.xml-structure.php
    if (is_string($element))
    {
        $element = (strlen($element) > 5 && substr($element, -4) === '.xml') 
            ? simplexml_load_file(DATAPATH_INPUT.$element)
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

// ----------------------------
function entete(){
echo '<!DOCTYPE html>
<html lang="fr">
  <meta charset="UTF-8">  
  <meta name="keywords" content="HTML, CSS, JavaScript">
  <meta name="description" content="JF`s Autonomous Buoys Placement">
  <meta name="author" content="Jean Fruitet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.25, target-densitydpi=device-dpi">
  <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
  <title>JF`s Autonomous Buoys Placement</title>
  <!-- Feuilles de style -->
<link rel="stylesheet" href="../css/style.css"> 
</head>
<body>
<h2>Placement automatique de bouées de régate radiocommandée</h2>
';
}

// -------------------------
function enqueue(){
    echo '</body>
</head>';
}

