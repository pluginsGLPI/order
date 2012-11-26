<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

$LANG['plugin_order']['title'][1] = "Gestion des commandes";

$LANG['plugin_order'][0] = "N° commande";
$LANG['plugin_order'][1] = "Date commande";
$LANG['plugin_order'][2] = "Description";
$LANG['plugin_order'][3] = "Budget";
$LANG['plugin_order'][4] = "Infos Fournisseur";
$LANG['plugin_order'][5] = "Validation";
$LANG['plugin_order'][6] = "Livraison";
$LANG['plugin_order'][7] = "Commande";
$LANG['plugin_order'][8] = "Autre équipement";
$LANG['plugin_order'][9] = "Type d'autre équipement";
$LANG['plugin_order'][10] = "Qualité";
$LANG['plugin_order'][11] = "Commandes liées";
$LANG['plugin_order'][12] = "Budget consommé";
$LANG['plugin_order'][13] = "Prix Total (HT)";
$LANG['plugin_order'][14] = "Prix Total (TTC)";
$LANG['plugin_order'][15] = "Prix Total + Frais de port (HT)";
$LANG['plugin_order'][25] = "TVA";
$LANG['plugin_order'][26] = "Frais de port";
$LANG['plugin_order'][28] = "N° Facture";
$LANG['plugin_order'][30] = "N° devis";
$LANG['plugin_order'][31] = "N° commande";
$LANG['plugin_order'][32] = "Conditions de paiement";
$LANG['plugin_order'][39] = "Nom de la commande";
$LANG['plugin_order'][40] = "Lieu de livraison de la commande";
$LANG['plugin_order'][42] = "Impossible de lier le même matériel à plusieurs lignes détail";
$LANG['plugin_order'][44] = "Un numéro de commande est obligatoire !";
$LANG['plugin_order'][45] = "Impossible de générer du matériel non réceptionné";
$LANG['plugin_order'][46] = "Impossible de lier du matériel non réceptionné";
$LANG['plugin_order'][47] = "Informations sur la commande";
$LANG['plugin_order'][48] = "Une ou plusieurs lignes sélectionnées n'ont pas de matériel associé";
$LANG['plugin_order'][49] = "La date de commande doit être comprise dans les dates déclarées pour le budget sélectionné.";
$LANG['plugin_order'][50] = "Date de livraison estimée";
$LANG['plugin_order'][51] = "La date estimée est dépassée";
$LANG['plugin_order'][52] = "Supprimer le lien";
$LANG['plugin_order'][53] = "Date de livraison effective";
$LANG['plugin_order'][54] = "Dépassement date de livraison estimée";
$LANG['plugin_order'][55] = "Commandes en retard";
$LANG['plugin_order'][56] = "Auteur";
$LANG['plugin_order'][57] = "Groupe auteur";
$LANG['plugin_order'][58] = "Destinataire";
$LANG['plugin_order'][59] = "Groupe destinataire";
$LANG['plugin_order'][60] = "Element de la commande";

$LANG['plugin_order']['bill'][0] = "Facture";
$LANG['plugin_order']['bill'][1] = "Type de facture";
$LANG['plugin_order']['bill'][2] = "Statut de la facture";
$LANG['plugin_order']['bill'][3] = "un numéro de facture est obligatoire";
$LANG['plugin_order']['bill'][4] = "Factures";
$LANG['plugin_order']['bill'][5] = "Statut facturation";
$LANG['plugin_order']['bill'][6] = "Payée";
$LANG['plugin_order']['bill'][7] = "En cours de paiement";

