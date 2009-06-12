<?php
/*----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------
   LICENSE

   This file is part of GLPI.

   GLPI is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   ----------------------------------------------------------------------*/
/*----------------------------------------------------------------------
    Original Author of file: 
    Purpose of file:
    ----------------------------------------------------------------------*/
	   
$title = "Gestion des commandes";

$LANG['plugin_order'][0] = "N° commande";
$LANG['plugin_order'][1] = "Date commande";
$LANG['plugin_order'][2] = "Description";
$LANG['plugin_order'][3] = "Budget";
$LANG['plugin_order'][4] = "".$title."";
$LANG['plugin_order'][11] = "Aucune commande trouvée";
$LANG['plugin_order'][12] = "Bon livraison";
$LANG['plugin_order'][13] = "Prix Total (HT)";
$LANG['plugin_order'][14] = "Prix Total (TTC)";
$LANG['plugin_order'][21] = "Réceptionner";
$LANG['plugin_order'][22] = "Créer";
$LANG['plugin_order'][25] = "TVA";
$LANG['plugin_order'][26] = "Total HT";
$LANG['plugin_order'][27] = "Total TTC";
$LANG['plugin_order'][28] = "N° Facture";
$LANG['plugin_order'][30] = "Certificat(s)";
$LANG['plugin_order'][31] = "N° commande fournisseur";
$LANG['plugin_order'][32] = "Conditions de paiement";
$LANG['plugin_order'][33] = "Les champs marqués d'une * sont obligatoires";
$LANG['plugin_order'][34] = "Générer les entêtes des commandes à partir des informations financières";
$LANG['plugin_order'][35] = "Supprimer les entêtes des commandes générées à partir des informations financières";
$LANG['plugin_order'][36] = "Quantité reçue éronnée";
$LANG['plugin_order'][37] = "Commandes générées à partir des informations financières:";
$LANG['plugin_order'][38] = "Statuts par défaut:";
$LANG['plugin_order'][39] = "Nom de la commande";
$LANG['plugin_order'][40] = "Lieu de livraison de la commande";
$LANG['plugin_order'][41] = "Opération réalisée avec succès.";
$LANG['plugin_order'][42] = "Impossible de lier le même matériel à plusieurs lignes détail";
$LANG['plugin_order'][43] = "Retour à la page menu";
$LANG['plugin_order'][44] = "Un numéro de commande est obligatoire !";
$LANG['plugin_order'][45] = "Impossible de générer du matériel non réceptionné";
$LANG['plugin_order'][46] = "Impossible de lier du matériel non réceptionné";
$LANG['plugin_order'][47] = "Informations sur la commande";



$LANG['plugin_order']['status'][0]="Statut commande";
$LANG['plugin_order']['status'][1]="En cours de livraison";
$LANG['plugin_order']['status'][2]="Livrée";
$LANG['plugin_order']['status'][3] ="Gestion des statuts par défaut";
$LANG['plugin_order']['status'][4] ="Statut par défaut à la création d'une commande:";
$LANG['plugin_order']['status'][5] ="Statut par défaut d'une commande livrée:";
$LANG['plugin_order']['status'][6] ="Statut par défaut d'une commande pas totalement livrée:";
$LANG['plugin_order']['status'][7] ="En attente d'approuvation";
$LANG['plugin_order']['status'][8] ="Réceptionné";
$LANG['plugin_order']['status'][9] ="En cours d'édition";
$LANG['plugin_order']['status'][10] ="Annulée";
$LANG['plugin_order']['status'][11] ="En attente de livraison";

$LANG['plugin_order']['item'][0]="Matériel(s) lié(s)";
$LANG['plugin_order']['item'][1]="Dissocier le(s) matériels(s)";
$LANG['plugin_order']['item'][2]="Aucun matériel associé";
$LANG['plugin_order']['item'][3]="Pas de matériel lié à la commande";

