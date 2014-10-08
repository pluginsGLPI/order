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

class PluginOrderLink extends CommonDBChild {

   public $dohistory = true;
   static public $itemtype  = 'PluginOrderOrder';
   static public $items_id  = 'plugin_order_orders_id';
   
   static function getTypeName($nb=0) {
      

      return __("Generation", "order");
   }
   
   static function getTable() {
      return "glpi_plugin_order_orders_items";
   }
   
   static function canCreate() {
      return plugin_order_haveRight('delivery', 'w');
   }

   static function canView() {
      return plugin_order_haveRight('delivery', 'r');
   }
   
   static function getTypesThanCannotBeGenerared() {
      return array('ConsumableItem', 'CartridgeItem', 'SoftwareLicense', 'Contract');
   }
   
   function showItemGenerationForm($params) {
      global $CFG_GLPI;
      
      // Retrieve configuration for generate assets feature
      $config = PluginOrderConfig::getConfig();
      
      echo "<a href='" . $_SERVER["HTTP_REFERER"] . "'>" . __("Back") . "</a></br><br>";
      echo "<form method='post' name='order_deviceGeneration' id='order_deviceGeneration' action=\"" .
         Toolbox::getItemTypeFormURL("PluginOrderLink")."\">";
      echo "<div class='center'>";
         
      echo "<table class='tab_cadre_fixe'>";
      $colspan = "7";
      if (Session::isMultiEntitiesMode()) {
         $colspan = "8";
      }
      echo "<tr><th colspan='$colspan'>" . __("Generate item", "order") . "</tr></th>";
      echo "<tr><th>" . __("Product reference", "order") . "</th>";
      echo "<th>" . __("Name") . "</th>";
      echo "<th>" . __("Serial Number") . "</th>";
      echo "<th>" . __("Inventory number") . "</th>";
      echo "<th>" . __("Template name") . "</th>";
      if (Session::isMultiEntitiesMode() && count($_SESSION['glpiactiveentities']) > 1) {
         echo "<th>" . __("Entity") . "</th>";
      }
      echo "</tr>";
      echo "<input type='hidden' name='plugin_order_orders_id' value=" .
         $params["plugin_order_orders_id"] . ">";
      echo "<input type='hidden' name='plugin_order_references_id' value=" .
         $params["plugin_order_references_id"] . ">";

      $order = new PluginOrderOrder();
      $order->getFromDB($params["plugin_order_orders_id"]);
      
      $reference = new PluginOrderReference();
      $i         = 0;
      $found     = false;

      foreach ($params["item"] as $key => $val)
         if ($val == 1) {
            $detail = new PluginOrderOrder_Item();
            $detail->getFromDB($key);
            if (!$detail->fields["items_id"]) {
               echo "<tr class='tab_bg_1'><td align='center'>" . $_POST["name"][$key] . "</td>";
               $templateID = $reference->checkIfTemplateExistsInEntity($params["id"][$key],
                                                                       $params['itemtype'][$key],
                                                                       $order->fields["entities_id"]);

               if ($templateID) {
                  $item = new $params['itemtype'][$key]();
                  $item->getFromDB($templateID);
                  
                  $name        = $item->fields["name"];
                  $serial      = $item->fields["serial"];
                  $otherserial = $item->fields["otherserial"];
               } else {
                  $name        = false;
                  $serial      = false;
                  $otherserial = false;
               }

               if (!$name) {
                  echo "<td><input type='text' size='20' name='id[$i][name]'></td>";
               } else {
                  echo "<td align='center'>".Dropdown::EMPTY_VALUE."</td>";
                  echo "<input type='hidden' name='id[$i][name]' value=''>";
               }
               
               echo "<td align='center'><input type='text' size='20' name='id[$i][serial]'></td>";

               if ($otherserial) {
                  echo "<td align='center'>".Dropdown::EMPTY_VALUE."</td>";
                  echo "<input type='hidden' name='id[$i][otherserial]' value=''>";
               } else {
                  echo "<td><input type='text' size='20' name='id[$i][otherserial]'></td>";
               }
                              
               echo "<td align='center'>";
               if ($templateID) {
                  echo $reference->getTemplateName($params['itemtype'][$key], $templateID);
               }
               echo "</td>";
               
               if (Session::isMultiEntitiesMode() && count($_SESSION['glpiactiveentities']) > 1) {
                  echo "<td>";
                  $entity_restrict = ($order->fields["is_recursive"] ?
                     getSonsOf('glpi_entities',$order->fields["entities_id"])
                        : $order->fields["entities_id"]);
                  Entity::Dropdown(array('name' => "id[$i][entities_id]",
                                         'value' => $order->fields["entities_id"],
                                         'entity' => $entity_restrict));
                  echo "</td>";
               } else {
                  echo "<input type='hidden' name='id[$i][entities_id]' value=" .
                     $_SESSION["glpiactive_entity"] . ">";
               }
               echo "</tr>";
               echo "<input type='hidden' name='id[$i][itemtype]' value=" . $params['itemtype'][$key] . ">";
               echo "<input type='hidden' name='id[$i][id]' value=" . $params["id"][$key] . ">";
               echo "<input type='hidden' name='id[$i][plugin_order_orders_id]' value=" .
                  $params["plugin_order_orders_id"] . ">";
               $found = true;
            }
            $i++;
         }

      if ($found) {
         echo "<tr><td align='center' colspan='$colspan' class='tab_bg_2'>";
         echo "<input type='submit' name='generate' class='submit' value=" .
            __("Generate", "order") . "></td></tr>";
      } else {
         echo "<tr><td align='center' colspan='$colspan' class='tab_bg_2'>" .
            __("No item to generate", "order") . "</td></tr>";
      }

      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }
   
