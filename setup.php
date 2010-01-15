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

define("ORDER_DEVICE_NOT_DELIVRED", 0);
define("ORDER_DEVICE_DELIVRED", 1);

// Define order status
define("ORDER_STATUS_DRAFT", 0);
define("ORDER_STATUS_WAITING_APPROVAL", 1);
define("ORDER_STATUS_APPROVED", 2);
define("ORDER_STATUS_PARTIALLY_DELIVRED", 3);
define("ORDER_STATUS_COMPLETLY_DELIVERED", 4);
define("ORDER_STATUS_CANCELED", 5);

/* init the hooks of the plugins -needed- */
function plugin_init_order() {
	global $PLUGIN_HOOKS, $CFG_GLPI, $LANG, $ORDER_VALIDATION_STATUS, $ORDER_TEMPLATE_TABLES;
                                    
	$ORDER_VALIDATION_STATUS = array (ORDER_STATUS_DRAFT,
                                    ORDER_STATUS_WAITING_APPROVAL);
		
	$ORDER_TEMPLATE_TABLES = array ('Computer',
                                    'Monitor',
                                    'NetworkEquipment',
                                    'Peripheral',
                                    'Printer',
                                    'Phone');
   
   	
   /* load changeprofile function */
   $PLUGIN_HOOKS['change_profile']['order'] = array('PluginOrderProfile','changeProfile');
		                                 
	Plugin::registerClass('PluginOrderOrder', array(
		'doc_types' => true,
		'massiveaction_noupdate_types' => true
	));
   
   Plugin::registerClass('PluginOrderReference', array(
		'doc_types' => true,
		'massiveaction_noupdate_types' => true
	));
	
	Plugin::registerClass('PluginOrderReference_Supplier', array(
		'doc_types' => true
	));
   
   $plugin = new Plugin;
   
	if ($plugin->isActivated("order"))
	{

		$PLUGIN_HOOKS['pre_item_update']['order'] = 'plugin_pre_item_update_order';
      
		/* link to the config page in plugins menu */
		if (plugin_order_haveRight("order", "w") || haveRight("config", "w"))
			$PLUGIN_HOOKS['config_page']['order'] = 'front/config.form.php';
	
		/*if glpi is loaded */
		if (isset ($_SESSION["glpiID"])) {
	
			if (plugin_order_haveRight("order", "r") || plugin_order_haveRight("reference", "r")) {
            
				$PLUGIN_HOOKS['redirect_page']['order'] = "front/order.form.php";
            $PLUGIN_HOOKS['menu_entry']['order'] = 'front/menu.php';
            $PLUGIN_HOOKS['headings']['order'] = 'plugin_get_headings_order';
				$PLUGIN_HOOKS['headings_action']['order'] = 'plugin_headings_actions_order';
            //menu
            if (plugin_order_haveRight("order","r")) {
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['menu']['title'] = $LANG['plugin_order']['menu'][0];
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['menu']['page']  = '/plugins/order/front/menu.php';
            }
            //order
            if (plugin_order_haveRight("order","r")) {
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['order']['title'] = $LANG['plugin_order']['menu'][4];
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['order']['page']  = '/plugins/order/front/order.php';
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['order']['links']['search'] = '/plugins/order/front/order.php';
            }
            //references
            if (plugin_order_haveRight("reference","r")) {
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['reference']['title'] = $LANG['plugin_order']['menu'][5];
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['reference']['page']  = '/plugins/order/front/reference.php';
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['reference']['links']['search'] = '/plugins/order/front/reference.php';
            }
            //budget
            if (plugin_order_haveRight("budget","r")) {
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['budget']['title'] = $LANG['plugin_order']['menu'][6];
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['budget']['page']  = '/plugins/order/front/budget.php';
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['budget']['links']['search'] = '/plugins/order/front/budget.php';
            }
         }

         if (plugin_order_haveRight("order","w")) {
            //order
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['order']['links']['add']    = '/plugins/order/front/order.form.php';
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['order']['links']['config'] = '/plugins/order/front/config.form.php';
         }
         if (plugin_order_haveRight("reference","w")) {
            //references
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['reference']['links']['add']    = '/plugins/order/front/reference.form.php';
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['reference']['links']['config'] = '/plugins/order/front/config.form.php';
         }
         if (plugin_order_haveRight("budget","w")) {
            //budget
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['budget']['links']['add']    = '/plugins/order/front/budget.form.php';
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['budget']['links']['config'] = '/plugins/order/front/config.form.php';
         }
            if (haveRight("config","w")) {
               $PLUGIN_HOOKS['submenu_entry']['order']['config'] = 'front/config.form.php';
            }
            $PLUGIN_HOOKS['use_massive_action']['order'] = 1;
         
				//$PLUGIN_HOOKS['submenu_entry']['order']["<img  src='" . $CFG_GLPI["root_doc"] . "/pics/menu_show.png' title='" . $LANG['plugin_order'][43] . "' alt='" . $LANG['plugin_order'][43] . "'>"] = 'index.php';
			
			$PLUGIN_HOOKS['pre_item_purge']['order'] = 'plugin_pre_item_purge_order';
         $PLUGIN_HOOKS['item_purge']['order'] = 'plugin_pre_item_purge_order';
		}
	}
}

/* get the name and the version of the plugin - needed- */
function plugin_version_order() {
	global $LANG;

	return array (
		'name' => $LANG['plugin_order']['title'][1],
		'version' => '1.2.0',
		'author' => 'Benjamin Fontan, Walid Nouh, Xavier Caillaud',
		'homepage' => 'https://forge.indepnet.net/projects/show/order',
		'minGlpiVersion' => '0.80',
		
	);
}

/* check prerequisites before install : may print errors or add to message after redirect -optional- */
function plugin_order_check_prerequisites() {
	if (GLPI_VERSION >= 0.80) {
		return true;
	} else {
		echo "GLPI version not compatible need 0.80";
	}
}

function plugin_order_check_config() {
	return true;
}

function plugin_order_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_order_profile"][$module])&&in_array($_SESSION["glpi_plugin_order_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

?>