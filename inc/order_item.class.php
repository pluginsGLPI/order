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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderOrder_Item extends CommonDBTM {

   // From CommonDBRelation
   public $itemtype_1 = "PluginOrderOrder";
   public $items_id_1 = 'plugin_order_orders_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';
   
   function canCreate() {
      return plugin_order_haveRight('order', 'w');
   }

   function canView() {
      return plugin_order_haveRight('order', 'r');
   }

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['title'][1]." - ".$LANG['plugin_order']['menu'][5];
   }

   static function updateItem($item) {
      global $LANG;
      
      //TO DO : Must do check same values or update infocom
      $plugin = new Plugin();
      if ($plugin->isActivated("order")) {
         if (isset ($item->fields["id"])) {
         
            $item->getFromDB($item->input["id"]);

            if (isset ($item->fields["itemtype"]) & isset ($item->fields["items_id"])) {
               $PluginOrderLink = new PluginOrderLink();
               $PluginOrderOrder = new PluginOrderOrder();
               $orderitem = new self();
               $PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier();
      
               $detail_id = $PluginOrderLink->isItemLinkedToOrder($item->fields["itemtype"],$item->fields["items_id"]);
               if ($detail_id > 0) {
               
                  $field_set = false;
                  $unset_fields = array (
                     "order_number",
                     "delivery_number",
                     "budgets_id",
                     "suppliers_id",
                     "value",
                     "buy_date"
                  );
                  
                  
                  $orderitem->getFromDB($detail_id);
                  $PluginOrderOrder->getFromDB($orderitem->fields["plugin_order_orders_id"]);
                  $PluginOrderOrder_Supplier->getFromDBByOrder($orderitem->fields["plugin_order_orders_id"]);
      
                  $value["order_number"] = $PluginOrderOrder->fields["num_order"];
                  $value["delivery_number"] = $orderitem->fields["delivery_number"];
                  $value["budgets_id"] = $PluginOrderOrder->fields["budgets_id"];
                  $value["suppliers_id"] = $PluginOrderOrder->fields["suppliers_id"];
                  if (isset($PluginOrderOrder_Supplier->fields["num_bill"]) && !empty($PluginOrderOrder_Supplier->fields["num_bill"])) {
                     $unset_fields[] = "bill";
                     $value["bill"] = $PluginOrderOrder_Supplier->fields["num_bill"];
                  }
                  $value["value"] = $orderitem->fields["price_discounted"];
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
      }
   }

   static function getClasses($all=false) {
   
      static $types = array('Computer',
                              'Monitor',
                              'NetworkEquipment',
                              'Peripheral',
                              'Printer',
                              'Phone',
                              'ConsumableItem',
                              'CartridgeItem',
                              'SoftwareLicense',
                              //'Software',
                              'Contract',
                              'PluginOrderOther');
      
      if ($all) {
         return $types;
      }
      
      foreach ($types as $key=>$type) {
         if (!class_exists($type)) {
            continue;
         }
         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   function getPricesATI($priceHT, $taxes) {
      if (!$priceHT)
         return 0;
      else
         return $priceHT + (($priceHT * $taxes) / 100);
   }

   function checkIFReferenceExistsInOrder($plugin_order_orders_id, $plugin_order_references_id) {
      global $DB;

      $query = "SELECT `id`
               FROM `".$this->getTable()."`
               WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
               AND `plugin_order_references_id` = '$plugin_order_references_id' ";
      $result = $DB->query($query);
      if ($DB->numrows($result))
         return true;
      else
         return false;
   }

   function addDetails($plugin_order_references_id, $itemtype, $plugin_order_orders_id, $quantity, $price, $discounted_price, $taxes) {

      if ($quantity > 0) {
         for ($i = 0; $i < $quantity; $i++) {
            $input["plugin_order_orders_id"] = $plugin_order_orders_id;
            $input["plugin_order_references_id"] = $plugin_order_references_id;
            $input["itemtype"] = $itemtype;
            $input["price_taxfree"] = $price;
            $input["price_discounted"] = $price - ($price * ($discounted_price / 100));
            $input["states_id"] = PluginOrderOrder::ORDER_STATUS_DRAFT;
            $input["price_ati"] = $this->getPricesATI($input["price_discounted"], Dropdown::getDropdownName("glpi_plugin_order_ordertaxes",$taxes));
            $input["discount"] = $discounted_price;

            $this->add($input);
         }
      }
   }

   /* show details of orders */
   function showItem($target, $ID) {

      $this->showFormDetail($target, $ID);
      $this->showAddForm($target, $ID);
   }

   function showAddForm($target, $plugin_order_orders_id){
      global  $CFG_GLPI, $LANG,$DB;

      $order=new PluginOrderOrder();
      $reference=new PluginOrderReference();

      if ($order->canUpdateOrder($plugin_order_orders_id))
      {

         $canedit=$order->can($plugin_order_orders_id,'w');

         if ($canedit)
         {
            echo "<form method='post' name='order_detail_form' id='order_detail_form'  action=\"$target\">";
            echo "<input type='hidden' name='plugin_order_orders_id' value=\"$plugin_order_orders_id\">";
            echo "<div class='center'>";
            echo"<table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='6'>".$LANG['plugin_order']['detail'][5]."</th></tr>";

            if ($order->fields["suppliers_id"])
            {
               echo "<tr>";
               echo "<th align='center'>".$LANG['common'][17]."</th>";
               echo "<th align='center'>".$LANG['plugin_order']['reference'][1]."</th>";
               echo "<th align='center'>".$LANG['plugin_order']['detail'][7]."</th>";
               echo "<th align='center'>".$LANG['plugin_order']['detail'][4]."</th>";
               echo "<th align='center'>".$LANG['plugin_order']['detail'][25]."</th>";
               echo "<th></th>";
               echo"</tr>";
               echo "<tr>";
               echo "<td class='tab_bg_1' align='center'>";
               $reference->dropdownAllItems("itemtype", true, 0, $order->fields["id"], $order->fields["suppliers_id"], $order->fields["entities_id"], $CFG_GLPI["root_doc"]."/plugins/order/ajax/detail.php",true);
               echo "</td>";
               echo "<td class='tab_bg_1' align='center'><span id='show_reference'>&nbsp;</span></td>";
               echo "<td class='tab_bg_1' align='center'><span id='show_quantity'>&nbsp;</span></td>";
               echo "<td class='tab_bg_1' align='center'><span id='show_priceht'>&nbsp;</span></td>";
               echo "<td class='tab_bg_1' align='center'><span id='show_pricediscounted'>&nbsp;</span></td>";
               echo "<td class='tab_bg_1' align='center'><span id='show_validate'>&nbsp;</span></td>";
               echo "</tr>";
            }
            else
               echo "<tr><td align='center'>".$LANG['plugin_order']['detail'][27]."</td></tr>";

            echo "</table></div></form>";
         }
      }
   }
   
   function queryDetail($ID) {
      global $DB;
      
      $query="SELECT `".$this->getTable()."`.`id` AS IDD, `glpi_plugin_order_references`.`id`,
               `glpi_plugin_order_references`.`itemtype`,`glpi_plugin_order_references`.`types_id`,`glpi_plugin_order_references`.`models_id`, `glpi_plugin_order_references`.`manufacturers_id`, `glpi_plugin_order_references`.`name`,
               `".$this->getTable()."`.`price_taxfree`, `".$this->getTable()."`.`price_ati`, `".$this->getTable()."`.`price_discounted`,
               `".$this->getTable()."`.`discount`
               FROM `".$this->getTable()."`, `glpi_plugin_order_references`
               WHERE `".$this->getTable()."`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`
               AND `".$this->getTable()."`.`plugin_order_orders_id` = '$ID'
               GROUP BY `glpi_plugin_order_references`.`id`,`".$this->getTable()."`.`price_taxfree`,`".$this->getTable()."`.`discount`
               ORDER BY `glpi_plugin_order_references`.`name` ";

      $result=$DB->query($query);
      
      return $result;
   }
   
   function queryRef($plugin_order_orders_id,$plugin_order_references_id,$price_taxfree,$discount,$states_id=false) {
      global $DB;
      
      $query = "SELECT `id`, `items_id`
               FROM `glpi_plugin_order_orders_items` 
               WHERE `plugin_order_orders_id` = '" . $plugin_order_orders_id."' 
               AND `plugin_order_references_id` = '" . $plugin_order_references_id ."' 
               AND `price_taxfree` LIKE '" . $price_taxfree ."'
               AND `discount` LIKE '" . $discount ."' ";
      if ($states_id)
         $query.= "AND `states_id` = '".$states_id."' ";
                  
      $result=$DB->query($query);
      
      return $result;
   }
   
   function showFormDetail($target,$plugin_order_orders_id) {
      global  $CFG_GLPI, $LANG,$DB;

      $PluginOrderOrder = new PluginOrderOrder();
      $PluginOrderReference = new PluginOrderReference();
      $PluginOrderReception = new PluginOrderReception();
      
      $canedit=$PluginOrderOrder->can($plugin_order_orders_id,'w') && $PluginOrderOrder->canUpdateOrder($plugin_order_orders_id);
      
      $result_ref=$this->queryDetail($plugin_order_orders_id);
      $numref=$DB->numrows($result_ref);
         
      $rand=mt_rand();

      while ($data_ref=$DB->fetch_array($result_ref)){
         
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if (!$numref)
            echo "<tr><th>" . $LANG['plugin_order']['detail'][20] . "</th></tr></table></div>";
         else {
            
            $refID = $data_ref["id"];
            $price_taxfree = $data_ref["price_taxfree"];
            $discount = $data_ref["discount"];
            
            $rand = mt_rand();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('detail$rand','detail', '".GLPI_ROOT."/pics/plus.png','".GLPI_ROOT."/pics/moins.png');\">";
            echo "<img alt='' name='detail' src=\"".GLPI_ROOT."/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>".$LANG['plugin_order']['detail'][7]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][1]."</th>";
            echo "<th>".$LANG['common'][5]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][6]."</th>";
            echo "<th>".$LANG['common'][22]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][4]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][25]."</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            /* quantity */
            $quantity = $this->getTotalQuantityByRefAndDiscount($plugin_order_orders_id,$refID,$price_taxfree,$discount);
            echo "<td align='center'>".$quantity."</td>";
            /* type */
            $item = new $data_ref["itemtype"]();
            echo "<td align='center'>".$item->getTypeName()."</td>";
            /* manufacturer */
            echo "<td align='center'>".Dropdown::getDropdownName("glpi_manufacturers",$data_ref["manufacturers_id"])."</td>";
            /* reference */
            echo "<td align='center'>";
            echo $PluginOrderReference->getReceptionReferenceLink($data_ref);
            echo "</td>";
            /* type */
            echo "<td align='center'>";
            if (file_exists(GLPI_ROOT."/inc/".strtolower($data_ref["itemtype"])."type.class.php"))
               echo Dropdown::getDropdownName(getTableForItemType($data_ref["itemtype"]."Type"), $data_ref["types_id"]);
            echo "</td>";
            /* modele */
            echo "<td align='center'>";
            if (file_exists(GLPI_ROOT."/inc/".strtolower($data_ref["itemtype"])."model.class.php"))
               echo Dropdown::getDropdownName(getTableForItemType($data_ref["itemtype"]."Model"), $data_ref["models_id"]);
            echo "</td>";
            echo "<td align='center'>".formatNumber($data_ref["price_taxfree"])."</td>";
            /* reduction */
            echo "<td align='center'>".formatNumber($data_ref["discount"])."</td>";
            echo "</tr></table>";

            echo "<div class='center' id='detail$rand' style='display:none'>";
            echo "<form method='post' name='order_detail_form$rand' id='order_detail_form$rand'  action=\"" . $CFG_GLPI["root_doc"] . "/plugins/order/front/order.form.php\">";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr>";
            if($canedit)
               echo "<th></th>";
            if ($data_ref["itemtype"] != 'SoftwareLicense')
               echo "<th>".$LANG['common'][2]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][4]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][25]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][18]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][19]."</th></tr>";
            
            $query="SELECT `".$this->getTable()."`.`id` AS IDD, `glpi_plugin_order_references`.`id`, `glpi_plugin_order_references`.`name`,
               `".$this->getTable()."`.`price_taxfree`, `".$this->getTable()."`.`price_discounted`,
               `".$this->getTable()."`.`discount`
               FROM `".$this->getTable()."`, `glpi_plugin_order_references`
               WHERE `".$this->getTable()."`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`
               AND `".$this->getTable()."`.`plugin_order_references_id` = '".$refID."'
               AND `".$this->getTable()."`.`price_taxfree` LIKE '".$price_taxfree."'
               AND `".$this->getTable()."`.`discount` LIKE '".$discount."'
               AND `".$this->getTable()."`.`plugin_order_orders_id` = '$plugin_order_orders_id' ";
            if ($data_ref["itemtype"] == 'SoftwareLicense')
               $query.=" GROUP BY `glpi_plugin_order_references`.`name` "; 
            $query.=" ORDER BY `glpi_plugin_order_references`.`name` ";

            $result=$DB->query($query);
            $num=$DB->numrows($result);
            
            while ($data=$DB->fetch_array($result)){
               
               echo "<tr class='tab_bg_1'>";
               if ($canedit){
                  echo "<td width='10'>";
                  $sel="";
                  if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
                  echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
                  echo "<input type='hidden' name='plugin_order_orders_id' value='" . 
                     $plugin_order_orders_id . "'>";
                  echo "</td>";
               }
               if ($data_ref["itemtype"] != 'SoftwareLicense')
                  echo "<td align='center'>".$data["IDD"]."</td>";

               /* reference */
               echo "<td align='center'>";
               echo $PluginOrderReference->getReceptionReferenceLink($data);
               echo "</td>";
               echo "<td align='center'>".formatNumber($data["price_taxfree"])."</td>";
               /* reduction */
               echo "<td align='center'>".formatNumber($data["discount"])."</td>";
               /* price with reduction */
               echo "<td align='center'>".formatNumber($data["price_discounted"])."</td>";
               /* status  */
               echo "<td align='center'>".$PluginOrderReception->getReceptionStatus($data["IDD"]).
                  "</td></tr>";

            }
            echo "</table>";
            if ($canedit) {
               
               echo "<div class='center'>";
               echo "<table width='950px' class='tab_glpi'>";
               echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''>"; 
               echo "</td><td class='center'>"; 
               echo "<a onclick= \"if ( markCheckboxes('order_detail_form$rand') ) return false;\" href='#'>".$LANG['buttons'][18]."</a></td>";

               echo "<td>/</td><td class='center'>"; 
               echo "<a onclick= \"if ( unMarkCheckboxes('order_detail_form$rand') ) " .
                      " return false;\" href='#'>".$LANG['buttons'][19]."</a>";
               echo "</td><td align='left' width='80%'>";
               echo "<input type='submit' onclick=\"return confirm('" . 
                  $LANG['plugin_order']['detail'][36] . "')\" name='delete_item' value=\"".
                     $LANG['buttons'][6]."\" class='submit'>";
               echo "</td>";
               echo "</table>";
               echo "</div>";
            }
            echo "</form></div>";
         }
         echo "<br>";
      }
   }

   function getTotalQuantityByRefAndDiscount($plugin_order_orders_id, $plugin_order_references_id,
                                             $price_taxfree, $discount) {
      global $DB;

      $query = "SELECT COUNT(*) AS quantity
               FROM `".$this->getTable()."`
               WHERE  `plugin_order_orders_id` = '$plugin_order_orders_id'
               AND `plugin_order_references_id` = '$plugin_order_references_id'
               AND `price_taxfree` LIKE '$price_taxfree'
               AND `discount` LIKE '$discount'";
      $result = $DB->query($query);
      return ($DB->result($result, 0, 'quantity'));
   }

   function getTotalQuantityByRef($plugin_order_orders_id, $plugin_order_references_id) {
      global $DB;

      $query = "SELECT COUNT(*) AS quantity
               FROM `".$this->getTable()."`
               WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
               AND `plugin_order_references_id` = '$plugin_order_references_id' ";
      $result = $DB->query($query);
      return ($DB->result($result, 0, 'quantity'));
   }

   function getDeliveredQuantity($plugin_order_orders_id, $plugin_order_references_id,
                                 $price_taxfree, $discount) {
      global $DB;

      $query = "SELECT COUNT(*) AS deliveredquantity
                  FROM `".$this->getTable()."`
                  WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
                  AND `plugin_order_references_id` = '$plugin_order_references_id'
                  AND `price_taxfree` LIKE '$price_taxfree'
                  AND `discount` LIKE '$discount'
                  AND `states_id` = '".PluginOrderOrder::ORDER_STATUS_WAITING_APPROVAL."' ";
      $result = $DB->query($query);
      return ($DB->result($result, 0, 'deliveredquantity'));
   }

   function updateDelivryStatus($plugin_order_orders_id) {
      global $DB;

      $order = new PluginOrderOrder;
      $order->getFromDB($plugin_order_orders_id);

      $query = "SELECT `states_id`
               FROM `".$this->getTable()."`
               WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'";
      $result = $DB->query($query);
      $all_delivered = true;

      while ($data = $DB->fetch_array($result))
         if (!$data["states_id"])
            $all_delivered = false;

      if ($all_delivered 
            && $order->fields["states_id"] != PluginOrderOrder::ORDER_STATUS_COMPLETLY_DELIVERED)
         $order->updateOrderStatus($plugin_order_orders_id, 
                                   PluginOrderOrder::ORDER_STATUS_COMPLETLY_DELIVERED);
      else if ($order->fields["states_id"] != PluginOrderOrder::ORDER_STATUS_PARTIALLY_DELIVRED)
         $order->updateOrderStatus($plugin_order_orders_id, 
                                   PluginOrderOrder::ORDER_STATUS_PARTIALLY_DELIVRED);
   }

   function getAllPrices($plugin_order_orders_id) {
      global $DB;

      $query = "SELECT SUM(`price_ati`) AS priceTTC, SUM(`price_discounted`) AS priceHT
               FROM `".$this->getTable()."`
               WHERE `plugin_order_orders_id` = '$plugin_order_orders_id' ";
      $result = $DB->query($query);
      return $DB->fetch_array($result);
   }

   function getOrderInfosByItem($itemtype, $items_id) {
      global $DB;
      $query = "SELECT `glpi_plugin_order_orders`.*
               FROM `glpi_plugin_order_orders`, `".$this->getTable()."`
               WHERE `glpi_plugin_order_orders`.`id` = `".$this->getTable().
                  "`.`plugin_order_orders_id`
               AND `".$this->getTable()."`.`itemtype` = '$itemtype'
               AND `".$this->getTable()."`.`items_id` = '$items_id' ";
      $result = $DB->query($query);
      if ($DB->numrows($result))
         return $DB->fetch_array($result);
      else
         return false;
   }

   function showPluginFromItems($itemtype, $ID) {
      global $LANG,$CFG_GLPI;

      $infos = $this->getOrderInfosByItem($itemtype, $ID);
      if ($infos) {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr align='center'><th colspan='2'>" . $LANG['plugin_order'][47] . ": </th></tr>";
         echo "<tr align='center'><td class='tab_bg_2'>" . $LANG['plugin_order'][39] . "</td>";
         echo "<td class='tab_bg_2'>";
         $item = new $itemtype();
         $link=getItemTypeFormURL('PluginOrderOrder');
         if ($this->canView())
            echo "<a href=\"".$link."?id=".$infos["id"]."\">".$infos["name"]."</a>";
         else
            echo $infos["name"];
         echo "</td></tr>";
         echo "<tr align='center'><td class='tab_bg_2'>" . 
            $LANG['plugin_order']['detail'][21] . "</td>";
         echo "<td class='tab_bg_2'>" . convDate($infos["order_date"]) . "</td></tr>";
         echo "</table></div>";
      }
   }
}

?>