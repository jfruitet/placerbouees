<?php
// Affichage de slide élémentaire en PHP - JF
// A Placer sous le nom index.php dans un dossier d'images

// $prefixe : élément des noms de fichiers à accepter
// $suffixe : élément du nom de fihier permettant le tri
// par exemple "robonav"

/*
 mb_eregi_replace(
    string $pattern,
    string $replacement,
    string $string,
    ?string $options = null
): string|false|null
*/

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
	$path="./";

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
                        	$g= mb_eregi_replace($ext,"",$f) ;
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
            $str= '<td>'.$i.'</td><td><a href="'.$key.'">'.$value."</a></td>";
            if ($i%2==0){
                $str2.= '<tr>'.$str;
            }
            else{
                $str2.=$str.'</tr>'."\n";
            }
	       	$i++;
    	}
	}
    
    $str1='';
	if ($nobj1 > 0) {
	    asort($tf1);
	    $i=0;
        foreach ($tf1 as $key => $value){
            $str= '<td>'.$i.'</td><td><a href="'.$key.'">'.$value."</a></td>";
            if ($i%2==0){
                $str1.= '<tr>'.$str;
            }
            else{
                $str1.= $str.'</tr>'."\n";
            }
	       	$i++;
    	}
	}
    
}


afficheDossier('robonav','auto','json');

?>
<!DOCTYPE html>
<html lang="fr">
  <meta charset="UTF-8">  
  <meta name="keywords" content="HTML, CSS, JavaScript">
  <meta name="description" content="RoBoNav`s Home">
  <meta name="author" content="Jean Fruitet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.25, target-densitydpi=device-dpi">
  <!-- base href="http://localhost/robonav/" target="_blank" -->
  <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
  <title>RoBoNav`s Data</title>
  <!-- Feuilles de style -->
<link rel="stylesheet" href="../css/style.css"> 
</head>
<body>
<h3>Fichiers de positionnement disponibles</h3>
<a href="../">Retour</a>
<h4>Fichiers corrigés manuellement</h4>
<table><tr><th>#</th><th>Fichier</th><th>#</th><th>Fichier</th></tr>
<?php
echo $str2;
?>
</table>
<h4>Fichiers générés de façon automatique</h4>
<table><tr><th>#</th><th>Fichier</th><th>#</th><th>Fichier</th></tr>
<?php
echo $str1;
?>
</table>
<p>
<a name="credits"></a>
<p align="center"><b>Crédits</b><br><span class="small">Sites de navigation, intégration javascript et PHP : JF
<br>
Ces données sont fournies &quot;en l'état&quot;.
Leur utilisation n'engage pas auteurs du programme.</span>
<br>
Contacts <a href="mailto:robonav@free.fr?Subject=RoBoNav, Data"><span class="small">RoBoNav</span></a>
</p>

</body>
</html>
