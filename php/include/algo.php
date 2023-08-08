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

$tab_distances=array();

// ------------------------------------
function calcule_rectangle_bouees_debug(){
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
        echo "<hr><br>Ligne [".$index." : ".$index2."] ";
        // Extrémités       
        $xc1=$ligne_xsaisie[$index];
        $xc2=$ligne_xsaisie[$index2];
        $yc1=$ligne_ysaisie[$index];
        $yc2=$ligne_ysaisie[$index2];
        echo "C".$index.": [".$xc1.",".$yc1."] C".$index2.": [".$xc2.",".$yc2."]<br>\n";
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
            echo "<br>P".$i.": [".$xp.",".$yp."]\n";               
            if ($yc2>=$yc1){
                if (($yp<$yc1) || ($yp>$yc2)) {
                    // exterieur
                    echo "<br>Extérieur 1<br>\n";            
                }
                else{
                    // interieur
                    // Calculer la distance entre le segment et le sommet courant du polygone
                    // y= $yp;
                    // xc= point d'intersection entre segment de ligne et droite horizontale y=yp
                    // equation du segment
                    echo "<br> <b>Intérieur 1</b> <br>\n";
                    $xc = round(($yp-$yc1)*($xc2-$xc1)/($yc2-$yc1) + $xc1); 
                    $distance1= distancePointsEcran($xc,$yp,$xp,$yp);
                    $distance2 = distanceEcran2Earth($xc,$yp,$xp,$yp);
                    echo "<br>Intersection entre le sommet  ".$i." [".$xp.",".$yp."] du polygone et le segment C".$index."[".$xc1.",".$yc1."] - C".$index2."[".$xc2.",".$yc2."] au point I[".$xc.",".$yp."]<br>\n";
                    echo "<br> Distance écran = ".$distance1." Distance terrain = ".$distance2."\n";
                    $tab_distances[$k]= '{"sommet_poly":'.$i.', "coordonnees":['.$xp.','.$yp.'], "segment_ligne":['.$index.','.$index2.'], "intersection":['.$xc.','.$yp.'], "distanceecran":'.$distance1.', "distanceterrain":'.$distance2.'}';
                    $k++; 
                } 
            }                
            else { // ($yc2<$yc1)
                if (($yp>$yc1) || ($yp<$yc2)) {
                    // exterieur
                    echo "<br>Extérieur 2<br>\n";            
                }
                else{
                    // interieur
                    // Calculer la distance entre le segment et le sommet courant du polygone
                    // y= $yp;
                    // xc= point d'intersection entre segment de ligne et droite horizontale y=yp
                    // equation du segment
                    echo "<br> <b>Intérieur 2</b> <br>\n";
                    $xc = round(($yp-$yc1)*($xc2-$xc1)/($yc2-$yc1) + $xc1); 
                    $distance1= distancePointsEcran($xc,$yp,$xp,$yp);
                    $distance2 = distanceEcran2Earth($xc,$yp,$xp,$yp);
                    echo "<br>Intersection entre le sommet  ".$i." [".$xp.",".$yp."] du polygone et le segment C".$index."[".$xc1.",".$yc1."] - C".$index2."[".$xc2.",".$yc2."] au point I[".$xc.",".$yp."]<br>\n";
                    echo "<br> Distance écran = ".$distance1." Distance terrain = ".$distance2."\n";
                    $tab_distances[$k]= '{"sommet_poly":'.$i.', "coordonnees":['.$xp.','.$yp.'], "segment_ligne":['.$index.','.$index2.'], "intersection":['.$xc.','.$yp.'], "distanceecran":'.$distance1.', "distanceterrain":'.$distance2.'}';
                    $k++; 
                }             
            }
        }             
        $index++;
    }
    
    //
    echo "<br>Points d'intersection trouvés\n";
    for ($i=0;$i<count($tab_distances); $i++){
        echo ("<br>".$tab_distances[$i]);
    }
    
} 


