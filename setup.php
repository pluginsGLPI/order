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

define ("ORDER_DEVICE_NOT_DELIVRED",0);   
define ("ORDER_DEVICE_DELIVRED",1);   

// Define order status
define ("ORDER_STATUS_DRAFT",0);   
define ("ORDER_STATUS_WAITING_APPROVAL",1);   
define ("ORDER_STATUS_APPROVED",2);   
define ("ORDER_STATUS_PARTIALLY_DELIVRED",3);
define ("ORDER_STATUS_COMPLETLY_DELIVERED",4);
define ("ORDER_STATUS_CANCELED",5);


include_once ("inc/plugin_order.auth.function.php");
include_once ("inc/plugin_order.profile.class.php");
 
/* init the hooks of the plugins -needed- */
function plugin_init_order() {
	global $PLUGIN_HOOKS,$CFG_GLPI,$LANG,$ORDER_AVAILABLE_TYPES,$ORDER_RESTRICTED_TYPES,$ORDER_VALIDATION_STATUS,$ORDER_TYPE_TABLES,$ORDER_MODEL_TABLES,$ORDER_TEMPLATE_TABLES;

	$ORDER_AVAILABLE_TYPES = array (SOFTWARELICENSE_TYPE, COMPUTER_TYPE, MONITOR_TYPE, NETWORKING_TYPE, PHONE_TYPE, PRINTER_TYPE, PERIPHERAL_TYPE, CONSUMABLE_ITEM_TYPE, CARTRIDGE_ITEM_TYPE);
	$ORDER_RESTRICTED_TYPES = array(0, SOFTWARELICENSE_TYPE, CONSUMABLE_ITEM_TYPE,CARTRIDGE_ITEM_TYPE);
	$ORDER_VALIDATION_STATUS = array(ORDER_STATUS_DRAFT,ORDER_STATUS_WAITING_APPROVAL);

	$ORDER_TYPE_TABLES = array(COMPUTER_TYPE=>"glpi_type_computers",
								MONITOR_TYPE=>"glpi_type_monitors",
								PRINTER_TYPE=>"glpi_type_printers",
								NETWORKING_TYPE=>"glpi_type_printers",
								SOFTWARE_TYPE=>"glpi_type_softwares",
								PERIPHERAL_TYPE=>"glpi_type_peripherals",
								PHONE_TYPE=>"glpi_type_phones",
								CARTRIDGE_ITEM_TYPE=>"glpi_dropdown_cartridge_type",
								CONSUMABLE_ITEM_TYPE=>"glpi_dropdown_consumable_type");
	$ORDER_MODEL_TABLES = array(COMPUTER_TYPE=>"glpi_dropdown_model",
								MONITOR_TYPE=>"glpi_dropdown_model_monitors",
								PRINTER_TYPE=>"glpi_dropdown_model_printers",
								NETWORKING_TYPE=>"glpi_dropdown_model_printers",
								SOFTWARE_TYPE=>"glpi_dropdown_model_softwares",
								PERIPHERAL_TYPE=>"glpi_dropdown_model_peripherals",
								PHONE_TYPE=>"glpi_dropdown_model_phones");
	$ORDER_TEMPLATE_TABLES = array(COMPUTER_TYPE,
								MONITOR_TYPE,
								PRINTER_TYPE,
								NETWORKING_TYPE,
								PERIPHERAL_TYPE,
								PHONE_TYPE);
	
	/* params : plugin name - string type - number - class - table - form page */
	registerPluginType('order', 'PLUGIN_ORDER_TYPE', 3150, array(
		'classname'  => 'PluginOrder',
		'tablename'  => 'glpi_plugin_order',
		'formpage'   => 'front/plugin_order.form.php',
		'searchpage' => 'front/plugin_order.order.php',
		'typename'   => $LANG['plugin_order']['title'][1],
		'deleted_tables' => true,
		'specif_entities_tables' => true,
		'recursive_type' => true
		));

	registerPluginType('order', 'PLUGIN_ORDER_REFERENCE_TYPE', 3151, array(
		'classname'  => 'PluginOrderReference',
		'tablename'  => 'glpi_plugin_order_references',
		'formpage'   => 'front/plugin_order.reference.form.php',
		'searchpage' => 'front/plugin_order.reference.php',
		'typename'   => $LANG['plugin_order']['reference'][1],
		'deleted_tables' => true,
		'specif_entities_tables' => true,
		'recursive_type' => true
		));

	registerPluginType('order', 'PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE', 3152, array(
		'classname'  => 'PluginOrderReferenceManufacturer',
		'tablename'  => 'glpi_plugin_order_references_manufacturers',
		'formpage'   => 'front/plugin_order.referencemanufacturer.form.php',
		'searchpage' => '',
		'typename'   => $LANG['plugin_order']['reference'][5],
		));

	$PLUGIN_HOOKS['pre_item_update']['order'] = 'plugin_pre_item_update_order';
	
	array_push($CFG_GLPI["massiveaction_noupdate_types"],PLUGIN_ORDER_REFERENCE_TYPE);
	
	/* link to the config page in plugins menu */
	if (plugin_order_haveRight("order", "w") || haveRight("config", "w"))
		$PLUGIN_HOOKS['config_page']['order'] = 'front/plugin_order.config.php';
		
	/* load changeprofile function */
	$PLUGIN_HOOKS['change_profile']['order'] = 'plugin_order_changeprofile';
	
	/*if glpi is loaded */
	if (isset($_SESSION["glpiID"])){
		
		if(plugin_order_haveRight("order","r") || plugin_order_haveRight("reference","r"))
		{
			$PLUGIN_HOOKS['redirect_page']['order'] = "front/plugin_order.form.php";
			$PLUGIN_HOOKS['submenu_entry']['order']["<img  src='".$CFG_GLPI["root_doc"]."/pics/menu_show.png' title='".$LANG['plugin_order'][43]."' alt='".$LANG['plugin_order'][43]."'>"] = 'index.php';
			$PLUGIN_HOOKS['menu_entry']['order'] = true;
			$PLUGIN_HOOKS['use_massive_action']['order']=1;
			
			if (haveRight("config","w"))
				$PLUGIN_HOOKS['submenu_entry']['order']['config'] = 'front/plugin_order.config.php';
		}

		if(plugin_order_haveRight("order","r")){
			$PLUGIN_HOOKS['submenu_entry']['order']['search']['order'] = 'front/plugin_order.order.php';
			$PLUGIN_HOOKS['headings']['order'] = 'plugin_get_headings_order';
			$PLUGIN_HOOKS['headings_action']['order'] = 'plugin_headings_actions_order';
		}

		if(plugin_order_haveRight("reference","r")){
			$PLUGIN_HOOKS['submenu_entry']['order']['search']['reference'] = 'front/plugin_order.reference.php';
		}
		
		if (plugin_order_haveRight("reference","w")){
			$PLUGIN_HOOKS['submenu_entry']['order']['add']['reference'] = 'front/plugin_order.reference.form.php';
		}

		if (plugin_order_haveRight("order","w")){
			$PLUGIN_HOOKS['submenu_entry']['order']['add']['order'] = 'front/plugin_order.form.php';
			$PLUGIN_HOOKS['pre_item_delete']['order'] = 'plugin_pre_item_delete_order';
			$PLUGIN_HOOKS['item_purge']['order'] = 'plugin_item_purge_order';
		}
	}
}

/* get the name and the version of the plugin - needed- */
function plugin_version_order(){
	global $LANG;
	
	return array (
		'name' => $LANG['plugin_order']['title'][1],
		'version' => '1.0.0',
		'author'=>'Benjamin Fontan & Walid Nouh',
		'homepage'=>'http://glpi-project.org/wiki/doku.php?id='.substr($_SESSION["glpilanguage"],0,2).':plugins:pluginslist',
		'minGlpiVersion' => '0.72',
	);	
}

/* check prerequisites before install : may print errors or add to message after redirect -optional- */
function plugin_order_check_prerequisites(){
	if (GLPI_VERSION>=0.72){
		return true;
	} else {
		echo "GLPI version not compatible need 0.72";
	}
}

function plugin_order_check_config(){
	return true;
}

/* define rights for the plugin types -needed- */
function plugin_order_haveTypeRight($type,$right){
	switch ($type){
		case PLUGIN_ORDER_TYPE :
			return plugin_order_haveRight("order",$right);
		case PLUGIN_ORDER_REFERENCE_TYPE:
			return plugin_order_haveRight("reference",$right);
		case PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE:
			return plugin_order_haveRight("reference",$right);
	}
}
?>