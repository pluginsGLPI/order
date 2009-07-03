<?php

/*
 * @version $Id: dropdownDocument.php 4635 2007-03-25 14:21:15Z moyo $
 ------------------------------------------------------------------------- 
 GLPI - Gestionnaire Libre de Parc Informatique 
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

// Direct access to file
$NEEDED_ITEMS = array("computer","monitor","printer","networking","software","peripheral","phone","consumable","cartridge");
define('GLPI_ROOT', '../../..');

include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/plugins/order/inc/plugin_order.dropdown.function.php");
include (GLPI_ROOT . "/plugins/order/inc/plugin_order.reference.function.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!defined('GLPI_ROOT')) {
	die("Can not acces directly to this file");
}
checkCentralAccess();

if ($_POST["device_type"])
{
	switch ($_POST["field"])
	{
		case "type":
			if (plugin_order_getTypeTable($_POST["device_type"]) !== false)
				dropdownValue(plugin_order_getTypeTable($_POST["device_type"]), "FK_type");
		break;
		case "model":
			//if (!in_array($_POST["device_type"], $ORDER_RESTRICTED_TYPES) )
			if (plugin_order_getModelTable($_POST["device_type"]) !== false)
				dropdownValue(plugin_order_getModelTable($_POST["device_type"]), "FK_model");
			else
				return "";	
				
		break;
		case "template":
			if (in_array($_POST["device_type"],$ORDER_TEMPLATE_TABLES) )
			{
				$commonitem = new CommonItem;
				$commonitem->setType($_POST["device_type"],true);
				plugin_order_dropdownTemplate("template", $_POST["entity_restrict"], $commonitem->obj->table);
			}
			else
				return "";	
		break;				
	}	
}
else
	return "";

?>