$LANG['plugin_order']['config'][0] = "Configuration du plugin";
$LANG['plugin_order']['config'][1] = "TVA par défaut";
$LANG['plugin_order']['config'][2] = "Utiliser le circuit de validation";
$LANG['plugin_order']['config'][3] = "Actions automatiques lors de la réception";
$LANG['plugin_order']['config'][4] = "Activer la génération automatique";
$LANG['plugin_order']['config'][5] = "Nom par défaut";
$LANG['plugin_order']['config'][6] = "Numéro de série par défaut";
$LANG['plugin_order']['config'][7] = "Numéro d'inventaire par défaut";
$LANG['plugin_order']['config'][8] = "Entité par défaut";
$LANG['plugin_order']['config'][9] = "Catégorie par défaut";
$LANG['plugin_order']['config'][10] = "Titre par défaut";
$LANG['plugin_order']['config'][11] = "Description par défaut";
$LANG['plugin_order']['config'][12] = "Statut par défaut";
$LANG['plugin_order']['config'][13] = "Cycle de vie d'une commande";
$LANG['plugin_order']['config'][14] = "Statut avant la validation";
$LANG['plugin_order']['config'][15] = "Statut en attente de validation";
$LANG['plugin_order']['config'][16] = "Statut lorsque la commande est validée";
$LANG['plugin_order']['config'][17] = "Statut lorsqu'au moins un matériel est livré";
$LANG['plugin_order']['config'][18] = "Statut lorsque tous les matériels sont livrés";
$LANG['plugin_order']['config'][19] = "Statut lorsque la commande est annulée";
$LANG['plugin_order']['config'][20] = "Pas de TVA";
$LANG['plugin_order']['config'][21] = "Statut lorsque la commande est payée";
$LANG['plugin_order']['config'][22] = "Génération d'un bon de commande en ODT";
$LANG['plugin_order']['config'][23] = "Activer la qualité des fournisseurs";
$LANG['plugin_order']['config'][24] = "Afficher les informations fournisseur de la commande";
$LANG['plugin_order']['config'][25] = "Couleur lorsque la date de livraison estimée est dépassée";
$LANG['plugin_order']['config'][26] = "Copier les documents de la commande à la génération d'un matériel";
$LANG['plugin_order']['config'][27] = "Rubrique par défaut pour l'ajout de documents à une commande";

$LANG['plugin_order']['delivery'][1] = "Réception matériel(s)";
$LANG['plugin_order']['delivery'][2] = "Réceptionner matériel";
$LANG['plugin_order']['delivery'][3] = "Générer matériel associé";
$LANG['plugin_order']['delivery'][4] = "Réception en masse matériels";
$LANG['plugin_order']['delivery'][5] = "Matériel(s) réceptionné(s)";
$LANG['plugin_order']['delivery'][6] = "Nombre à réceptionner";
$LANG['plugin_order']['delivery'][9] = "Générer";
$LANG['plugin_order']['delivery'][11] = "Lier à un matériel existant";
$LANG['plugin_order']['delivery'][12] = "Supprimer le lien avec le matériel";
$LANG['plugin_order']['delivery'][13] = "Matériel généré à partir de la commande";
$LANG['plugin_order']['delivery'][14] = "Matériel lié à la commande";
$LANG['plugin_order']['delivery'][15] = "Matériel délié de la commande";
$LANG['plugin_order']['delivery'][16] = "Matériel déjà lié à un objet d'inventaire";
$LANG['plugin_order']['delivery'][17] = "Aucun matériel à générer";
$LANG['plugin_order']['delivery'][17] = "Commande totalement livrée";

$LANG['plugin_order']['detail'][1] = "Equipement";
$LANG['plugin_order']['detail'][2] = "Référence";
$LANG['plugin_order']['detail'][4] = "Prix unitaire (HT)";
$LANG['plugin_order']['detail'][5] = "Ajouter à la commande";
$LANG['plugin_order']['detail'][6] = "Type";
$LANG['plugin_order']['detail'][7] = "Quantité";
$LANG['plugin_order']['detail'][18] = "Prix remisé (HT)";
$LANG['plugin_order']['detail'][19] = "Statut";
$LANG['plugin_order']['detail'][20] = "Pas de matériel à réceptionner";
$LANG['plugin_order']['detail'][21] = "Date de livraison";
$LANG['plugin_order']['detail'][25] = "Remise en %";
$LANG['plugin_order']['detail'][27] = "Veuillez sélectionner un fournisseur";
$LANG['plugin_order']['detail'][29] = "Aucun matériel sélectionné";
$LANG['plugin_order']['detail'][30] = "Matériel(s) généré(s) avec succès";
$LANG['plugin_order']['detail'][31] = "Matériel(s) réceptionné(s) avec succès";
$LANG['plugin_order']['detail'][32] = "Matériel(s) déjà réceptionné(s)";
$LANG['plugin_order']['detail'][33] = "Le pourcentage de remise doit-être compris entre 0 et 100";
$LANG['plugin_order']['detail'][34] = "Ajout référence";
$LANG['plugin_order']['detail'][35] = "Suppression référence";
$LANG['plugin_order']['detail'][36] = "Voulez vous vraiment supprimer ce(s) détail(s) ? Les matériels livrés ne seront plus liés à la commande !";
$LANG['plugin_order']['detail'][37] = "Il n'y a pas assez de matériels à réceptionner";
$LANG['plugin_order']['detail'][38] = "Voulez vous vraiment annuler cette commande ? Cette option est irréversible!";
$LANG['plugin_order']['detail'][39] = "Voulez vous vraiment annuler la demande de validation ?";
$LANG['plugin_order']['detail'][40] = "Voulez vous modifier le contenu de la commande ? ";
$LANG['plugin_order']['detail'][41] = "Souhaitez-vous vraiment mettre à jour cette référence commandée ? ";

