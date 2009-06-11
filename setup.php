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
    
include_once ("inc/plugin_order.functions_auth.php");
include_once ("inc/plugin_order.profile.class.php");

define ("ORDER_STATUS_NOT_DELIVERED",0);   
define ("ORDER_STATUS_DELIVERED",1);    
/* init the hooks of the plugins -needed- */
function plugin_init_order() {
	global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
	
	/* params : plugin name - string type - number - class - table - form page */
	registerPluginType('order', 'PLUGIN_ORDER_TYPE', 3150, array(
		'classname'  => 'plugin_order',
		'tablename'  => 'glpi_plugin_order',
		'formpage'   => 'front/plugin_order.form.php',
		'searchpage' => 'front/plugin_order.order.php',
		'typename'   => $LANG['plugin_order'][4],
		'deleted_tables' => true,
		'specif_entities_tables' => true,
		'recursive_type' => true
		));

	registerPluginType('order', 'PLUGIN_ORDER_REFERENCE_TYPE', 3151, array(
		'classname'  => 'plugin_order_reference',
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
		'typename'   => $LANG['plugin_order']['reference'][1],
		'deleted_tables' => true,
		'specif_entities_tables' => true,
		'recursive_type' => true
		));

	/* link to the config page in plugins menu */
	if (plugin_order_haveRight("order", "w") || haveRight("config", "w"))
		$PLUGIN_HOOKS['config_page']['order'] = 'front/plugin_order.config.php';
		
	/* load changeprofile function */
	$PLUGIN_HOOKS['change_profile']['order'] = 'plugin_order_changeprofile';
	
	/*if glpi is loaded */
	if (isset($_SESSION["glpiID"])){
		if(plugin_order_haveRight("order","r")){
			$PLUGIN_HOOKS['menu_entry']['order'] = true;
			$PLUGIN_HOOKS['submenu_entry']['order']['search']['order'] = 'front/plugin_order.order.php';
			//$PLUGIN_HOOKS['submenu_entry']['order']['search']['reference'] = 'front/plugin_order.reference.php';
			$PLUGIN_HOOKS['submenu_entry']['order']["<img  src='".$CFG_GLPI["root_doc"]."/pics/menu_show.png' title='".$LANG['plugin_order'][43]."' alt='".$LANG['plugin_order'][43]."'>"]['reference'] = 'index.php';
			$PLUGIN_HOOKS['submenu_entry']['order']["<img  src='".$CFG_GLPI["root_doc"]."/pics/menu_show.png' title='".$LANG['plugin_order'][43]."' alt='".$LANG['plugin_order'][43]."'>"]['order'] = 'index.php';
			$PLUGIN_HOOKS['headings']['order'] = 'plugin_get_headings_order';
			$PLUGIN_HOOKS['headings_action']['order'] = 'plugin_headings_actions_order';
		}

		if(plugin_order_haveRight("reference","r")){
			$PLUGIN_HOOKS['submenu_entry']['order']['search']['reference'] = 'front/plugin_order.reference.php';
		}
		
		if (plugin_order_haveRight("reference","w")){
			$PLUGIN_HOOKS['submenu_entry']['order']['add']['reference'] = 'front/plugin_order.reference.form.php';
			$PLUGIN_HOOKS['submenu_entry']['order']['config']['reference'] = 'front/plugin_order.config.php';
		}

		if (plugin_order_haveRight("order","w")){
			$PLUGIN_HOOKS['submenu_entry']['order']['add']['order'] = 'front/plugin_order.form.php';
			//$PLUGIN_HOOKS['submenu_entry']['order']['config']['order'] = 'front/plugin_order.config.php';
			$PLUGIN_HOOKS['pre_item_delete']['order'] = 'plugin_pre_item_delete_order';
			$PLUGIN_HOOKS['item_purge']['order'] = 'plugin_item_purge_order';
			$PLUGIN_HOOKS['use_massive_action']['order']=1;
		}
	}
}

/* get the name and the version of the plugin - needed- */
function plugin_version_order(){
	global $LANG;
	
	return array (
		'name' => $LANG['plugin_order'][4],
		'version' => '1.0.0',
		'author'=>'',
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
			break;
		case PLUGIN_ORDER_REFERENCE_TYPE:
			return true;
			break;
	}
}
?>