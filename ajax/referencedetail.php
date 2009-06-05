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
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");
include (GLPI_ROOT."/plugins/order/inc/plugin_order.functions_dropdown.php");
include (GLPI_ROOT."/plugins/order/inc/plugin_order.reference.function.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!defined('GLPI_ROOT')){
	die("Can not acces directly to this file");
	}


checkCentralAccess();

if ($_POST["FK_reference"] > 0)
{
	$price = plugin_order_getPriceByReferenceAndSupplier($_POST["FK_reference"],$_POST["FK_enterprise"]);
	switch ($_POST["update"])
	{
		case 'quantity':
			autocompletionTextField("quantity","glpi_plugin_order_detail","quantity",0,5);
		break;
		case 'priceht':
			autocompletionTextField("price","glpi_plugin_order_detail","price",$price,5);
		break;
		case 'pricediscounted':
			autocompletionTextField("reductedprice","glpi_plugin_order_detail","reductedprice",$price,5);
		break;
		case 'validate':
			echo "<input type='hidden' name='FK_reference' value='".$_POST["FK_reference"]."' class='submit' >";
			echo "<input type='submit' name='add_detail' value=\"".$LANG['buttons'][7]."\" class='submit' >";
		break;					
	}	
}
else
	return "";
?>