<?php
// Algorithme de placement

// Appliquer une transformation pour ramener la figure face au vent 
// La figure est tournée dans une direction apparente du vent de 0°
// Désormais il est facile de déterminer un alignement de bouées face au vent :
// Bouées en travers du vent : y=constante
// Bouées dans le sens du vent : x=constante
// Dog leg au vent : y=minimum
// Porte sous le vent : y=maximum
// Départ : minimum<y<maximum au plus proche du chemin des concurrent 
// Ecart entre les bouées de départ, d'arrivée, porte, dog leg : entre 10 m et 20m

// Déterminer un rectangle vertical (face au vent apparent venant du nord) 
// inclus dans le plan d'eau au plus près de la polyligne d'évolution des concurrents

/*********************************************
 * 
 * Points d'intersection trouvés
 * $tab_distances
{"sommet_poly":0, "coordonnees":[38844,-28276], "segment_ligne":[0,1], "intersection":[48846,-28276], "distanceecran":10002, "distanceterrain":10.51}
{"sommet_poly":6, "coordonnees":[38844,-28276], "segment_ligne":[0,1], "intersection":[48846,-28276], "distanceecran":10002, "distanceterrain":10.51}
{"sommet_poly":1, "coordonnees":[-65038,12649], "segment_ligne":[1,2], "intersection":[42391,12649], "distanceecran":107429, "distanceterrain":112.91}
{"sommet_poly":5, "coordonnees":[38353,-3438], "segment_ligne":[1,2], "intersection":[45975,-3438], "distanceecran":7622, "distanceterrain":8.01}
{"sommet_poly":2, "coordonnees":[-55068,25942], "segment_ligne":[2,3], "intersection":[38794,25942], "distanceecran":93862, "distanceterrain":98.65}
{"sommet_poly":3, "coordonnees":[-20332,39716], "segment_ligne":[3,4], "intersection":[33952,39716], "distanceecran":54284, "distanceterrain":57.05}
{"sommet_poly":4, "coordonnees":[24195,31929], "segment_ligne":[3,4], "intersection":[36936,31929], "distanceecran":12741, "distanceterrain":13.39}
**********************************************/
$tab_distances=null;