$LANG['plugin_order']['detail'][0]="Détail(s)";
$LANG['plugin_order']['detail'][1]="Type";
$LANG['plugin_order']['detail'][2]="Référence";
$LANG['plugin_order']['detail'][3]="Quantité livrée";
$LANG['plugin_order']['detail'][4]="Prix unitaire (HT)";	
$LANG['plugin_order']['detail'][5]="Ajouter à la commande";	
$LANG['plugin_order']['detail'][6]="Ajouter";	
$LANG['plugin_order']['detail'][7]="Quantité commandée";
$LANG['plugin_order']['detail'][8]="Prix unitaire (TTC)";
$LANG['plugin_order']['detail'][9]="Prix total (HT)";
$LANG['plugin_order']['detail'][10]="Prix total (TTC)";
$LANG['plugin_order']['detail'][11]="Fabricant";
$LANG['plugin_order']['detail'][12]="Quantité reçue";
$LANG['plugin_order']['detail'][13]="Génération matériel";
$LANG['plugin_order']['detail'][14]="Ligne";
$LANG['plugin_order']['detail'][15]="Réseau";
$LANG['plugin_order']['detail'][16]="Type";
$LANG['plugin_order']['detail'][17]="Ligne(s) détail";
$LANG['plugin_order']['detail'][18]="Prix unitaire remisé (HT)";
$LANG['plugin_order']['detail'][19]="Statut matériel";
$LANG['plugin_order']['detail'][20]="Pas de matériel à réceptionner";
$LANG['plugin_order']['detail'][21]="Date de livraison";
$LANG['plugin_order']['detail'][22]="Matériel associé";
$LANG['plugin_order']['detail'][23]="Matériel non réceptionné";
$LANG['plugin_order']['detail'][24]="Utilisateur";
$LANG['plugin_order']['detail'][25]="Remise (%)";
$LANG['plugin_order']['detail'][26]="Lieu";
$LANG['plugin_order']['detail'][27]="Veuillez sélectionner un fournisseur";
$LANG['plugin_order']['detail'][28]="Cette référence est déjà utilisée dans la commande";
$LANG['plugin_order']['detail'][29]="Aucun matériel sélectionné";
$LANG['plugin_order']['detail'][30]="Matériel(s) généré(s) avec succès";
$LANG['plugin_order']['detail'][31]="Matériel(s) réceptionné(s) avec succès";
$LANG['plugin_order']['detail'][32]="Matériel(s) déjà réceptionné(s)";


$LANG['plugin_order']['delivery'][1]="Réception matériel(s)";
$LANG['plugin_order']['delivery'][2]="Réceptionner matériel";
$LANG['plugin_order']['delivery'][3]="Générer matériel associé";
$LANG['plugin_order']['delivery'][4]="Matériel réceptionné";
$LANG['plugin_order']['delivery'][5]="Matériel(s) réceptionné(s)";
$LANG['plugin_order']['delivery'][6]="Numéro de série";
$LANG['plugin_order']['delivery'][7]="Numéro d'inventaire";
$LANG['plugin_order']['delivery'][8]="Nom";
$LANG['plugin_order']['delivery'][9]="Générer";
$LANG['plugin_order']['delivery'][10]="L'un des matériels que vous essayez de générer n'est pas réceptionné";
$LANG['plugin_order']['delivery'][11]="Lier à un matériel existant";
$LANG['plugin_order']['delivery'][12]="Supprimer le lien avec le matériel";

$LANG['plugin_order']['profile'][0] = "Gestion des droits"; 
$LANG['plugin_order']['profile'][1] = "$title"; 

$LANG['plugin_order']['reference'][1]="Référence produit";
$LANG['plugin_order']['reference'][2]="Ajouter une référence produit";
$LANG['plugin_order']['reference'][3]="Liste des références";
$LANG['plugin_order']['reference'][4]="Type de matériel";
$LANG['plugin_order']['reference'][5]="Fournisseur pour une référence";

$LANG['plugin_order']['setup'][1] = "Catégorie";
$LANG['plugin_order']['setup'][2] = "Plugin non utilisable depuis le helpdesk";
$LANG['plugin_order']['setup'][11] = "Serveur";
$LANG['plugin_order']['setup'][12] = "Langage";
$LANG['plugin_order']['setup'][14] = "Fournisseur";
$LANG['plugin_order']['setup'][15] = "Elément(s) associé(s)";
$LANG['plugin_order']['setup'][23] = "Associer";
$LANG['plugin_order']['setup'][24] = "Dissocier";
$LANG['plugin_order']['setup'][25] = "Associer à l'application Web";
$LANG['plugin_order']['setup'][28] = "Editeur ";

$LANG['plugin_order']['infocom'][1]="Certains champs ne peuvent-être modifiés : ils proviennent d'une commande";

$LANG['plugin_order']['history'][1]="Matériel généré depuis la commande";


$LANG['plugin_order']['menu'][1] = "Gérer les commandes";
$LANG['plugin_order']['menu'][2] = "Gérer le catalogue de références produits";
?>