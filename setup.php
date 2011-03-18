<?php
/*
 * @version $Id: HEADER 1 2010-03-03 21:49 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

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
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: plugin order v1.3.0 - GLPI 0.78.3
// ---------------------------------------------------------------------- */

/* init the hooks of the plugins -needed- */
function plugin_init_order() {
	global $PLUGIN_HOOKS, $CFG_GLPI, $LANG;

   /* load changeprofile function */
   $PLUGIN_HOOKS['change_profile']['order'] = array('PluginOrderProfile','changeProfile');
   
   if (class_exists('PluginOrderOrder_Item')) { // only if plugin activated
      $PLUGIN_HOOKS['pre_item_purge']['order'] = array('Profile'=>array('PluginOrderProfile', 'purgeProfiles'));
      $PLUGIN_HOOKS['pre_item_update']['order'] = array('Infocom'=>array('PluginOrderOrder_Item', 'updateItem'));
      $PLUGIN_HOOKS['item_purge']['order'] = array();
      foreach (PluginOrderOrder_Item::getClasses(true) as $type) {
         $PLUGIN_HOOKS['item_purge']['order'][$type] = 'plugin_item_purge_order';
      }
   }
   
	Plugin::registerClass('PluginOrderOrder', array(
		'doc_types' => true,
		'massiveaction_noupdate_types' => true,
		'notificationtemplates_types' => true
	));
   
   Plugin::registerClass('PluginOrderReference', array(
		'doc_types' => true,
		'massiveaction_noupdate_types' => true
	));
	
	Plugin::registerClass('PluginOrderOrder_Item', array(
	  'notificationtemplates_types'  => true
	));
	
	/*Plugin::registerClass('PluginOrderReference_Supplier', array(
		'doc_types' => true
	));*/
   
   /*if glpi is loaded */
   if (getLoginUserID()) {
   
      /* link to the config page in plugins menu */
      if (plugin_order_haveRight("order", "w") || haveRight("config", "w"))
			$PLUGIN_HOOKS['config_page']['order'] = 'front/config.form.php';
	
      if (plugin_order_haveRight("order", "r") || plugin_order_haveRight("reference", "r")) {

         $PLUGIN_HOOKS['menu_entry']['order'] = 'front/menu.php';
         $PLUGIN_HOOKS['headings']['order'] = 'plugin_get_headings_order';
         $PLUGIN_HOOKS['headings_action']['order'] = 'plugin_headings_actions_order';
         
         // Manage redirects
         $PLUGIN_HOOKS['redirect_page']['order']['order']      = "front/order.form.php";
         $PLUGIN_HOOKS['redirect_page']['order']['reference']  = "front/reference.form.php";
         $PLUGIN_HOOKS['redirect_page']['order']['reception']  = "front/reception.form.php";         

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
   }
}

/* get the name and the version of the plugin - needed- */
function plugin_version_order() {
	global $LANG;

	return array (
		'name' => $LANG['plugin_order']['title'][1],
		'version' => '1.3.0',
		'author' => 'Benjamin Fontan, Walid Nouh, Xavier Caillaud, François Legastelois',
		'homepage' => 'https://forge.indepnet.net/projects/show/order',
		'minGlpiVersion' => '0.78.2',
		
	);
}

/* check prerequisites before install : may print errors or add to message after redirect -optional- */
function plugin_order_check_prerequisites(){
	$splitted=explode(".",trim(GLPI_VERSION));
	if ($splitted[0]<10) $splitted[0].="0";
	if ($splitted[1]<10) $splitted[1].="0";
	$cur_version = $splitted[0]*10000+$splitted[1]*100;
	if (isset($splitted[2])) {
		if ($splitted[2]<10) $splitted[2].="0";
		$cur_version+=$splitted[2];
	}
	if ($cur_version>=7820){
		return true;
	} else {
		echo "GLPI version not compatible need 0.78.2";
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

	if (isset($_SESSION["glpi_plugin_order_profile"][$module])
	      && in_array($_SESSION["glpi_plugin_order_profile"][$module],$matches[$right])) {
		return true;
	} else {
	   return false;
	}
}

?>