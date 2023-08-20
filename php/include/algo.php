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


function tab_echange(&$tab1, &$tab2){
// echange les contenus d'un tableau de deux éléments
    $aux=$tab1[0];
    $tab1[0]=$tab2[0];
    $tab2[0]=$aux;
    $aux=$tab1[1];
    $tab1[1]=$tab2[1];
    $tab2[1]=$aux;    
}

 
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
    if ($x1==$x2) {
        return $y1;
    }
    else {
        return (round(($x-$x1)*($y2-$y1)/($x2-$x1) + $y1));
    }   
}


// On balaye le polygone avec des droite verticale pour trouver un rectangle de navigation 
// suffisemment haut et large 
// Donnees en output 
// ($x1, $x2, $y1, $y2)

// ------------------------------------------
function rechercher_rectangle_utile($incrementX, $xInitial, $xFinal, $sensprogression){
global $debug2;
global $twd_degre;
global $twd_radian;
global $x1;
global $x2;
global $y1;
global $y2; 
global $x1Lon;
global $y1Lat;
global $x2Lon;
global $y2Lat;
global $intersectionmin;
global $intersectionmax;
global $coordonneesmin;
global $coordonneesmax;
global $poly_xsaisie;
global $poly_ysaisie;
global $distance_H_MillePixels;
global $distance_V_MillePixels; 
global $deltaXpixelsSite;
global $deltaYpixelsSite;
global $xPasse1;
global $xPasse2;
global $deltaXpixelsDixMetres;
global $deltaYpixelsCinquanteMetres;

/**************************************
 *     Première passe
 **************************************/
    $numZoneExploree=0; // On a besoin de 2 zones contigües pour placer les bouées
      
    $x=$xInitial; // Démarrer la recherche à 0 mètres du sommet

    if ($debug2){    
        echo "<br>Rechercher Rectangle<br><b>Données en entrée</b> : Incrémentx:".$incrementX.", xInitial:".$xInitial.", xFinal:".$xFinal.", Sens progression:".$sensprogression."\n";
        if ($sensprogression>0){
            echo "<br>Progression vers l'EST \n";
        }
        else{
            echo "<br>Progression vers l'OUEST \n";
        }
        echo "<br>Valeurs de départ: Droite verticale [x0:".$xInitial."], Droite verticale suivante [x:".$x."]<br>\n";
    }
    
    // Droite d'équation x=constante
    // Tant que x<$posxFin

    $encore=true;
    $distanceH=0;    // Ecart horizontal entre deux droites verticales du rectangle de navigation
    $maxDistanceV=0;    // Maximas sur l'écart vertical entre deux points du polygone de navigation
    $indexMax=0;
    $xMax=$x;
    $yMaxPasse1[0]=1000000; // Enregistre les minima et maxima de l'intersection de la droite verticale avec le polygone  
    $yMaxPasse1[1]=-1000000;

    while ($encore) { // On cherche les intersections avec le polygone
        if ($sensprogression>0){
            $encore = ($x<=$xFinal);
        }
        else{
            $encore = ($x>=$xFinal);    
        }     
        $nbintersections=0;           
        $tab_Intersections=array(); // Les valeurs y d'intersection
        if (false){    
            echo "<br>Exploration <br>\n";
            echo (" x0:".$xInitial.", x:".$x);
            echo "<br>\n";
        }

        for ($i=0; $i<count($poly_ysaisie); $i++){
            // calculer l'intersection de la droite verticale avec le polygone pour un segment entre deux sommets consécutifs
            $xp1=$poly_xsaisie[$i]; // sommet i
            $yp1=$poly_ysaisie[$i];
            if ($i<count($poly_ysaisie)-1) { // indice du sommet suivant
                $i2=$i+1;
            } 
            else {
                $i2=0;
            }
            $xp2=$poly_xsaisie[$i2];    // sommet suivant
            $yp2=$poly_ysaisie[$i2];
            if (false){  
                echo "<br>Sommet ".$i.": [".$xp1.",".$yp1."]\n";
                echo "<br>Sommet ".$i2.": [".$xp2.",".$yp2."]\n";            
            } 
    
            if (($x>=min($xp1, $xp2)) && ($x>=max($xp1, $xp2))) { // Intersection possible 
                $tab_Intersections[$nbintersections]=intersectionVerticale($x,$xp1,$yp1,$xp2,$yp2); 
                $nbintersections++;
            }
        }

    if ($debug2){    
        echo "<br>".$nbintersections." intersections.<br>Table des intersections<br>\n";
        print_r($tab_Intersections);
        echo "<br>\n";
    }
    
    switch ($nbintersections) {
        case 0 : break; // Sortie du polygone
        case 1 : break; // Sommet, on ne traite pas
        case 2 :  // Intérieur. Calculer la dimension verticale entre deux intersections
            $distanceVerticale =abs($tab_Intersections[1]-$tab_Intersections[0]);
            //$deltaXpixelsSite=howMuchXPixelsForMeters(20.0);
            //$deltaYpixelsCinquanteMetres=howMuchYPixelsForMeters(50.0);
            if ($distanceVerticale >= $deltaYpixelsSite){
                // On a un rectangle candidat
                if ($distanceVerticale>$maxDistanceV){
                    $maxDistanceV=$distanceVerticale; 
                    $indexMax=$i;
                    $xMax=$x;
                    $distanceH=abs($xInitial-$x);                
                }
                if ($debug2){    
                    echo "<br>2 intersections<br>Distance verticale : ".$distanceVerticale." Distance horizontale(X0,X) : ".$distanceH."\n";
                }                
                $numZoneExploree++;
                $xPasse1=$x;    // Droite verticale trouvée
                $yMaxPasse1[0]=$tab_Intersections[0];
                $yMaxPasse1[1]=$tab_Intersections[1];                 
                $encore=false; // Traitement  1ère passe terminé
            }
            
            break; 
        case 3 : // // polygone concave + passage par un sommet ; il faut faire un grand pas vers l'Est (ou vers l'Ouest)
            $xInitial=$x+$incrementX;
            $x=$xInitial+$incrementX;
            break; 
        default : // polygone convexe avec au moins une concavité selon l'axe Nord / Sud
                // On traite les deux premiers couples
                // Ordonner les Y dans le sens croissant
            if (sort($tab_Intersections)){
                $distanceVerticale = abs($tab_Intersections[1]-$tab_Intersections[0]);
                $distanceVerticale2 =  abs($tab_Intersections[3]-$tab_Intersections[2]);  

                if (($distanceVerticale>$maxDistanceV) || ($distanceVerticale2>$maxDistanceV)){
                    $maxDistanceV=max($distanceVerticale, $distanceVerticale2); 
                    $indexMax=$i;
                    $xMax=$x;
                    $distanceH=abs($xInitial-$x);                
                }
                            
                if (($distanceVerticale >= $distanceVerticale2) && ($distanceVerticale >= $deltaYpixelsSite)){
                    // On a un rectangle candidat
                    // Passer au placement des bouées
                    if ($debug2){    
                        echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".$distanceH."\n";
                    }

                    $numZoneExploree++; 
                    $xPasse1=$x;    // Droite verticale trouvée   
                    $yMaxPasse1[0]=$tab_Intersections[0];
                    $yMaxPasse1[1]=$tab_Intersections[1];                
                    $encore=false;                        
                }
                else{
                    if ($distanceVerticale2 >= $deltaYpixelsSite){
                        // On a un rectangle candidat
                        // Passer au placement des bouées
                        if ($debug2){    
                            echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".$distanceH."\n";
                        }

                        $numZoneExploree++;
                        $xPasse1=$x;    // Droite verticale trouvée 
                        $yMaxPasse1[0]=$tab_Intersections[2];
                        $yMaxPasse1[1]=$tab_Intersections[3];                  
                        $encore=false;
                    }
                }                   
            }                        
            break;             
        }
        $x=$x+$incrementX; 
    }

    if ($debug2){    
        echo "<br><b>Fin du premier passage</b>.<br>Distance verticale maximale: ".$maxDistanceV." (Y0:".$yMaxPasse1[0].", Y1:".$yMaxPasse1[1].")<br>\n";
        echo "<br>Distance  au point de départ horizontal: ".$distanceH."\n";
    }
 

/***************************************
 * Seconde passe
 * *************************************/

    // On veut trouver la plus proche droite verticale élognée d'au moins 20m de $xpasse1

    $xInitial=$x+$incrementX; // Démarrer la recherche immédiatement à proximité
    $x=$xInitial+$incrementX;

    if ($debug2){
        echo "<br><br><b>Début de la seconde passe</b>";    
        echo "<br>Progression ".$sensprogression."\n";
        echo "<br>Valeurs de départ de l'exploration (X0:".$xInitial.")<br>\n";
        echo "<br>Abscisse de la droite verticale trouvée à la passe N°1 : ".$xPasse1."\n";    // Droite verticale trouvée
    }

    // Droite d'équation x=constante
    // Tant que x<$posxFin

    $encore=true;

    $distanceHPasse1=abs($xPasse1-$x);    // Ecart horizontal avec la droite verticale trouvée à la première passe 
    $distanceH=0;    // Ecart horizontal entre deux droites verticales
    $maxDistanceV=0;    // Maximas sur l'écart vertical entre deux points du polygone de navigation
    $indexMax=0;
    $xMax=$x;
    $yMaxPasse2[0]=1000000;
    $yMaxPasse2[1]=-1000000;

    while ($encore) { // On cherche les intersections avec le polygone
 
        if ($sensprogression>0){
            $encore = ($x<=$xFinal);
        }
        else{
            $encore = ($x>=$xInitial);    
        }     
        $nbintersections=0;           
        $tab_Intersections=array(); // Les valeurs y d'intersection
        if (false){    
            echo "<br>Exploration \n";
            echo (" x0:".$xInitial.", x:".$x);
            echo "<br>\n";
        }

        for ($i=0; $i<count($poly_ysaisie); $i++){
            // calculer l'intersection avec le polygone
            $xp1=$poly_xsaisie[$i];
            $yp1=$poly_ysaisie[$i];
            if ($i<count($poly_ysaisie)-1) {
                $i2=$i+1;
            } 
            else {
                $i2=0;
            }
            $xp2=$poly_xsaisie[$i2];
            $yp2=$poly_ysaisie[$i2];
            if (false){  
                echo "<br>Sommet ".$i.": [".$xp1.",".$yp1."]\n";
                echo "<br>Sommet ".$i2.": [".$xp2.",".$yp2."]\n";            
            } 
      
            if (($x>=min($xp1, $xp2)) && ($x>=max($xp1, $xp2))) { // Intersection possible 
                $tab_Intersections[$nbintersections]=intersectionVerticale($x,$xp1,$yp1,$xp2,$yp2); 
                $nbintersections++;
            }
        }

        if (false){    
            echo "<br>".$nbintersections." intersections.<br>Table des intersections<br>\n";
            print_r($tab_Intersections);
            echo "<br>\n";
        }
    
        switch ($nbintersections) {
            case 0 : break; // Sortie du polygone
        case 1 : break; // Sommet, on ne traite pas
        case 2 :  // Calculer la dimension verticale entre deux intersections
            $distanceVerticale =abs($tab_Intersections[1]-$tab_Intersections[0]);
            if ($distanceVerticale>$maxDistanceV){
                $maxDistanceV=$distanceVerticale; 
                $indexMax=$i;
                $xMax=$x;
                $distanceH=abs($xInitial-$x);               
            }
            $distanceHPasse1=abs($xPasse1-$x); 
            if ($distanceVerticale >= $deltaYpixelsSite){
                // On a une droite candidate
                if ($debug2){    
                    echo "<br>Distance verticale : ".$distanceVerticale." horizontale : ".$distanceH."\n";
                    echo "<br><i>Distance à la droite fournie par la passe N°1</i> : <b>".$distanceHPasse1."</b>\n";
                    
                }

                $numZoneExploree++;
                if ($distanceHPasse1 >= $deltaXpixelsSite){
                    $encore=false;
                    $xPasse2=$x;
                    $yMaxPasse2[0]=$tab_Intersections[0];
                    $yMaxPasse2[1]=$tab_Intersections[1];                    
                }              
            }
            
            break; // Intérieur
        case 3 : // polygone concave, passage par un sommet concave; il faut faire un pas de plus
                $xInitial=$x+$incrementX;
                $x=$xInitial+$incrementX;
            break; 
        default : // polygone convexe avec au moins une concavité selon l'axe Nord / Sud
                // On traite les deux premiers couples
                // Ordonner les Y dans le sens croissant
            if (sort($tab_Intersections)){
                $distanceVerticale = abs($tab_Intersections[1]-$tab_Intersections[0]);
                $distanceVerticale2 =  abs($tab_Intersections[3]-$tab_Intersections[2]);  

                if (($distanceVerticale>$maxDistanceV) || ($distanceVerticale2>$maxDistanceV)){
                    $maxDistanceV=max($distanceVerticale, $distanceVerticale2); 
                    $indexMax=$i;
                    $xMax=$x;
                    $distanceH=abs($xInitial-$x);  
                }
                $distanceHPasse1=abs($xPasse1-$x);               
                            
                if (($distanceVerticale >= $distanceVerticale2) && ($distanceVerticale >= $deltaYpixelsSite)){
                    // On a un rectangle candidat
                    // Passer au placement des bouées
                    if ($debug2){    
                        echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".abs($xInitial-$x)."\n";
                    }

                    $numZoneExploree++; 
                    if ($distanceHPasse1 >= $deltaXpixelsSite){
                        $encore=false;
                        $xPasse2=$x;
                        $yMaxPasse2[0]=$tab_Intersections[0];
                        $yMaxPasse2[1]=$tab_Intersections[1];
                    }    
                }
                else{
                    if ($distanceVerticale2 >= $deltaYpixelsSite){
                        // On a un rectangle candidat
                        // Passer au placement des bouées
                        if ($debug2){    
                            echo "<br>Distance verticale : ".$distanceVerticale." Distance horizontale : ".abs($xInitial-$x)."\n";
                        }
                        
                        //droiteVerticale($numZoneExploree,$xInitial, $tab_Intersections[2], $x, $tab_Intersections[3],$y0);
                        $numZoneExploree++;
                        if ($distanceHPasse1 >= $deltaXpixelsSite){
                            $xPasse2=$x;
                            $yMaxPasse2[0]=$tab_Intersections[2];
                            $yMaxPasse2[1]=$tab_Intersections[3];
                            $encore=false;
                        }    
                    }
                }                   
            }                        
            break;         
        }
        $x=$x+$incrementX; 
    }

    if ($debug2){    
        echo "<br><b>Deuxième passe</b>. Distance verticale maximale: ".$maxDistanceV." Distance  horizontale: ".$distanceHPasse1." Y0:".$yMaxPasse2[0]." Y1:".$yMaxPasse2[1];
        echo "<br>\n";
    }
    
    if (($distanceHPasse1>= $deltaXpixelsSite) && ($maxDistanceV>=$deltaYpixelsSite)){
        $distanceX=abs($xPasse2-$xPasse1);
        $distanceY=min(abs($yMaxPasse1[0]-$yMaxPasse1[1]),abs($yMaxPasse2[0]-$yMaxPasse2[1]));
    
        if ($debug2){
            echo "<b>Succès APPARENT</b>\n";
            echo "<br><br>Recherche d'un rectangle inclus\n";    
            echo "<br><i>Droite verticale initiale x=".$xPasse1."</i>\n";
            echo "<br><i>Droite verticale finale x=".$xPasse2."</i>\n";
            echo "<br><i>Droite horizontale initiale inférieure y=".$yMaxPasse1[0]."</i>\n";
            echo "<br><i>Droite horizontale initiale supérieure y=".$yMaxPasse1[1]."</i>\n";        
            echo "Distance initiale verticale DY1: ".abs($yMaxPasse1[1]-$yMaxPasse1[0])."</i>\n";
            echo "<br><i>Droite horizontale finale inférieure y=".$yMaxPasse2[0]."</i>\n";
            echo "<br><i>Droite horizontale finale supérieure y=".$yMaxPasse2[1]."</i>\n";      
            echo "Distance finale verticale DY2: ".abs($yMaxPasse2[1]-$yMaxPasse2[0])."</i>\n";           
            echo "<br>Longueur verticale : ".$distanceY." Largeur horizontale: ".$distanceX;    
            echo "<br />\n";
        }
    }
    
/******************************************
 * Placement des bouées 
 * ****************************************/ 
    $xMinPasse=min($xPasse1, $xPasse2);
    $xMaxPasse=max($xPasse1, $xPasse2);
    
    $minY1= min($yMaxPasse1[0], $yMaxPasse1[1]);   
    $minY2= min($yMaxPasse2[0], $yMaxPasse2[1]);
    $maxY1= max($yMaxPasse1[0], $yMaxPasse1[1]);
    $maxY2= max($yMaxPasse2[0], $yMaxPasse2[1]);
    
    if ($debug2){
        echo "<br>xPasse1:".$xPasse1.", xPasse2:".$xPasse2."\n";
        echo "<br>minY1:".$minY1.", maxY1:".$maxY1." DistanceV1:".abs($maxY1-$minY1)." \n";
        echo "<br>minY2:".$minY2.", maxY2:".$maxY2." DistanceV2:".abs($maxY2-$minY2)."\n";
        echo "<br>DeltaXpixelsDixmètres=".$deltaXpixelsDixMetres." DeltaXpixelsSite=".$deltaXpixelsSite."\n";
    }    
    
    $minY= max($minY1, $minY2);
    $maxY= min($maxY1, $maxY2);

    if ($debug2){
        echo "<br><br><b>Après préparation des données</b>\n";
        echo "<br>xMinPasse:".$xMinPasse.", xMaxPasse:".$xMaxPasse.", DistanceHorizontale:".abs($xMaxPasse-$xMinPasse)." \n";                
        echo "<br>minY:".$minY.", maxY:".$maxY."\n";
        echo "<br>Distance utile verticale DistanceV: ".abs($maxY-$minY)."\n";
        echo "<br>DeltaYpixelsCinquantemètres=".$deltaYpixelsCinquanteMetres." DeltaYpixelsSite=".$deltaYpixelsSite."\n";        
    }    

    
    if ($minY>=$maxY){
        if ($debug2){
            echo "<br>Echec : Rectangle utile vide<br>\n";
        }    
        return false;        
    }
    
    $xmin=$xMinPasse;
    $xmax=$xMaxPasse;
    if (abs($xMaxPasse-$xMinPasse)>$deltaXpixelsSite){
        // Réduire l'ampleur de la zone
        $xmax=$xMinPasse+$deltaXpixelsSite;        
    }

    $ymin=$minY;
    $ymax=$maxY; 
    if (abs($maxY-$minY)>$deltaXpixelsSite){
        // Réduire l'ampleur de la zone
        $ymax=$minY+$deltaYpixelsSite;         
    }    

    
    // Préparer les données 
    if ($twd_degre>=0 && $twd_degre<=90){
        // X croissant, y croissant
        $x1=$xmin;
        $x2=$xmax;
        $y1=$ymin;
        $y2=$ymax; 
    }
    else if ($twd_degre>90 && $twd_degre<=180){
        // X croissant, y décroissant
        $x1=$xmin;
        $x2=$xmax;
        $y1=$ymax;
        $y2=$ymin; 
    }
    else if ($twd_degre>180 && $twd_degre<=200){
        // X décroissant, y décroissant
        $x1=$xmax;
        $x2=$xmin;
        $y1=$ymax;
        $y2=$ymin; 
    }
    else if ($twd_degre>270 && $twd_degre<360){
        // X décroissant, y croissant
        $x1=$xmax;
        $x2=$xmin;
        $y1=$ymin;
        $y2=$ymax; 
    }

    // Coordonnées du rectangle où sont placées les bouées
    $x1Lon=get_lon_Xecran(setSaisieToDisplayX($x1,$y1, $twd_radian));
    $y1Lat=get_lat_Yecran(setSaisieToDisplayY($x1,$y1, $twd_radian));    
    $x2Lon=get_lon_Xecran(setSaisieToDisplayX($x2,$y2, $twd_radian));
    $y2Lat=get_lat_Yecran(setSaisieToDisplayY($x2,$y2, $twd_radian)); 

    return true;
}


