<?php
/*
 * @version $Id$
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

class PluginOrderBill extends CommonDropdown {

   public $dohistory         = true;
   public $first_level_menu  = "plugins";
   public $second_level_menu = "order";

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['bill'][0];
   }
   
   function canCreate() {
      return plugin_order_haveRight('bill', 'w');
   }

   function canView() {
      return plugin_order_haveRight('bill', 'r');
   }
   
   function post_getEmpty() {
      $this->fields['value'] = 0;
   }

   function prepareInputForAdd($input) {
      global $LANG;
      
      if (!isset ($input["number"]) || $input["number"] == '') {
         Session::addMessageAfterRedirect($LANG['plugin_order']['bill'][3], false, ERROR);
         return array ();
      }

      return $input;
   }

   function getAdditionalFields() {
      global $LANG;

      return array(array('name'  =>'suppliers_id',
                         'label' => $LANG['financial'][26],
                         'type'  => 'dropdownValue'),
                   array('name'  => 'value',
                         'label' => $LANG['financial'][21],
                         'type'  => 'text'),
                   array('name'  => 'number',
                         'label' => $LANG['financial'][4],
                         'type'  => 'text'),
                   array('name'  => 'billdate',
                         'label' => $LANG['common'][27],
                         'type'  => 'date'),
                   array('name'  => 'plugin_order_billtypes_id',
                         'label' => $LANG['common'][17],
                         'type'  => 'dropdownValue'),
                   array('name'  => 'plugin_order_billstates_id',
                         'label' => $LANG['joblist'][0],
                         'type'  => 'dropdownValue'),
                   array('name'  => 'plugin_order_orders_id',
                         'label' => $LANG['plugin_order'][7],
                         'type'  => 'dropdownValue'),
                   array('name'  => 'users_id_validation',
                         'label' => $LANG['validation'][21],
                         'type'  => 'UserDropdown'),
                   array('name'  => 'validationdate',
                         'label' => $LANG['validation'][4],
                         'type'  => 'date'));
   }
   
   
   function title() {
   }
   
   function defineTabs($options=array()) {
      global $LANG;

      $this->addStandardTab(__CLASS__,$ong,$options);
      $this->addStandardTab('Document',$ong,$options);
      $this->addStandardTab('Note',$ong,$options);
      $this->addStandardTab('Log',$ong,$options);
      return $ong;
   }
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['bill'][0];

      /* order_number */
      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'number';
      $tab[1]['name'] = $LANG['financial'][4];
      $tab[1]['datatype'] = 'itemlink';

      $tab[2]['table'] = $this->getTable();
      $tab[2]['field'] = 'billdate';
      $tab[2]['name'] = $LANG['common'][27];
      $tab[2]['datatype'] = 'datetime';

      $tab[3]['table']    = $this->getTable();
      $tab[3]['field']    = 'validationdate';
      $tab[3]['name']     = $LANG['validation'][4];
      $tab[3]['datatype'] = 'datetime';

      $tab[4]['table']     = getTableForItemType('User');
      $tab[4]['field']     = 'name';
      $tab[4]['linkfield'] = 'users_id_validation';
      $tab[4]['name']      = $LANG['validation'][21];

      $tab[5]['table'] = getTableForItemType('PluginOrderBillType');
      $tab[5]['field'] = 'name';
      $tab[5]['name']  = $LANG['common'][17];
  
      $tab[6]['table'] = getTableForItemType('PluginOrderBillState');
      $tab[6]['field'] = 'name';
      $tab[6]['name']  = $LANG['joblist'][0];

      $tab[7]['table']         = getTableForItemType('Supplier');
      $tab[7]['field']         = 'name';
      $tab[7]['name']          = $LANG['financial'][26];
      $tab[7]['datatype']      = 'itemlink';
      $tab[7]['itemlink_type'] = 'Supplier';

      $tab[8]['table']         = getTableForItemType('PluginOrderOrder');
      $tab[8]['field']         = 'name';
      $tab[8]['name']          = $LANG['plugin_order'][7];
      $tab[8]['datatype']      = 'itemlink';
      $tab[8]['itemlink_type'] = 'PluginOrderOrder';
 
      $tab[9]['table'] = $this->getTable();
      $tab[9]['field'] = 'name';
      $tab[9]['name'] = $LANG['common'][16];
      $tab[9]['datatype'] = 'itemlink';
  
      /* comments */
      $tab[16]['table']    = $this->getTable();
      $tab[16]['field']    = 'comment';
      $tab[16]['name']     = $LANG['plugin_order'][2];
      $tab[16]['datatype'] = 'text';

      /* ID */
      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['name']  = $LANG['common'][2];

      /* entity */
      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name'] = $LANG['entity'][0];

      $tab[86]['table']    = $this->getTable();
      $tab[86]['field']    = 'is_recursive';
      $tab[86]['name']     = $LANG['entity'][9];
      $tab[86]['datatype'] = 'bool';
      $tab[86]['massiveaction'] = false;

      return $tab;
   }

   static function showItems(PluginOrderBill $bill) {
      global $DB, $LANG;
      
      echo "<div class='spaced'><table class='tab_cadre_fixehov'>";
      echo "<tr><th>";
      Html::printPagerForm();
      echo "</th><th colspan='5'>";
      echo $LANG['document'][19];
      echo "</th></tr>";
      
      $bills_id = $bill->getID();
      $query = "SELECT * FROM `".getTableForItemType("PluginOrderOrder_Item");
      $query.= "` WHERE `plugin_order_bills_id` = '$bills_id'";
      $query.= getEntitiesRestrictRequest(" AND", getTableForItemType("PluginOrderOrder_Item"),
                                          "entities_id", $bill->getEntityID(), true);
      $query.= "GROUP BY `itemtype`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      if (!$number) {
         echo "</th><td>";
         echo $LANG['document'][19];
         echo "</td></tr>";
      } else {

         echo "<tr><th>".$LANG['common'][17]."</th>";
         echo "<th>".$LANG['entity'][0]."</th>";
         echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
         echo "<th>".$LANG['state'][0]."</th>";
         //echo "<th>".$LANG['plugin_order']['generation'][9]."</th>";
         echo "</tr>";

         $old_itemtype = '';
         $num          = 0;
         
         while ($data = $DB->fetch_array($result)) {
   
            if (!class_exists($data['itemtype'])) {
               continue;
            }
            $item = new $data['itemtype']();
            if ($item->canView()) {
               echo "<tr class='tab_bg_1'>";

               $ID = "";
               if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                    $ID = " (".$data["id"].")";
               }
               $name = NOT_AVAILABLE;
               if ($item->getFromDB($data["id"])) {
                    $name = $item->getLink();
               }

               echo "<td class='center top'>".$item->getTypeName()."</td>";
               echo "<td class='center top'>";
               echo Dropdown::getDropdownName('glpi_entities', $item->getEntityID())."</td>";
               
               $reference = new PluginOrderReference();
               $reference->getFromDB($data["plugin_order_references_id"]);
               if ($reference->canView()) {
                  echo "<td class='center'><a href='".$reference->getLinkURL()."'>";
                  echo $reference->getName()."</a></td>";
               } else {
                  echo "<td class='center'>".$reference->getName(true)."</td>";
               }
               echo "</td>";
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_plugin_order_deliverystates",
                                                                    $data["plugin_order_deliverystates_id"]);
               echo "</tr>";
            }
         }
      }
      echo "</table></div>";
   }
   
   static function showOrdersItems(PluginOrderBill $bill) {
      global $DB,$LANG,$CFG_GLPI;
      
      $reference  = new PluginOrderReference();
      
      $order = new PluginOrderOrder();
      $order->getFromDB($bill->fields['plugin_order_orders_id']);
      
      //Can write orders, and order is not already paid
      $canedit = $order->can($order->getID(), 'w')
                   && !$order->isPaid() && !$order->isCanceled();

      $query_ref = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD, " .
                     "`glpi_plugin_order_orders_items`.`plugin_order_references_id` AS id, " .
                     "`glpi_plugin_order_references`.`name`, " .
                     "`glpi_plugin_order_references`.`itemtype`, " .
                     "`glpi_plugin_order_references`.`manufacturers_id` " .
                   "FROM `glpi_plugin_order_orders_items`, `glpi_plugin_order_references` " .
                   "WHERE `plugin_order_orders_id` = '".$order->getID()."' " .
                     "AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id` " .
                  "GROUP BY `glpi_plugin_order_orders_items`.`plugin_order_references_id` " .
                  "ORDER BY `glpi_plugin_order_references`.`name`";
      $result_ref = $DB->query($query_ref);

      while ($data_ref = $DB->fetch_array($result_ref)) {
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if (!$DB->numrows($result_ref)) {
            echo "<tr><th>" . $LANG['plugin_order']['detail'][20] . "</th></tr></table></div>";

         } else {
            $order_item = new PluginOrderOrder_Item();
            
            $rand     = mt_rand();
            $itemtype = $data_ref["itemtype"];
            $item     = new $itemtype();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('generation$rand','generation', '".
               $CFG_GLPI['root_doc']."/pics/plus.png','".$CFG_GLPI['root_doc']."/pics/moins.png');\">";
            echo "<img alt='' name='generation' src=\"".$CFG_GLPI['root_doc']."/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>" . $LANG['plugin_order']['detail'][6] . "</th>";
            echo "<th>" . $LANG['common'][5] . "</th>";
            echo "<th>" . $LANG['plugin_order']['reference'][1] . "</th>";
            echo "</tr>";
            
            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>" . $item->getTypeName() . "</td>";

            //Entity
            echo "<td align='center'>";
            echo Dropdown::getDropdownName('glpi_entities', $order->getEntityID());
            echo "</td>";
            echo "<td>" . $reference->getReceptionReferenceLink($data_ref) . "</td>";
            echo "</tr></table>";

            echo "<div class='center' id='generation$rand' style='display:none'>";
            echo "<form method='post' name='bills_form$rand' id='bills_form$rand'  " .
                     "action='" . Toolbox::getItemTypeFormURL('PluginOrderBill') . "'>";
                        
            echo "<input type='hidden' name='plugin_order_orders_id'
                     value='" . $order->getID() . "'>";
            echo "<table class='tab_cadre_fixe'>";

            echo "<th></th>";
            echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][6]."</th>";
            echo "<th>".$LANG['common'][22]."</th>";
            echo "<th>".$LANG['plugin_order']['bill'][0]."</th>";
            echo "<th>".$LANG['plugin_order']['bill'][2]."</th>";
            echo "</tr>";

            $results = $order_item->queryBills($order->getID(), $data_ref['id']);
            while ($data = $DB->fetch_array($results)) {
               echo "<tr class='tab_bg_1'>";
               if ($canedit){
                  echo "<td width='10'>";
                  $sel = "";
                  if (isset($_GET["select"]) && $_GET["select"] == "all") {
                     $sel = "checked";
                  }
                  echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
                  echo "<input type='hidden' name='plugin_order_orders_id' value='" .
                      $order->getID() . "'>";
                  echo "</td>";
               }

               //Reference
               echo "<td align='center'>";
               echo $reference->getReceptionReferenceLink($data);
               echo "</td>";
               
               //Type
               echo "<td align='center'>";
               if (file_exists($CFG_GLPI['root_doc']."/inc/".strtolower($data["itemtype"])."type.class.php")) {
                  echo Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Type"),
                                                                     $data["types_id"]);
               }
               echo "</td>";
               //Model
               echo "<td align='center'>";
               if (file_exists($CFG_GLPI['root_doc']."/inc/".strtolower($data["itemtype"])."model.class.php")) {
                  echo Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Model"),
                                                 $data["models_id"]);
               }
               $bill = new PluginOrderBill();
               echo "<td align='center'>";
               if ($data["plugin_order_bills_id"] > 0) {
                  if ($bill->can($data['plugin_order_bills_id'], 'r')) {
                     echo "<a href='".$bill->getLinkURL()."'>".$bill->getName(true)."</a>";

                  } else {
                     echo $bill->getName();
                  }

               }
               echo "</td>";
               echo "<td align='center'>";
               echo Dropdown::getDropdownName(getTableForItemType('PluginOrderBillState'),
                                                                  $data['plugin_order_billstates_id']);
               echo "</td>";
               echo "</tr>";

            }
         }
            
         echo "</table>";
         if ($canedit) {
            echo "<div class='center'>";
            echo "<table width='950px' class='tab_glpi'>";
            echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"].
               "/pics/arrow-left.png\" alt=''></td><td class='center'>";
            echo "<a onclick= \"if ( markCheckboxes('bills_form$rand') ) " .
                  "return false;\" href='#'>".$LANG['buttons'][18]."</a></td>";

            echo "<td>/</td><td class='center'>";
            echo "<a onclick= \"if ( unMarkCheckboxes('bills_form$rand') ) " .
                  "return false;\" href='#'>".$LANG['buttons'][19]."</a>";
            echo "</td><td align='left' width='80%'>";
            echo "<input type='hidden' name='plugin_order_orders_id' value='".$order->getID()."'>";
            $order_item->dropdownBillItemsActions($order->getID());
            echo "</td>";
            echo "</table>";
            echo "</div>";

         }
         Html::closeForm();
         echo "</div>";
      }
      echo "<br>";
   }
   
   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query ="CREATE TABLE IF NOT EXISTS `glpi_plugin_order_bills` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
              `number` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
              `billdate` datetime DEFAULT NULL,
              `validationdate` datetime DEFAULT NULL,
              `comment` text COLLATE utf8_unicode_ci,
              `plugin_order_billstates_id` int(11) NOT NULL DEFAULT '0',
              `value` decimal(20,4) NOT NULL DEFAULT '0.0000',
              `plugin_order_billtypes_id` int(11) NOT NULL DEFAULT '0',
              `suppliers_id` int(11) NOT NULL DEFAULT '0',
              `plugin_order_orders_id` int(11) NOT NULL DEFAULT '0',
              `users_id_validation` int(11) NOT NULL DEFAULT '0',
              `entities_id` int(11) NOT NULL DEFAULT '0',
              `is_recursive` int(11) NOT NULL DEFAULT '0',
              `notepad` text COLLATE utf8_unicode_ci,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
         $DB->query($query) or die ($DB->error());
      } else {
         if (FieldExists("glpi_plugin_order_orders_suppliers", "num_bill")) {
            //Migrate bills
            $bill  = new PluginOrderBill();
            $query = "SELECT * FROM `glpi_plugin_order_orders_suppliers`";
            foreach (getAllDatasFromTable('glpi_plugin_order_orders_suppliers') as $data) {
               if (!is_null($data['num_bill'])
                  && $data['num_bill'] != ''
                     && !countElementsInTable('glpi_plugin_order_bills',
                                              "`number`='".$data['num_bill']."'")) {
                  //create new bill and link it to the order
                  $tmp['name']                   = $tmp['number'] = $data['num_bill'];
                  //Get supplier from the order
                  $tmp['suppliers_id']        = $data['suppliers_id'];
                  //Bill has the same entities_id and is_recrusive
                  $tmp['entities_id']            = $data['entities_id'];
                  $tmp['is_recursive']           = $data['is_recursive'];
                  //Link bill to order
                  $tmp['plugin_order_orders_id'] = $data['plugin_order_orders_id'];
                  //Create bill
                  $bills_id                      = $bill->add($tmp);
   
                  //All order items are now linked to this bill
                  $query = "UPDATE `glpi_plugin_order_orders_items` " .
                           "SET `plugin_order_bills_id`='$bills_id' " .
                           "WHERE `plugin_order_orders_id`='".$data['plugin_order_orders_id']."'";
                  $DB->query($query);
               }
            }
          }
         $migration->changeField($table, "value", "value", "decimal(20,4) NOT NULL DEFAULT '0.0000'");
         $migration->migrationOneTable($table);
       }
      $migration->dropField("glpi_plugin_order_orders_suppliers", "num_bill");
      $migration->migrationOneTable("glpi_plugin_order_orders_suppliers");
   }
   
   static function uninstall() {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      foreach (array ("glpi_displaypreferences", "glpi_documents_items", "glpi_bookmarks",
                       "glpi_logs") as $t) {
         $query = "DELETE FROM `$t` WHERE `itemtype`='".__CLASS__."'";
         $DB->query($query);
      }
      

      $DB->query("DROP TABLE IF EXISTS`".$table."`") or die ($DB->error());
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {

         if ($item->getType()=='PluginOrderOrder') {

            return self::getTypeName();

         } else if ($item->getType()==__CLASS__) {

            $ong[1] = $LANG['plugin_order']['menu'][4];
            $ong[2] = $LANG['plugin_order']['item'][0];
            return $ong;

         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='PluginOrderOrder') {

         $order_item     = new PluginOrderOrder_Item();
         $order_item->showBillsItems($item);

      } else if ($item->getType()==__CLASS__) {
         switch ($tabnum) {
            case 1 :
               self::showOrdersItems($item);
               break;

            case 2 :
               self::showItems($item);
               break;
         }
      }
      return true;
   }
}
?>