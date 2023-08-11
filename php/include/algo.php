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
 function placerBouees($numVerticale,$x1, $y1, $x2, $y2, $yDepart ){
    echo "<br>Droite verticale N°".$numVerticale." Zone explorées Départ (".$x1.", ".$y1."), Arrivée (".$x2.", ".$y2.")\n";
    $distanceX=abs($x2-$x1);
    $distanceY=abs($y2-$y1);
    echo "<br>Longueur verticale : ".$distanceY." Largeur horizontale: ".$distanceX;    
    echo "<br>Ligne de départ Y : ".$yDepart;
 }

?>