// Placement des bouées dans le rectangle ad hoc
// ---------------------------------
function placer_bouees($x1, $x2, $y1, $y2){

global $debug2; 
global $twd_radian;
global $nbouees;   
global $balises_xsaisie;
global $balises_ysaisie;
global $balisesEcran;   // Objet balises fixes dans les coordonnées écran initial (avant rotation face au vent)
global $tableBoueesFixesSaisieParcours;
$tableBoueesFixesSaisieParcours=array();
global $boueesFixesParcours; // Le tableau des bouées fixes à enregistrer
$boueesFixesParcours=array();
global $boueesMobilesParcours;
$boueesMobilesParcours=array();   

$balisesIn=array(); // Tableau des balises contenues dans le rectangle utile

if ($debug2){
    echo "<br><br><b>placer_bouees</b> ::Données en entrée\n";
    echo ($x1.", ".$x2.", ".$y1.", ".$y2."\n");
    echo "<br>\n";

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
}

    $nboueesFixes= 6-$nbouees;
if ($debug2){   
    if ($nboueesFixes>0){
        echo "<br><b>Placement de ".$nbouees." bouées autonomes et de ".$nboueesFixes." bouées fixes.</b>\n";
    }
    else{
        echo "<br><b>Placement de ".$nbouees." bouées autonomes</b>\n";        
    }
    echo "<br>Droite verticale X1: ".$x1."<br>Droite verticale X2: ".$x2."\n";  
    echo "<br>Droites horizontales Y1: ".$y1."\n";
    echo "<br>Droites horizontales Y2 : ".$y2."\n";
}
    // Commencer par calculer le rectangle utile    
if ($debug2){    
    echo "<br><b>Rectangle utile</b>\n"; 
}
    
if ($debug2){    
    echo "<br> Y1: ".$y1." Y1: ".$y2."\n";
    echo "<br>Hauteur: ".abs($y2-$y1)." pixels ==  ".distanceEcran2Earth(0,$y1,0,$y2)." mètres\n";
    echo "<br>Largeur: ".abs($x1-$x2)." pixels ==  ".distanceEcran2Earth($x1,0,$x2,0)." mètres\n";
    echo "<br>Coordonnées du rectangle utile<br>Point inférieur gauche (".$x1.",".$y1."), Point supérieur droit (".$x2.",".$y2.")\n";
}    
    // Recherche des bouées fixes incluses dans ce rectangle

if ($debug2){    
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
        if (($balises_ysaisie[$index]>=$y1) && ($balises_ysaisie[$index]<=$y2)){
            echo "<td bgcolor=\"green\">".$balises_ysaisie[$index]."</td>";
        }
        else{ 
            echo "<td>".$balises_ysaisie[$index]."</td>";
        }            
    }
    echo "</tr></table>\n";
}    

    // Ne traiter que les balises incluses dans le rectangle sélectionné

    $k=0;    
    for ($index=0; $index<count($balises_xsaisie); $index++){
        if (($balises_xsaisie[$index]>=$x1) && ($balises_xsaisie[$index]<=$x2)
            && ($balises_ysaisie[$index]>=$y1) && ($balises_ysaisie[$index]<=$y2)){
            $balisesIn[$k]=$index;            
            $k++;
        }
    }
    
    