// Calcule la distance entre chaque sommet du polygone et la ligne des concurrents
// ------------------------------------
function calculeTableDistances($debug){
global $tab_distances;
$tab_distances=array();
global $poly_xsaisie; // Tableau des coordonnées écran de la zone de navigation après rotation face au vent
global $poly_ysaisie;
global $ligne_xsaisie; // Tableau des coordonnées écran de la ligne de déambulation des concurrents après rotation face au vent
global $ligne_ysaisie;
    $k=0;
    $index=0;
    $index2=1;
    while ($index<count($ligne_ysaisie)-1){
        $index2=$index+1;
        if ($debug) {
            echo "<hr><br>Ligne [".$index." : ".$index2."] ";
        }
        // Extrémités       
        $xc1=$ligne_xsaisie[$index];
        $xc2=$ligne_xsaisie[$index2];
        $yc1=$ligne_ysaisie[$index];
        $yc2=$ligne_ysaisie[$index2];
        if ($debug) {echo "C".$index.": [".$xc1.",".$yc1."] C".$index2.": [".$xc2.",".$yc2."]<br>\n";}
        /*
        if ($yc1>$yc2){
            // Echanger les points
            $aux=$yc2;
            $yc2=$yc1;
            $yc1=$aux;
            $aux=$xc2;
            $xc2=$xc1;
            $xc1=$aux;           
        }
        */
        // Comparer      
        // Pour tous les segment de la zone de navigation 
        for ($i=0; $i<count($poly_ysaisie); $i++){
            // Rechercher si coupé par un droite horizontale
            $yp=$poly_ysaisie[$i];
            $xp=$poly_xsaisie[$i];
            if ($debug) {echo "<br>P".$i.": [".$xp.",".$yp."]\n";}               
            if ($yc2>=$yc1){
                if (($yp<$yc1) || ($yp>$yc2)) {
                    // exterieur
                    if ($debug) {echo "<br>Extérieur 1<br>\n";}            
                }
                else{
                    // interieur
                    // Calculer la distance entre le segment et le sommet courant du polygone
                    // y= $yp;
                    // xc= point d'intersection entre segment de ligne et droite horizontale y=yp
                    // equation du segment
                    if ($debug) {echo "<br> <b>Intérieur 1</b> <br>\n";}
                    $xc = round(($yp-$yc1)*($xc2-$xc1)/($yc2-$yc1) + $xc1); 
                    $distance1= distancePointsEcran($xc,$yp,$xp,$yp);
                    $distance2 = distanceEcran2Earth($xc,$yp,$xp,$yp);
                    if ($debug) {
                        echo "<br>Intersection entre le sommet  ".$i." [".$xp.",".$yp."] du polygone et le segment C".$index."[".$xc1.",".$yc1."] - C".$index2."[".$xc2.",".$yc2."] au point I[".$xc.",".$yp."]<br>\n";
                        echo "<br> Distance écran = ".$distance1." Distance terrain = ".$distance2."\n";
                    }
                    $tab_distances[$k]= '{"sommet_poly":'.$i.',"coordonnees":['.$xp.','.$yp.'],"segment_ligne":['.$index.','.$index2.'],"intersection":['.$xc.','.$yp.'],"distanceecran":'.$distance1.',"distanceterrain":'.$distance2.'}';
                    $k++; 
                } 
            }                
            else { // ($yc2<$yc1)
                if (($yp>$yc1) || ($yp<$yc2)) {
                    // exterieur
                    if ($debug) {echo "<br>Extérieur 2<br>\n";}            
                }
                else{
                    // interieur
                    // Calculer la distance entre le segment et le sommet courant du polygone
                    // y= $yp;
                    // xc= point d'intersection entre segment de ligne et droite horizontale y=yp
                    // equation du segment
                    if ($debug) {echo "<br> <b>Intérieur 2</b> <br>\n";}
                    $xc = round(($yp-$yc1)*($xc2-$xc1)/($yc2-$yc1) + $xc1); 
                    $distance1= distancePointsEcran($xc,$yp,$xp,$yp);
                    $distance2 = distanceEcran2Earth($xc,$yp,$xp,$yp);
                    if ($debug) {
                        echo "<br>Intersection entre le sommet  ".$i." [".$xp.",".$yp."] du polygone et le segment C".$index."[".$xc1.",".$yc1."] - C".$index2."[".$xc2.",".$yc2."] au point I[".$xc.",".$yp."]<br>\n";
                        echo "<br> Distance écran = ".$distance1." Distance terrain = ".$distance2."\n";
                    }
                    $tab_distances[$k]= '{"sommet_poly":'.$i.',"coordonnees":['.$xp.','.$yp.'],"segment_ligne":['.$index.','.$index2.'],"intersection":['.$xc.','.$yp.'],"distanceecran":'.$distance1.',"distanceterrain":'.$distance2.'}';                                       
                    $k++; 
                }             
            }
        }             
        $index++;
    }    
} 

//---------------------------------
function intersectionVerticale($x,$x1,$y1,$x2,$y2){  
    return (round(($x-$x1)*($y2-$y1)/($x2-$x1) + $y1));
}

// Placer des bouées par couples dans un rectangle vertical
// $yDepart : ordonnées de la ligne de départ (la ligne de distance est minimale avec la ligne des concurrents)
// ---------------------------------
 function droiteVerticale($numVerticale,$x1, $y1, $x2, $y2, $yDepart ){
    $distanceX=abs($x2-$x1);
    $distanceY=abs($y2-$y1);
 }


