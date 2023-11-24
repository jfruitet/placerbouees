<?php

define("DATAPATH_INPUT", "../json/"); // Les données de description de site seront lues dans ce dossier.
define("DATAPATH_OUTPUT", "../data/"); // Les données de placement des bouées sont sauvegardées dans ce dossier.

// Ces valeurs sont fonction de la boîte écran cansvasw et cansvash
define("INCREMENT", 30000);  // environ 10 mètres ; à améliorer en s'adaptant au plan d'eau
define("GRAND_INCREMENT", 60000); // environ 20 mètres


// Ecart entre bouées de départ en tenant compte de la bordure de sécurité
define ("ECARTBORDURE", 2); // Deux mètres pour éviter de taper la berge
define ("ECART_BOUEES_X_METRES_LONG", 14);
define ("ECART_BOUEES_X_METRES_COURT", 10);
// Distance du dog leg à la porte pour les grands plans d'eau en tenant compte de la bordure de sécurité
define ("ECART_BOUEES_Y_METRES_LONG", 60);
define ("ECART_BOUEES_Y_METRES_COURT", 50);


?>