$LANG['plugin_order']['generation'][0] = "Génération";
$LANG['plugin_order']['generation'][1] = "Génération du bon de commande";
$LANG['plugin_order']['generation'][2] = "Bon de commande";
$LANG['plugin_order']['generation'][3] = "Adresse de facturation";
$LANG['plugin_order']['generation'][4] = "Adresse de livraison";
$LANG['plugin_order']['generation'][5] = "le";
$LANG['plugin_order']['generation'][6] = "Qté";
$LANG['plugin_order']['generation'][7] = "Désignation";
$LANG['plugin_order']['generation'][8] = "Prix Unitaire";
$LANG['plugin_order']['generation'][9] = "Montant HT";
$LANG['plugin_order']['generation'][10] = "Emetteur d'ordre";
$LANG['plugin_order']['generation'][11] = "Destinataire";
$LANG['plugin_order']['generation'][12] = "N° de Cde";
$LANG['plugin_order']['generation'][13] = "Taux de remise";
$LANG['plugin_order']['generation'][14] = "TOTAL H.T.";
$LANG['plugin_order']['generation'][15] = "TOTAL T.T.C.";
$LANG['plugin_order']['generation'][16] = "Signature de l’émetteur d’ordre";
$LANG['plugin_order']['generation'][17] = "€";

$LANG['plugin_order']['history'][2] = "Ajout";
$LANG['plugin_order']['history'][4] = "Suppression";

$LANG['plugin_order']['infocom'][1] = "Certains champs ne peuvent-être modifiés : ils proviennent d'une commande";

$LANG['plugin_order']['item'][0] = "Matériel(s) lié(s)";
$LANG['plugin_order']['item'][2] = "Aucun matériel associé";

$LANG['plugin_order']['mailing'][2] = "par";

$LANG['plugin_order']['menu'][0] = "Menu principal";
$LANG['plugin_order']['menu'][1] = "Commandes";
$LANG['plugin_order']['menu'][2] = "Catalogue de références produits";
$LANG['plugin_order']['menu'][4] = "Commandes";
$LANG['plugin_order']['menu'][5] = "Références";
$LANG['plugin_order']['menu'][6] = "Factures";

$LANG['plugin_order']['parser'][1] = "Utiliser ce modèle";
$LANG['plugin_order']['parser'][2] = "Aucun fichier trouvé dans le répertoire";
$LANG['plugin_order']['parser'][3] = "Utiliser cette signature";
$LANG['plugin_order']['parser'][4] = "Merci de sélectionner un modèle dans vos préférences";

$LANG['plugin_order']['profile'][0] = "Gestion des droits";
$LANG['plugin_order']['profile'][1] = "Valider une commande";
$LANG['plugin_order']['profile'][2] = "Annuler une commande";
$LANG['plugin_order']['profile'][3] = "Modifier une commande validée";
$LANG['plugin_order']['profile'][4] = "Associer la commande à un ticket";

$LANG['plugin_order']['reference'][1] = "Référence produit";
$LANG['plugin_order']['reference'][2] = "Ajouter un fournisseur";
$LANG['plugin_order']['reference'][3] = "Liste des références";
$LANG['plugin_order']['reference'][5] = "Fournisseur pour une référence";
$LANG['plugin_order']['reference'][6] = "Une référence du même nom existe déjà";
$LANG['plugin_order']['reference'][7] = "Référence(s) actuellement utilisée(s)";
$LANG['plugin_order']['reference'][8] = "Impossible de créer une référence sans nom";
$LANG['plugin_order']['reference'][9] = "Impossible de créer une référence sans type";
$LANG['plugin_order']['reference'][10] = "Référence produit fournisseur";
$LANG['plugin_order']['reference'][11] = "Vue par type de matériel";
$LANG['plugin_order']['reference'][12] = "Sélectionnez le type de matériel souhaité";
$LANG['plugin_order']['reference'][13] = "Copier la référence";
$LANG['plugin_order']['reference'][14] = "Copie de";

