<?php

/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2023 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderOrder_Supplier extends CommonDBChild {

   public static $rightname = 'plugin_order_order';

   public static $itemtype  = 'PluginOrderOrder';

   public static $items_id  = 'plugin_order_orders_id';

   public $dohistory        = true;


   public static function getTypeName($nb = 0) {
      return __("Supplier Detail", "order");
   }

   public function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'            => 'common',
         'name'          => __('Supplier Detail', 'order'),
      ];

      $tab[] = [
         'id'            => 1,
         'table'         => self::getTable(),
         'field'         => 'num_quote',
         'name'          => __('Quote number', 'order'),
         'datatype'      => 'text',
         'autocomplete'  => true,
      ];

      $tab[] = [
         'id'            => 2,
         'table'         => self::getTable(),
         'field'         => 'num_order',
         'name'          => __('Order number'),
         'datatype'      => 'text',
         'autocomplete'  => true,
      ];

      $tab[] = [
         'id'            => 4,
         'table'         => 'glpi_suppliers',
         'field'         => 'name',
         'name'          => __('Supplier'),
         'datatype'      => 'itemlink',
         'itemlink_type' => 'Supplier',
         'forcegroupby'  => true,
      ];

      $tab[] = [
         'id'            => 30,
         'table'         => self::getTable(),
         'field'         => 'id',
         'name'          => __('ID'),
      ];

      $tab[] = [
         'id'            => 80,
         'table'         => 'glpi_entities',
         'field'         => 'completename',
         'name'          => __('Entity'),
      ];

      return $tab;
   }


   public function defineTabs($options = []) {
      $ong = [];
      $this->addStandardTab('PluginOrderOrder_Supplier', $ong, $options);

      return $ong;
   }


   public function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_order_orders_id']) || $input['plugin_order_orders_id'] <= 0) {
         return false;
      }
      return $input;
   }


   public function getFromDBByOrder($plugin_order_orders_id) {
      global $DB;

      $query = "SELECT * FROM `".self::getTable()."`
               WHERE `plugin_order_orders_id` = '".$plugin_order_orders_id."' ";
      if ($result = $DB->query($query)) {
         if (!$DB->numrows($result)) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }


   public function showForm ($ID, $options = []) {
      $plugin_order_orders_id = -1;
      if (isset($options['plugin_order_orders_id'])) {
         $plugin_order_orders_id = $options['plugin_order_orders_id'];
      }

      $this->initForm($ID, $options);
      $this->showFormHeader($options);
      $PluginOrderOrder = new PluginOrderOrder();
      $PluginOrderOrder->getFromDB($plugin_order_orders_id);
      echo Html::hidden('plugin_order_orders_id', ['value' => $plugin_order_orders_id]);
      echo Html::hidden('entities_id', ['value' => $PluginOrderOrder->getEntityID()]);
      echo Html::hidden('is_recursive', ['value' => $PluginOrderOrder->isRecursive()]);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Supplier").": </td>";
      $supplier = $PluginOrderOrder->fields["suppliers_id"];
      if ($ID > 0) {
         $supplier = $this->fields["suppliers_id"];
      }

      echo "<td>";
      $link = Toolbox::getItemTypeFormURL('Supplier');
      echo "<a href=\"".$link. "?id=".$supplier."\">" .
         Dropdown::getDropdownName("glpi_suppliers", $supplier)."</a></td>";
      echo Html::hidden('suppliers_id', ['value' => $supplier]);
      echo "</td>";

      /* number of quote */
      echo "<td>".__("Quote number", "order").": </td><td>";
      echo Html::input(
         'num_quote',
         [
            'value' => $this->fields['num_quote'],
         ]
      );
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "</td><td colspan='2'></td>";

      /* num order supplier */
      echo "<td>".__("Order number").": </td><td>";
      echo Html::input(
         'num_order',
         [
            'value' => $this->fields['num_order'],
         ]
      );
      echo "</td>";

      echo "</tr>";

      $options['candel'] = false;
      $this->showFormButtons($options);
      return true;
   }


   public static function showOrderSupplierInfos($ID) {

      $order = new PluginOrderOrder();
      $order->getFromDB($ID);

      Session::initNavigateListItems(__CLASS__,
                                     __("Order", "order") ." = ". $order->fields["name"]);

      $candelete = $order->can($ID, UPDATE);
      $rand      = mt_rand();

      $link = Toolbox::getItemTypeFormURL(__CLASS__);

      echo "<form method='post' name='show_supplierinfos$rand' id='show_supplierinfos$rand' " .
            "action=\"".$link."\">";
      echo "<div class='center'>";
      echo Html::hidden('plugin_order_orders_id', ['value' => $ID]);

      $table = self::getTable();
      $nb_elements = countElementsInTable($table, ['plugin_order_orders_id' => $ID]);

      if ($nb_elements > 0) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='4'>".__("Supplier Detail", "order")."</th></tr>";
         echo "<tr>";
         echo "<th>&nbsp;</th>";
         echo "<th>".__("Supplier")."</th>";
         echo "<th>".__("Quote number", "order")."</th>";
         echo "<th>".__("Order number")."</th>";
         echo "</tr>";

         $data = getAllDataFromTable($table, ['plugin_order_orders_id' => $ID]);
         foreach ($data as $cur) {
            Session::addToNavigateListItems(__CLASS__, $cur['id']);
            echo Html::hidden("item[".$cur["id"]."]", ['value' => $ID]);
            echo "<tr class='tab_bg_1 center'>";
            echo "<td>";
            if ($candelete) {
               echo "<input type='checkbox' name='check[".$cur["id"]."]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all') {
                  echo " checked ";
               }
               echo ">";
            }
            echo "</td>";
            echo "<td><a href='".$link."?id=".$cur["id"]."&plugin_order_orders_id=".$ID."'>"
              .Dropdown::getDropdownName("glpi_suppliers", $cur["suppliers_id"])."</a></td>";
            echo "<td>".$cur["num_quote"]."</td>";
            echo "<td>".$cur["num_order"]."</td>";
            echo "</tr>";
         }
         echo "</table></div>";

         if ($candelete) {
            $formname = 'show_supplierinfos' . $rand;
            echo "<table width='950px'>";
            $arrow = "fas fa-level-up-alt";

            echo "<tr>";
            echo "<td><i class='$arrow fa-flip-horizontal fa-lg mx-2'></i></td>";
            echo "<td class='center' style='white-space:nowrap;'>";
            echo "<a onclick= \"if ( markCheckboxes('$formname') ) return false;\" href='#'>" . __('Check all') . "</a></td>";
            echo "<td>/</td>";
            echo "<td class='center' style='white-space:nowrap;'>";
            echo "<a onclick= \"if ( unMarkCheckboxes('$formname') ) return false;\" href='#'>" . __('Uncheck all') . "</a></td>";
            echo "<td class='left' width='80%'>";

            echo "<input type='submit' name='delete' ";
            echo "value=\"" . addslashes(_sx('button', 'Delete permanently')) . "\" class='btn btn-primary'>&nbsp;";
            echo "</td></tr>";
            echo "</table>";
         }

      }
      Html::closeForm();
   }


   public static function showDeliveries($suppliers_id) {
      global $DB;

      $query = "SELECT COUNT(`glpi_plugin_order_orders_items`.`plugin_order_references_id`) AS ref,
                       `glpi_plugin_order_orders_items`.`plugin_order_deliverystates_id` as sid,
                       `glpi_plugin_order_orders`.`entities_id`
                  FROM `glpi_plugin_order_orders_items`
                  LEFT JOIN `glpi_plugin_order_orders`
                     ON (`glpi_plugin_order_orders`.`id` = `glpi_plugin_order_orders_items`.`plugin_order_orders_id`)
                  WHERE `glpi_plugin_order_orders`.`suppliers_id` = '".$suppliers_id."'
                  AND `glpi_plugin_order_orders_items`.`states_id` = '".PluginOrderOrder::ORDER_DEVICE_DELIVRED."' "
                  .getEntitiesRestrictRequest(" AND ", "glpi_plugin_order_orders", '', '', true);
      $query .= "GROUP BY `glpi_plugin_order_orders`.`entities_id`,
                         `glpi_plugin_order_orders_items`.`plugin_order_deliverystates_id`";
      $result = $DB->query($query);
      $nb     = $DB->numrows($result);

      echo "<br><div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th>".__("Entity")."</th>";
      echo "<th>".__("Delivery statistics", "order")."</th>";
      echo "</tr>";

      if ($nb) {
         for ($i = 0; $i < $nb; $i++) {
            $ref               = $DB->result($result, $i, "ref");
            $entities_id       = $DB->result($result, $i, "entities_id");
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
            echo "<td>".$name."&nbsp;:".$ref."</td>";
            echo "</tr>";
         }
      }
      echo "</table>";
      echo "</div>";
   }


   public static function install(Migration $migration) {
      global $DB;

      $default_charset = DBConnection::getDefaultCharset();
      $default_collation = DBConnection::getDefaultCollation();
      $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

      $table = self::getTable();

      if (!$DB->tableExists($table)) {
         if (!$DB->tableExists("glpi_plugin_order_suppliers")) {
            $migration->displayMessage("Installing $table");

            //install
            $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_orders_suppliers` (
                     `id` int {$default_key_sign} NOT NULL auto_increment,
                     `entities_id` int {$default_key_sign} NOT NULL default '0',
                     `is_recursive` tinyint NOT NULL default '0',
                     `plugin_order_orders_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
                     `suppliers_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
                     `num_quote` varchar(255) default NULL,
                     `num_order` varchar(255) default NULL,
                     `num_bill` varchar(255) default NULL,
                     PRIMARY KEY  (`id`),
                     KEY `plugin_order_orders_id` (`plugin_order_orders_id`),
                     KEY `entities_id` (`entities_id`),
                     KEY `suppliers_id` (`suppliers_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->query($query) or die ($DB->error());
         } else {
            //Upgrade
            $migration->displayMessage("Upgrading $table");

            //1.2.0
            $migration->renameTable("glpi_plugin_order_suppliers", $table);

            $migration->addField($table, "entities_id", "int {$default_key_sign} NOT NULL default '0'");
            $migration->addField($table, "is_recursive", "tinyint NOT NULL default '0'");
            $migration->addField($table, "suppliers_id",
                                 "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)'");
            $migration->changeField($table, "ID", "id", "int {$default_key_sign} NOT NULL auto_increment");
            $migration->changeField($table, "FK_order", "plugin_order_orders_id",
                                    "int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)'");
            $migration->changeField($table, "numquote", "num_quote",
                                    "varchar(255) default NULL");
            $migration->changeField($table, "numbill", "num_bill",
                                    "varchar(255) default NULL");
            $migration->changeField($table, "numorder", "num_order",
                                    "varchar(255) default NULL");
            $migration->addKey($table, "plugin_order_orders_id");
            $migration->addKey($table, "suppliers_id");
            $migration->migrationOneTable($table);

            Plugin::migrateItemType([3154 => 'PluginOrderOrder_Supplier'],
                                    ["glpi_savedsearches", "glpi_savedsearches_users",
                                     "glpi_displaypreferences", "glpi_documents_items",
                                     "glpi_infocoms", "glpi_logs", "glpi_items_tickets"],
                                    []);

            //1.5.0
            $query = "SELECT `suppliers_id`, `entities_id`,`is_recursive`,`id`
                      FROM `glpi_plugin_order_orders` ";
            foreach ($DB->request($query) as $data) {
               $query = "UPDATE `glpi_plugin_order_orders_suppliers` SET
                           `suppliers_id` = '{$data["suppliers_id"]}'
                         WHERE `plugin_order_orders_id` = '{$data["id"]}' ";
               $DB->query($query) or die($DB->error());

               $query = "UPDATE `glpi_plugin_order_orders_suppliers` SET
                           `entities_id` = '{$data["entities_id"]}',
                           `is_recursive` = '{$data["is_recursive"]}'
                         WHERE `plugin_order_orders_id` = '{$data["id"]}' ";
               $DB->query($query) or die($DB->error());
            }
         }
      }
   }


   public static function uninstall() {
      global $DB;

      //Old table name
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_order_detail`") or die ($DB->error());

      //Current table name
      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());

   }


   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Supplier':
            return [1 => __("Orders", "order")];
            break;
         case 'PluginOrderOrder':
            $config = PluginOrderConfig::getConfig();
            if ($config->canUseSupplierInformations() && $item->fields['suppliers_id']) {
               return [1 => __("Supplier Detail", "order")];
            }
            break;
         default:
            return '';
      }
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Supplier':
            PluginOrderReference_Supplier::showReferencesFromSupplier($item->getField('id'));
            self::showDeliveries($item->getField('id'));
            PluginOrderSurveySupplier::showGlobalNotation($item->getField('id'));
            break;
         case 'PluginOrderOrder':
            $order_supplier = new self();
            if ($item->can($item->getID(), READ)) {
               self::showOrderSupplierInfos($item->getID());
               $order_supplier->showForm("", ['plugin_order_orders_id' => $item->getID()]);
            }
            break;
      }
      return true;
   }


}
