<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
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

class PluginOrderReception extends CommonDBTM {

   public $dohistory = true;
   public $itemtype  = 'PluginOrderOrder';
   public $items_id  = 'plugin_order_orders_id';

   static function getTable() {
      return "glpi_plugin_order_orders_items";
   }
   
   static function getTypeName($nb=0) {
      return __("Delivery", "order");
   }
   
   static function canCreate() {
      return plugin_order_haveRight('delivery', 'w');
   }

   static function canUpdate() {
      return plugin_order_haveRight('delivery', 'w');
   }

   function canUpdateItem() {
      return true;
   }

   static function canView() {
      return plugin_order_haveRight('delivery', 'r');
   }
   
   function canViewItem() {
      return true;
   }
   
   function getOrdersID() {
      return $this->fields["plugin_order_orders_id"];
   }
   
   function getFromDBByOrder($plugin_order_orders_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->table."`
               WHERE `plugin_order_orders_id` = '" . $plugin_order_orders_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
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
   
   function checkThisItemStatus($detailID, $states_id) {
      global $DB;
      
      $query = "SELECT `states_id`
               FROM `glpi_plugin_order_orders_items`
               WHERE `id` = '$detailID' ";
      $result = $DB->query($query);
      if ($DB->result($result, 0, "states_id") == $states_id) {
         return true;
      } else {
         return false;
      }
   }
   
   function checkItemStatus($plugin_order_orders_id, $plugin_order_references_id, $states_id) {
      return countElementsInTable("glpi_plugin_order_orders_items",
                                  "`plugin_order_orders_id` = '$plugin_order_orders_id'
                                      AND `plugin_order_references_id` = '$plugin_order_references_id'
                                         AND `states_id` = '".$states_id."'");
   }
   
   function deleteDelivery($detailID) {
      global $DB;
      
      $detail = new PluginOrderOrder_Item();
      $detail->getFromDB($detailID);
      
      if ($detail->fields["itemtype"] == 'SoftwareLicense') {
      
         $result = $PluginOrderOrder_Item->queryRef($_POST["plugin_order_orders_id"],
                                                    $detail->fields["plugin_order_references_id"],
                                                    $detail->fields["price_taxfree"],
                                                    $detail->fields["discount"],
                                                    PluginOrderOrder::ORDER_DEVICE_DELIVRED);
         $nb = $DB->numrows($result);
         
         if ($nb) {
            for ($i = 0; $i < $nb; $i++) {
               $detailID = $DB->result($result, $i, 'id');

               $input["id"]                             = $detailID;
               $input["delivery_date"]                  = 'NULL';
               $input["states_id"]                      = PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED;
               $input["delivery_number"]                = "";
               $input["plugin_order_deliverystates_id"] = 0;
               $input["delivery_comment"]               = "";
               $detail->update($input);
            }
         }
      
      } else {
         $values["id"]                             = $detailID;
         $values["date"]                           = 0;
         $values["states_id"]                      = PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED;
         $values["delivery_number"]                = "";
         $values["plugin_order_deliverystates_id"] = 0;
         $values["delivery_comment"]               = "";
         $detail->update($values);
      }
   }
   
   function defineTabs($options=array()) {
      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   function showForm ($ID, $options=array()) {
      

      if (!self::canView()) {
         return false;
      }

      if ($ID > 0) {
         $this->check($ID, 'r');
      } else {
         // Create item
         $this->check(-1, 'w', $options);
      }

      $this->showTabs($options);
      $this->showFormHeader($options);
      
      $order_order = new PluginOrderOrder();
      $order_order->getFromDB($this->getOrdersID());

      $order_reference = new PluginOrderReference();
      $order_reference->getFromDB($this->fields["plugin_order_references_id"]);
      
      $canedit = $order_order->can($this->getOrdersID(), 'w')
                  && !$order_order->canUpdateOrder()  && !$order_order->isCanceled();
      
      echo "<input type='hidden' name='plugin_order_orders_id' value='" .
         $this->getOrdersID() . "'>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . __("Reference") . ": </td>";
      echo "<td>";
      $data         = array();
      $data["id"]   = $this->fields["plugin_order_references_id"];
      $data["name"] = $order_reference->fields["name"];
      echo $order_reference->getReceptionReferenceLink($data);
      echo "</td>";

      echo "<td>".__("Taken delivery", "order")."</td>";
      echo "<td>";
      Dropdown::showYesNo('states_id', $this->fields['states_id']);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . __("Delivery form") . ": </td>";
      echo "<td>";
      if ($canedit) {
         Html::autocompletionTextField($this,"delivery_number");
      } else {
         echo $this->fields["delivery_number"];
      }
      echo "</td>";
      
      echo "<td>" . __("Delivery date") . ": </td>";
      echo "<td>";
      if ($canedit) {
         Html::showDateFormItem("delivery_date", $this->fields["delivery_date"], true, 1);
      } else {
         echo Html::convDate($this->fields["delivery_date"]);
      }
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . __("Delivery status", "order") . ": </td>";
      echo "<td>";
      if ($canedit) {
         PluginOrderDeliveryState::Dropdown(
                        array('name'  => "plugin_order_deliverystates_id",
                              'value' => $this->fields["plugin_order_deliverystates_id"]));
      } else {
         echo Dropdown::getDropdownName("glpi_plugin_order_deliverystates",
                                        $this->fields["plugin_order_deliverystates_id"]);
      }
      echo "</td>";
      
      echo "<td>".__("Bill", "order")."</td>";
      echo "<td>";
      if (plugin_order_haveRight("bill", "w")) {
         PluginOrderBill::Dropdown(array('name'  => "plugin_order_bills_id",
                                         'value' => $this->fields["plugin_order_bills_id"]));
      } elseif (plugin_order_haveRight("bill", "r")) {
         echo Dropdown::getDropdownName("glpi_plugin_order_bills",
                                        $this->fields["plugin_order_bills_id"]);
      }
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><td>";
      //comments of order
      echo __("Comments") . ": </td>";
      echo "<td colspan='3'>";
      if ($canedit) {
         echo "<textarea cols='100' rows='4' name='delivery_comment'>" .
             $this->fields["delivery_comment"] . "</textarea>";
      } else {
         echo $this->fields["delivery_comment"];
      }
      echo "</td>";
      echo "</tr>";
      $options['candel'] = false;
      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }
   
   function showOrderReception($orders_id) {
      global $DB, $CFG_GLPI;

      $order_order      = new PluginOrderOrder();
      $order_item       = new PluginOrderOrder_Item();
      $reference        = new PluginOrderReference();
      $order_order->getFromDB($orders_id);
      
      Session::initNavigateListItems($this->getType(),
                            __("Order", "order") ." = ". $order_order->fields["name"]);
      
      $canedit = self::canCreate()
                   && !$order_order->canUpdateOrder()  && !$order_order->isCanceled();
                            
      $result_ref = $order_item->queryDetail($orders_id);
      $numref     = $DB->numrows($result_ref);
      
      while ($data_ref=$DB->fetch_array($result_ref)){
         
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if (!$numref) {
            echo "<tr><th>" . __("No item to take delivery of", "order") . "</th></tr></table></div>";
         } else {
            $references_id  = $data_ref["id"];
            $typeRef        = $data_ref["itemtype"];
            $price_taxfree  = $data_ref["price_taxfree"];
            $discount       = $data_ref["discount"];

            $item = new $typeRef();
            $rand = mt_rand();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('reception$rand','reception_img$rand', '".
               $CFG_GLPI['root_doc']."/pics/plus.png','".$CFG_GLPI['root_doc']."/pics/moins.png');\">";
            echo "<img alt='' name='reception_img$rand' src=\"".$CFG_GLPI['root_doc']."/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>" . __("Type") . "</th>";
            echo "<th>" . __("Manufacturer") . "</th>";
            echo "<th>" . __("Product reference", "order") . "</th>";
            echo "<th>" . __("Delivered items", "order") . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>" . $item->getTypeName() . "</td>";
            echo "<td align='center'>" . Dropdown::getDropdownName("glpi_manufacturers",
                                                                   $data_ref["manufacturers_id"]) . "</td>";
            echo "<td>" . $reference->getReceptionReferenceLink($data_ref) . "</td>";
            $total = $order_item->getTotalQuantityByRefAndDiscount($orders_id,
                                                                   $references_id,
                                                                   $data_ref["price_taxfree"],
                                                                   $data_ref["discount"]);
            echo "<td>" . $order_item->getDeliveredQuantity($orders_id,
                                                            $references_id,
                                                            $data_ref["price_taxfree"],
                                                            $data_ref["discount"])
                                                            . " / " . $total . "</td>";
            echo "</tr></table>";

            echo "<div class='center' id='reception$rand' style='display:none'>";
            echo "<form method='post' name='order_reception_form$rand' id='order_reception_form$rand'" .
                  " action=\"" . Toolbox::getItemTypeFormURL("PluginOrderReception")."\">";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr>";
            echo "<th width='15'></th>";
            if ($typeRef != 'SoftwareLicense') {
               echo "<th>" . __("ID") . "</th>";
            }
            echo "<th>" . __("Reference") . "</th>";
            echo "<th>" . __("Status") . "</th>";
            echo "<th>" . __("Delivery date") . "</th>";
            echo "<th>" . __("Delivery form") . "</th>";
            echo "<th>" . __("Delivery status", "order") . "</th>";
            echo "</tr>";
            
            $query = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD,
                             `glpi_plugin_order_references`.`id` AS id,
                             `glpi_plugin_order_references`.`templates_id`,
                             `glpi_plugin_order_orders_items`.`states_id`,
                             `glpi_plugin_order_orders_items`.`comment`,
                             `glpi_plugin_order_orders_items`.`plugin_order_deliverystates_id`,
                             `glpi_plugin_order_orders_items`.`delivery_date`,
                             `glpi_plugin_order_orders_items`.`delivery_number`,
                             `glpi_plugin_order_references`.`name`,
                             `glpi_plugin_order_references`.`itemtype`,
                             `glpi_plugin_order_orders_items`.`items_id`
                    FROM `glpi_plugin_order_orders_items`, `glpi_plugin_order_references`
                    WHERE `plugin_order_orders_id` = '$orders_id'
                    AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = '".$references_id."'
                    AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`
                    AND `glpi_plugin_order_orders_items`.`discount` LIKE '".$discount."'
                    AND `glpi_plugin_order_orders_items`.`price_taxfree` LIKE '".$price_taxfree."' ";
            if ($typeRef == 'SoftwareLicense') {
               $query.=" GROUP BY `glpi_plugin_order_references`.`name` ";
            }
            $query.=" ORDER BY `glpi_plugin_order_references`.`name` ";

            $result = $DB->query($query);
            $num    = $DB->numrows($result);
            
            while ($data=$DB->fetch_array($result)){
               $random   = mt_rand();
               $detailID = $data["IDD"];
               Session::addToNavigateListItems($this->getType(), $detailID);
               echo "<tr class='tab_bg_2'>";
               $status    = 1;
               if ($typeRef != 'SoftwareLicense') {
                  $status = $this->checkThisItemStatus($detailID,
                                                       PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED);
               }

               if ($canedit && $status) {
                  echo "<td width='15' align='left'>";
                  $sel = "";
                  if (isset ($_GET["select"]) && $_GET["select"] == "all") {
                     $sel = "checked";
                  }
                  
                  echo "<input type='checkbox' name='item[" . $detailID . "]' value='1' $sel>";
                  echo "</td>";
               } else {
                  echo "<td width='15' align='left'></td>";
               }
               if ($typeRef != 'SoftwareLicense') {
                  echo "<td align='center'>" . $data["IDD"]."&nbsp;";
                  Html::showTooltip($data['comment']);
                  echo "</td>";
               }
               echo "<td align='center'>" . $reference->getReceptionReferenceLink($data) . "</td>";
               echo "<td align='center'>";
               $link=Toolbox::getItemTypeFormURL($this->getType());
               if ($canedit && $data["states_id"] == PluginOrderOrder::ORDER_DEVICE_DELIVRED) {
                  echo "<a href=\"" . $link . "?id=".$data["IDD"]."\">";
               }
               echo $this->getReceptionStatus($detailID);
               if ($canedit && $data["states_id"] == PluginOrderOrder::ORDER_DEVICE_DELIVRED) {
                  echo "</a>";
               }
               echo "</td>";
               echo "<td align='center'>" . Html::convDate($data["delivery_date"]) . "</td>";
               echo "<td align='center'>" . $data["delivery_number"] . "</td>";
               echo "<td align='center'>" .
                  Dropdown::getDropdownName("glpi_plugin_order_deliverystates",
                                            $data["plugin_order_deliverystates_id"]) . "</td>";
               echo "<input type='hidden' name='id[$detailID]' value='$detailID'>";
               echo "<input type='hidden' name='name[$detailID]' value='" . $data["name"] . "'>";
               echo "<input type='hidden' name='plugin_order_references_id[$detailID]' value='" . $data["id"] . "'>";
               echo "<input type='hidden' name='itemtype[$detailID]' value='" . $data["itemtype"] . "'>";
               echo "<input type='hidden' name='templates_id[$detailID]' value='" . $data["templates_id"] . "'>";
               echo "<input type='hidden' name='states_id[$detailID]' value='" . $data["states_id"] . "'>";

            }
            echo "</table>";
            if ($canedit && $this->checkItemStatus($orders_id,
                                                   $references_id,
                                                   PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED)) {
                              Html::openArrowMassives("order_reception_form$rand", true);
               echo "<input type='hidden' name='plugin_order_orders_id' value='$orders_id'>";
               $this->dropdownReceptionActions($typeRef, $references_id,
                                               $orders_id);
               Html::closeArrowMassives(array());
                                                      
                                                      
               $rand = mt_rand();
               
               if ($typeRef != 'SoftwareLicense') {
                  echo "<div id='massreception" . $orders_id.$rand."'></div>\n";
                  
                  echo "<script type='text/javascript' >\n";
                  echo "function viewmassreception" . $orders_id . "$rand(){\n";
                  $params = array ('plugin_order_orders_id'     => $orders_id,
                                   'plugin_order_references_id' => $references_id);
                  Ajax::updateItemJsCode("massreception".$orders_id.$rand,
                                       $CFG_GLPI["root_doc"]."/plugins/order/ajax/massreception.php",
                                       $params, false);
                  echo "};";
                  echo "</script>\n";
                  echo "<p><a href='javascript:viewmassreception".$orders_id."$rand();'>";
                  echo __("Take item delivery (bulk)", "order")."</a></p><br>\n";
               }
            }
            Html::closeForm();
            echo "</div>";
         }
         echo "<br>";
      }
   }
   
   function dropdownReceptionActions($itemtype, $plugin_order_references_id, $plugin_order_orders_id) {
      global $CFG_GLPI;
      
      $rand = mt_rand();

      echo "<select name='receptionActions$rand' id='receptionActions$rand'>";
      echo "<option value='0' selected>".Dropdown::EMPTY_VALUE."</option>";
      echo "<option value='reception'>" . __("Take item delivery", "order") . "</option>";
      echo "</select>";
      $params = array ('action' => '__VALUE__', 'itemtype' => $itemtype,
                       'plugin_order_references_id'=>$plugin_order_references_id,
                       'plugin_order_orders_id'=>$plugin_order_orders_id);
      Ajax::updateItemOnSelectEvent("receptionActions$rand", "show_receptionActions$rand",
                                  $CFG_GLPI["root_doc"] . "/plugins/order/ajax/receptionactions.php",
                                  $params);
      echo "<span id='show_receptionActions$rand'>&nbsp;</span>";
   }
   
   function getReceptionStatus($ID) {
      global $DB;

      $detail = new PluginOrderOrder_Item();
      $detail->getFromDB($ID);

      switch ($detail->fields["states_id"]) {
         case PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED :
            return __("Waiting for delivery", "order");
            
         case PluginOrderOrder::ORDER_DEVICE_DELIVRED :
            return __("Taken delivery", "order");
            
         default :
            return "";
      }
   }

   function updateBulkReceptionStatus($params) {
      global $DB;

      $query = "SELECT `id`, `itemtype`, 'entities_id'
               FROM `glpi_plugin_order_orders_items`
               WHERE `plugin_order_orders_id` = '" . $params["plugin_order_orders_id"] ."'
               AND `plugin_order_references_id` = '" . $params["plugin_order_references_id"] ."'
               AND `states_id` = 0 ";
               
      $result  = $DB->query($query);
      $nb      = $DB->numrows($result);
      
      if ($nb < $params['number_reception']) {
         Session::addMessageAfterRedirect(__("Not enough items to deliver", "order"), true, ERROR);
      } else {
         for ($i = 0; $i < $params['number_reception']; $i++) {
            $this->receptionOneItem($DB->result($result, $i, 0), $params['plugin_order_orders_id'],
                        $params["delivery_date"], $params["delivery_number"],
                        $params["plugin_order_deliverystates_id"]);
            
            // Automatic generate asset
            $options = array( "itemtype"    => $DB->result($result, $i, "itemtype"),
                              "items_id"    => $DB->result($result, $i, "id"),
                              "entities_id" => $DB->result($result, $i, "entities_id"),
                              "plugin_order_orders_id"
                                         => $params['plugin_order_orders_id'],
                              "plugin_order_references_id"
                                         => $params["plugin_order_references_id"]);
            
            self::generateAsset($options);
            $this->updateReceptionStatus(array('item' => array($DB->result($result, $i, 0) => 'on')));
         }
         self::updateDelivryStatus($params['plugin_order_orders_id']);
      }
   }
   
   function receptionOneItem($detailID, $plugin_order_orders_id, $delivery_date,
                             $delivery_number, $plugin_order_deliverystates_id) {
      global $CFG_GLPI;
      
      $detail                                  = new PluginOrderOrder_Item();
      $input["id"]                             = $detailID;
      $input["delivery_date"]                  = $delivery_date;
      $input["states_id"]                      = PluginOrderOrder::ORDER_DEVICE_DELIVRED;
      $input["delivery_number"]                = $delivery_number;
      $input["plugin_order_deliverystates_id"] = $plugin_order_deliverystates_id;
      $detail->update($input);

      Session::addMessageAfterRedirect(__("Item successfully taken delivery", "order"), true);
   }
   
   function receptionAllItem($detailID, $plugin_order_references_id, $plugin_order_orders_id,
                             $delivery_date, $delivery_number, $plugin_order_deliverystates_id) {
      global $DB;
      
      
      $detail = new PluginOrderOrder_Item();
      $detail->getFromDB($detailID);
      $result = $detail->queryRef($_POST["plugin_order_orders_id"],
                                  $plugin_order_references_id,
                                  $detail->fields["price_taxfree"],
                                  $detail->fields["discount"],
                                  PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED);
      $nb = $DB->numrows($result);

      if ($nb) {
         for ($i = 0; $i < $nb; $i++) {
            $detailID                                = $DB->result($result, $i, 'id');
            $input["id"]                             = $detailID;
            $input["delivery_date"]                  = $delivery_date;
            $input["states_id"]                      = PluginOrderOrder::ORDER_DEVICE_DELIVRED;
            $input["delivery_number"]                = $delivery_number;
            $input["plugin_order_deliverystates_id"] = $plugin_order_deliverystates_id;
            $detail->update($input);
         }
      }
      Session::addMessageAfterRedirect(__("Item successfully taken delivery", "order"), true);
   }
   
   function updateReceptionStatus($params) {

      $detail                 = new PluginOrderOrder_Item();
      $plugin_order_orders_id = 0;
      if (isset ($params["item"])) {
         foreach ($params["item"] as $key => $val)
            if ($val == 1) {
               if ($params["itemtype"][$key] == 'SoftwareLicense') {
                  $this->receptionAllItem($key,$params["plugin_order_references_id"][$key],
                                          $params["plugin_order_orders_id"],
                                          $params["delivery_date"], $params["delivery_number"],
                                          $params["plugin_order_deliverystates_id"]);
                                          
                  $plugin_order_orders_id = $params["plugin_order_orders_id"];
               } else {
                  if ($detail->getFromDB($key)) {
                     if (!$plugin_order_orders_id) {
                        $plugin_order_orders_id = $detail->fields["plugin_order_orders_id"];
                     }

                     if ($detail->fields["states_id"] == PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED) {
                        $this->receptionOneItem($key, $plugin_order_orders_id,
                                                $params["delivery_date"], $params["delivery_number"],
                                                $params["plugin_order_deliverystates_id"]);
                     } else {
                        Session::addMessageAfterRedirect(__("Item already taken delivery", "order"), true, ERROR);
                     }
                     
                     
                     // Automatic generate asset
                     $options = array( "itemtype"    => $params["itemtype"][$key],
                                       "items_id"    => $key,
                                       'entities_id' => $detail->getEntityID(),
                                       "plugin_order_orders_id"
                                          => $detail->fields["plugin_order_orders_id"],
                                       "plugin_order_references_id"
                                          => $params["plugin_order_references_id"][$key]);

                     self::generateAsset($options);
                  }
               }
            }// $val == 1

         self::updateDelivryStatus($plugin_order_orders_id);
      } else {
         Session::addMessageAfterRedirect(__("No item selected", "order"), false, ERROR);
      }
   }
   
   static function updateDelivryStatus($orders_id) {
      global $DB;

      $config = PluginOrderConfig::getConfig();
      $order  = new PluginOrderOrder();

      $order->getFromDB($orders_id);

      $query = "SELECT `states_id`
                FROM `glpi_plugin_order_orders_items`
                WHERE `plugin_order_orders_id` = '$orders_id'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      $delivery_status = 0;
      $is_delivered    = 1; //Except order to be totally delivered
      if ($number) {
         while ($data = $DB->fetch_array($result)) {
            if ($data["states_id"] == PluginOrderOrder::ORDER_DEVICE_DELIVRED) {
               $delivery_status = 1;
            } else {
               $is_delivered    = 0;
            }
         }
      }

      //Are all items delivered ?
      if ($is_delivered && !$order->isDelivered()) {
          $order->updateOrderStatus($orders_id, $config->getDeliveredState());
         //At least one item is delivered
      } else {
         if ($delivery_status) {
            $order->updateOrderStatus($orders_id,
                                      $config->getPartiallyDeliveredState());
         }
      }
   }
   
   function prepareInputForUpdate($input) {
      if (isset($input['states_id']) && !$input['states_id']) {
         $input['delivery_date']                  = null;
         $input['delivery_number']                = '';
         $input['plugin_order_deliverystates_id'] = 0;
      }
      return $input;
   }
   
   function post_updateItem($history = 1) {
      self::updateDelivryStatus($this->fields['plugin_order_orders_id']);
   }
   
   function post_purgeItem() {
      self::updateDelivryStatus($this->fields['plugin_order_orders_id']);
   }
   
   /**
   *
   * @param $options
   *
   * return nothing
   */
   static function generateAsset($options = array()) {
      // Retrieve configuration for generate assets feature
      $config = PluginOrderConfig::getConfig();

      if ($config->canGenerateAsset()) {
         // Automatic generate assets on delivery
         
         $rand = mt_rand();
         $item = array( "name"                     => $config->getGeneratedAssetName().$rand,
                        "serial"                   => $config->getGeneratedAssetSerial().$rand,
                        "otherserial"              => $config->getGeneratedAssetOtherserial().$rand,
                        "entities_id"              => $options['entities_id'],
                        "itemtype"                 => $options["itemtype"],
                        "id"                       => $options["items_id"],
                        "plugin_order_orders_id"   => $options["plugin_order_orders_id"]);

         $options_gen = array("plugin_order_orders_id"     => $options["plugin_order_orders_id"],
                              "plugin_order_references_id" => $options["plugin_order_references_id"],
                              "id"                         => array($item));

         if($config->canGenerateTicket()) {
            $options_gen["generate_ticket"] =
               array("entities_id" => $options['entities_id'], 
                     "tickettemplates_id" => $config->fields['tickettemplates_id_delivery']);
         }

         $link = new PluginOrderLink();
         $link->generateNewItem($options_gen);
      }
   }

   static function countForOrder(PluginOrderOrder $item) {
      return countElementsInTable('glpi_plugin_order_orders_items',
                                  "`plugin_order_orders_id` = '".$item->getID()."'");
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      
      
      if ($item->getType()=='PluginOrderOrder') {
         if (plugin_order_haveRight('delivery', 'r')
            && $item->getState() > PluginOrderOrderState::DRAFT) {
            return self::createTabEntry(__("Item delivered", "order"), 
                                        self::countForOrder($item));
         }
      }
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item->getType() == 'PluginOrderOrder') {
         $reception = new self();
         $reception->showOrderReception($item->getID());
      }
      
      return true;
   }
   
}

?>