$LANG['plugin_order']['status'][0] = "Statut commande";
$LANG['plugin_order']['status'][1] = "En cours de livraison";
$LANG['plugin_order']['status'][2] = "Livrée";
$LANG['plugin_order']['status'][3] = "Statut de la livraison";
$LANG['plugin_order']['status'][4] = "Statut non spécifié";
$LANG['plugin_order']['status'][7] = "En attente d'approbation";
$LANG['plugin_order']['status'][8] = "Réceptionné";
$LANG['plugin_order']['status'][9] = "En cours d'édition";
$LANG['plugin_order']['status'][10] = "Annulée";
$LANG['plugin_order']['status'][11] = "En attente de livraison";
$LANG['plugin_order']['status'][12] = "Validée";
$LANG['plugin_order']['status'][13] = "Statistique livraison";
$LANG['plugin_order']['status'][14] = "La commande est validée, toute modification est interdite";
$LANG['plugin_order']['status'][15] = "Vous ne pouvez pas supprimer ce statut";
$LANG['plugin_order']['status'][16] = "Payée";
$LANG['plugin_order']['status'][17] = "Non payée";
$LANG['plugin_order']['status'][18] = "Valeur payée";
$LANG['plugin_order']['status'][19] = "Résumé de la facturation";
$LANG['plugin_order']['status'][20] = "Livraison en retard";

$LANG['plugin_order']['survey'][0] = "Qualité fournisseur";
$LANG['plugin_order']['survey'][1] = "Qualité du suivi administratif (contrat, factures, courrier...)";
$LANG['plugin_order']['survey'][2] = "Qualité du suivi commercial, fréquence des visites, réactivité";
$LANG['plugin_order']['survey'][3] = "Disponibilité des interlocuteurs fournisseur";
$LANG['plugin_order']['survey'][4] = "Qualité des prestations collaborateurs du fournisseur";
$LANG['plugin_order']['survey'][5] = "Fiabilité sur les disponibilités annoncées";
$LANG['plugin_order']['survey'][6] = "Trés insatisfait";
$LANG['plugin_order']['survey'][7] = "Trés satisfait";
$LANG['plugin_order']['survey'][8] = "Note moyenne sur 10 (X points / 5)";
$LANG['plugin_order']['survey'][9] = "Note globale du fournisseur";
$LANG['plugin_order']['survey'][10] = "Note";
$LANG['plugin_order']['survey'][11] = "Commentaire du Sondage";

$LANG['plugin_order']['validation'][0] = "Merci d'ajouter au moins un équipement à votre commande.";
$LANG['plugin_order']['validation'][1] = "Demande de validation de la commande";
$LANG['plugin_order']['validation'][2] = "Commande validée";
$LANG['plugin_order']['validation'][3] = "Commande en cours de livraison";
$LANG['plugin_order']['validation'][4] = "Commande totalement livrée";
$LANG['plugin_order']['validation'][5] = "Commande annulée";
$LANG['plugin_order']['validation'][6] = "Circuit de validation d'une commande";
$LANG['plugin_order']['validation'][7] = "Demande de validation de la commande effectuée";
$LANG['plugin_order']['validation'][8] = "Commande en cours d'édition";
$LANG['plugin_order']['validation'][9] = "Valider la commande";
$LANG['plugin_order']['validation'][10] = "La commande est validée";
$LANG['plugin_order']['validation'][11] = "Faire valider la commande";
$LANG['plugin_order']['validation'][12] = "Annuler la commande";
$LANG['plugin_order']['validation'][13] = "Annuler la demande de validation";
$LANG['plugin_order']['validation'][14] = "Annulation la demande de validation effectuée";
$LANG['plugin_order']['validation'][15] = "Commande en édition";
$LANG['plugin_order']['validation'][16] = "Annulation de la validation effectuée";
$LANG['plugin_order']['validation'][17] = "Modifier la commande";
$LANG['plugin_order']['validation'][18] = "Commentaire de la validation";
$LANG['plugin_order']['validation'][19] = "Editeur de la validation";

$LANG['plugin_order']['budget_over'][0] = "Le total des commandes liées à ce budget est supérieur à sa valeur.";
$LANG['plugin_order']['budget_over'][1] = "Le total des commandes liées à ce budget est égal à sa valeur.";

$LANG['plugin_order']['install'][0] = "Installation ou mise à jour du plugin";
?>