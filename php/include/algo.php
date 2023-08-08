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
function calcule_rectangle_bouees(){
global $tab_distances;
$tab_distances=array();
global $poly_xsaisie; // Tableau des coordonnées écran de la zone de navigation après rotation face au vent
global $poly_ysaisie;
global $ligne_xsaisie; // Tableau des coordonnées écran de la ligne de déambulation des concurrents après rotation face au vent
global $ligne_ysaisie;
    $k=0;
    $index=0;
    while ($index<count($ligne_ysaisie)-1){
        // Extrémités       
        $xc1=$ligne_xsaisie[$index];
        $xc2=$ligne_xsaisie[$index+1];
        $yc1=$ligne_ysaisie[$index];
        $yc2=$ligne_ysaisie[$index+1];
        if ($yc1>$yc2){
            // Echanger
            $aux=$yc2;
            $yc2=$yc1;
            $yc1=$aux;
        }
        // Comparer      
        // Pour tous les segment de la zone de navigation 
        for ($i=0; $i<count($poly_ysaisie); $i++){
            // Rechercher si coupé par un droite horizontale
            $yp=$poly_ysaisie[$i];
            $xp=$poly_xsaisie[$i];
                           
            if (($yp<$yc1) || ($yp>$yc2)){
                // exterieur
            }
            else{
                // interieur
                // Calculer la distance entre le segment et le sommet courant du polygone
                // y= $yp;
                // xc= point d'intersection entre segment de ligne et droite horizontale y=yp
                // equation du segment
                $xc = round(($yp-$yc2)*($xc2-$xc1)/($yc2-$yc1)); 
                $distance = distanceEcran($xc,$yp,$xp,$yp);
                echo ("<br>Distance entre le sommet  ".$i." du polygone et le segment ".$index." = ".$distance);
                $indexsuivant=$index+1;
                $tab_distances[$k]= '{"sommet_poly":'.$i.', "coordonnees":['.$xp.','.$yp.'], "segment_ligne":['.$index.','.$indexsuivant.'], "intersection":['.$xc.','.$yp.'], "distance":'.$distance.'}';
                $k++; 
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
