<?php
// algo.php, fichier inclus des fonction de calcul


// Algorithme de placement

// initial.php : Initialise les variables globales
//  Applique une transformation pour ramener la figure face au vent 
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

/******************************************************************************
 * Nouvel algorithme 
 * V2. On tente d'améliorer l'algorithme en introduisant une mesure simple.
 * 1) Calculer un premier rectangle 'A' et la distance 'a' de la ligne de départ au chemin déambulation des concurrents.
 * 2) Calculer un second rectangle en démarrant depuis le milieu vertical du précédant
 * 3) etc.
 * 4) Classer les distances de minimum au maximum.
 * 5) Sélectionner le rectangle le plus proche
 * 
 * ****************************************************************************/


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

    $xPasse1=-100000000; // les abscisses recherchées pour le placement des bouées
    $xPasse2=100000000;
    $yMaxPasse1=array();    // Les ordonnées des droites déterminant le rectangle de placement des bouées 
    $yMaxPasse2=array();

//-------------------------------------
function tab_echange(&$tab1, &$tab2){
// echange les contenus d'un tableau de deux éléments
    $aux=$tab1[0];
    $tab1[0]=$tab2[0];
    $tab2[0]=$aux;
    $aux=$tab1[1];
    $tab1[1]=$tab2[1];
    $tab2[1]=$aux;    
}

// Calcule la distance minimale entre les sommets de la ligne des concurrents 
// et le centre de la ligne de départ du rectangle considéré
// ------------------------------------
function calculeDistancesDepartConcurrents($xouest, $xest, $ysud, $ynord, $debug){

global $ligne_xsaisie; // Tableau des coordonnées écran de la ligne de déambulation des concurrents après rotation face au vent
global $ligne_ysaisie;
    $tab_distances=array();
     // Milieu Départ
    $xp = round(($xouest+$xest) / 2.0);
    // Au premier tiers 
    $yp = round((2*$ysud+$ynord) / 3.0);
    
    $k=0;
    $index=0;
    while ($index<count($ligne_ysaisie)){
        $xc=$ligne_xsaisie[$index];
        $yc=$ligne_ysaisie[$index];
        $tab_distances[$k]= sqrt(($xp-$xc)*($xp-$xc)+($yp-$yc)*($yp-$yc));  
        if ($debug&& false) {
            echo "<br>Sommet ".$index.": [".$xc.",".$yc."] Distance ".$tab_distances[$k]."<br>\n";
        }
                                     
        $k++;          
        $index++;
    }  
    
    // Retourner la valeur min
    $distancemin=10000000;
    if (count($tab_distances)>0){        
        for ($k=0; $k<count($tab_distances); $k++){
            if ($tab_distances[$k]<$distancemin){
                $distancemin=$tab_distances[$k];
            }
        }
    } 
    if ($debug) {
            echo "<br>Départ (X,Y) : [".$xp.",".$yp."] Distance : ".$distancemin."\n";
    }

    return $distancemin;     
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
        return (($x-$x1)*($y2-$y1)/($x2-$x1) + $y1);
    }   
}

/***************************
 * Recherche un rectangle vertical dont les dimensions sont suffisantes pour placer un parcours
 ***************************/
 
// On balaye le polygone avec des droite verticale pour trouver un rectangle de navigation 
// suffisemment haut et large 
// Données en input : increment de progression, positions x initiale et finale, sens de progression
// Donnees en output : ($xouest, $xest, $ysud, $ynord)