if ($debug2){    
    echo "<br>Balises incluses<br>\n";
    print_r($balisesIn);
    echo "<br>\n";
}
 
    // Pour les bouées fixes, vérifier si elles peuvent être affectées à une position sur le parcours
    // Alignement horizontal. Il y a 3 zones : 
    // Dog leg au vent : y> 3 * Hauteur / 4
    // Porte sous le vent : y < Hauteur / 4
    // Depart / Arrivée : Hauteur /4 > y < 3 * Hauteur / 4
    // Il faut ensuite considérer l'alignement vertical
    $porte_tribord=false;
    $porte_babord=false;
    $depart_tribord=false;
    $depart_babord=false;
    $dog_leg1=false;
    $dog_leg2=false;
    
    $quartHauteur=abs($y2-$y1) / 4;
    $demiLargeur=abs($x1-$x2) / 2;
    
    $k=0; 
    for ($index=0; $index<count($balisesIn); $index++){
        if ($balises_ysaisie[$balisesIn[$index]]<=$quartHauteur){ // Porte sous le vent
            if ($balises_xsaisie[$balisesIn[$index]]<=$demiLargeur){
                // Porte tribord 
                if ($debug2){ echo "<br>Porte sous le vent tribord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}
                $porte_tribord=true;
            }
            else{
                // Porte bâbord
                if ($debug2){echo "<br>Porte sous le vent bâbord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}                 
                $porte_babord=true;
            } 
        } 
        else if ($balises_ysaisie[$balisesIn[$index]]>=3*$quartHauteur){ // Dog Leg ?
            if ($balises_xsaisie[$balisesIn[$index]]<=$demiLargeur){
                // Dog leg bâbord 2
                if ($debug2){ echo "<br>Dog leg N°2 bâbord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}
                $dog_leg2=true;                 
            }
            else{
                // Dog leg bâbord 1
                if ($debug2){ echo "<br>Dog leg N°1 bâbord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}                 
                $dog_leg1=true;                 
            }         
        }
        else {
            // ((($balises_ysaisie[$balisesIn[$index]]>$quartHauteur)) && ($balises_ysaisie[$balisesIn[$index]]<3*$quartHauteur)){
            // Entre deux : Départ ?
            if ($balises_xsaisie[$balisesIn[$index]]<=$demiLargeur){
                // Départ / Arrivée bâbord
                if ($debug2){echo "<br>Arrivée / Départ bâbord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}                                 
                $depart_babord=true;                 
            }
            else{
                // Départ / Arrivée tribord
                if ($debug2){echo "<br>Arrivée / Départ tribord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}                                                 
                $depart_tibord=true;
            }                     
        }

if ($debug2){        
        echo "<br>Distance au bord gauche ".abs($balises_xsaisie[$balisesIn[$index]]-$x1)."\n";
        echo "<br>Distance au bord droit ".abs($balises_xsaisie[$balisesIn[$index]]-$x2)."\n";
        echo "<br>Distance au bord supérieur ".abs($balises_ysaisie[$balisesIn[$index]]-$y2)."\n";
        echo "<br>Distance au bord inférieur ".abs($balises_ysaisie[$balisesIn[$index]]-$y1)."<br>\n";   
}        
        if ($porte_tribord){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$x1)<ECART_BORDURE){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"PorteTribord"}', false);
                $k++;
            }        
        }
        if ($porte_babord){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$x2)<ECART_BORDURE){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"PorteBabord"}', false);
                $k++;
            }        
        }
        // Depart / Arrivée
        if ($depart_tribord){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$x2)<ECART_BORDURE){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"DepartTribord"}', false);
                $k++;
            }        
        }
        if ($depart_babord){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$x1)<ECART_BORDURE){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"DepartBabord"}', false);
                $k++;
            }        
        }
        // Dog Leg bâbord
        if ($dog_leg1){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$x2)<ECART_BORDURE){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"DogLeg1"}', false);
                $k++;
            }        
        }
        if ($dog_leg2){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$x1)<ECART_BORDURE){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"DogLeg2"}', false);
                $k++;
            }        
        }            
    } 
    
    // Afficher les balises retenues
