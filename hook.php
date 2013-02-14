<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

function plugin_order_install() {
   global $LANG;
   foreach (glob(GLPI_ROOT . '/plugins/order/inc/*.php') as $file) {
      //Do not load datainjection files (not needed and avoid missing class error message)
      if (!preg_match('/injection.class.php/', $file) ) {
         include_once ($file);
      }
   }

   echo "<center>";
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr><th>".$LANG['plugin_order']['install'][0]."<th></tr>";

   echo "<tr class='tab_bg_1'>";
   echo "<td align='center'>";
   
   $migration = new Migration("1.5.1");
   $classes = array('PluginOrderConfig', 'PluginOrderBillState', 'PluginOrderBillType',
                    'PluginOrderOrderState', 'PluginOrderOrder','PluginOrderOrder_Item',
                    'PluginOrderReference', 'PluginOrderDeliveryState',
                    'PluginOrderNotificationTargetOrder',
                    'PluginOrderOrder_Supplier', 'PluginOrderBill', 'PluginOrderOrderPayment',
                    'PluginOrderOrderType', 'PluginOrderOther', 'PluginOrderOtherType',
                    'PluginOrderPreference', 'PluginOrderProfile', 'PluginOrderReference_Supplier',
                    'PluginOrderSurveySupplier', 'PluginOrderOrderTaxe');
   foreach ($classes as $class) {
      if ($plug=isPluginItemType($class)) {
         $plugname=strtolower($plug['plugin']);
         $dir=GLPI_ROOT . "/plugins/$plugname/inc/";
         $item=strtolower($plug['class']);
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            call_user_func(array($class,'install'),$migration);
         }
      }
   }

   echo "</td>";
   echo "</tr>";
   echo "</table></center>";

   return true;
}

function plugin_order_uninstall() {
   foreach (glob(GLPI_ROOT . '/plugins/order/inc/*.php') as $file) {
      //Do not load datainjection files (not needed and avoid missing class error message)
      if (!preg_match('/injection.class.php/', $file) ) {
         include_once ($file);
      }
   }

   $classes = array('PluginOrderConfig', 'PluginOrderBill', 'PluginOrderBillState',
                    'PluginOrderBillType', 'PluginOrderOrderState', 'PluginOrderOrder',
                    'PluginOrderOrder_Item', 'PluginOrderReference', 'PluginOrderDeliveryState',
                    'PluginOrderNotificationTargetOrder',
                    'PluginOrderOrder_Supplier', 'PluginOrderOrderPayment','PluginOrderOrderTaxe',
                    'PluginOrderOrderType', 'PluginOrderOther', 'PluginOrderOtherType',
                    'PluginOrderPreference', 'PluginOrderProfile', 'PluginOrderReference_Supplier',
                    'PluginOrderSurveySupplier');
      foreach ($classes as $class) {
         call_user_func(array($class,'uninstall'));
      }

   return true;
}

/* define dropdown tables to be manage in GLPI : */
function plugin_order_getDropdown() {
   /* table => name */
   global $LANG;

   $plugin = new Plugin();
   if ($plugin->isActivated("order")) {
      return array ('PluginOrderOrderTaxe'     => $LANG['plugin_order'][25],
                    'PluginOrderOrderPayment'  => $LANG['plugin_order'][32],
                    'PluginOrderOrderType'     => $LANG['common'][17],
                    'PluginOrderOrderState'    => $LANG['plugin_order']['status'][0],
                    'PluginOrderOtherType'     => $LANG['plugin_order'][9],
                    'PluginOrderDeliveryState' => $LANG['plugin_order']['status'][3],
                    'PluginOrderBillState'     => $LANG['plugin_order']['bill'][2],
                    'PluginOrderBillType'      => $LANG['plugin_order']['bill'][1]);
   } else {
      return array ();
   }
}