// ------------------------------------------
function rechercher_rectangle_utile($incrementX, $xInitial, $xFinal, $sensprogression){
global $debug2;
global $twd_degre;
global $twd_radian;
global $xouest;
global $xest;
global $ysud;
global $ynord; 

global $exitLonLat; // Pour debug

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
global $ecartBordure; // Deux mètres pour éviter de taper la berge
global $deltaBordure;

if ($debug2){
    echo "<br>/*********************************/\n";
    echo "<br> RECHERCHE RECTANGLE\n";    
    echo "<br>/*********************************/\n";
    echo "<br><br>IncrementX: ".$incrementX.", xInitial: ". $xInitial.", xFinal: ".$xFinal.", sensprogression: ". $sensprogression."\n";
    echo "<br><br><b>Première passe</b>\n";       
}


/**************************************
 *     Première passe
 **************************************/
    $numZoneExploree=0; // On a besoin de 2 zones contigües pour placer les bouées
      
    $x=$xInitial; // Démarrer la recherche à 0 mètres du point de départ de la recherche
    
    

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
    $xTrouve=$x;    // Abscisse de la droite verticale prise en considération pour le second côté du rectangle
    $yMaxPasse1[0]=1000000; // Enregistre les minima et maxima de l'intersection de la droite verticale avec le polygone  
    $yMaxPasse1[1]=-1000000;

    $nbtests=0;
    while ($encore) { // On cherche les intersections avec le polygone
        if ($sensprogression < 0){
            $encore = ($x>=$xFinal);   
            if ($debug2){
                echo "<br>SORTIE BOUCLE quand X:$x < XFinal:$xFinal\n";                                         
            } 
        }
        else{
            $encore = ($x<=$xFinal);   
            if ($debug2){
                echo "<br>SORTIE BOUCLE quand X:$x > XFinal:$xFinal\n";                                         
            }         
        }    
        $nbtests++;
    
        $nbintersections=0;           
        $tab_Intersections=array(); // Les valeurs y d'intersection
        if ($debug2){    
            echo "<br>Exploration XInitial:".$xInitial.", x traité:".$x."\n";
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
   
            if (($x>min($xp1, $xp2)) && ($x<max($xp1, $xp2))) { // Intersection possible 
                if ($debug2 && false){  
                    echo "<br>Intersection verticale avec le segment de Sommet ".$i.": [".$xp1.",".$yp1."] et de Sommet ".$i2.": [".$xp2.",".$yp2."]\n";            
                }              
                $tab_Intersections[$nbintersections]=intersectionVerticale($x,$xp1,$yp1,$xp2,$yp2); 
                $nbintersections++;
            }
        }

        if ($debug2){    
            echo "<br>".$nbintersections." intersections.\n";
            //echo "<br>Table des intersections<br>\n";
            //print_r($tab_Intersections);
            //echo "<br>\n";
        }
    
        switch ($nbintersections) {
        case 0 : break; // Sortie du polygone
        case 1 : break; // Sommet, on ne traite pas
        case 2 :  // Intérieur. Calculer la dimension verticale entre deux intersections
            $distanceVerticale =abs($tab_Intersections[1]-$tab_Intersections[0]);
            //$deltaXpixelsSite=howMuchXPixelsForMeters(20.0);
            //$deltaYpixelsCinquanteMetres=howMuchYPixelsForMeters(50.0);
            if ($distanceVerticale >= $deltaYpixelsSite){
                // On a une droite verticale candidate
                if ($distanceVerticale>$maxDistanceV){
                    $maxDistanceV=$distanceVerticale; 
                    $indexMax=$i;
                    $xTrouve=$x;
                    $distanceH=abs($xInitial-$x);                
                }
                if ($debug2 && false){    
                    echo "<br>2 intersections<br>Distance verticale : ".$distanceVerticale." Distance horizontale(X0,X) : ".$distanceH."\n";
                }                
                $numZoneExploree++;
                $xPasse1=$x;    // Droite verticale trouvée
                $yMaxPasse1[0]=$tab_Intersections[0];
                $yMaxPasse1[1]=$tab_Intersections[1];                 
                $encore=false; // Traitement  1ère passe terminé
            }
            
            break; 
        case 3 : // // polygone concave + passage par un sommet ; il faut faire un pas vers l'Est (ou vers l'Ouest)
            if ($debug2 && false){    
                echo "<br>3 intersections : on est sur un sommet. <br> On se décale d'un pas\n";
            }                        
            $x=$x+$incrementX; // On se décale d'un pas
            break; 
        default : // polygone convexe avec au moins une concavité selon l'axe Nord / Sud
                // On traite les deux premiers couples
                // Ordonner les Y dans le sens croissant
            if ($debug2 && false){    
                echo "<br>4 intersections ou plus : on ne conserve que le plus favorables des deux premiers couples\n";
            }                        
                
            if (sort($tab_Intersections)){
                $distanceVerticale = abs($tab_Intersections[1]-$tab_Intersections[0]);
                $distanceVerticale2 =  abs($tab_Intersections[3]-$tab_Intersections[2]);  

                if (($distanceVerticale>$maxDistanceV) || ($distanceVerticale2>$maxDistanceV)){
                    $maxDistanceV=max($distanceVerticale, $distanceVerticale2); 
                    $indexMax=$i;
                    $xTrouve=$x;
                    $distanceH=abs($xInitial-$x);                
                }
                            
                if (($distanceVerticale >= $distanceVerticale2) && ($distanceVerticale >= $deltaYpixelsSite)){
                    // On a une droite verticale candidate
                    // Passer à la recherche de la seconde droite
                    if ($debug2){    
                        echo "<br>2 intersections<br>Distance verticale : ".$distanceVerticale." Distance horizontale(X0,X) : ".$distanceH."\n";
                    }                

                    $numZoneExploree++; 
                    $xPasse1=$x;    // Droite verticale trouvée   
                    $yMaxPasse1[0]=$tab_Intersections[0];
                    $yMaxPasse1[1]=$tab_Intersections[1];                
                    $encore=false;                        
                }
                else{
                    if ($distanceVerticale2 >= $deltaYpixelsSite){
                        // On a une droite verticale candidate
                        // Passer à la recherche de la seconde droite
                        if ($debug2 && false){    
                            echo "<br>2 intersections<br>Distance verticale : ".$distanceVerticale." Distance horizontale(X0,X) : ".$distanceH."\n";
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
//     $xPasse1=-100000000; // les abscisses recherchées pour le placement des bouées
//    $xPasse2=100000000;
    if ($xPasse1>-100000000){
        if ($debug2){   
        echo "<br> NbTests:".$nbtests."\n"; 
            echo "<br><b>Fin de la premère passe</b>.<br>Distance verticale maximale: ".$maxDistanceV." (Y0:".$yMaxPasse1[0].", Y1:".$yMaxPasse1[1].")<br>\n";
            echo "<br>Abscisse de la droite verticale : ".$xPasse1."\n";
            echo "<br>Distance  au point de départ horizontal: ".$distanceH."\n";
        }
    }
    else{
        // Echec
        echo '{"OK":0}';
        // exit;
        return false;
    }

/***************************************
 * Seconde passe
 * *************************************/

    // On veut trouver la plus proche droite verticale éloignée d'au moins Dix mètres de $xpasse1

    // Démarrer la recherche immédiatement à l'endroit où on est sorti
    $x=$xPasse1+$incrementX;

if ($debug2){
    echo "<br><br><b>Deuxième passe</b>\n";       
    echo "<br><br>IncrementX: ".$incrementX.", xInitial Passe 2: ". $xPasse1.", xFinal: ".$xFinal.", sensprogression: ". $sensprogression."\n";

}
    if ($debug2){
        echo "<br><br><b>Début de la seconde passe</b>";    
        echo "<br>Progression ".$sensprogression." Increment : ".$incrementX."\n";
        echo "<br>Abscisse de la droite verticale trouvée à la passe N°1 : ".$xPasse1."\n";    // Première droite verticale trouvée
        echo "<br>Valeurs de départ de l'exploration (xInitial:".$xPasse1." x:".$x.")<br>\n";
    }

    // Droite d'équation x=constante
    // Tant que x<$posxFin

    $encore=true;    
    $distanceHPasse1=abs($xPasse1-$x);    // Ecart horizontal avec la droite verticale trouvée à la première passe 
    $distanceH=0;    // Ecart horizontal entre deux droites verticales
    $maxDistanceV=0;    // Maximas sur l'écart vertical entre deux points du polygone de navigation
    $indexMax=0;
    $yMaxPasse2[0]=1000000;
    $yMaxPasse2[1]=-1000000;
    $nbtests=0;
    
    while ($encore) { // On cherche les intersections avec le polygone
       if ($sensprogression < 0){
            $encore = ($x>=$xFinal);   
            if ($debug2){
                echo "<br>SORTIE BOUCLE quand $x < $xFinal\n";                                         
            } 
        }
        else{
            $encore = ($x<=$xFinal);   
            if ($debug2){
                echo "<br>SORTIE BOUCLE quand $x > $xFinal\n";                                         
            }         
        }    
        $nbtests++;
    
        $nbintersections=0;           
        $tab_Intersections=array(); // Les valeurs y d'intersection
        if ($debug2){    
            echo "<br>Exploration x:".$x."\n";
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
            if ($debug2){  
                echo "<br>Sommet ".$i.": [".$xp1.",".$yp1."]\n";
                echo " Sommet ".$i2.": [".$xp2.",".$yp2."]\n";            
            } 
      
            if (($x>min($xp1, $xp2)) && ($x<max($xp1, $xp2))) { // Intersection possible 
                $tab_Intersections[$nbintersections]=intersectionVerticale($x,$xp1,$yp1,$xp2,$yp2); 
                $nbintersections++;
            }
        }

        if ($debug2){    
            echo "<br>".$nbintersections." intersections.\n";
            //echo "<br>Table des intersections<br>\n";
            //print_r($tab_Intersections);
            //echo "<br>\n";
        }
    
        switch ($nbintersections) {
            case 0 : break; // Sortie du polygone
            case 1 : break; // Sommet, on ne traite pas
            case 2 :  // Calculer la dimension verticale entre deux intersections
                $distanceVerticale =abs($tab_Intersections[1]-$tab_Intersections[0]);
                if ($distanceVerticale>$maxDistanceV){
                    $maxDistanceV=$distanceVerticale; 
                    $indexMax=$i;
                    $xTrouve=$x;            
                }
                $distanceHPasse1=abs($xPasse1-$x); 
                if ($distanceVerticale >= $deltaYpixelsSite){
                    // On a une droite candidate
                    if ($debug2 && false){    
                        echo "<br>2 intersections\n";              
                        echo "<br>Distance verticale: ".$distanceVerticale."\n";
                        echo "<br><i>Distance à la droite fournie par la passe N°1</i> : <b>".$distanceHPasse1."</b>\n";
                        echo "<br>On veut un écart horizontal minimal de ".$deltaXpixelsSite." pixels\n";                    
                    }

                    $numZoneExploree++;
                    if ($distanceHPasse1 >= $deltaXpixelsSite){
                        $encore=false;
                        $xPasse2=$x;
                        $yMaxPasse2[0]=$tab_Intersections[0];
                        $yMaxPasse2[1]=$tab_Intersections[1];                    
                    } 
                    else{
                        if ($debug2 && false){    
                             echo "<br><i>Distance horizontale trop faible ! </i>\n";                    
                        }                    
                    }             
                }
            
                break; // Intérieur
            case 3 : // polygone concave, passage par un sommet concave; il faut faire un pas de plus
                if ($debug2 && false){    
                    echo "<br>3 intersections : on est sur un sommet. <br> On se décale d'un pas\n";
                }                        
                $x=$x+$incrementX; // On se décale d'un pas
            break; 
        default : // polygone convexe avec au moins une concavité selon l'axe Nord / Sud
                // On traite les deux premiers couples
                // Ordonner les Y dans le sens croissant
            if ($debug2 && false){    
                echo "<br>4 intersections ou plus : on ne conserve que le plus favorables des deux premiers couples\n";
            }                        
                                
            if (sort($tab_Intersections)){
                $distanceVerticale = abs($tab_Intersections[1]-$tab_Intersections[0]);
                $distanceVerticale2 =  abs($tab_Intersections[3]-$tab_Intersections[2]);  

                if (($distanceVerticale>$maxDistanceV) || ($distanceVerticale2>$maxDistanceV)){
                    $maxDistanceV=max($distanceVerticale, $distanceVerticale2); 
                    $indexMax=$i;
                    $xTrouve=$x; 
                }
                $distanceHPasse1=abs($xPasse1-$x);               
                            
                if (($distanceVerticale >= $distanceVerticale2) && ($distanceVerticale >= $deltaYpixelsSite)){
                    // On a un rectangle candidat
                    // Passer au placement des bouées
                    if ($debug2 && false){    
                        echo "<br>Distance verticale : ".$distanceVerticale."<br>Distance horizontale avec l'abscisse de la passe 1 : ".$distanceHPasse1."\n";
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
                        if ($debug2 && false){    
                            echo "<br>Distance verticale : ".$distanceVerticale."<br>Distance horizontale avec l'abscisse de la passe 1 : ".$distanceHPasse1."\n";
                            echo "<br>Distance horizontale avec le début de la recherche de la seconde passe:".$distanceH."\n";
                        }
                        
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
        echo "<br> NbTests:".$nbtests."\n"; 
        echo "<br><b>Deuxième passe</b>.<br>Distance verticale maximale: ".$maxDistanceV."<br>Distance  horizontale: ".$distanceHPasse1." Y0:".$yMaxPasse2[0]." Y1:".$yMaxPasse2[1];
        echo "<br>xPasse1: ".$xPasse1." xPasse2:".$xPasse2."\n";
        echo "<br>Distance horizontale :".abs($xPasse1-$xPasse2)."\n";
    }
    
    if (($distanceHPasse1>= $deltaXpixelsSite) && ($maxDistanceV>=$deltaYpixelsSite)){
        $distanceX=abs($xPasse2-$xPasse1);
        $distanceY=min(abs($yMaxPasse1[0]-$yMaxPasse1[1]),abs($yMaxPasse2[0]-$yMaxPasse2[1]));
    
        if ($debug2){
            echo "<br><b>Succès APPARENT</b>\n";
            echo "<br><br>Recherche d'un rectangle inclus\n";    
            echo "<br><i>Droite verticale initiale x=".$xPasse1."</i>\n";
            echo "<br><i>Droite verticale finale x=".$xPasse2."</i>\n";
            echo "<br><i>Droite horizontale initiale inférieure y=".$yMaxPasse1[0]."</i>\n";
            echo "<br><i>Droite horizontale initiale supérieure y=".$yMaxPasse1[1]."</i>\n";        
            echo "<br><b>Distance initiale verticale DY1:</b><i>".abs($yMaxPasse1[1]-$yMaxPasse1[0])."</i>\n";
            echo "<br><i>Droite horizontale finale inférieure y=".$yMaxPasse2[0]."</i>\n";
            echo "<br><i>Droite horizontale finale supérieure y=".$yMaxPasse2[1]."</i>\n";      
            echo "<br><b>Distance finale verticale DY2:</b><i>".abs($yMaxPasse2[1]-$yMaxPasse2[0])."</i>\n";           
            echo "<br><b>Longueur verticale :</b> <i>".$distanceY."</i> <b>Largeur horizontale:</b> <i>".$distanceX."</i>\n";    
            echo "<br />\n";
        }
    }
    else{
        // Echec
        echo '{"OK":0}';
        return false;
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
        echo "<br>DeltaXpixels =".$deltaXpixelsSite." DeltaXpixelsSite=".$deltaXpixelsSite."\n";
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
    
   
    if (abs($xMaxPasse-$xMinPasse)>$deltaXpixelsSite){
        // Réduire l'ampleur de la zone
        $xmax=$xMinPasse+$deltaXpixelsSite;        
    }


    if (abs($maxY-$minY)>$deltaXpixelsSite){
        // Réduire l'ampleur de la zone
        $ymax=$minY+$deltaYpixelsSite;         
    }    
    
    

if ($debug2){
    echo "<br><br><b>Avant préparation</b> \n";
    echo ($xMinPasse.", ".$xMaxPasse.", ".$minY.", ".$maxY."\n");
    echo "<br>TWD_degre ".$twd_degre."\n";
    echo "<br>TWD_radian ".$twd_radian."\n";    
}    

        $xouest=$xMinPasse;
        $xest=$xMaxPasse;
        $ysud=$minY;
        $ynord=$maxY;   
        
        // Coordonnées géographiques du rectangle où sont placées les bouées
        rectangleScreenToWorld($xouest,$xest,$ysud,$ynord);
    
if ($debug2){
    echo "<br><br><b>Fin de recherche_rectangle</b> ::Données en sortie\n";
    echo ("Ouest:".$xouest.", Est:".$xest.", Sud: ".$ysud.", Nord: ".$ynord."\n");
    echo "<br>\n";
}    

    return true;
}


// Modifie $exitLonLat
// ------------------------
function rectangleScreenToWorld(){
// Coordonnées du rectangle où sont placées les bouées
global $exitLonLat; 
global $xouest;
global $xest;
global $ysud;
global $ynord;     
global $twd_radian;
    $exitLonLat=array();
    $exitLonLat[0]=new stdClass();
    $exitLonLat[0]->lon=get_lon_Xecran(setSaisieToDisplayX($xouest,$ysud, $twd_radian));
    $exitLonLat[0]->lat=get_lat_Yecran(setSaisieToDisplayY($xouest,$ysud, $twd_radian));
    $exitLonLat[1]=new stdClass();
    $exitLonLat[1]->lon=get_lon_Xecran(setSaisieToDisplayX($xouest,$ynord, $twd_radian));
    $exitLonLat[1]->lat=get_lat_Yecran(setSaisieToDisplayY($xouest,$ynord, $twd_radian));
    $exitLonLat[2]=new stdClass();
    $exitLonLat[2]->lon=get_lon_Xecran(setSaisieToDisplayX($xest,$ynord, $twd_radian));
    $exitLonLat[2]->lat=get_lat_Yecran(setSaisieToDisplayY($xest,$ynord, $twd_radian));
    $exitLonLat[3]=new stdClass();
    $exitLonLat[3]->lon=get_lon_Xecran(setSaisieToDisplayX($xest,$ysud, $twd_radian));
    $exitLonLat[3]->lat=get_lat_Yecran(setSaisieToDisplayY($xest,$ysud, $twd_radian));
}

// Placement des bouées dans le rectangle ad hoc
// ---------------------------------
function placer_bouees($xouest, $xest, $ysud, $ynord){
global $yDepart;
global $ecartBordure; // Deux mètres pour éviter de taper la berge
global $deltaBordure; // Nb de pixels équivalent à 2 mètres
global $debug3; 
global $twd_degre;
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

$porte_tribord=false;
$porte_babord=false;
$depart_tribord=false;
$depart_babord=false;
$dog_leg1=false;
$dog_leg2=false;
$name="";
$color= "";
$fillcolor= "";
    
$quartHauteur=abs($ynord-$ysud) / 6; // On resserre les distances
$demiLargeur=abs($xouest-$xest) / 4;

$nboueesFixes= 6-$nbouees;

$balisesIn=array(); // Tableau des balises contenues dans le rectangle utile

//$yDepart = ($ysud+$ynord)/2;

$yDepart = (2*$ysud+$ynord)/3;


if ($debug3){
    echo "<br><br><b>placer_bouees</b> ::Données en entrée\n";
    echo ($xouest.", ".$xest.", ".$ysud.", ".$ynord."\n");
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


    // Commencer par calculer le rectangle utile      
if ($debug3){   
    if ($nboueesFixes>0){
        echo "<br><b>Placement de ".$nbouees." bouées autonomes et de ".$nboueesFixes." bouées fixes.</b>\n";
    }
    else{
        echo "<br><b>Placement de ".$nbouees." bouées autonomes</b>\n";        
    }
    echo "<br>Droite verticale X1 Ouest: ".$xouest."<br>Droite verticale X2 Est: ".$xest."\n";  
    echo "<br>Droites horizontales Y1 Sud: ".$ysud."\n";
    echo "<br>Droites horizontales Y2 Nord: ".$ynord."\n";
   
    echo "<br><b>Rectangle utile</b>\n";  
    echo "<br> Y1 Sud: ".$ysud." Y2 Nord: ".$ynord."\n";
    echo "<br>Hauteur: ".abs($ynord-$ysud)." pixels ==  ".distanceEcran2Earth(0,$ysud,0,$ynord)." mètres\n";
    echo "<br>Largeur: ".abs($xouest-$xest)." pixels ==  ".distanceEcran2Earth($xouest,0,$xest,0)." mètres\n";
    echo "<br>Coordonnées du rectangle utile<br>Point inférieur gauche (".$xouest.",".$ysud."), Point supérieur droit (".$xest.",".$ynord.")\n";
}    
    // Recherche des bouées fixes incluses dans ce rectangle

if ($debug3){    
    echo "<br>Affichage des Balises fixes incluses dans le rectangle utile<br>\n<table border=\"1\"><tr><td>Id</td>\n"; 
    for ($index=0; $index<count($balisesEcran); $index++){
        echo "<td>".$balisesEcran[$index]->id."</td>";
    }
    echo "</tr><tr><td>Nom</td>\n";
    for ($index=0; $index<count($balisesEcran); $index++){
        echo "<td>".$balisesEcran[$index]->name."</td>";
    }
    echo "</tr><tr><td>X</td>\n";
    for ($index=0; $index<count($balises_xsaisie); $index++){
        if (($balises_xsaisie[$index]>=$xouest) && ($balises_xsaisie[$index]<$xest)){
            echo "<td bgcolor=\"yellow\">".$balises_xsaisie[$index]."</td>";            
        }
        else{ 
            echo "<td>".$balises_xsaisie[$index]."</td>";
        }    
    }
    echo "</tr><tr><td>Y</td>\n";
    for ($index=0; $index<count($balises_ysaisie); $index++){
        if (($balises_ysaisie[$index]>=$ysud) && ($balises_ysaisie[$index]<$ynord)){
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
        if (($balises_xsaisie[$index]>=$xouest) && ($balises_xsaisie[$index]<$xest)
            && ($balises_ysaisie[$index]>=$ysud) && ($balises_ysaisie[$index]<$ynord)){
            $balisesIn[$k]=$index;            
            $k++;
        }
    }
    
    
if ($debug3){    
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
    // On va changer de méthode et comparer les distances
    
    $k=0; 
    for ($index=0; $index<count($balisesIn); $index++){
        if (distanceVEcran($balises_ysaisie[$balisesIn[$index]], $ysud) < $quartHauteur){ // Porte sous le vent
            if (distanceHEcran($balises_xsaisie[$balisesIn[$index]],$xouest) < $demiLargeur){
                // Porte tribord 
                if ($debug3){ echo "<br>Porte sous le vent tribord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}
                $porte_tribord=true;
            }
            else if (distanceHEcran($balises_xsaisie[$balisesIn[$index]],$xest) < $demiLargeur){
                // Porte bâbord
                if ($debug3){echo "<br>Porte sous le vent bâbord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}                 
                $porte_babord=true;
            } 
        } 
        else if (distanceVEcran($balises_ysaisie[$balisesIn[$index]],$ynord) < $quartHauteur){ // Dog Leg ?
            if (distanceHEcran($balises_xsaisie[$balisesIn[$index]],$xouest) < $demiLargeur){
                // Dog leg bâbord 2
                if ($debug3){ echo "<br>Dog leg N°2 bâbord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}
                $dog_leg2=true;                 
            }
            else if (distanceHEcran($balises_xsaisie[$balisesIn[$index]],$xest) < $demiLargeur){
                // Dog leg bâbord 1
                if ($debug3){ echo "<br>Dog leg N°1 bâbord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}                 
                $dog_leg1=true;                 
            }         
        }
        else {
            // Entre deux : Départ ?
            if (distanceVEcran($balises_xsaisie[$balisesIn[$index]], $yDepart) < $deltaBordure){ // Départ ?
                if (distanceHEcran($balises_xsaisie[$balisesIn[$index]],$xouest) < $deltaBordure){
                    // Départ / Arrivée bâbord
                    if ($debug3){echo "<br>Arrivée / Départ bâbord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}                                 
                    $depart_babord=true; 
                }
                else if (distanceHEcran($balises_xsaisie[$balisesIn[$index]],$xest) < $deltaBordure){    
                    // Départ / Arrivée tribord
                    if ($debug3){echo "<br>Arrivée / Départ tribord:".$balisesIn[$index]." X:".$balises_xsaisie[$balisesIn[$index]]." Y:".$balises_ysaisie[$balisesIn[$index]]."\n";}                                 
                    $depart_tribord=true; 
                }                                
            }
        }

if ($debug3){        
        echo "<br>Distance au bord gauche ".abs($balises_xsaisie[$balisesIn[$index]]-$xouest)."\n";
        echo "<br>Distance au bord droit ".abs($balises_xsaisie[$balisesIn[$index]]-$xest)."\n";
        echo "<br>Distance au bord supérieur ".abs($balises_ysaisie[$balisesIn[$index]]-$ynord)."\n";
        echo "<br>Distance au bord inférieur ".abs($balises_ysaisie[$balisesIn[$index]]-$ysud)."<br>\n";   
}        
        if ($porte_tribord){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$xouest)<$deltaBordure){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"PorteTribord"}', false);
                $k++;
            }        
        }
        if ($porte_babord){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$xest)<$deltaBordure){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"PorteBabord"}', false);
                $k++;
            }        
        }
        // Depart / Arrivée
        if ($depart_tribord){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$xest)<$deltaBordure){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"DepartTribord"}', false);
                $k++;
            }        
        }
        if ($depart_babord){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$xouest)<$deltaBordure){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"DepartBabord"}', false);
                $k++;
            }        
        }
        // Dog Leg bâbord
        if ($dog_leg1){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$xest)<$deltaBordure){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"DogLeg1"}', false);
                $k++;
            }        
        }
        if ($dog_leg2){
            if (abs($balises_xsaisie[$balisesIn[$index]]-$xouest)<$deltaBordure){
                $tableBoueesFixesSaisieParcours[$k]=json_decode('{"id":'.$balisesEcran[$balisesIn[$index]]->id.',"xs":'.$balises_xsaisie[$balisesIn[$index]].',"ys":'.$balises_ysaisie[$balisesIn[$index]].',"name":"'.$balisesEcran[$balisesIn[$index]]->name.'","franchissement":"DogLeg2"}', false);
                $k++;
            }        
        }            
    } 
    
    // Afficher les balises retenues
if ($debug3){
    echo "<br>Balises retenues\n";
    for ($index=0; $index<count($tableBoueesFixesSaisieParcours); $index++){
        echo "<br>".json_encode($tableBoueesFixesSaisieParcours[$index])."\n";
    }  
}    



    // Convertir ces balises en Longitude, Latitude
    // {"site":"LePlessis","twd":60,"boueesfixes":[{"boueefixe":true,"id":4,"lon":-1.4743316798018502,"lat":47.24381872961287,"color":"yellow","fillcolor":"red"},{"boueefixe":true,"id":7,"lon":-1.4739890647643559,"lat":47.24395125770921,"color":"black","fillcolor":"green"},{"boueefixe":true,"id":6,"lon":-1.4737391242861835,"lat":47.24383766219806,"color":"black","fillcolor":"green"},{"boueefixe":true,"id":10,"lon":-1.4736127498871077,"lat":47.24355156979959,"color":"black","fillcolor":"green"},{"boueefixe":true,"id":8,"lon":-1.4739385150047255,"lat":47.24372196306633,"color":"black","fillcolor":"green"},{"boueefixe":true,"id":6,"lon":-1.4737391242861835,"lat":47.24383766219806,"color":"blue","fillcolor":"red"}],"boueesmobiles":[{"boueefixe":false,"id":0,"lon":-1.4742839383621993,"lat":47.24373879203094,"color":"yellow","fillcolor":"green"},{"boueefixe":false,"id":1,"lon":-1.474222155322651,"lat":47.24367147617248,"color":"purple","fillcolor":"green"},{"boueefixe":false,"id":2,"lon":-1.4741659889230618,"lat":47.24359995307286,"color":"purple","fillcolor":"red"},{"boueefixe":false,"id":3,"lon":-1.4737700158059577,"lat":47.24388183823017,"color":"blue","fillcolor":"red"}]}
    
    for ($index=0; $index<count($tableBoueesFixesSaisieParcours); $index++){
        // {"boueefixe":true,"id":4,"lon":-1.4743316798018502,"lat":47.24381872961287,"color":"yellow","fillcolor":"red"}
        if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DogLeg1"){
            $color= "navy";
            $fillcolor= "red";
            $name="Dog leg1";
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DogLeg2"){       
            $color= "navy";
            $fillcolor= "red";
            $name="Dog leg2";
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="PorteBabord"){
            $color= "purple";
            $fillcolor= "red";
            $name="Porte bâbord";
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="PorteTribord"){
            $color= "purple";
            $fillcolor= "green";
            $name="Porte tribord";
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DepartBabord"){
            $color= "yellow";
            $fillcolor= "red";
            $name="Départ bâbord";
        }
        else {
            $color= "yellow";
            $fillcolor= "green";
            $name="Départ tribord";
        }
        // repasser en coordonnées écran
        $ecranX=setSaisieToDisplayX($tableBoueesFixesSaisieParcours[$index]->xs, $tableBoueesFixesSaisieParcours[$index]->ys, $twd_radian);
        $ecranY=setSaisieToDisplayY($tableBoueesFixesSaisieParcours[$index]->xs, $tableBoueesFixesSaisieParcours[$index]->ys, $twd_radian);
        // repasser en coordonnées géographiques
        $lon=get_lon_Xecran($ecranX);
        $lat=get_lat_Yecran($ecranY);
        // Formater la sortie
        $boueesFixesParcours[$index]='{"boueefixe":true,"id":'.$tableBoueesFixesSaisieParcours[$index]->id.',"name":"'.$name.'","lon":'.$lon.',"lat":'.$lat.',"color":"'.$color.'","fillcolor":"'.$fillcolor.'"}';        
    }  
    // Debug
if ($debug3){
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
                $tab_BoueesMobiles['dog_leg2']->xs=$xouest+$deltaBordure;
                $tab_BoueesMobiles['dog_leg2']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['dog_leg2']->color="navy";
                $tab_BoueesMobiles['dog_leg2']->fillcolor="red";
                $tab_BoueesMobiles['dog_leg2']->name="Dog leg2"; 
            }     
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DogLeg2"){
            $tab_BoueesMobiles['dog_leg2']->fixe=true;
            if (isset($tab_BoueesMobiles['dog_leg1']->fixe) && ($tab_BoueesMobiles['dog_leg1']->fixe==false)){
                $tab_BoueesMobiles['dog_leg1']->xs=$xest-$deltaBordure;
                $tab_BoueesMobiles['dog_leg1']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['dog_leg1']->color="navy";
                $tab_BoueesMobiles['dog_leg1']->fillcolor="red";
                $tab_BoueesMobiles['dog_leg1']->name="Dog leg1";  
            }                  
        }        
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="PorteBabord"){
            $tab_BoueesMobiles['porte_babord']->fixe=true;
            if (isset($tab_BoueesMobiles['porte_tribord']->fixe) && ($tab_BoueesMobiles['porte_tribord']->fixe==false)){
                $tab_BoueesMobiles['porte_tribord']->xs=$xouest+$deltaBordure;
                $tab_BoueesMobiles['porte_tribord']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['porte_tribord']->color="purple";
                $tab_BoueesMobiles['porte_tribord']->fillcolor="green";  
                $tab_BoueesMobiles['porte_tribord']->name="Porte tribord";                
            }             
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="PorteTribord"){
            $tab_BoueesMobiles['porte_tribord']->fixe=true;
            if (isset($tab_BoueesMobiles['porte_babord']->fixe) && ($tab_BoueesMobiles['porte_babord']->fixe==false)){
                $tab_BoueesMobiles['porte_babord']->xs=$xest-$deltaBordure;
                $tab_BoueesMobiles['porte_babord']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['porte_babord']->color="purple";
                $tab_BoueesMobiles['porte_babord']->fillcolor="red"; 
                $tab_BoueesMobiles['porte_babord']->name="Porte bâbord";                
            }             
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DepartBabord"){
            $tab_BoueesMobiles['depart_babord']->fixe=true;
            if (isset($tab_BoueesMobiles['depart_tribord']->fixe) && ($tab_BoueesMobiles['depart_tribord']->fixe==false)){
                $tab_BoueesMobiles['depart_tribord']->xs=$xest-$deltaBordure;
                $tab_BoueesMobiles['depart_tribord']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['depart_tribord']->color="yellow";
                $tab_BoueesMobiles['depart_tribord']->fillcolor="green";
                $tab_BoueesMobiles['depart_tribord']->name="Départ tribord";                   
            }             
        }
        else if ($tableBoueesFixesSaisieParcours[$index]->franchissement=="DepartTribord"){
            $tab_BoueesMobiles['depart_tribord']->fixe=true;
            if (isset($tab_BoueesMobiles['depart_babord']->fixe) && ($tab_BoueesMobiles['depart_babord']->fixe==false)){
                $tab_BoueesMobiles['depart_babord']->xs=$xouest+$deltaBordure;
                $tab_BoueesMobiles['depart_babord']->ys=$tableBoueesFixesSaisieParcours[$index]->ys;
                $tab_BoueesMobiles['depart_babord']->color="yellow";
                $tab_BoueesMobiles['depart_babord']->fillcolor="red";
                $tab_BoueesMobiles['depart_babord']->name="Départ bâbord";                   
            }             
        }        
    }

if ($debug3){    
    echo "<br><br>Bouées mobiles<br>\n";
    print_r($tab_BoueesMobiles);
}
    
    /***************************************************
     * 
     *  Placement des bouées mobiles
     * 
     * *************************************************/
    $k=0;
    foreach( $tab_BoueesMobiles as $key => $value){
        if (!isset($value->fixe)){
            //echo "<br> Creer la bouee\n";
            if ($key=="dog_leg1"){
                $ecranX=setSaisieToDisplayX($xest-$deltaBordure,$ynord-$deltaBordure, $twd_radian);
                $ecranY=setSaisieToDisplayY($xest-$deltaBordure,$ynord-$deltaBordure, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"name":"Dog leg1","lon":'.$lon.',"lat":'.$lat.',"color":"navy","fillcolor":"red"}';        
                $k++;                
            }
            else if ($key=="dog_leg2"){
                $ecranX=setSaisieToDisplayX($xouest+$deltaBordure,$ynord-$deltaBordure, $twd_radian);
                $ecranY=setSaisieToDisplayY($xouest+$deltaBordure,$ynord-$deltaBordure, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"name":"Dog leg2","lon":'.$lon.',"lat":'.$lat.',"color":"navy","fillcolor":"red"}';        
                $k++;                            
            }
            else if ($key=="porte_tribord"){
                $ecranX=setSaisieToDisplayX($xouest+$deltaBordure,$ysud+$deltaBordure, $twd_radian);
                $ecranY=setSaisieToDisplayY($xouest+$deltaBordure,$ysud+$deltaBordure, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"name":"Porte tribord","lon":'.$lon.',"lat":'.$lat.',"color":"purple","fillcolor":"green"}';        
                $k++;                            
            }        
            else if ($key=="porte_babord"){
                $ecranX=setSaisieToDisplayX($xest-$deltaBordure,$ysud+$deltaBordure, $twd_radian);
                $ecranY=setSaisieToDisplayY($xest-$deltaBordure,$ysud+$deltaBordure, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"name":"Porte bâbord","lon":'.$lon.',"lat":'.$lat.',"color":"purple","fillcolor":"red"}';        
                $k++;  
            }                       
            else if ($key=="depart_tribord"){
                $ecranX=setSaisieToDisplayX($xest-$deltaBordure, $yDepart, $twd_radian);
                $ecranY=setSaisieToDisplayY($xest-$deltaBordure, $yDepart, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"name":"Départ tribord","lon":'.$lon.',"lat":'.$lat.',"color":"yellow","fillcolor":"green"}';        
                $k++;  
            }        
            else if ($key=="depart_babord"){
                $ecranX=setSaisieToDisplayX($xouest+$deltaBordure, $yDepart, $twd_radian);
                $ecranY=setSaisieToDisplayY($xouest+$deltaBordure, $yDepart, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"name":"Départ bâbord","lon":'.$lon.',"lat":'.$lat.',"color":"yellow","fillcolor":"red"}';        
                $k++;  
            }          
        }
        else if (isset($value) && isset($value->fixe) && ($value->fixe==false) 
            && isset($value->xs) && isset($value->ys) && isset($value->color) && isset($value->fillcolor)){
                // completer 
if ($debug3){
                echo "<br> Key:".$key."<br>\n";
                print_r($value);
                echo "<br>\n";
}                                                
                $ecranX=setSaisieToDisplayX($value->xs,$value->ys, $twd_radian);
                $ecranY=setSaisieToDisplayY($value->xs,$value->ys, $twd_radian);
                $lon=get_lon_Xecran($ecranX);
                $lat=get_lat_Yecran($ecranY);
                // Formater la sortie
                $boueesMobilesParcours[$k]='{"boueefixe":false,"id":'.$k.',"name":"'.$value->name.'","lon":'.$lon.',"lat":'.$lat.',"color":"'.$value->color.'","fillcolor":"'.$value->fillcolor.'"}';
                $k++;              
        }
    }  

    // Debug
    if ($debug3){    
        echo "<br>Liste des bouées mobiles ajoutées au parcours\n";
        for ($index=0; $index<count($boueesMobilesParcours); $index++){        
            echo "<br>".$boueesMobilesParcours[$index];
        }    
    }     
}
 
 
 
?>