if ($debug2){
    echo "<br>Balises retenues\n";
    for ($index=0; $index<count($tableBoueesFixesSaisieParcours); $index++){
        echo "<br>".json_encode($tableBoueesFixesSaisieParcours[$index])."\n";
    }  
}    

// Ajouter des points remarquables pour tester l'algoritme



    // Convertir ces balises en Longitude, Latitude
    // {"site":"LePlessis","twd":60,"boueesfixes":[{"boueefixe":true,"id":4,"lon":-1.4743316798018502,"lat":47.24381872961287,"color":"yellow","fillcolor":"red"},{"boueefixe":true,"id":7,"lon":-1.4739890647643559,"lat":47.24395125770921,"color":"black","fillcolor":"green"},{"boueefixe":true,"id":6,"lon":-1.4737391242861835,"lat":47.24383766219806,"color":"black","fillcolor":"green"},{"boueefixe":true,"id":10,"lon":-1.4736127498871077,"lat":47.24355156979959,"color":"black","fillcolor":"green"},{"boueefixe":true,"id":8,"lon":-1.4739385150047255,"lat":47.24372196306633,"color":"black","fillcolor":"green"},{"boueefixe":true,"id":6,"lon":-1.4737391242861835,"lat":47.24383766219806,"color":"blue","fillcolor":"red"}],"boueesmobiles":[{"boueefixe":false,"id":0,"lon":-1.4742839383621993,"lat":47.24373879203094,"color":"yellow","fillcolor":"green"},{"boueefixe":false,"id":1,"lon":-1.474222155322651,"lat":47.24367147617248,"color":"purple","fillcolor":"green"},{"boueefixe":false,"id":2,"lon":-1.4741659889230618,"lat":47.24359995307286,"color":"purple","fillcolor":"red"},{"boueefixe":false,"id":3,"lon":-1.4737700158059577,"lat":47.24388183823017,"color":"blue","fillcolor":"red"}]}
    
    for ($index=0; $index<count($tableBoueesFixesSaisieParcours); $index++){
        // {"boueefixe":true,"id":4,"lon":-1.4743316798018502,"lat":47.24381872961287,"color":"yellow","fillcolor":"red"}
        if (($tableBoueesFixesSaisieParcours[$index]->franchissement=="DogLeg1") || ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DogLeg2")){
            $color= "navy";
            $fillcolor= "red";
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="PorteBabord"){
            $color= "purple";
            $fillcolor= "red";
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="PorteTribord"){
            $color= "purple";
            $fillcolor= "green";
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DepartBabord"){
            $color= "yellow";
            $fillcolor= "red";
        }
        else {
            $color= "yellow";
            $fillcolor= "green";
        }
        // repasser en coordonnées écran
        $ecranX=setSaisieToDisplayX($tableBoueesFixesSaisieParcours[$index]->xs, $tableBoueesFixesSaisieParcours[$index]->ys, $twd_radian);
        $ecranY=setSaisieToDisplayY($tableBoueesFixesSaisieParcours[$index]->xs, $tableBoueesFixesSaisieParcours[$index]->ys, $twd_radian);
        // repasser en coordonnées géographiques
        $lon=get_lon_Xecran($ecranX);
        $lat=get_lat_Yecran($ecranY);
        // Formater la sortie
        $boueesFixesParcours[$index]='{"boueefixe":true,"id":'.$tableBoueesFixesSaisieParcours[$index]->id.',"lon":'.$lon.',"lat":'.$lat.',"color":"'.$color.'","fillcolor":"'.$fillcolor.'"}';        
    }  
    // Debug
