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
$AJAX_INCLUDE=1;
include (GLPI_ROOT."/inc/includes.php");
include (GLPI_ROOT."/plugins/order/inc/plugin_order.dropdown.function.php");
include (GLPI_ROOT."/plugins/order/inc/plugin_order.reference.function.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!defined('GLPI_ROOT')){
	die("Can not acces directly to this file");
	}

checkCentralAccess();
$rand = plugin_order_dropdownReferencesByEnterprise("reference",$_POST["device_type"],$_POST["FK_enterprise"]);
$paramsaction=array('FK_reference'=>'__VALUE__',
		   	  'entity_restrict'=>$_POST["entity_restrict"],
		   	  'FK_enterprise'=>$_POST["FK_enterprise"],		   	  
		   	  'orderID'=>$_POST["orderID"],
		   	  'device_type'=>$_POST["device_type"]
		);

$fields = array ("quantity","priceht","pricediscounted", "taxes", "validate");
foreach ($fields as $field)
{
	$paramsaction['update'] = $field;
	ajaxUpdateItem("show_$field",$CFG_GLPI["root_doc"]."/plugins/order/ajax/referencedetail.php",$paramsaction,false,"dropdown_reference$rand");
	ajaxUpdateItemOnSelectEvent("dropdown_reference$rand","show_$field",$CFG_GLPI["root_doc"]."/plugins/order/ajax/referencedetail.php",$paramsaction);
}	
?>