   function showOrderLink($plugin_order_orders_id) {
      global $DB, $CFG_GLPI;

      $PluginOrderOrder      = new PluginOrderOrder();
      $PluginOrderOrder_Item = new PluginOrderOrder_Item();
      $PluginOrderReference  = new PluginOrderReference();
      $PluginOrderReception  = new PluginOrderReception();
      
      $PluginOrderOrder->getFromDB($plugin_order_orders_id);
      $canedit = self::canCreate()
                  && !$PluginOrderOrder->canUpdateOrder()
                     && !$PluginOrderOrder->isCanceled();
      
      $query_ref = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD, " .
                     "`glpi_plugin_order_orders_items`.`plugin_order_references_id` AS id, " .
                     "`glpi_plugin_order_references`.`name`, " .
                     "`glpi_plugin_order_references`.`itemtype`, " .
                     "`glpi_plugin_order_references`.`manufacturers_id`, " .
                     "`glpi_plugin_order_orders_items`.`price_taxfree`, " .
                     "`glpi_plugin_order_orders_items`.`discount` " .
                   "FROM `glpi_plugin_order_orders_items`, `glpi_plugin_order_references` " .
                   "WHERE `plugin_order_orders_id` = '$plugin_order_orders_id' " .
                     "AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`  " .
                        "AND `glpi_plugin_order_orders_items`.`states_id` = '".PluginOrderOrder::ORDER_DEVICE_DELIVRED."'   " .
                  "GROUP BY `glpi_plugin_order_orders_items`.`plugin_order_references_id` " .
                  "ORDER BY `glpi_plugin_order_references`.`name`";

      $result_ref = $DB->query($query_ref);
      $numref     = $DB->numrows($result_ref);

      while ($data_ref = $DB->fetch_array($result_ref)){
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if (!$numref) {
            echo "<tr><th>" . __("No item to take delivery of", "order") . "</th></tr></table></div>";
         } else {
            $plugin_order_references_id = $data_ref["id"];
            $itemtype                   = $data_ref["itemtype"];
            $canuse                     = ($itemtype != 'PluginOrderOther');
            $item                       = new $itemtype();
            $rand                       = mt_rand();
            
            $query = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD, " .
                        "`glpi_plugin_order_references`.`id` AS id," .
                        "`glpi_plugin_order_references`.`templates_id`, " .
                        "`glpi_plugin_order_orders_items`.`states_id`, " .
                        "`glpi_plugin_order_orders_items`.`delivery_date`," .
                        "`glpi_plugin_order_orders_items`.`delivery_number`, " .
                        "`glpi_plugin_order_references`.`name`, " .
                        "`glpi_plugin_order_references`.`itemtype`, " .
                        "`glpi_plugin_order_orders_items`.`items_id`," .
                        "`glpi_plugin_order_orders_items`.`price_taxfree`, " .
                        "`glpi_plugin_order_orders_items`.`discount`
                    FROM `glpi_plugin_order_orders_items`, `glpi_plugin_order_references`
                    WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
                    AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = '".$plugin_order_references_id."'
                    AND `glpi_plugin_order_orders_items`.`states_id` = '".PluginOrderOrder::ORDER_DEVICE_DELIVRED."'
                    AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id` ";
            if ($itemtype == 'SoftwareLicense')
               $query.=" GROUP BY `glpi_plugin_order_orders_items`.`price_taxfree`," .
                        "`glpi_plugin_order_orders_items`.`discount` ";
            $query.=" ORDER BY `glpi_plugin_order_references`.`name` ";

            $result = $DB->query($query);
            $num    = $DB->numrows($result);
            
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('generation$rand','generation_img$rand', " .
                    "'".$CFG_GLPI['root_doc']."/pics/plus.png','".$CFG_GLPI['root_doc']."/pics/moins.png');\">";
            echo "<img alt='' name='generation_img$rand' src=\"".$CFG_GLPI['root_doc']."/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>" . __("Type") . "</th>";
            echo "<th>" . __("Manufacturer") . "</th>";
            echo "<th>" . __("Product reference", "order") . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>" . $item->getTypeName() . "</td>";
            echo "<td align='center'>" .
               Dropdown::getDropdownName("glpi_manufacturers", $data_ref["manufacturers_id"]) .
                  "</td>";
            echo "<td>" . $PluginOrderReference->getReceptionReferenceLink($data_ref) . "&nbsp;($num)</td>";
            echo "</tr></table>";

            echo "<div class='center' id='generation$rand' style='display:none'>";
            echo "<form method='post' name='order_generation_form$rand' " .
                  "id='order_generation_form$rand'  action=\"" .
                     Toolbox::getItemTypeFormUrl(__CLASS__)."\">";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr>";
            if ($canedit & $canuse) {
               echo "<th width='15'></th>";
            }
            if ($itemtype != 'SoftwareLicense') {
               echo "<th>" . __("ID") . "</th>";
            } else {
               echo "<th>" . __("Quantity", "order") . "</th>";
            }
            echo "<th>" . __("Reference") . "</th>";
            echo "<th>" . __("Status") . "</th>";
            echo "<th>" . __("Delivery date") . "</th>";
            echo "<th>" . _n("Associated item", "Associated items", 2) . "</th></tr>";
            
            while ($data=$DB->fetch_array($result)){
               $random   = mt_rand();
               $detailID = $data["IDD"];

               echo "<tr class='tab_bg_2'>";
               if ($canedit & $canuse) {
                  echo "<td width='15' align='left'>";
                  $sel = "";
                  if (isset ($_GET["select"]) && $_GET["select"] == "all") {
                     $sel = "checked";
                  }
                  
                  echo "<input type='checkbox' name='item[" . $detailID . "]' value='1' $sel>";
                  echo "</td>";
               }
               
               if ($itemtype != 'SoftwareLicense') {
                  echo "<td align='center'>" . $data["IDD"] . "</td>";
               } else {
                  echo "<td align='center'>" .
                     $PluginOrderOrder_Item->getTotalQuantityByRefAndDiscount($plugin_order_orders_id,
                                                                              $plugin_order_references_id,
                                                                              $data["price_taxfree"],
                                                                              $data["discount"]);
                     echo "</td>";
               }
               echo "<td align='center'>" . $PluginOrderReference->getReceptionReferenceLink($data) . "</td>";
               echo "<td align='center'>" . $PluginOrderReception->getReceptionStatus($detailID) . "</td>";
               echo "<td align='center'>" . Html::convDate($data["delivery_date"]) . "</td>";
               echo "<td align='center'>" . $this->getReceptionItemName($data["items_id"], $data["itemtype"]);
               echo "<input type='hidden' name='id[$detailID]' value='$detailID'>";
               echo "<input type='hidden' name='name[$detailID]' value='" . $data["name"] . "'>";
               echo "<input type='hidden' name='itemtype[$detailID]' value='" . $data["itemtype"] . "'>";
               echo "<input type='hidden' name='templates_id[$detailID]' value='" . $data["templates_id"] . "'>";
               echo "<input type='hidden' name='states_id[$detailID]' value='" . $data["states_id"] . "'>";

            }
            echo "</tr>";
            echo "</table>";
            if ($canedit & $canuse) {
               Html::openArrowMassives("order_generation_form$rand", true);
               echo "<input type='hidden' name='plugin_order_orders_id' value='$plugin_order_orders_id'>";
               $entities_id = $PluginOrderOrder->fields['entities_id'];
               
               $this->dropdownLinkActions($itemtype, $plugin_order_references_id, $plugin_order_orders_id, $entities_id);
               Html::closeArrowMassives(array());
            }
            
            Html::closeForm();
            echo "</div>";
         }
         echo "<br>";
      }
   }
   
   function getLinkedItemDetails($itemtype, $items_id) {
      
      
      $comments = "";
      switch ($itemtype) {
         case 'Computer' :
         case 'Monitor' :
         case 'NetworkEquipment' :
         case 'Peripheral' :
         case 'Phone' :
         case 'Printer' :
         default :
            
            $item = new $itemtype();
            $item->getFromDB($items_id);

            if ($item->getField("name")) {
               $comments = "<strong>" . __("Name") .
                  ":</strong> " . $item->getField("name");
            }

            if ($item->getField("entities_id")) {
               $comments = "<strong>" . __("Entity") . ":</strong> " .
                  Dropdown::getDropdownName("glpi_entities", $item->getField("entities_id"));
            }

            if ($item->getField("serial") != '') {
               $comments .= "<br><strong>" . __("Serial Number") . ":</strong> " .
                  $item->getField("serial");
            }

            if ($item->getField("otherserial") != '') {
               $comments .= "<br><strong>" . __("Inventory number") . ":</strong> " .
                  $item->getField("otherserial");
            }
            if ($item->getField("locations_id")) {
               $comments .= "<br><strong>" . __("Location") . ":</strong> " .
                  Dropdown::getDropdownName('glpi_locations', $item->getField("locations_id"));
            }

            if ($item->getField("users_id")) {
               $comments .= "<br><strong>" . __("User") . ":</strong> " .
                  Dropdown::getDropdownName('glpi_users', $item->getField("users_id"));
            }
            break;
         case 'ConsumableItem' :
            $ci = new Consumable();
            if ($ci->getFromDB($items_id)) {
               $ct = new ConsumableItem();
               $ct->getFromDB($ci->fields['consumableitems_id']);
               $comments = "<strong>" . __("Entity") . ":</strong> " .
                  Dropdown::getDropdownName("glpi_entities", $ct->fields["entities_id"]);
               $comments .= '<br><strong>' . __("Consumable") . ' : </strong> #' . $items_id;
               $comments .= '<br><strong>' . __("Consumable type") . ' : </strong>' .
                  $ct->fields['name'];
               $comments .= '<br><strong>' . __("Manufacturer") . ' : </strong>' .
                  Dropdown::getDropdownName('glpi_manufacturers', $ct->fields['manufacturers_id']);
               $comments .= '<br><strong>' . __("State") . ' : </strong>' .
                  (!$ci->fields['users_id'] ? __("In stock") : __("Used"));
               if ($ci->fields['users_id']) {
                  $comments .= '<br><strong>' . __("User") . ' : </strong>' .
                     Dropdown::getDropdownName('glpi_users', $ci->fields['users_id']);
               }
            }
            break;
         case 'CartridgeItem' :
            $ci = new Cartridge();
            if ($ci->getFromDB($items_id)) {
               $ct = new CartridgeItem();
               $ct->getFromDB($ci->fields['cartridgeitems_id']);
               $comments = "<strong>" . __("Entity") . ":</strong> " .
                  Dropdown::getDropdownName("glpi_entities", $ct->fields["entities_id"]);
               $comments .= '<br><strong>' . __("Cartridge") . ' : </strong> #' . $items_id;
               $comments .= '<br><strong>' . _n("New", "New", 2) . ' : </strong>' .
                  $ct->fields['name'];
               $comments .= '<br><strong>' . __("Manufacturer") . ' : </strong>' .
                  Dropdown::getDropdownName('glpi_manufacturers', $ct->fields['manufacturers_id']);
            }
      }

      return ($comments);
   }

   function getReceptionItemName($items_id, $itemtype) {
      global $CFG_GLPI;
      
      if ($items_id == 0) {
         return (__("No associated item", "order"));
      } else {
         switch ($itemtype) {
            case 'ConsumableItem' :
            case 'CartridgeItem' :
               $table = getTableForItemType($itemtype);
               if ($itemtype == 'ConsumableItem') {
                  $item = new Consumable();
               } else {
                  $item = new Cartridge();
               }
               $item->getFromDB($items_id);
               $item_type = new $itemtype();
               $item_type->getFromDB($item->fields[getForeignKeyFieldForTable($table)]);
               return $item_type->getLink(true);
            default :
               $item = new $itemtype();
               $item->getFromDB($items_id);
               return $item->getLink(true);
         }
      }
   }

   function dropdownLinkActions($itemtype,$plugin_order_references_id,$plugin_order_orders_id, $entities_id) {
      global $CFG_GLPI;
      
      $rand       = mt_rand();
      $reception  = new PluginOrderReception();
      $actions[0] = Dropdown::EMPTY_VALUE;
      if ($reception->checkItemStatus($plugin_order_orders_id, $plugin_order_references_id,
                                      PluginOrderOrder::ORDER_DEVICE_DELIVRED)) {
         if (!in_array($itemtype, self::getTypesThanCannotBeGenerared())) {
            $actions['generation'] = __("Generate item", "order");
         }
         if ($itemtype::canView()) {
            $actions['createLink'] = __("Link to an existing item", "order");
            $actions['deleteLink'] = __("Delete item link", "order");
         }
      }
      $rand   = Dropdown::showFromArray('generationActions', $actions);
      $params = array ('action'                     => '__VALUE__',
                       'itemtype'                   => $itemtype,
                       'plugin_order_references_id' => $plugin_order_references_id,
                       'plugin_order_orders_id'     => $plugin_order_orders_id,
                       'entities_id'                => $entities_id);
      Ajax::updateItemOnSelectEvent("dropdown_generationActions$rand", "show_generationActions$rand",
                                  $CFG_GLPI["root_doc"] . "/plugins/order/ajax/linkactions.php",
                                  $params);
      echo "<span id='show_generationActions$rand'>&nbsp;</span>";
   }
   
   function itemAlreadyLinkedToAnOrder($itemtype, $items_id, $plugin_order_orders_id,
                                       $detailID = 0) {
      global $DB;
      if (!in_array($itemtype, self::getTypesThanCannotBeGenerared())) {
         $query = "SELECT COUNT(*) AS cpt
                  FROM `glpi_plugin_order_orders_items`
                  WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
                  AND `items_id` = '$items_id'
                  AND `itemtype` = '$itemtype' ";

         $result = $DB->query($query);
         if ($DB->result($result, 0, "cpt") > 0) {
            return true;
         }
         else {
            return false;
         }
      } else {
         $detail = new PluginOrderOrder_Item();
         $detail->getFromDB($detailID);
         if (!$detail->fields['items_id']) {
            return false;
         } else {
            return true;
         }
      }
   }
   
   function isItemLinkedToOrder($itemtype, $items_id) {
      global $DB;
      
      $query = "SELECT `id`
                FROM `glpi_plugin_order_orders_items`
                WHERE `itemtype` = '$itemtype'
                   AND `items_id` = '$items_id' ";
      $result = $DB->query($query);
      if ($DB->numrows($result))
         return ($DB->result($result, 0, 'id'));
      else
         return 0;
   }
   
   function generateInfoComRelatedToOrder($entity, $detailID, $itemtype, $items_id, $templateID = 0) {
      global $CFG_GLPI;
      
      //Do not try to generate infocoms if itemtype doesn't support it (ie contracts...)
      if(in_array($itemtype, $CFG_GLPI["infocom_types"])) {
         
         // Retrieve configuration for generate assets feature
         $config = PluginOrderConfig::getConfig();

         $fields = array ();
         
         //Create empty infocom, in order to forward entities_id and is_recursive
         $ic = new Infocom();
         if (!$ic->getFromDBforDevice($itemtype, $items_id)) {
            $infocomID = false;
            
         } else {
            $infocomID    = $ic->fields["id"];
         }
         
         $detail = new PluginOrderOrder_Item();
         $detail->getFromDB($detailID);
         $order  = new PluginOrderOrder();
         $order->getFromDB($detail->fields["plugin_order_orders_id"]);
         
         $order_supplier = new PluginOrderOrder_Supplier();
         $order_supplier->getFromDBByOrder($detail->fields["plugin_order_orders_id"]);

         if ($templateID) {
            if ($ic->getFromDBforDevice($itemtype, $templateID)) {
               $fields = $ic->fields;
               unset ($fields["id"]);
               if (isset ($fields["immo_number"])) {
                  $fields["immo_number"] = autoName($fields["immo_number"], "immo_number", 1,
                                                    'Infocom', $entity);
               }
   
               if (empty ($fields['buy_date'])) {
                  unset ($fields['buy_date']);
               }
            }
         }

         $fields["entities_id"]     = $entity;
         $fields["itemtype"]        = $itemtype;
         $fields["items_id"]        = $items_id;
         $fields["order_number"]    = $order->fields["num_order"];
         $fields["delivery_number"] = $detail->fields["delivery_number"];
         $fields["budgets_id"]      = $order->fields["budgets_id"];
         $fields["suppliers_id"]    = $order->fields["suppliers_id"];
         $fields["value"]           = $detail->fields["price_discounted"];
         $fields["order_date"]      = $order->fields["order_date"];
         if (!is_null($detail->fields["delivery_date"])) {
            $fields["delivery_date"]   = $detail->fields["delivery_date"];
         }

         // Get bill data
         if ($config->canAddBillDetails()) {
            $bill = new PluginOrderBill();
            if ($bill->getFromDB($detail->fields["plugin_order_bills_id"])) {
               $fields['bill'] = $bill->fields['number'];
               $fields['warranty_date'] = $bill->fields['billdate'];
            }
         }
         
         foreach (array('warranty_date', 'buy_date', 'inventory_date') as $date) {
            if (!isset($fields[$date])) {
               $fields[$date] = 'NULL';
            }
         }
         $fields['_no_warning'] = true;

         if ($infocomID) {
            $fields['id']              = $infocomID;
            $ic->update($fields);
         } else {
            $ic->add($fields);
         }
      }
   }
   
   function removeInfoComRelatedToOrder($itemtype, $items_id) {
      $infocom = new InfoCom();
      $infocom->getFromDBforDevice($itemtype, $items_id);
      $input["id"]              = $infocom->fields["id"];
      $input["order_number"]    = "";
      $input["delivery_number"] = "";
      $input["budgets_id"]      = 0;
      $input["suppliers_id"]    = 0;
      $input["bill"]            = "";
      $input["value"]           = 0;
      $input["order_date"]      = NULL;
      $input["delivery_date"]   = NULL;
      $infocom->update($input);
}

   function createLinkWithItem($detailID = 0, $items_id = 0, $itemtype = 0,
                               $plugin_order_orders_id = 0, $entity = 0, $templateID = 0,
                               $history = true, $check_link = true) {
      global $DB;

      if (!$check_link
         || !$this->itemAlreadyLinkedToAnOrder($itemtype, $items_id, $plugin_order_orders_id,
                                               $detailID)) {
         $detail     = new PluginOrderOrder_Item();
         $restricted = array('ConsumableItem', 'CartridgeItem');
                       
         if ($itemtype == 'SoftwareLicense') {
            $detail->getFromDB($detailID);
            $query = "SELECT `id`
               FROM `glpi_plugin_order_orders_items`
               WHERE `plugin_order_orders_id` = '" . $plugin_order_orders_id."'
               AND `plugin_order_references_id` = '" . $detail->fields["plugin_order_references_id"] ."'
               AND `price_taxfree` LIKE '" . $detail->fields["price_taxfree"] ."'
               AND `discount` LIKE '" . $detail->fields["discount"] ."'
               AND `states_id` = 1 ";
            $result = $DB->query($query);
            $nb     = $DB->numrows($result);

            if ($nb) {
               for ($i = 0; $i < $nb; $i++) {
                  $ID                = $DB->result($result, $i, 'id');
                  $input["id"]       = $ID;
                  $input["items_id"] = $items_id;
                  $detail->update($input);

                  $this->generateInfoComRelatedToOrder($entity, $ID, $itemtype, $items_id, 0);
                  
                  $lic              = new SoftwareLicense();
                  $lic->getFromDB($items_id);
                  $values["id"]     = $lic->fields["id"];
                  $values["number"] = $lic->fields["number"]+1;
                  $lic->update($values);
                  
               }
               
               if ($history) {
                  $order     = new PluginOrderOrder();
                  $new_value = __("Item linked to order", "order") . ' : ' . $lic->getField("name");
                  $order->addHistory('PluginOrderOrder', '', $new_value, $plugin_order_orders_id);
               }
            }
            
         } elseif (in_array($itemtype, $restricted)) {
         
            if ($itemtype == 'ConsumableItem') {
               $item = new Consumable();
               $type = 'Consumable';
            } elseif ($itemtype == 'CartridgeItem') {
               $item = new Cartridge();
               $type = 'Cartridge';
            }
            $detail->getFromDB($detailID);
            $input["tID"]     = $items_id;
            $input["date_in"] = $detail->fields["delivery_date"];
            $newID            = $item->add($input);

            $input["id"]       = $detailID;
            $input["items_id"] = $newID;
            $input["itemtype"] = $itemtype;
            if ($detail->update($input)) {
               $this->generateInfoComRelatedToOrder($entity, $detailID, $type, $newID, 0);
            }

         } elseif ($itemtype == 'Contract') {
            $input["id"]       = $detailID;
            $input["items_id"] = $items_id;
            $input["itemtype"] = $itemtype;
            if ($detail->update($input)) {
               $detail->getFromDB($detailID);
               
               
               $item = new Contract();
               if ($item->update(array('id'  => $items_id,
                                   'cost' => $detail->fields["price_discounted"]))) {
                  $order = new PluginOrderOrder();
                  $order->getFromDB($plugin_order_orders_id);
                  if (!countElementsInTable('glpi_contracts_suppliers',
                                            "`contracts_id`='$items_id'
                                            AND `suppliers_id`='".$order->fields['suppliers_id']."'")) {
                     $contract_supplier = new Contract_Supplier();
                     $contract_supplier->add(array('contracts_id' => $items_id,
                                                   'suppliers_id'  => $order->fields['suppliers_id']));
                  }
               }
            }
         } else {
            $input["id"]       = $detailID;
            $input["items_id"] = $items_id;
            $input["itemtype"] = $itemtype;
            if ($detail->update($input)) {
               $this->generateInfoComRelatedToOrder($entity, $detailID, $itemtype, $items_id,
                     $templateID);
               
               if ($history) {
                  $order = new PluginOrderOrder();
                  $order->getFromDB($detail->fields["plugin_order_orders_id"]);
                  $item  = new $itemtype();
                  $item->getFromDB($items_id);
                  $new_value = __("Item linked to order", "order") . ' : ' . $item->getField("name");
                  $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);
               }
            }
         }
         if ($history) {
            $order = new PluginOrderOrder();
            $order->getFromDB($detail->fields["plugin_order_orders_id"]);
            $new_value = __("Item linked to order", "order") . ' : ' . $order->fields["name"];
            $order->addHistory($itemtype, '', $new_value, $items_id);
         }
         
         Session::addMessageAfterRedirect(__("Item linked to order", "order"), true);
      } else {
         Session::addMessageAfterRedirect(__("Item already linked to another one", "order"), true, ERROR);
      }

   }
   
   function deleteLinkWithItem($detailID, $itemtype, $plugin_order_orders_id) {
      global $DB;
      
      if ($itemtype == 'SoftWareLicense') {
            
         $detail  = new PluginOrderOrder_Item();
         $detail->getFromDB($detailID);
         $license = $detail->fields["items_id"];
         
         $this->removeInfoComRelatedToOrder($itemtype, $license);
         $result = $PluginOrderOrder_Item->queryRef($detail->fields["plugin_order_orders_id"],
                                                    $detail->fields["plugin_order_references_id"],
                                                    $detail->fields["price_taxfree"],
                                                    $detail->fields["discount"],
                                                    PluginOrderOrder::ORDER_DEVICE_DELIVRED);
         
         $nb = $DB->numrows($result);

         if ($nb) {
            for ($i = 0; $i < $nb; $i++) {
               $ID                = $DB->result($result, $i, 'id');
               $input["id"]       = $ID;
               $input["items_id"] = 0;
               $detail->update($input);
               
               $lic = new SoftwareLicense();
               $lic->getFromDB($license);
               $values["id"]     = $lic->fields["id"];
               $values["number"] = $lic->fields["number"]-1;
               $lic->update($values);
            }
            
            $order     = new PluginOrderOrder();
            $order->getFromDB($detail->fields["plugin_order_orders_id"]);
            $new_value = __("Item unlink form order", "order") . ' : ' . $order->fields["name"];
            $order->addHistory($itemtype, '', $new_value, $license);

            $item      = new $itemtype();
            $item->getFromDB($license);
            $new_value = __("Item unlink form order", "order") . ' : ' . $item->getField("name");
            $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);
         }
      } else {
      
         $order = new PluginOrderOrder();
         $order->getFromDB($plugin_order_orders_id);
               
         $detail = new PluginOrderOrder_Item();
         $detail->getFromDB($detailID);
         $items_id = $detail->fields["items_id"];

         $this->removeInfoComRelatedToOrder($itemtype, $items_id);

         if ($items_id != 0) {
            $input = $detail->fields;
            $input["items_id"] = 0;
            $detail->update($input);
         } else
            Session::addMessageAfterRedirect(__("One or several selected rows haven't linked items", "order"), TRUE, ERROR);

         $new_value = __("Item unlink form order", "order") . ' : ' . $order->fields["name"];
         $order->addHistory($itemtype, '', $new_value, $items_id);
         
         $item = new $itemtype();
         $item->getFromDB($items_id);
         $new_value = __("Item unlink form order", "order") . ' : ' . $item->getField("name");
         $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);
      }
   }
   
   function generateNewItem($params) {
      global $DB;
      
      // Retrieve plugin configuration
      $config    = new PluginOrderConfig();
      $reference = new PluginOrderReference();
      
      foreach ($params["id"] as $tmp => $values) {
         //If itemtype cannot be generated, go to the new occurence
         if (in_array($values['itemtype'], self::getTypesThanCannotBeGenerared())) {
            continue;
         }

         $entity = $values["entities_id"];
         //------------- Template management -----------------------//
         //Look for a template in the entity
         $templateID = $reference->checkIfTemplateExistsInEntity($values["id"], $values["itemtype"],
                                                                 $entity);
         
         $item  = new $values["itemtype"]();
         if ($values['itemtype'])
         $order = new PluginOrderOrder();
         $order->getFromDB($values["plugin_order_orders_id"]);
         $reference->getFromDB($params["plugin_order_references_id"]);
         
          
         if ($templateID) {

            $item->getFromDB($templateID);
            unset ($item->fields["is_template"]);
            unset ($item->fields["date_mod"]);

            $fields = array ();
            foreach ($item->fields as $key => $value) {
               if ($value != ''
                  && (!isset ($fields[$key]) || $fields[$key] == '' || $fields[$key] == 0))
                  $input[$key] = $value;
            }
            
            if($config->getGeneratedAssetState()) {
               $input["states_id"] = $config->getGeneratedAssetState();
            }

            $input["entities_id"] = $entity;
            $input["serial"]      = $values["serial"];
            
            if ($item->fields['name']) {
               $input["name"] = autoName($item->fields["name"], "name", $templateID,
                                         $values["itemtype"], $entity);
            } else {
               $input["name"] = $values["name"];
            }
            
            if ($item->getField('otherserial') != NOT_AVAILABLE) {
               if ($item->fields['otherserial']) {
                  $input["otherserial"] = autoName($item->fields["otherserial"], "otherserial",
                                                   $templateID, $values["itemtype"], $entity);
               } else {
                  $input["otherserial"] = $values["otherserial"];
               }
            }
            
         } elseif($values["itemtype"] == 'Contract') {
            $input["name"]             = $values["name"];
            $input["entities_id"]      = $entity;
            $input['contracttypes_id'] = $reference->fields['types_id'];
            
         } else {
            if($config->getGeneratedAssetState()) {
               $input["states_id"]     = $config->getGeneratedAssetState();
            } else {
               $input["states_id"]     = 0;
            }
            
            $input["entities_id"]      = $entity;
            $input["serial"]           = $values["serial"];
            $input["otherserial"]      = $values["otherserial"];
            $input["name"]             = $values["name"];
            // Get bill data
            if ($config->canAddLocation()) {
               $input['locations_id'] = $order->fields['locations_id'];
            }
            $input["manufacturers_id"] = $reference->fields["manufacturers_id"];
            $typefield                 = getForeignKeyFieldForTable(getTableForItemType($values["itemtype"]."Type"));
            $input[$typefield]         = $reference->fields["types_id"];
            $modelfield                = getForeignKeyFieldForTable(getTableForItemType($values["itemtype"]."Model"));
            $input[$modelfield]        = $reference->fields["models_id"];
               
         }
         $input = Toolbox::addslashes_deep($input);
         $newID = $item->add($input);
         
         // Attach new ticket if option is on
         if (isset($params['generate_ticket'])) {
            $tkt = new TicketTemplate();
            if ($tkt->getFromDB($params['generate_ticket']['tickettemplates_id'])) { 
               $input = array();
               $input = Ticket::getDefaultValues($entity);
               $ttp        = new TicketTemplatePredefinedField();
               $predefined = $ttp->getPredefinedFields($params['generate_ticket']['tickettemplates_id'], true);
               if (count($predefined)) {
                  foreach ($predefined as $predeffield => $predefvalue) {
                     $input[$predeffield] = $predefvalue;
                  }
               }

               $input['entities_id']         = $entity;
               $input['_users_id_requester'] = empty($order->fields['users_id']) ? Session::getLoginUserID() : $order->fields['users_id'];
               $input['items_id']            = $newID;
               $input['itemtype']            = $values["itemtype"];

               $ticket = new Ticket();
               $ticketID = $ticket->add($input);
            }
         }

         //-------------- End template management ---------------------------------//
         $this->createLinkWithItem($values["id"], $newID, $values["itemtype"],
                                   $values["plugin_order_orders_id"], $entity, $templateID, 
                                   false, false);

         //Add item's history
         $new_value = __("Item generated by using order", "order") . ' : ' . $order->fields["name"];
         $order->addHistory($values["itemtype"], '', $new_value, $newID);

         //Add order's history
         $new_value  = __("Item generated by using order", "order") . ' : ';
         $new_value .= $item->getTypeName() . " -> " . $item->getField("name");
         $order->addHistory('PluginOrderOrder', '', $new_value, $values["plugin_order_orders_id"]);

         //Copy order documents if needed
         self::copyDocuments($values['itemtype'], $newID, $values["plugin_order_orders_id"], $entity);
         
         Session::addMessageAfterRedirect(__("Item successfully selected", "order"), true);

      }
   }

   static function countForOrder(PluginOrderOrder $item) {
      return countElementsInTable('glpi_plugin_order_orders_items',
                                  "`plugin_order_orders_id` = '".$item->getID()."' ".
                                  "AND `states_id` = '".PluginOrderOrder::ORDER_DEVICE_DELIVRED."'");
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      
      
      if ($item->getType()=='PluginOrderOrder') {
         if ($item->checkIfDetailExists($item->getID(), true)) {
            return self::createTabEntry(_n("Associated item", "Associated items", 2), 
                                        self::countForOrder($item));
         }
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item->getType() == 'PluginOrderOrder') {
         $link = new self();
         $link->showOrderLink($item->getID());
      }
      return true;
   }
   
   
   /**
    * Copy order documents into the newly generated item
    * @since 1.5.3
    * @param unknown_type $itemtype
    * @param unknown_type $items_id
    * @param unknown_type $orders_id
    * @param unknown_type $entity
    */
   static function copyDocuments($itemtype, $items_id, $orders_id, $entity) {
      global $CFG_GLPI;
      
      $config        = PluginOrderConfig::getConfig();
      
      if ($config->canCopyDocuments() && in_array($itemtype, $CFG_GLPI["document_types"])) {
         $document      = new Document();

         foreach (getAllDatasFromTable('glpi_documents_items',
                                         "`itemtype`='PluginOrderOrder'
                                           AND `items_id`='$orders_id'") as $doc) {
            $document->getFromDB($doc['documents_id']);
            $newdocument = clone $document;
            $newdocument->fields['entities_id'] = $entity;
            $newdocument->fields['items_id'] = $items_id;
            $newdocument->fields['itemtype'] = $itemtype;
            unset($newdocument->fields['id']);
            $document->add($newdocument->fields);
         }
      }
   }
}
?>
