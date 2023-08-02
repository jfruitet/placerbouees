<?php
// enregistre un fichier JSON de placement de bouées 

$debug = true;
$mydata = new stdClass();
$reponse_ok = array("ok"=>1);
$reponse_not_ok = array("ok"=>0);
$data = null;

// Get the JSON contents
if (isset($_SERVER['REQUEST_METHOD']) && (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')) {
  throw new Exception('Only POST requests are allowed');
}
if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false)) {
  throw new Exception('Content-Type must be application/json');
}

if (isset($_POST) && !empty($_POST)) {
    $data = $_POST;  
}
else {
    // Read the input stream
    $data = file_get_contents("php://input");
    
    if ($debug){
        file_put_contents("debug_test.txt", $data."\n");
    }
}

if (isset($data) && (!empty($data)))
{
    $mydata = json_decode($data,true);
    //print_r($mydata);
    $filename="placementbouees_".$mydata['twd']."_".date("Ymd").".json";
    if ($handle = fopen("../data/".$filename, "w")){
        fwrite($handle, $data);
        fclose($handle);
    }
     
    // return value
    $reponse = json_encode($reponse_ok); // Chasser la première accolade
    echo $reponse; 
}
else {
    echo json_encode($reponse_not_ok);
}
?>


