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
    
if (!defined('GLPI_ROOT'))
	define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");
useplugin('order',true);

commonHeader($LANG['plugin_order'][4],$_SERVER['PHP_SELF'],"plugins","order");
echo "<div class='center'>";
echo "<b>".$LANG['plugin_order'][38]."</b><br><br>";
echo "<b>".$LANG['plugin_order'][37]."</b><br>";
echo "<a href=".$_SERVER["PHP_SELF"]."?action=createorder>".$LANG['plugin_order'][34]."</a><br>";
echo "<a href=".$_SERVER["PHP_SELF"]."?action=deleteorder>".$LANG['plugin_order'][35]."</a><br><br>";
echo "</div>";
commonFooter();

$action=$_GET["action"];
/* create orders with infocoms */
if($action=="createorder") {
	$DB=new DB;
	$entity=$_SESSION["glpiactive_entity"];
	$query=" 	INSERT INTO `glpi_plugin_order`(name, budget, date, FK_enterprise, deliverynum, numbill, price, comment, status, FK_entities)
			SELECT num_commande, budget, buy_date, FK_enterprise , bon_livraison,facture, sum(value), comments, 2, $entity FROM glpi_infocoms  
			WHERE num_commande !='' 
			GROUP BY num_commande";
	$DB->query($query);
	$query=" 	INSERT INTO glpi_plugin_order_device (FK_device, FK_order, device_type)
			SELECT FK_DEVICE, glpi_plugin_order.ID, device_type FROM glpi_infocoms, glpi_plugin_order 
			WHERE num_commande!='' 
			AND `glpi_infocoms`.num_commande=`glpi_plugin_order`.name;";
	$DB->query($query);
/* delete orders created with infocoms */
} elseif($action=="deleteorder") {
	$DB=new DB;
	$query=" 	DELETE FROM `glpi_plugin_order`WHERE name in (
			SELECT num_commande FROM glpi_infocoms  
			WHERE num_commande !='' GROUP BY num_commande )";
	$DB->query($query);
	$query=" 	DELETE FROM glpi_plugin_order_device WHERE FK_device in (
			SELECT FK_DEVICE FROM glpi_infocoms, glpi_plugin_order 
			WHERE num_commande!='' 
			AND `glpi_infocoms`.num_commande=`glpi_plugin_order`.name);";
	$DB->query($query);
}
?>