// Placement des bouées dans le rectangle ad hoc
// ---------------------------------
function placer_bouees($x1, $x2, $y1_0, $y1_1, $y2_0, $y2_1,$ligneDepartY){
global $nbouees;   
global $balises_xsaisie;
global $balises_ysaisie;
global $balisesEcran;   // Objet balises fixes dans les coordonnées écran initial (avant rotation face au vent)
$balisesIn=array(); // Tableau des balises contenues dans le rectangle utile

    echo "<br>Balises fixes<br>\n<table border=\"1\"><tr>\n";
    for ($index=0; $index<count($balisesEcran); $index++){
        echo "<td>".$balisesEcran[$index]->id."</td>";
    }
    echo "</tr><tr>\n";
    for ($index=0; $index<count($balisesEcran); $index++){
        echo "<td>".$balisesEcran[$index]->name."</td>";
    }
    echo "</tr><tr>\n";
    for ($index=0; $index<count($balises_xsaisie); $index++){
        echo "<td>".$balises_xsaisie[$index]."</td>";
    }
    echo "</tr><tr>\n";
    for ($index=0; $index<count($balises_ysaisie); $index++){
        echo "<td>".$balises_ysaisie[$index]."</td>";
    }
    echo "</tr></table>\n";
    

    $nboueesFixes= 6-$nbouees;
    if ($nboueesFixes>0){
        echo "<br><b>Placement de ".$nbouees." bouées autonomes et de ".$nboueesFixes." bouées fixes.</b>\n";
    }
    else{
        echo "<br><b>Placement de ".$nbouees." bouées autonomes</b>\n";        
    }
    echo "<br>Ligne de départ horizontale initiale: Y=".$ligneDepartY;
    echo "<br>Droite verticale N°1: ".$x1."<br>Droite verticale N°2: ".$x2."\n";  
    echo "<br>Droites horizontales Passe 1: ".$y1_0.", ".$y1_1."\n";
    echo "<br>Droites horizontales Passe 2: ".$y2_0.", ".$y2_1."\n";

    // Commencer par calculer le rectangle utile    
    echo "<br><b>Rectangle utile</b>\n";
    $minY1= min($y1_0, $y1_1);
    $maxY1= max($y1_0, $y1_1);
    $minY2= min($y2_0, $y2_1);
    $maxY2= max($y2_0, $y2_1);
    $maxY=min($maxY1, $maxY2);
    $minY=max($minY1, $minY2);   
    echo "<br> MinY: ".$minY." MaxY: ".$maxY."\n";
    echo "<br>Hauteur: ".abs($maxY-$minY)." pixels ==  ".distanceEcran2Earth(0,$minY,0,$maxY)." mètres\n";
    echo "<br>Largeur: ".abs($x1-$x2)." pixels ==  ".distanceEcran2Earth($x1,0,$x2,0)." mètres\n";
    echo "<br>Coordonnées du rectangle utile<br>Point supérieur gauche (".$x1.",".$maxY."), Point inférieur droit (".$x2.",".$minY.")\n";
    
    // Recherche des bouées fixes incluses dans ce rectangle

    echo "<br>Balises fixes incluses dans le rectangle utile<br>\n<table border=\"1\"><tr><td>Id</td>\n";
    for ($index=0; $index<count($balisesEcran); $index++){
        echo "<td>".$balisesEcran[$index]->id."</td>";
    }
    echo "</tr><tr><td>Nom</td>\n";
    for ($index=0; $index<count($balisesEcran); $index++){
        echo "<td>".$balisesEcran[$index]->name."</td>";
    }
    echo "</tr><tr><td>X</td>\n";
    for ($index=0; $index<count($balises_xsaisie); $index++){
        if (($balises_xsaisie[$index]>=$x1) && ($balises_xsaisie[$index]<=$x2)){
            echo "<td bgcolor=\"yellow\">".$balises_xsaisie[$index]."</td>";
            
        }
        else{ 
            echo "<td>".$balises_xsaisie[$index]."</td>";
        }    
    }
    echo "</tr><tr><td>Y</td>\n";
    for ($index=0; $index<count($balises_ysaisie); $index++){
        if (($balises_ysaisie[$index]>=$minY) && ($balises_ysaisie[$index]<=$maxY)){
            echo "<td bgcolor=\"green\">".$balises_ysaisie[$index]."</td>";
        }
        else{ 
            echo "<td>".$balises_ysaisie[$index]."</td>";
        }            
    }
    echo "</tr></table>\n";
    $k=0;    
    for ($index=0; $index<count($balises_xsaisie); $index++){
        if (($balises_xsaisie[$index]>=$x1) && ($balises_xsaisie[$index]<=$x2)
            && ($balises_ysaisie[$index]>=$minY) && ($balises_ysaisie[$index]<=$maxY)){
            $balisesIn[$k]=$index;            
            $k++;
        }
    }
    echo "<br>Balises incluses<br>\n";
    print_r($balisesIn);
    echo "<br>\n";
    
      
}
 
 
?>
