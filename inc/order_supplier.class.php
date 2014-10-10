<?php
/*
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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderOrder_Supplier extends CommonDBChild {
   
   static public $itemtype  = 'PluginOrderOrder';
   static public $items_id  = 'plugin_order_orders_id';
   public $dohistory = true;
   
   static function getTypeName($nb=0) {
      return __("Supplier Detail", "order");
   }
   
   static function canCreate() {
      return plugin_order_haveRight('order', 'w');
   }

   static function canView() {
      return plugin_order_haveRight('order', 'r');
   }
   
   function getSearchOptions() {
      

      $tab = array();
    
      $tab['common'] = __("Supplier Detail", "order");

      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'num_quote';
      $tab[1]['name'] = __("Quote number", "order");
      $tab[1]['datatype'] = 'text';

      $tab[2]['table'] = $this->getTable();
      $tab[2]['field'] = 'num_order';
      $tab[2]['name'] = __("Order number");
      $tab[2]['datatype'] = 'text';
      
      $tab[4]['table'] = 'glpi_suppliers';
      $tab[4]['field'] = 'name';
      $tab[4]['name'] = __("Supplier");
      $tab[4]['datatype']='itemlink';
      $tab[4]['itemlink_type']='Supplier';
      $tab[4]['forcegroupby']=true;
      
      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['name']=__("ID");

      /* entity */
      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name'] = __("Entity");
      
      return $tab;
   }
   
   function defineTabs($options=array()) {
      
      $ong = array();
      
      $this->addStandardTab('PluginOrderOrder_Supplier',$ong,$options);

      return $ong;
   }
   
   function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_order_orders_id']) || $input['plugin_order_orders_id'] <= 0) {
         return false;
      }
      return $input;
   }
   
   function getFromDBByOrder($plugin_order_orders_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
               WHERE `plugin_order_orders_id` = '" . $plugin_order_orders_id . "' ";
      if ($result = $DB->query($query)) {
         if (!$DB->numrows($result)) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }
   
   function showForm ($ID, $options=array()) {
      
      if (!self::canView())
         return false;
      
      $plugin_order_orders_id = -1;
      if (isset($options['plugin_order_orders_id'])) {
         $plugin_order_orders_id = $options['plugin_order_orders_id'];
      }
        
      if ($ID > 0) {
         $this->check($ID, 'r');
      } else {
         $input = array('plugin_order_orders_id' => $plugin_order_orders_id);
         $this->check(-1, 'w', $input);
         $this->getFromDBByOrder($plugin_order_orders_id);
      }
      
      if (strpos($_SERVER['PHP_SELF'],"order_supplier")) {
         $this->showTabs($options);
      }
      $this->showFormHeader($options);
      $PluginOrderOrder = new PluginOrderOrder();
      $PluginOrderOrder->getFromDB($plugin_order_orders_id);
      echo "<input type='hidden' name='plugin_order_orders_id' value='$plugin_order_orders_id'>";
      echo "<input type='hidden' name='entities_id' value='".$PluginOrderOrder->getEntityID()."'>";
      echo "<input type='hidden' name='is_recursive' value='".$PluginOrderOrder->isRecursive()."'>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Supplier") . ": </td>";
      $supplier = $PluginOrderOrder->fields["suppliers_id"];
      if ($ID > 0) {
         $supplier = $this->fields["suppliers_id"];
      }
         
      echo "<td>";
      $link = Toolbox::getItemTypeFormURL('Supplier');
      echo "<a href=\"" . $link. "?id=" . $supplier . "\">" .
         Dropdown::getDropdownName("glpi_suppliers", $supplier) . "</a></td>";
      echo "<input type='hidden' name='suppliers_id' value='".$supplier."'>";
      echo "</td>";

      /* number of quote */
      echo "<td>" . __("Quote number", "order") . ": </td><td>";
      Html::autocompletionTextField($this,"num_quote");
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "</td><td colspan='2'></td>";
      
      /* num order supplier */
      echo "<td>" . __("Order number") . ": </td><td>";
      Html::autocompletionTextField($this,"num_order");


      echo "</tr>";
      
      $options['candel'] = false;
      $this->showFormButtons($options);
      return true;
   }
   
   static function showOrderSupplierInfos($ID) {
      global $DB, $CFG_GLPI;

      $table = getTableForItemType(__CLASS__);
      $order = new PluginOrderOrder();
      $order->getFromDB($ID);

      Session::initNavigateListItems(__CLASS__,
                                     __("Order", "order") ." = ". $order->fields["name"]);

      $candelete = $order->can($ID,'w');
      $rand      = mt_rand();
      
      
      echo "<form method='post' name='show_supplierinfos$rand' id='show_supplierinfos$rand' " .
            "action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\">";
      echo "<div class='center'>";
      echo "<input type='hidden' name='plugin_order_orders_id' value='" . $ID . "'>";
      
      if (countElementsInTable($table, "`plugin_order_orders_id` = '$ID'") > 0) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='4'>".__("Supplier Detail", "order")."</th></tr>";
         echo "<tr><th>&nbsp;</th>";
         echo "<th>" . __("Supplier") . "</th>";
         echo "<th>" . __("Quote number", "order") . "</th>";
         echo "<th>" . __("Order number") . "</th>";
         echo "</tr>";

         foreach (getAllDatasFromTable($table, "`plugin_order_orders_id` = '$ID'") as $data) {
            Session::addToNavigateListItems(__CLASS__,$data['id']);
            echo "<input type='hidden' name='item[" . $data["id"] . "]' value='" . $ID . "'>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td>";
            if ($candelete) {
               echo "<input type='checkbox' name='check[" . $data["id"] . "]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all') {
                  echo " checked ";
               }
               echo ">";
            }
            echo "</td>";
            $link=Toolbox::getItemTypeFormURL(__CLASS__);
            echo "<td><a href='".$link."?id=".$data["id"]."&plugin_order_orders_id=".$ID."'>" .
               Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]) . "</a></td>";
            echo "<td>";
            echo $data["num_quote"];
            echo "</td>";
            echo "<td>";
            echo $data["num_order"];
            echo "</td>";
            echo "</tr>";
         }
         echo "</table></div>";

         if ($candelete) {
            Html::openArrowMassives("show_supplierinfos$rand", true);
            Html::closeArrowMassives(array('delete' => __("Delete permanently")));
         }

      }
      Html::closeForm();
   }
   
   function checkIfSupplierInfosExists($plugin_order_orders_id) {
      
      if ($plugin_order_orders_id) {
         $devices = getAllDatasFromTable($this->getTable(),
                                         "`plugin_order_orders_id` = '$plugin_order_orders_id' ");
         if (!empty($devices)) {
            return true;
         } else {
            return false;
         }
      }
   }
   
   static function showDeliveries($suppliers_id) {
      global $DB;
      
      $query = "SELECT COUNT(`glpi_plugin_order_orders_items`.`plugin_order_references_id`) AS ref,
                       `glpi_plugin_order_orders_items`.`plugin_order_deliverystates_id` as sid,
                       `glpi_plugin_order_orders`.`entities_id`
                  FROM `glpi_plugin_order_orders_items`
                  LEFT JOIN `glpi_plugin_order_orders`
                     ON (`glpi_plugin_order_orders`.`id` = `glpi_plugin_order_orders_items`.`plugin_order_orders_id`)
                  WHERE `glpi_plugin_order_orders`.`suppliers_id` = '".$suppliers_id."'
                  AND `glpi_plugin_order_orders_items`.`states_id` = '".PluginOrderOrder::ORDER_DEVICE_DELIVRED."' "
                  .getEntitiesRestrictRequest(" AND ","glpi_plugin_order_orders",'','',true);
      $query.= "GROUP BY `glpi_plugin_order_orders`.`entities_id`,
                         `glpi_plugin_order_orders_items`.`plugin_order_deliverystates_id`";
      $result = $DB->query($query);
      $nb     = $DB->numrows($result);
      
      echo "<br><div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th>".__("Entity")."</th>";
      echo "<th>".__("Delivery statistics", "order") ."</th>";
      echo "</tr>";
      
      if ($nb) {
         for ($i = 0 ; $i < $nb ; $i++) {
            $ref               = $DB->result($result,$i,"ref");
            $entities_id       = $DB->result($result,$i,"entities_id");
            $deliverystates_id = $DB->result($result, $i, "sid");
            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo Dropdown::getDropdownName("glpi_entities", $entities_id);
            echo "</td>";
            if ($deliverystates_id > 0) {
               $name = Dropdown::getDropdownName("glpi_plugin_order_deliverystates",
                                                 $deliverystates_id);
            } else {
               $name = __("No specified status", "order");
            }
            echo "<td>" .$name. "&nbsp;:".$ref."</td>";
            echo "</tr>";
         }
      }
      echo "</table>";
      echo "</div>";
   }
   
   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      
      if (!TableExists($table)) {
         if (!TableExists("glpi_plugin_order_suppliers")) {
            $migration->displayMessage("Installing $table");

            //install
            $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_orders_suppliers` (
                     `id` int(11) NOT NULL auto_increment,
                     `entities_id` int(11) NOT NULL default '0',
                     `is_recursive` tinyint(1) NOT NULL default '0',
                     `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
                     `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
                     `num_quote` varchar(255) collate utf8_unicode_ci default NULL,
                     `num_order` varchar(255) collate utf8_unicode_ci default NULL,
                     `num_bill` varchar(255) collate utf8_unicode_ci default NULL,
                     PRIMARY KEY  (`id`),
                     KEY `plugin_order_orders_id` (`plugin_order_orders_id`),
                     KEY `entities_id` (`entities_id`),
                     KEY `suppliers_id` (`suppliers_id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die ($DB->error());
         } else {
            //Upgrade
            $migration->displayMessage("Upgrading $table");

            //1.2.0
            $migration->renameTable("glpi_plugin_order_suppliers", $table);

            $migration->addField($table, "entities_id", "int(11) NOT NULL default '0'");
            $migration->addField($table, "is_recursive", "tinyint(1) NOT NULL default '0'");
            $migration->addField($table, "suppliers_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)'");
            $migration->changeField($table, "ID", "id",  "int(11) NOT NULL auto_increment");
            $migration->changeField($table, "FK_order", "plugin_order_orders_id",
                                    "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)'");
            $migration->changeField($table, "numquote", "num_quote",
                                    "varchar(255) collate utf8_unicode_ci default NULL");
            $migration->changeField($table, "numbill", "num_bill",
                                    "varchar(255) collate utf8_unicode_ci default NULL");
            $migration->changeField($table, "numorder", "num_order",
                                    "varchar(255) collate utf8_unicode_ci default NULL");
            $migration->addKey($table, "plugin_order_orders_id");
            $migration->addKey($table, "suppliers_id");
            $migration->migrationOneTable($table);

            Plugin::migrateItemType(array(3154 => 'PluginOrderOrder_Supplier'),
                                    array("glpi_bookmarks", "glpi_bookmarks_users",
                                          "glpi_displaypreferences", "glpi_documents_items",
                                          "glpi_infocoms", "glpi_logs", "glpi_tickets"),
                                    array());

            //1.5.0
            $query = "SELECT `suppliers_id`, `entities_id`,`is_recursive`,`id` " .
                     "FROM `glpi_plugin_order_orders` ";
            foreach ($DB->request($query) as $data) {
               $query = "UPDATE `glpi_plugin_order_orders_suppliers`
                         SET `suppliers_id` = '".$data["suppliers_id"]."'
                         WHERE `plugin_order_orders_id` = '".$data["id"]."' ";
               $DB->query($query) or die($DB->error());
      
               $query = "UPDATE `glpi_plugin_order_orders_suppliers`
                         SET `entities_id` = '".$data["entities_id"]."',
                             `is_recursive` = '".$data["is_recursive"]."'
                         WHERE `plugin_order_orders_id` = '".$data["id"]."' ";
               $DB->query($query) or die($DB->error());
               
             
            }
         }
      }
   }
   
   static function uninstall() {
      global $DB;

      //Old table name
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_order_detail`") or die ($DB->error());
      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `".getTableForItemType(__CLASS__)."`") or die ($DB->error());
      
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      
      if (get_class($item) == 'Supplier') {
         return array(1 => __("Orders", "order"));
      } elseif (get_class($item) == 'PluginOrderOrder') {
         $config = PluginOrderConfig::getConfig();
         if ($config->canUseSupplierInformations() && $item->fields['suppliers_id']) {
            return array(1 => __("Supplier Detail", "order"));
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if (get_class($item) == 'Supplier') {
         PluginOrderReference_Supplier::showReferencesFromSupplier($item->getField('id'));
         self::showDeliveries($item->getField('id'));
         PluginOrderSurveySupplier::showGlobalNotation($item->getField('id'));
      } elseif (get_class($item) == 'PluginOrderOrder') {
         $order_supplier = new self();
         self::showOrderSupplierInfos($item->getID());
         if (!$order_supplier->checkIfSupplierInfosExists($item->getID())
            && $item->can($item->getID(),'w')) {
            self::showOrderSupplierInfos($item->getID());
            $order_supplier->showForm("", array('plugin_order_orders_id' => $item->getID()));
         }
      }
      return true;
   }
   
}

?>