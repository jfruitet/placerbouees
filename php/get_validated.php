<?php
// Retourne la liste des placements validés

// $prefixe : élément des noms de fichiers à accepter
// $suffixe : élément du nom de fihier permettant le tri
// par exemple "robonav"


$str1='';
$str2='';

// ------------------
function afficheDossier($prefixe='', $suffixe='', $extension='json'){
$nobj1=0;
$nobj2=0;

$ndir=0;
$tf1=array();    // Liste des fichiers acceptés contenant le suffixe
$tf2=array();    // Liste des fichiers acceptés ne contenant pas le suffixe
$t_ext=array(); // liste d'extensions de fichiers
$str='';

global $str1;
global $str2;

 	$t_ext=explode(',',$extension);

// DEBUG
//echo "<br>Extensions :\n";
//print_r($t_ext);
//exit;
	$tf=array();
	$sep = '/';
	$path="../data/";

	$h1=opendir($path);
    while ($f = readdir($h1) )
    {
		if (($f != ".") && ($f != "..")) {
			// Les fichiers commençant par '_' ne sont pas affichés
			// Ni le fichier par defaut ni le fichier de cache ne sont affichés
			// Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
			// les fichier n'ayant pas la bonne extension ne sont pas affichés
	        if (!is_dir($path.$sep.$f)){
                if ((!empty($prefixe) && (substr($f,0,3) == substr($prefixe,0,3)))
					 // Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
					|| empty($prefixe)) {
					foreach(  $t_ext as $ext){
						if (strpos($f, $ext) !== false){
                        	//$g= mb_eregi_replace($ext,"",$f) ;
                            $g= preg_replace('/'.$ext.'/i',"",$f) ;
							// DEBUG
							// echo "<br>g:$g  g+:$g$ext  f:$f\n ";
        			  		if ((substr($g,0,1) != "_") // Les fichiers commençant par '_' ne sont pas affichés
								&&
								(strtoupper($g.$ext) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
								)
							{
			            	   	if (preg_match("/".$suffixe."/", $f)){
                                    $nobj1++;
	               				    $tf1[$f] = $f;
                                }
                                else{
                                    $nobj2++;
	               				    $tf2[$f] = $f;                                
                                }
							}
						}
					}
				} // fin traitement d'un fichier
			} // fin du test sur entrees speciales . et ..
		}  // fin du while sur les entrees du repertoire traite
	}
	closedir($h1);

    $str2='';
	if ($nobj2 > 0) {
	    asort($tf2);
	    $i=0;
        foreach ($tf2 as $key => $value){
            $str2.= '{"id":'.$i.',"file":"'.$value.'"},';
	       	$i++;
    	}
	}
    
    $str1='';
	if ($nobj1 > 0) {
	    asort($tf1);
	    $i=0;
        foreach ($tf1 as $key => $value){
            $str1.= '{"id":'.$i.',"file":"'.$value.'"},';
	       	$i++;
    	}
	}
}

afficheDossier('robonav','auto','json');
$str1=substr($str1, 0, strlen($str1)-1); // Chasser la dernière virgule
$str2=substr($str2, 0, strlen($str2)-1);
echo '{"valide":['.$str2.']}';
?>

