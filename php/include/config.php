<?php

define("DATAPATH_INPUT", "../json/"); // Les donn�es de description de site seront lues dans ce dossier.
define("DATAPATH_OUTPUT", "../data/"); // Les donn�es de placement des bou�es sont sauvegard�es dans ce dossier.

// Ces valeurs sont fonction de la bo�te �cran cansvasw et cansvash
define("INCREMENT", 30000);  // environ 10 m�tres ; � am�liorer en s'adaptant au plan d'eau
define("GRAND_INCREMENT", 60000); // environ 20 m�tres


// Ecart entre bou�es de d�part en tenant compte de la bordure de s�curit�
define ("ECARTBORDURE", 2); // Deux m�tres pour �viter de taper la berge
define ("ECART_BOUEES_X_METRES_LONG", 14);
define ("ECART_BOUEES_X_METRES_COURT", 10);
// Distance du dog leg � la porte pour les grands plans d'eau en tenant compte de la bordure de s�curit�
define ("ECART_BOUEES_Y_METRES_LONG", 60);
define ("ECART_BOUEES_Y_METRES_COURT", 50);


?>
