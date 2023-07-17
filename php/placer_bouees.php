<?php
// Calcul du placement de bouées de régate en fonction de la direction du vent
$debug=true;
$twd_degre=0;
$twd_radian=0.0;
$data=null;

if (isset($_GET) && !empty($_GET)){
    if ($debug){
        print_r($_GET);  
    }
    if (isset($_GET['twd'])){
        $twd_degre=$_GET['twd'];
    }
}
else if (isset($_POST) && !empty($_POST)){
    $data = $_POST;  
}
else {
    // Read the input stream
    $data = file_get_contents("php://input");
}
    
if ($debug && !empty($data)){
    print_r($data);
    file_put_contents("debug_test.txt", $data);
    if (isset($data['twd'])){
        $twd_degre=$data['twd'];
    }
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

?>