if ($debug2){
    echo "<br>Bouées fixes retenues pour le parcours\n";
    for ($index=0; $index<count($boueesFixesParcours); $index++){        
        echo "<br>".$boueesFixesParcours[$index];
    }
}    
    // Compléter les bouées fixes avec des bouées mobiles alignées
    // "boueesmobiles":[{"boueefixe":false,"id":0,"lon":-1.4742839383621993,"lat":47.24373879203094,"color":"yellow","fillcolor":"green"},
    
    $tab_BoueesMobiles = array();
    $tab_BoueesMobiles['dog_leg1']=new stdClass();
    //$tab_BoueesMobiles['dog_leg1']->fixe=false;
    $tab_BoueesMobiles['dog_leg2']=new stdClass();
    //$tab_BoueesMobiles['dog_leg2']->fixe=false;
    $tab_BoueesMobiles['depart_tribord']=new stdClass();
    //$tab_BoueesMobiles['depart_tribord']->fixe=false;
    $tab_BoueesMobiles['depart_babord']=new stdClass();
    //$tab_BoueesMobiles['depart_babord']->fixe=false;
    $tab_BoueesMobiles['porte_tribord']=new stdClass();
    //$tab_BoueesMobiles['porte_tribord']->fixe=false;
    $tab_BoueesMobiles['porte_babord']=new stdClass();
    //$tab_BoueesMobiles['porte_babord']->fixe=false;
    
    for ($index=0; $index<count($tableBoueesFixesSaisieParcours); $index++){       
        if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DogLeg1"){
            $tab_BoueesMobiles['dog_leg1']->fixe=true;
            if (isset($tab_BoueesMobiles['dog_leg2']->fixe) && ($tab_BoueesMobiles['dog_leg2']->fixe==false)){
                $tab_BoueesMobiles['dog_leg2']->xs=$x1+ECART_BORDURE;
                $tab_BoueesMobiles['dog_leg2']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['dog_leg2']->color="navy";
                $tab_BoueesMobiles['dog_leg2']->fillcolor="red";
            }     
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DogLeg2"){
            $tab_BoueesMobiles['dog_leg2']->fixe=true;
            if (isset($tab_BoueesMobiles['dog_leg1']->fixe) && ($tab_BoueesMobiles['dog_leg1']->fixe==false)){
                $tab_BoueesMobiles['dog_leg1']->xs=$x2-ECART_BORDURE;
                $tab_BoueesMobiles['dog_leg1']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['dog_leg1']->color="navy";
                $tab_BoueesMobiles['dog_leg1']->fillcolor="red";
            }                  
        }        
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="PorteBabord"){
            $tab_BoueesMobiles['porte_babord']->fixe=true;
            if (isset($tab_BoueesMobiles['porte_tribord']->fixe) && ($tab_BoueesMobiles['porte_tribord']->fixe==false)){
                $tab_BoueesMobiles['porte_tribord']->xs=$x1+ECART_BORDURE;
                $tab_BoueesMobiles['porte_tribord']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['porte_tribord']->color="purple";
                $tab_BoueesMobiles['porte_tribord']->fillcolor="green";                
            }             
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="PorteTribord"){
            $tab_BoueesMobiles['porte_tribord']->fixe=true;
            if (isset($tab_BoueesMobiles['porte_babord']->fixe) && ($tab_BoueesMobiles['porte_babord']->fixe==false)){
                $tab_BoueesMobiles['porte_babord']->xs=$x2-ECART_BORDURE;
                $tab_BoueesMobiles['porte_babord']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['porte_babord']->color="purple";
                $tab_BoueesMobiles['porte_babord']->fillcolor="red";                 
            }             
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DepartBabord"){
            $tab_BoueesMobiles['depart_babord']->fixe=true;
            if (isset($tab_BoueesMobiles['depart_tribord']->fixe) && ($tab_BoueesMobiles['depart_tribord']->fixe==false)){
                $tab_BoueesMobiles['depart_tribord']->xs=$x2-ECART_BORDURE;
                $tab_BoueesMobiles['depart_tribord']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['depart_tribord']->color="yellow";
                $tab_BoueesMobiles['depart_tribord']->fillcolor="green";                   
            }             
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DepartTribord"){
            $tab_BoueesMobiles['depart_tribord']->fixe=true;
            if (isset($tab_BoueesMobiles['depart_babord']->fixe) && ($tab_BoueesMobiles['depart_babord']->fixe==false)){
                $tab_BoueesMobiles['depart_babord']->xs=$x1+ECART_BORDURE;
                $tab_BoueesMobiles['depart_babord']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['depart_babord']->color="yellow";
                $tab_BoueesMobiles['depart_babord']->fillcolor="red";                   
            }             
        }        
    }

