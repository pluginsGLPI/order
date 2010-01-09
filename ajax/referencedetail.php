<?php
/*
 * @version $Id: HEADER 1 2009-09-21 14:58 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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
 
// ----------------------------------------------------------------------
// Original Author of file: NOUH Walid & Benjamin Fontan
// Purpose of file: plugin order v1.1.0 - GLPI 0.72
// ----------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!defined('GLPI_ROOT')){
   die("Can not acces directly to this file");
}

useplugin('order', true);

checkCentralAccess();

$PluginOrderReferenceManufacturer = new PluginOrderReferenceManufacturer();
$PluginOrder = new PluginOrder;


if ($_POST["FK_reference"] > 0)
{  
   
   $PluginOrder->getFromDB($_POST["orderID"]);
   $taxes = $PluginOrder->fields["taxes"];
	$price = $PluginOrderReferenceManufacturer->getPriceByReferenceAndSupplier($_POST["FK_reference"],$_POST["FK_enterprise"]);
	switch ($_POST["update"])
	{
		case 'quantity':
			autocompletionTextField("quantity","glpi_plugin_order_detail","quantity",0,5);
         break;
		case 'priceht':
			echo "<input type='text' name='price' value=\"".formatNumber($price,true)."\" size='5'>";
         break;
		case 'pricediscounted':
			echo "<input type='text' name='discount' value=\"".formatNumber("discount",true)."\" size='5'>";
         break;
		case 'taxes':
			echo getDropdownname("glpi_dropdown_plugin_order_taxes", $taxes);
			echo "<input type='hidden' name='taxes' value='".$taxes."'>";
         break;
		case 'validate':
			echo "<input type='hidden' name='device_type' value='".$_POST["device_type"]."' class='submit' >";
			echo "<input type='hidden' name='FK_reference' value='".$_POST["FK_reference"]."' class='submit' >";
			echo "<input type='submit' name='add_detail' value=\"".$LANG['buttons'][8]."\" class='submit' >";
         break;					
	}	
}
else
	return "";
?>