// ------------------------------------
function calcule_rectangle_bouees(){
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
        //echo "<hr><br>Ligne [".$index." : ".$index2."] ";
        // Extrémités       
        $xc1=$ligne_xsaisie[$index];
        $xc2=$ligne_xsaisie[$index2];
        $yc1=$ligne_ysaisie[$index];
        $yc2=$ligne_ysaisie[$index2];
        //echo "C".$index.": [".$xc1.",".$yc1."] C".$index2.": [".$xc2.",".$yc2."]<br>\n";
        // Comparer      
        // Pour tous les segment de la zone de navigation 
        for ($i=0; $i<count($poly_ysaisie); $i++){
            // Rechercher si coupé par un droite horizontale
            $yp=$poly_ysaisie[$i];
            $xp=$poly_xsaisie[$i];
            //echo "<br>P".$i.": [".$xp.",".$yp."]\n";               
            if ($yc2>=$yc1){
                if (($yp<$yc1) || ($yp>$yc2)) {
                    // exterieur
                    //echo "<br>Extérieur 1<br>\n";            
                }
                else{
                    // interieur
                    // Calculer la distance entre le segment et le sommet courant du polygone
                    // y= $yp;
                    // xc= point d'intersection entre segment de ligne et droite horizontale y=yp
                    // equation du segment
                    //echo "<br> <b>Intérieur 1</b> <br>\n";
                    $xc = round(($yp-$yc1)*($xc2-$xc1)/($yc2-$yc1) + $xc1); 
                    $distance1= distancePointsEcran($xc,$yp,$xp,$yp);
                    $distance2 = distanceEcran2Earth($xc,$yp,$xp,$yp);
                    //echo "<br>Intersection entre le sommet  ".$i." [".$xp.",".$yp."] du polygone et le segment C".$index."[".$xc1.",".$yc1."] - C".$index2."[".$xc2.",".$yc2."] au point I[".$xc.",".$yp."]<br>\n";
                    //echo "<br> Distance écran = ".$distance1." Distance terrain = ".$distance2."\n";
                    $tab_distances[$k]= '{"sommet_poly":'.$i.', "coordonnees":['.$xp.','.$yp.'], "segment_ligne":['.$index.','.$index2.'], "intersection":['.$xc.','.$yp.'], "distanceecran":'.$distance1.', "distanceterrain":'.$distance2.'}';
                    $k++; 
                } 
            }                
            else { // ($yc2<$yc1)
                if (($yp>$yc1) || ($yp<$yc2)) {
                    // exterieur
                    //echo "<br>Extérieur 2<br>\n";            
                }
                else{
                    // interieur
                    // Calculer la distance entre le segment et le sommet courant du polygone
                    // y= $yp;
                    // xc= point d'intersection entre segment de ligne et droite horizontale y=yp
                    // equation du segment
                    //echo "<br> <b>Intérieur 2</b> <br>\n";
                    $xc = round(($yp-$yc1)*($xc2-$xc1)/($yc2-$yc1) + $xc1); 
                    $distance1= distancePointsEcran($xc,$yp,$xp,$yp);
                    $distance2 = distanceEcran2Earth($xc,$yp,$xp,$yp);
                    //echo "<br>Intersection entre le sommet  ".$i." [".$xp.",".$yp."] du polygone et le segment C".$index."[".$xc1.",".$yc1."] - C".$index2."[".$xc2.",".$yc2."] au point I[".$xc.",".$yp."]<br>\n";
                    //echo "<br> Distance écran = ".$distance1." Distance terrain = ".$distance2."\n";
                    $tab_distances[$k]= '{"sommet_poly":'.$i.', "coordonnees":['.$xp.','.$yp.'], "segment_ligne":['.$index.','.$index2.'], "intersection":['.$xc.','.$yp.'], "distanceecran":'.$distance1.', "distanceterrain":'.$distance2.'}';
                    $k++; 
                }             
            }
        }             
        $index++;
    }
    
    //
    echo "<br>Points d'intersection trouvés\n";
    for ($i=0;$i<count($tab_distances); $i++){
        echo ("<br>".$tab_distances[$i]);
    }
    
} 


?>