if ($debug2){    
    echo "<br><br>Bouées mobiles<br>\n";
    print_r($tab_BoueesMobiles);
}
    
    $k=0;
    foreach( $tab_BoueesMobiles as $key => $value){
        if (!isset($value->fixe)){
            //echo "<br> Creer la bouee\n";
            if ($key=="dog_leg1"){
                $ecranX=setSaisieToDisplayX($x2-ECART_BORDURE,$y2, $twd_radian);
                $ecranY=setSaisieToDisplayY($x2-ECART_BORDURE,$y2, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"lon":'.$lon.',"lat":'.$lat.',"color":"navy","fillcolor":"red"}';        
                $k++;                
            }
            else if ($key=="dog_leg2"){
                $ecranX=setSaisieToDisplayX($x1+ECART_BORDURE,$y2, $twd_radian);
                $ecranY=setSaisieToDisplayY($x1+ECART_BORDURE,$y2, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"lon":'.$lon.',"lat":'.$lat.',"color":"navy","fillcolor":"red"}';        
                $k++;                            
            }
            else if ($key=="porte_tribord"){
                $ecranX=setSaisieToDisplayX($x1+ECART_BORDURE,$y1, $twd_radian);
                $ecranY=setSaisieToDisplayY($x1+ECART_BORDURE,$y1, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"lon":'.$lon.',"lat":'.$lat.',"color":"purple","fillcolor":"green"}';        
                $k++;                            
            }        
            else if ($key=="porte_babord"){
                $ecranX=setSaisieToDisplayX($x2-ECART_BORDURE,$y1, $twd_radian);
                $ecranY=setSaisieToDisplayY($x2-ECART_BORDURE,$y1, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"lon":'.$lon.',"lat":'.$lat.',"color":"purple","fillcolor":"red"}';        
                $k++;  
            }                       
            else if ($key=="depart_tribord"){
                $ecranX=setSaisieToDisplayX($x2-ECART_BORDURE, ($y1+$y2)/2, $twd_radian);
                $ecranY=setSaisieToDisplayY($x2-ECART_BORDURE, ($y1+$y2)/2, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"lon":'.$lon.',"lat":'.$lat.',"color":"yellow","fillcolor":"green"}';        
                $k++;  
            }        
            else if ($key=="depart_babord"){
                $ecranX=setSaisieToDisplayX($x1+ECART_BORDURE, ($y1+$y2)/2, $twd_radian);
                $ecranY=setSaisieToDisplayY($x1+ECART_BORDURE, ($y1+$y2)/2, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"lon":'.$lon.',"lat":'.$lat.',"color":"yellow","fillcolor":"red"}';        
                $k++;  
            }          
        }
        else if (isset($value) && isset($value->fixe) && ($value->fixe==false) 
            && isset($value->xs) && isset($value->ys) && isset($value->color) && isset($value->fillcolor)){
                // completer 
if ($debug2){
                echo "<br> Key:".$key."<br>\n";
                print_r($value);
                echo "<br>\n";
}                                                
                $ecranX=setSaisieToDisplayX($value->xs,$value->ys, $twd_radian);
                $ecranY=setSaisieToDisplayY($value->xs,$value->ys, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"lon":'.$lon.',"lat":'.$lat.',"color":"'.$value->color.'","fillcolor":"'.$value->fillcolor.'"}';
                $k++;              
        }
    }  

    // Debug
    if ($debug2){    
        echo "<br>Liste des bouées mobiles ajoutées au parcours\n";
        for ($index=0; $index<count($boueesMobilesParcours); $index++){        
            echo "<br>".$boueesMobilesParcours[$index];
        }    
    }     
}
 
 
 
?>