/* define dropdown relations */
function plugin_order_getDatabaseRelations() {
   $plugin = new Plugin();
   if ($plugin->isActivated("order")) {
      return array (
         "glpi_plugin_order_orderpayments" => array (
            "glpi_plugin_order_orders" => "plugin_order_orderpayments_id"),
         "glpi_plugin_order_ordertaxes" => array (
            "glpi_plugin_order_orders" => "plugin_order_ordertaxes_id"),
         "glpi_plugin_order_ordertypes" => array (
            "glpi_plugin_order_orders" => "plugin_order_ordertypes_id"),
         "glpi_plugin_order_orderstates" => array (
            "glpi_plugin_order_orders" => "plugin_order_orderstates_id"),
         "glpi_plugin_order_deliverystates" => array (
            "glpi_plugin_order_orders_items" => "plugin_order_deliverystates_id"),
         "glpi_plugin_order_orders" => array (
            "glpi_plugin_order_orders_items"     => "plugin_order_orders_id",
            "glpi_plugin_order_orders_suppliers" => "plugin_order_orders_id"),
         "glpi_plugin_order_references" => array (
            "glpi_plugin_order_orders_items"         => "plugin_order_references_id",
            "glpi_plugin_order_references_suppliers" => "plugin_order_references_id"),
         
         "glpi_entities" => array ("glpi_plugin_order_orders"     => "entities_id",
                                   "glpi_plugin_order_references" => "entities_id",
                                   "glpi_plugin_order_others"     => "entities_id",
                                   "glpi_plugin_order_bills"      => "entities_id"),
         "glpi_budgets" => array ("glpi_plugin_order_orders" => "budgets_id"),
         
         "glpi_plugin_order_othertypes" => array ("glpi_plugin_order_others" => "othertypes_id"),
         "glpi_suppliers" => array ("glpi_plugin_order_orders"               => "suppliers_id",
                                    "glpi_plugin_order_orders_suppliers"     => "suppliers_id",
                                    "glpi_plugin_order_references_suppliers" => "suppliers_id"),
                                    
         "glpi_manufacturers" => array ("glpi_plugin_order_references" => "manufacturers_id"),
         "glpi_contacts"      => array ("glpi_plugin_order_orders"     => "contacts_id"),
         "glpi_locations"     => array ("glpi_plugin_order_orders"     => "locations_id"),
         "glpi_profiles"      => array ("glpi_plugin_order_profiles"   => "profiles_id"));

   } else {
      return array ();
   }
}

////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_order_getAddSearchOptions($itemtype) {
   global $LANG;

   $plugin = new Plugin();
   
   $sopt = array();
   if ($plugin->isInstalled('order')
      && $plugin->isActivated('order')
         && plugin_order_haveRight("order","r")) {
      if (in_array($itemtype, PluginOrderOrder_Item::getClasses(true))) {
         $sopt[3160]['table']         = 'glpi_plugin_order_orders';
         $sopt[3160]['field']         = 'name';
         $sopt[3160]['linkfield']     = '';
         $sopt[3160]['name']          = $LANG['plugin_order'][39];
         $sopt[3160]['forcegroupby']  = true;
         $sopt[3160]['datatype']      = 'itemlink';
         $sopt[3160]['itemlink_type'] = 'PluginOrderOrder';

         $sopt[3161]['table']         = 'glpi_plugin_order_orders';
         $sopt[3161]['field']         = 'num_order';
         $sopt[3161]['linkfield']     = '';
         $sopt[3161]['name']          = $LANG['plugin_order'][0];
         $sopt[3161]['forcegroupby']  =  true;
         $sopt[3161]['datatype']      = 'itemlink';
         $sopt[3161]['itemlink_type'] = 'PluginOrderOrder';
      }
   }
   return $sopt;
}

function plugin_order_addSelect($type, $ID, $num) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   if ($table == "glpi_plugin_order_references" && $num != 0) {
      return "`$table`.`itemtype`, `$table`.`$field` AS `ITEM_$num`, ";
   } else {
      return "";
   }

}

function plugin_order_addLeftJoin($type,$ref_table,$new_table,$linkfield,
                                       &$already_link_tables) {

   switch ($new_table){
      case "glpi_plugin_order_orders" : // From items
         $out = " LEFT JOIN `glpi_plugin_order_orders_items` " .
                  "ON (`$ref_table`.`id` = `glpi_plugin_order_orders_items`.`items_id` " .
                     "AND `glpi_plugin_order_orders_items`.`itemtype` = '$type') ";
         $out.= " LEFT JOIN `glpi_plugin_order_orders` " .
                  "ON (`glpi_plugin_order_orders`.`id` = `glpi_plugin_order_orders_items`.`plugin_order_orders_id`) ";
         return $out;
         break;
      case "glpi_budgets" : // From order list
         $out = " LEFT JOIN `glpi_budgets` " .
                  "ON (`glpi_plugin_order_orders`.`budgets_id` = `glpi_budgets`.`id`) ";
         return $out;
         break;
      case "glpi_contacts" : // From order list
         $out = " LEFT JOIN `glpi_contacts` " .
                  "ON (`glpi_plugin_order_orders`.`contacts_id` = `glpi_contacts`.`id`) ";
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

   $reference = new PluginOrderReference();

   switch ($table . '.' . $field) {
      /* display associated items with order */
      case "glpi_plugin_order_references.types_id" :
         if ($data["itemtype"] == 'PluginOrderOther') {
            $file = GLPI_ROOT."/plugins/order/inc/othertype.class.php";
         } else {
            $file = GLPI_ROOT."/inc/".strtolower($data["itemtype"])."type.class.php";
         }
         if (file_exists($file)) {
            return Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Type"),
                                             $data["ITEM_" . $num]);
         } else {
            return " ";
         }
         break;
      case "glpi_plugin_order_references.models_id" :
         if (file_exists(GLPI_ROOT."/inc/".strtolower($data["itemtype"])."model.class.php")) {
            return Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Model"),
                                             $data["ITEM_" . $num]);
 
         } else {
            return " ";
         }
         break;
      case "glpi_plugin_order_references.templates_id" :
         if (!$data["ITEM_" . $num]) {
            return " ";

         } else {
            return $reference->getTemplateName($data["itemtype"], $data["ITEM_" . $num]);
         }
         break;
   }
   return "";
}

