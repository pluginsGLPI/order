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

function plugin_order_install() {
   global $DB;

   include_once(GLPI_ROOT."/plugins/order/inc/profile.class.php");

   if (TableExists("glpi_plugin_order_detail")) {
      if (!FieldExists("glpi_plugin_order_detail","discount")) { // version 1.1.0
      
         $DB->runFile(GLPI_ROOT ."/plugins/order/sql/update-1.1.0.sql");
         
         /* Update en 1.1.0 */

         $query = "SELECT `name` FROM `glpi_dropdown_plugin_order_taxes` ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         if ($number) {
            while ($data=$DB->fetch_array($result)) {
               $findme   = ',';
               if(strpos($data["name"], $findme)) {
                  $name= str_replace(',', '.', $data["name"]);
                  $query = "UPDATE `glpi_dropdown_plugin_order_taxes`
                        SET `name` = '".$name."'
                        WHERE `name`= '".$data["name"]."'";
                  $DB->query($query) or die($DB->error());
               }
            }
         }

         if (FieldExists("glpi_plugin_order","numordersupplier")) {
            $query = "SELECT `numordersupplier`,`numbill`,`ID` FROM `glpi_plugin_order` ";
            $result = $DB->query($query);
            $number = $DB->numrows($result);
            if ($number) {
               while ($data=$DB->fetch_array($result)) {
                  $query = "INSERT INTO  `glpi_plugin_order_suppliers`
                        (`ID`, `FK_order`, `numorder`, `numbill`) VALUES
                     (NULL, '".$data["ID"]."', '".$data["numordersupplier"]."', '".$data["numbill"]."') ";
                  $DB->query($query) or die($DB->error());
               }
            }

            if (FieldExists('glpi_plugin_order', 'numordersupplier')) {
               $query = "ALTER TABLE `glpi_plugin_order` DROP `numordersupplier`";
               $DB->query($query) or die($DB->error());
            }

            if (FieldExists('glpi_plugin_order', 'numbill')) {
               $query = "ALTER TABLE `glpi_plugin_order` DROP `numbill`";
               $DB->query($query) or die($DB->error());
            }
         }
      }
      
      //Post 1.1.0
      $DB->runFile(GLPI_ROOT ."/plugins/order/sql/update-1.2.0.sql");
      
      $query = "SELECT `suppliers_id`,`id` FROM `glpi_plugin_order_orders` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number) {
         while ($data=$DB->fetch_array($result)) {
            $query = "UPDATE `glpi_plugin_order_orders_suppliers`
                  SET `suppliers_id` = '".$data["suppliers_id"]."'
                  WHERE `plugin_order_orders_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }
      
      $query = "SELECT `entities_id`,`is_recursive`,`id` FROM `glpi_plugin_order_orders` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number) {
         while ($data=$DB->fetch_array($result)) {
            $query = "UPDATE `glpi_plugin_order_orders_suppliers`
                  SET `entities_id` = '".$data["entities_id"]."'
                  AND `is_recursive` = '".$data["is_recursive"]."'
                  WHERE `plugin_order_orders_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }
      
      $query = "SELECT `entities_id`,`is_recursive`,`id` FROM `glpi_plugin_order_references` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number) {
         while ($data=$DB->fetch_array($result)) {
            $query = "UPDATE `glpi_plugin_order_references_suppliers`
                  SET `entities_id` = '".$data["entities_id"]."'
                  AND `is_recursive` = '".$data["is_recursive"]."'
                  WHERE `plugin_order_references_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }

      Plugin::migrateItemType(
         array(3150=>'PluginOrderOrder',
               3151=>'PluginOrderReference',
               3152=>'PluginOrderReference_Supplier',
               3153=>'PluginOrderBudget',
               3154=>'PluginOrderOrder_Supplier',
               3155=>'PluginOrderReception'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_order_orders_items", "glpi_plugin_order_references"));

   }
   if (!TableExists("glpi_plugin_order_orders")) { // not installed
      $DB->runFile(GLPI_ROOT ."/plugins/order/sql/empty-1.2.0.sql");
   }

   PluginOrderProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_order_uninstall() {
	global $DB;

	/* drop all the plugin tables */
	$tables = array (
		"glpi_plugin_order_orders",
		"glpi_plugin_order_orders_items",
		"glpi_plugin_order_profiles",
		"glpi_plugin_order_ordertaxes",
		"glpi_plugin_order_orderpayments",
		"glpi_plugin_order_references",
		"glpi_plugin_order_references_suppliers",
		"glpi_plugin_order_configs",
		"glpi_plugin_order_budgets",
      "glpi_plugin_order_orders_suppliers",
      "glpi_plugin_order_others",
      "glpi_plugin_order_othertypes"
	);

	foreach ($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old tables
	$tables = array (
		"glpi_plugin_order",
		"glpi_plugin_order_detail",
		"glpi_plugin_order_device",
		"glpi_plugin_order_profiles",
		"glpi_dropdown_plugin_order_status",
		"glpi_dropdown_plugin_order_taxes",
		"glpi_dropdown_plugin_order_payment",
		"glpi_plugin_order_references",
		"glpi_plugin_order_references_manufacturers",
		"glpi_plugin_order_config",
		"glpi_plugin_order_budgets",
      "glpi_plugin_order_suppliers"
	);

	foreach ($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
	$in = "IN (" . implode(',', array (
		"'PluginOrderOrder'",
		"'PluginOrderReference'",
		"'PluginOrderReference_Supplier'",
		"'PluginOrderBudget'"
	)) . ")";

	$tables = array (
      "glpi_displaypreferences",
		"glpi_documents_items",
		"glpi_bookmarks",
		"glpi_logs"
	);

	foreach ($tables as $table) {
		$query = "DELETE FROM `$table` WHERE (`itemtype` " . $in." ) ";
		$DB->query($query);
	}

	return true;
}

/* define dropdown tables to be manage in GLPI : */
function plugin_order_getDropdown() {
	/* table => name */
	global $LANG;

	$plugin = new Plugin();
	if ($plugin->isActivated("order"))
		return array (
			'PluginOrderOrderTaxe' => $LANG['plugin_order'][25],
			'PluginOrderOrderPayment' => $LANG['plugin_order'][32],
			'PluginOrderOtherType' => $LANG['plugin_order'][9]
		);
	else
		return array ();
}

/* define dropdown relations */
function plugin_order_getDatabaseRelations() {
	$plugin = new Plugin();
	if ($plugin->isActivated("order"))
		return array (
			"glpi_plugin_order_orderpayments" => array (
				"glpi_plugin_order_orders" => "plugin_order_orderpayments_id"
			),
			"glpi_plugin_order_ordertaxes" => array (
				"glpi_plugin_order_orders" => "plugin_order_ordertaxes_id"
			),
			"glpi_plugin_order_ordertaxes" => array (
				"glpi_plugin_order_orders" => "plugin_order_ordertaxes_id"
			),
			"glpi_entities" => array (
				"glpi_plugin_order_orders" => "entities_id",
				"glpi_plugin_order_references" => "entities_id",
				"glpi_plugin_order_others" => "entities_id"
			),
			"glpi_budgets" => array (
				"glpi_plugin_order_orders" => "budgets_id"
			)
		);
	else
		return array ();
}

////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_order_getAddSearchOptions($itemtype) {
   global $LANG;

   $sopt = array();
   if (plugin_order_haveRight("order","r")) {
      if (in_array($itemtype, PluginOrderOrder_Item::getClasses())) {
         $sopt[3160]['table']         = 'glpi_plugin_order_orders';
         $sopt[3160]['field']         = 'name';
         $sopt[3160]['linkfield']     = '';
         $sopt[3160]['name']          = $LANG['plugin_order'][39];
         $sopt[3160]['forcegroupby']  = true;
         $sopt[3160]['datatype']      = 'itemlink';
         $sopt[3160]['itemlink_type'] = 'PluginOrderOrder';

         $sopt[3161]['table']        = 'glpi_plugin_order_orders';
         $sopt[3161]['field']        = 'num_order';
         $sopt[3161]['linkfield']    = '';
         $sopt[3161]['name']         = $LANG['plugin_order'][0];
         $sopt[3161]['forcegroupby'] =  true;
         $sopt[3161]['datatype']      = 'itemlink';
         $sopt[3161]['itemlink_type'] = 'PluginOrderOrder';
      }
   }
   return $sopt;
}

function plugin_order_forceGroupBy($type){

	return true;
	switch ($type){
		case 'PluginOrderOrder':
			return true;
			break;

	}
	return false;
}

function plugin_order_addSelect($type, $ID, $num) {

	$searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

	if ($table == "glpi_plugin_order_references" && $num!=0)
		return "`$table`.`itemtype`, `$table`.`$field` AS `ITEM_$num`, ";
	else
		return "";

}

function plugin_order_addLeftJoin($type,$ref_table,$new_table,$linkfield,
                                       &$already_link_tables) {
   
	switch ($new_table){
		case "glpi_plugin_order_orders" : // From items
			$out= " LEFT JOIN `glpi_plugin_order_orders_items` ON (`$ref_table`.`id` = `glpi_plugin_order_orders_items`.`items_id` AND `glpi_plugin_order_orders_items`.`itemtype` = '$type') ";
			$out.= " LEFT JOIN `glpi_plugin_order_orders` ON (`glpi_plugin_order_orders`.`id` = `glpi_plugin_order_orders_items`.`plugin_order_orders_id`) ";
			return $out;
			break;
      case "glpi_budgets" : // From order list
			$out= " LEFT JOIN `glpi_budgets` ON (`glpi_plugin_order_orders`.`budgets_id` = `glpi_budgets`.`id`) ";
			return $out;
			break;
		case "glpi_contacts" : // From order list
			$out= " LEFT JOIN `glpi_contacts` ON (`glpi_plugin_order_orders`.`contacts_id` = `glpi_contacts`.`id`) ";
			return $out;
			break;
	}

	return "";
}
/* display custom fields in the search */
function plugin_order_giveItem($type, $ID, $data, $num) {
	global $CFG_GLPI, $LANG;

	$searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   $PluginOrderReference = new PluginOrderReference;
   $PluginOrderOrder = new PluginOrderOrder;

	switch ($table . '.' . $field) {
		/* display associated items with order */
		case "glpi_plugin_order_orders.states_id" :
			return $PluginOrderOrder->getDropdownStatus($data["ITEM_" . $num]);
         break;
		case "glpi_plugin_order_references.types_id" :
         if (file_exists(GLPI_ROOT."/inc/".strtolower($data["itemtype"])."type.class.php"))
            return Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Type"), $data["ITEM_" . $num]);
         else
            return " ";
         break;
		case "glpi_plugin_order_references.models_id" :
         if (file_exists(GLPI_ROOT."/inc/".strtolower($data["itemtype"])."model.class.php"))
            return Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Model"), $data["ITEM_" . $num]);
         else
            return " ";
         break;
		case "glpi_plugin_order_references.templates_id" :
			if (!$data["ITEM_" . $num])
				return " ";
			else
				return $PluginOrderReference->getTemplateName($data["itemtype"], $data["ITEM_" . $num]);
         break;
	}
	return "";
}

function plugin_pre_item_update_order($item) {
	global $LANG;
   
   //TO DO : Must do check same values or update infocom
	switch (get_class($item)) {
      case 'Infocom' :
         if (isset ($item->fields["id"])) {
            $item->getFromDB($item->input["id"]);

            if (isset ($item->fields["itemtype"]) & isset ($item->fields["items_id"])) {
               $PluginOrderLink = new PluginOrderLink;
               $PluginOrderOrder = new PluginOrderOrder;
               $PluginOrderOrder_Item = new PluginOrderOrder_Item;
               $PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier;
      
               $detail_id = $PluginOrderLink->isItemLinkedToOrder($item->fields["itemtype"],$item->fields["items_id"]);
               if ($detail_id > 0) {
               
                  $field_set = false;
                  $unset_fields = array (
                     "order_number",
                     "delivery_number",
                     "budgets_id",
                     "suppliers_id",
                     "bill",
                     "value",
                     "buy_date"
                  );
                  
                  $PluginOrderOrder_Item->getFromDB($detail_id);
                  $PluginOrderOrder->getFromDB($PluginOrderOrder_Item->fields["plugin_order_orders_id"]);
                  $PluginOrderOrder_Supplier->getFromDBByOrder($PluginOrderOrder_Item->fields["plugin_order_orders_id"]);
      
                  $value["order_number"] = $PluginOrderOrder->fields["num_order"];
                  $value["delivery_number"] = $PluginOrderOrder_Item->fields["delivery_number"];
                  $value["budgets_id"] = $PluginOrderOrder->fields["budgets_id"];
                  $value["suppliers_id"] = $PluginOrderOrder->fields["suppliers_id"];
                  if (isset($PluginOrderOrder_Supplier->fields["num_bill"]))
                     $value["bill"] = $PluginOrderOrder_Supplier->fields["num_bill"];
                  $value["value"] = $PluginOrderOrder_Item->fields["price_discounted"];
                  $value["buy_date"] = $PluginOrderOrder->fields["order_date"];
      
                  foreach ($unset_fields as $field)
                     if (isset ($item->input[$field])) {
                        $field_set = true;
                        $item->input[$field] = $value[$field];
                     }
                  if ($field_set)
                     addMessageAfterRedirect($LANG['plugin_order']['infocom'][1], true, ERROR);
               }
            }
         }
         break;
   }
}

/* hook done on delete item case */
function plugin_pre_item_purge_order($item) {

	switch (get_class($item)) {
      case 'Profile' :
         // Manipulate data if needed
         $PluginOrderProfile = new PluginOrderProfile;
         $PluginOrderProfile->cleanProfiles($item->getField("id"));
         break;
   }
   return $item;
}

/* hook done on purge item case */
function plugin_item_purge_order($item) {

	$type = get_class($item);
   if (in_array($type, PluginOrderOrder_Item::getClasses())) {

      $temp = new PluginOrderOrder_Item();
      $temp->clean(array('itemtype' => $type,
                         'items_id' => $item->getField('id')));

      return true;
   }
   return false;
}

// Define headings added by the plugin
function plugin_get_headings_order($item,$withtemplate) {
   global $LANG;

   $type = get_Class($item);
   if ($type == 'Profile') {
      if ($item->getField('id') && $item->getField('interface')!='helpdesk') {
         return array(1 => $LANG['plugin_order']['title'][1]);
      }
   } else if (in_array($type, PluginOrderOrder_Item::getClasses()) || $type == 'Supplier' || $type == 'Budget') {
      if ($item->getField('id') && !$withtemplate) {
         // Non template case
         return array(1 => $LANG['plugin_order']['title'][1]);
      }
   } else if ($type == 'Notification') {
      if ($item->getField('id')) {
         return array(1 => $LANG['plugin_order']['title'][1]);
      }
   }
   return false;
}

// Define headings actions added by the plugin
function plugin_headings_actions_order($item) {

   if (in_array(get_class($item),PluginOrderOrder_Item::getClasses())||
		get_class($item)=='Profile' || 
		get_class($item)=='Supplier' || 
		get_class($item)=='Budget' || 
		get_class($item)=='Notification') {
		return array(
			1 => "plugin_headings_order",
		);
	}
	return false;
}

/* action heading */
function plugin_headings_order($item) {
	global $CFG_GLPI;
  
   $PluginOrderProfile=new PluginOrderProfile();
   $PluginOrderMailingSetting = new PluginOrderMailingSetting();
   $PluginOrderOrder_Item = new PluginOrderOrder_Item();
   $PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier();
   $PluginOrderBudget = new PluginOrderBudget();
	switch (get_class($item)) {
      case 'Profile' :
         if (!$PluginOrderProfile->GetfromDB($item->getField('id')))
            $PluginOrderProfile->createAccess($item->getField('id'));
         $PluginOrderProfile->showForm($CFG_GLPI["root_doc"]."/plugins/order/front/profile.form.php",$item->getField('id'));
         break;
      case 'Notification' :
         $PluginOrderMailingSetting->showFormMailing($CFG_GLPI["root_doc"]."/plugins/order/front/mailing.setting.php");
         break;
      case 'Supplier' :
         $PluginOrderOrder_Supplier->showReferencesFromSupplier($item->getField('id'));
         break;
      case 'Budget' :
         $PluginOrderBudget->getAllOrdersByBudget($_POST["id"]);
         break;
      default :
         if (in_array(get_class($item), PluginOrderOrder_Item::getClasses())) {
            $PluginOrderOrder_Item->showPluginFromItems(get_class($item),$item->getField('id'));
         }
         break;
   }
}

?>