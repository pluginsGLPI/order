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
    
/* create orders with infocoms */
function plugin_order_config_infocoms_create() {
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
}
/* delete orders created with infocoms */
function plugin_order_config_infocoms_delete() {
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

/* update default status config */
function plugin_order_config_default_status() {
	$DB=new DB;
	$query=" 	UPDATE glpi_plugin_order_config
			SET status_creation=".$_POST["status_creation"]."
			WHERE ID=1";
	$DB->query($query);
	$DB=new DB;
	$query=" 	UPDATE glpi_plugin_order_config
			SET status_delivered=".$_POST["status_delivered"]."
			WHERE ID=1";
	$DB->query($query);
	$DB=new DB;
	$query=" 	UPDATE glpi_plugin_order_config
			SET status_nodelivered=".$_POST["status_nodelivered"]."
			WHERE ID=1";
	$DB->query($query);
}
?>