function plugin_order_displayConfigItem($type, $ID, $data, $num) {
   global $CFG_GLPI, $LANG;
   
   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];
   switch ($table . '.' . $field) {
      case "glpi_plugin_order_orders.is_late":
         $message = "";
         if ($data["ITEM_" . $num]) {
            $config = PluginOrderConfig::getConfig();
            if ($config->getShouldBeDevileredColor() != '') {
               $message.= " style=\"background-color:".$config->getShouldBeDevileredColor().";\" ";
            }
         }
         return $message;

   }
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_order_MassiveActions($type) {
   global $LANG;

   switch ($type) {
      case 'PluginOrderOrder' :
         return array ("plugin_order_transfert" => $LANG['buttons'][48]);
         break;
         
      case 'PluginOrderReference':
         return array ("plugin_order_copy_reference"     => $LANG['plugin_order']['reference'][13],
                         "plugin_order_transfer_reference" => $LANG['buttons'][48]);
         break;
   }
   return array ();
}

function plugin_order_MassiveActionsDisplay($options=array()) {
   global $LANG;

   switch ($options['itemtype']) {
      case 'PluginOrderOrder' :
         switch ($options['action']) {
            // No case for add_document : use GLPI core one
            case "plugin_order_transfert" :
               Dropdown::show('Entity');
               echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
                  $LANG['buttons'][2] . "\" >";
               break;
         }
         break;
      case 'PluginOrderReference':
         switch ($options['action']) {
            case "plugin_order_copy_reference":
               echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
                     $LANG['buttons'][2] . "\" >";
               break;
                   
            case "plugin_order_transfer_reference" :
               Dropdown::show('Entity');
               echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
                     $LANG['buttons'][2] . "\" >";
               break;
         }
         break;
   }
   return "";
}

function plugin_order_MassiveActionsProcess($data) {
   global $LANG, $DB;

   switch ($data['action']) {
      case "plugin_order_transfert" :
         if ($data['itemtype'] == 'PluginOrderOrder') {
            $order = new PluginOrderOrder();
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $order->transfer($key,$data['entities_id']);
               }
            }
         }
         break;
      case "plugin_order_copy_reference" :
         if ($data['itemtype'] == 'PluginOrderReference') {
             $reference = new PluginOrderReference();
             foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $reference->copy($key);
               }
            }
         }
         break;
      case "plugin_order_transfer_reference" :
         if ($data['itemtype'] == 'PluginOrderReference') {
            $reference = new PluginOrderReference();
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $reference->transfer($key, $data['entities_id']);
               }
            }
         }
         break;
   }
}

/* hook done on purge item case */
function plugin_item_purge_order($item) {
   global $DB;
   $query = "UPDATE `glpi_plugin_order_orders_items`
             SET `items_id`='0'
             WHERE `itemtype`='".$item->getType()."' AND `items_id`='".$item->getField('id')."'";
   $DB->query($query);
    
   return true;
}

/**
 * Get itemtypes to migration from GLPI 0.72 to GLPI 0.78+
 */
function plugin_order_migratetypes($types) {
   $types[3150] = 'PluginOrderOrder';
   $types[3151] = 'PluginOrderReference';
   $types[3152] = 'PluginOrderReference_Supplier';
   $types[3153] = 'PluginOrderBudget';
   $types[3154] = 'PluginOrderReception';
   
   return $types;
}

function plugin_datainjection_populate_order() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginOrderOrderInjection']     = 'order';
   $INJECTABLE_TYPES['PluginOrderReferenceInjection'] = 'order';
}

function plugin_order_AssignToTicket($types) {
   global $LANG;

   if (plugin_order_haveRight("open_ticket", "1")) {
      $types['PluginOrderOrder'] = $LANG['plugin_order'][7];
   }
   return $types;
}
?>