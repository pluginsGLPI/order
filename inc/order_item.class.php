<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
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
// Purpose of file: plugin order v1.4.0 - GLPI 0.80
// ----------------------------------------------------------------------
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

   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['title'][1];

      /* order_number */
      $tab[1]['table']    = $this->getTable();
      $tab[1]['field']    = 'price_ati';
      $tab[1]['name']     = $LANG['plugin_order']['detail'][4];
      $tab[1]['datatype'] = 'decimal';

      $tab[2]['table']    = $this->getTable();
      $tab[2]['field']    = 'discount';
      $tab[2]['name']     = $LANG['plugin_order']['detail'][25];
      $tab[2]['datatype'] = 'decimal';

      $tab[3]['table']    = $this->getTable();
      $tab[3]['field']    = 'price_taxfree';
      $tab[3]['name']     = $LANG['plugin_order']['detail'][25];
      $tab[3]['datatype'] = 'decimal';
      
      return $tab;
   }

   static function updateItem($item) {
      global $LANG;
      
      //TO DO : Must do check same values or update infocom
      $plugin = new Plugin();
      if ($plugin->isActivated("order")) {
         if (isset ($item->fields["id"])) {
         
            $item->getFromDB($item->input["id"]);

            if (isset ($item->fields["itemtype"]) & isset ($item->fields["items_id"])) {
               $orderlink           = new PluginOrderLink();
               $order               = new PluginOrderOrder();
               $orderitem           = new self();
               $order_supplier      = new PluginOrderOrder_Supplier();
      
               $detail_id           = $orderlink->isItemLinkedToOrder($item->fields["itemtype"],
                                                                      $item->fields["items_id"]);
               if ($detail_id > 0) {
                  switch ($item->fields["itemtype"]) {
                     default:
                        $field_set    = false;
                        $unset_fields = array ("order_number", "delivery_number", "budgets_id",
                                               "suppliers_id", "value", "buy_date");
                        $orderitem->getFromDB($detail_id);
                        $order->getFromDB($orderitem->fields["plugin_order_orders_id"]);
                        $order_supplier->getFromDBByOrder($orderitem->fields["plugin_order_orders_id"]);
            
                        $value["order_number"]    = $order->fields["num_order"];
                        $value["delivery_number"] = $orderitem->fields["delivery_number"];
                        $value["budgets_id"]      = $order->fields["budgets_id"];
                        $value["suppliers_id"]    = $order->fields["suppliers_id"];
                        $value["value"]           = $orderitem->fields["price_discounted"];
                        $value["buy_date"]        = $order->fields["order_date"];
                        if (isset($order_supplier->fields["num_bill"]) 
                           && !empty($order_supplier->fields["num_bill"])) {
                           $unset_fields[]        = "bill";
                           $value["bill"]         = $order_supplier->fields["num_bill"];
                        }
            
                        foreach ($unset_fields as $field) {
                           if (isset ($item->input[$field])) {
                              $field_set           = true;
                              $item->input[$field] = $value[$field];
                           }
                        }
                        if ($field_set) {
                           addMessageAfterRedirect($LANG['plugin_order']['infocom'][1], true, ERROR);
                        }
                        break;
                     case 'Contract':
                        $orderitem->getFromDB($detail_id);
                        $order->getFromDB($orderitem->fields["plugin_order_orders_id"]);
                        $item->input['cost'] = $orderitem->fields["price_discounted"];
                        logDebug($item);
                        break;
                  }
               }
            }
         }
      }
   }

   static function getClasses($all = false) {
      global $ORDER_TYPES;
      
      $types = $ORDER_TYPES;
      
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
      return (!$priceHT?0:$priceHT + (($priceHT * $taxes) / 100));
   }

   function checkIFReferenceExistsInOrder($plugin_order_orders_id, $plugin_order_references_id) {
      return  (countElementsInTable($this->getTable(),
                                    "`plugin_order_orders_id` = '$plugin_order_orders_id'
                                       AND `plugin_order_references_id` = '$plugin_order_references_id' "));
   }

   function addDetails($plugin_order_references_id, $itemtype, $plugin_order_orders_id, $quantity, 
                       $price, $discounted_price, $plugin_order_ordertaxes_id) {
                          
      $pluginOrderConfig = new PluginOrderConfig();
      $config            = $pluginOrderConfig->getConfig();

      if ($quantity > 0) {
         for ($i = 0; $i < $quantity; $i++) {
            $input["plugin_order_orders_id"]     = $plugin_order_orders_id;
            $input["plugin_order_references_id"] = $plugin_order_references_id;
            $input["plugin_order_ordertaxes_id"] = $plugin_order_ordertaxes_id;
            $input["itemtype"]                   = $itemtype;
            $input["price_taxfree"]              = $price;
            $input["price_discounted"]           = $price - ($price * ($discounted_price / 100));
            $input["states_id"]                  = PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED;;

            $input["price_ati"]                  = $this->getPricesATI($input["price_discounted"], 
                                                                       Dropdown::getDropdownName("glpi_plugin_order_ordertaxes",
                                                                                                 $plugin_order_ordertaxes_id));
            $input["discount"]                   = $discounted_price;

            $this->add($input);
         }
      }
   }

   /* show details of orders */
   function showItem($ID) {

      $this->showFormDetail($ID);
      $this->showAddForm($ID);
   }

   function showAddForm($plugin_order_orders_id){
      global  $CFG_GLPI, $LANG,$DB;

      $order     = new PluginOrderOrder();
      $reference = new PluginOrderReference();

      if ($order->canUpdateOrder($plugin_order_orders_id)) {

         $canedit=$order->can($plugin_order_orders_id,'w');

         if ($canedit) {
            echo "<form method='post' name='order_detail_form' id='order_detail_form'  action=\"".
               getItemTypeFormURL('PluginOrderOrder')."\">";
            echo "<input type='hidden' name='plugin_order_orders_id' value=\"$plugin_order_orders_id\">";
            echo "<div class='center'>";
            echo"<table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='7'>".$LANG['plugin_order']['detail'][5]."</th></tr>";

            if ($order->fields["suppliers_id"]) {
               echo "<tr>";
               echo "<th align='center'>".$LANG['common'][17]."</th>";
               echo "<th align='center'>".$LANG['plugin_order']['reference'][1]."</th>";
               echo "<th align='center'>".$LANG['plugin_order']['detail'][7]."</th>";
               echo "<th align='center'>".$LANG['plugin_order']['detail'][4]."</th>";
               echo "<th align='center'>".$LANG['plugin_order'][25]."</th>";
               echo "<th align='center'>".$LANG['plugin_order']['detail'][25]."</th>";
               echo "<th></th>";
               echo"</tr>";
               echo "<tr>";
               echo "<td class='tab_bg_1' align='center'>";
               $params = array('myname'       => 'itemtype', 'ajax' => true, 
                               'orders_id'    => $order->fields["id"], 
                               'suppliers_id' => $order->fields['suppliers_id'],
                               'entity'       => $order->fields['entities_id'], 
                               'ajax_page'    => $CFG_GLPI["root_doc"]."/plugins/order/ajax/detail.php",
                               'filter'       => true, "class" => __CLASS__);
               $reference->dropdownAllItems($params);
               echo "</td>";
               echo "<td class='tab_bg_1' align='center'><span id='show_reference'>&nbsp;</span></td>";
               echo "<td class='tab_bg_1' align='center'><span id='show_quantity'>&nbsp;</span></td>";
               echo "<td class='tab_bg_1' align='center'><span id='show_priceht'>&nbsp;</span></td>";
               echo "<td class='tab_bg_1' align='center'><span id='show_taxe'>&nbsp;</span></td>";
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
               `glpi_plugin_order_references`.`itemtype`,`glpi_plugin_order_references`.`types_id`,
               `glpi_plugin_order_references`.`models_id`, 
               `glpi_plugin_order_references`.`manufacturers_id`,
               `glpi_plugin_order_references`.`name`,
               `".$this->getTable()."`.`price_taxfree`, `".$this->getTable()."`.`price_ati`, 
               `".$this->getTable()."`.`price_discounted`,
               `".$this->getTable()."`.`discount`, 
               `".$this->getTable()."`.`plugin_order_ordertaxes_id`
               FROM `".$this->getTable()."`, `glpi_plugin_order_references`
               WHERE `".$this->getTable()."`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`
               AND `".$this->getTable()."`.`plugin_order_orders_id` = '$ID'
               GROUP BY `glpi_plugin_order_references`.`id`,`".$this->getTable()."`.`price_taxfree`,`".
                  $this->getTable()."`.`discount`
               ORDER BY `glpi_plugin_order_references`.`name` ";

      $result=$DB->query($query);
      
      return $result;
   }
   
   function queryRef($plugin_order_orders_id, $plugin_order_references_id, $price_taxfree, 
                     $discount, $states_id = false) {
      global $DB;
      
      $query = "SELECT `id`, `items_id`
               FROM `glpi_plugin_order_orders_items` 
               WHERE `plugin_order_orders_id` = '" . $plugin_order_orders_id."' 
                  AND `plugin_order_references_id` = '" . $plugin_order_references_id ."' 
                     AND `price_taxfree` = '" . $price_taxfree ."'
                        AND `discount` = '" . $discount ."' ";

      if ($states_id) {
         $query.= "AND `states_id` = '".$states_id."' ";
      }

      $result=$DB->query($query);
      
      return $result;
   }
   
   function showFormDetail($plugin_order_orders_id) {
      global  $CFG_GLPI, $LANG,$DB;

      $order                = new PluginOrderOrder();
      $reference            = new PluginOrderReference();
      $PluginOrderReception = new PluginOrderReception();
      $result_ref           = $this->queryDetail($plugin_order_orders_id);
      $numref               = $DB->numrows($result_ref);
      $rand                 = mt_rand();
      $canedit              = $order->can($plugin_order_orders_id,'w') 
                              && $order->canUpdateOrder($plugin_order_orders_id);

      while ($data_ref=$DB->fetch_array($result_ref)){

         echo "<div class='center'>";
         
         echo "<form method='post' name='order_updatedetail_form$rand' id='order_updatedetail_form$rand'  " .
                  "action='" . getItemTypeFormURL('PluginOrderOrder') . "'>";
                  
         echo "<input type='hidden' name='plugin_order_orders_id' 
                  value='" . $plugin_order_orders_id . "'>";
                        
         echo "<table class='tab_cadre_fixe'>";
         if (!$numref) {
            echo "<tr><th>" . $LANG['plugin_order']['detail'][20] . "</th></tr></table></div>";

         } else {
            $refID         = $data_ref["id"];
            $price_taxfree = $data_ref["price_taxfree"];
            $discount      = $data_ref["discount"];
            
            $rand = mt_rand();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('detail$rand','detail', '".
                                                   GLPI_ROOT."/pics/plus.png','".
                                                   GLPI_ROOT."/pics/moins.png');\">";
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

            echo "<td><div id='viewaccept$rand' style='display:none;'>";
            echo "<input type='submit' onclick=\"return confirm('" . 
               $LANG['plugin_order']['detail'][41] . "');\" name='update_item' value=\"".
                  $LANG['buttons'][14]."\" class='submit'>";
            echo "</div></td>";
            
            /* quantity */
            $quantity = $this->getTotalQuantityByRefAndDiscount($plugin_order_orders_id, $refID, 
                                                                $price_taxfree, $discount);
            if($canedit) {
               echo "<td align='center'>";
               echo "<script type='text/javascript' >\n";
               echo "function showQuantity$rand() {\n";
               echo "Ext.get('quantity$rand').setDisplayed('none');";
               echo "Ext.get('viewaccept$rand').setDisplayed('block');";
               $params = array('maxlength' => 15,
                               'size'      => 8,
                               'name'      => 'quantity',
                               'data'      => rawurlencode($quantity));
               ajaxUpdateItemJsCode("viewquantity$rand", $CFG_GLPI["root_doc"]."/ajax/inputtext.php", 
                                    $params, false);
               echo "}";
               echo "</script>\n";
               echo "<div id='quantity$rand' class='center' onClick='showQuantity$rand()'>\n";
               echo $quantity;
               echo "</div>\n";
               echo "<div id='viewquantity$rand'>\n";
               echo "</div>\n";
               echo "</td>";
            }else{
               echo "<td align='center'>".$quantity."</td>";
            }
            /* type */
            $item = new $data_ref["itemtype"]();
            echo "<td align='center'>".$item->getTypeName()."</td>";
            /* manufacturer */
            echo "<td align='center'>".Dropdown::getDropdownName("glpi_manufacturers", 
                                                                 $data_ref["manufacturers_id"]).
               "</td>";
            /* reference */
            echo "<td align='center'>";

            echo "<input type='hidden' name='old_plugin_order_references_id' 
                     value='" . $refID . "'>";

            echo $reference->getReceptionReferenceLink($data_ref);
            echo "</td>";
            /* type */
            echo "<td align='center'>";
            if (file_exists(GLPI_ROOT."/inc/".strtolower($data_ref["itemtype"])."type.class.php"))
               echo Dropdown::getDropdownName(getTableForItemType($data_ref["itemtype"]."Type"), 
                                                                  $data_ref["types_id"]);
            echo "</td>";
            /* modele */
            echo "<td align='center'>";
            if (file_exists(GLPI_ROOT."/inc/".strtolower($data_ref["itemtype"])."model.class.php"))
               echo Dropdown::getDropdownName(getTableForItemType($data_ref["itemtype"]."Model"), 
                                              $data_ref["models_id"]);
            echo "</td>";
            if($canedit) {
               echo "<td align='center'>";
               echo "<input type='hidden' name='old_price_taxfree' value='" . $price_taxfree . "'>";
               echo "<script type='text/javascript' >\n";
               echo "function showPricetaxfree$rand() {\n";
               echo "Ext.get('pricetaxfree$rand').setDisplayed('none');";
               echo "Ext.get('viewaccept$rand').setDisplayed('block');";
               $params = array('maxlength' => 15,
                               'size'      => 8,
                               'name'      => 'price_taxfree',
                               'data'      => rawurlencode($price_taxfree));
               ajaxUpdateItemJsCode("viewpricetaxfree$rand", $CFG_GLPI["root_doc"]."/ajax/inputtext.php", $params,
                                    false);
               echo "}";
               echo "</script>\n";
               echo "<div id='pricetaxfree$rand' class='center' onClick='showPricetaxfree$rand()'>\n";
               echo formatNumber($price_taxfree);
               echo "</div>\n";
               echo "<div id='viewpricetaxfree$rand'>\n";
               echo "</div>\n";
               echo "</td>";
            }else{
               echo "<td align='center'>" . formatNumber($price_taxfree) . "</td>";
            }
            /* reduction */
            if($canedit) {
               echo "<td align='center'>";
               echo "<input type='hidden' name='old_discount' value='" . $discount . "'>";
               echo "<script type='text/javascript' >\n";
               echo "function showDiscount$rand() {\n";
               echo "Ext.get('discount$rand').setDisplayed('none');";
               echo "Ext.get('viewaccept$rand').setDisplayed('block');";
               $params = array('maxlength' => 15,
                               'size'      => 8,
                               'name'      => 'discount',
                               'data'      => rawurlencode($discount));
               ajaxUpdateItemJsCode("viewdiscount$rand", $CFG_GLPI["root_doc"]."/ajax/inputtext.php", $params,
                                    false);
               echo "}";
               echo "</script>\n";
               echo "<div id='discount$rand' class='center' onClick='showDiscount$rand()'>\n";
               echo formatNumber($discount);
               echo "</div>\n";
               echo "<div id='viewdiscount$rand'>\n";
               echo "</div>\n";
               echo "</td>";
            }else{
               echo "<td align='center'>" . formatNumber($discount) . "</td>";
            }
            echo "</tr></table></form>";

            echo "<div class='center' id='detail$rand' style='display:none'>";
            echo "<form method='post' name='order_detail_form$rand' id='order_detail_form$rand'  " .
                  "action=\"" . getItemTypeFormURL('PluginOrderOrder')."\">";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr>";
            if($canedit) {
               echo "<th></th>";
            }
            if ($data_ref["itemtype"] != 'SoftwareLicense') {
               echo "<th>".$LANG['common'][2]."</th>";
            }
            echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][4]."</th>";
            echo "<th>".$LANG['plugin_order'][25]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][25]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][18]."</th>";
            echo "<th>".$LANG['plugin_order'][14]."</th>";
            echo "<th>".$LANG['plugin_order']['detail'][19]."</th></tr>";
            
            $query="SELECT `".$this->getTable()."`.`id` AS IDD, `glpi_plugin_order_references`.`id`, 
                           `glpi_plugin_order_references`.`name`,
                           `".$this->getTable()."`.`price_taxfree`, 
                           `".$this->getTable()."`.`price_discounted`,
                           `".$this->getTable()."`.`discount`,
                           `".$this->getTable()."`.`plugin_order_ordertaxes_id`,
                           `".$this->getTable()."`.`price_ati`
                    FROM `".$this->getTable()."`, `glpi_plugin_order_references`
                    WHERE `".$this->getTable()."`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`
                      AND `".$this->getTable()."`.`plugin_order_references_id` = '".$refID."'
                         AND `".$this->getTable()."`.`price_taxfree` LIKE '".$price_taxfree."'
                            AND `".$this->getTable()."`.`discount` LIKE '".$discount."'
                                AND `".$this->getTable()."`.`plugin_order_orders_id` = '$plugin_order_orders_id'";

            if ($data_ref["itemtype"] == 'SoftwareLicense') {
               $query.=" GROUP BY `glpi_plugin_order_references`.`name` "; 

            }
            $query .= " ORDER BY `glpi_plugin_order_references`.`name` ";

            $result = $DB->query($query);
            $num    = $DB->numrows($result);
            
            while ($data=$DB->fetch_array($result)){
               
               echo "<tr class='tab_bg_1'>";
               if ($canedit){
                  echo "<td width='10'>";
                  $sel="";
                  if (isset($_GET["select"])&& $_GET["select"] == "all") {
                     $sel = "checked";
                  }
                  echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
                  echo "<input type='hidden' name='plugin_order_orders_id' value='" . 
                     $plugin_order_orders_id . "'>";
                  echo "</td>";
               }
               if ($data_ref["itemtype"] != 'SoftwareLicense') {
                  echo "<td align='center'>".$data["IDD"]."</td>";
               }

               /* reference */
               echo "<td align='center'>";
               echo $reference->getReceptionReferenceLink($data);
               echo "</td>";
               echo "<td align='center'>".formatNumber($data["price_taxfree"])."</td>";
               /* taxe */
               echo "<td align='center'>";
               echo Dropdown::getDropdownName(getTableForItemType("PluginOrderOrderTaxe"), 
                                                                  $data["plugin_order_ordertaxes_id"]);
               echo "</td>";
               /* reduction */
               echo "<td align='center'>".formatNumber($data["discount"])."</td>";
               /* price with reduction */
               echo "<td align='center'>".formatNumber($data["price_discounted"])."</td>";
               /* price ati */
               echo "<td align='center'>".formatNumber($data["price_ati"])."</td>";
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
               echo "<a onclick= \"if ( markCheckboxes('order_detail_form$rand') ) return false;\" href='#'>".
                  $LANG['buttons'][18]."</a></td>";

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

      $PluginOrderConfig = new PluginOrderConfig;
      $config = $PluginOrderConfig->getConfig();

      $query = "SELECT COUNT(*) AS deliveredquantity
                FROM `".$this->getTable()."`
                WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
                   AND `plugin_order_references_id` = '$plugin_order_references_id'
                      AND `price_taxfree` LIKE '$price_taxfree'
                         AND `discount` LIKE '$discount'
                            AND `states_id` != '0' ";
      $result = $DB->query($query);
      return ($DB->result($result, 0, 'deliveredquantity'));
   }

   function updateDelivryStatus($plugin_order_orders_id) {
      global $DB;

      $PluginOrderConfig = new PluginOrderConfig;
      $config = $PluginOrderConfig->getConfig();

      $order = new PluginOrderOrder;
      $order->getFromDB($plugin_order_orders_id);

      $query = "SELECT `states_id`
                FROM `".$this->getTable()."`
                WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      $all_delivered = true;
      
      if ($number) {
         while ($data = $DB->fetch_array($result))
            if (!$data["states_id"])
               $all_delivered = false;
      }
      if ($all_delivered 
            && $order->fields["plugin_order_orderstates_id"] != $config['order_status_completly_delivered'])
         $order->updateOrderStatus($plugin_order_orders_id, 
                                   $config['order_status_completly_delivered']);
      else if ($order->fields["plugin_order_orderstates_id"] != $config['order_status_partially_delivred'])
         $order->updateOrderStatus($plugin_order_orders_id, 
                                   $config['order_status_partially_delivred']);
   }

   function getAllPrices($plugin_order_orders_id) {
      global $DB;

      $query = "SELECT SUM(`price_ati`) AS priceTTC, SUM(`price_discounted`) AS priceHT,
                     SUM(`price_ati` - `price_discounted`) as priceTVA
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
      if ($DB->numrows($result)) {
         return $DB->fetch_array($result);
      } else {
         return false;
      }
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
         $link = getItemTypeFormURL('PluginOrderOrder');
         if ($this->canView()) {
            echo "<a href=\"".$link."?id=".$infos["id"]."\">".$infos["name"]."</a>";
         } else {
            echo $infos["name"];
         }
         echo "</td></tr>";
         echo "<tr align='center'><td class='tab_bg_2'>" . 
            $LANG['plugin_order']['detail'][21] . "</td>";
         echo "<td class='tab_bg_2'>" . convDate($infos["order_date"]) . "</td></tr>";
         echo "</table></div>";
      }
   }
   
   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      
      if (!TableExists($table)) {
         if (!TableExists("glpi_plugin_order_detail")) {
            $migration->displayMessage("Installing $table");
 
            //install
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
               `id` int(11) NOT NULL auto_increment,
               `entities_id` int(11) NOT NULL default '0',
               `is_recursive` tinyint(1) NOT NULL default '0',
               `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
               `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
               `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
               `plugin_order_references_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)',
               `plugin_order_deliverystates_id` int (11)  NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_deliverystates (id)',
               `plugin_order_ordertaxes_id` float NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)',
               `delivery_number` varchar(255) collate utf8_unicode_ci default NULL,
               `delivery_comment` text collate utf8_unicode_ci,
               `price_taxfree` float NOT NULL default 0,
               `price_discounted` float NOT NULL default 0,
               `discount` float NOT NULL default 0,
               `price_ati` float NOT NULL default 0,
               `states_id` int(11) NOT NULL default 1,
               `delivery_date` date default NULL,
               `plugin_order_bills_id` INT( 11 ) NOT NULL DEFAULT '0',
               PRIMARY KEY  (`id`),
               KEY `FK_device` (`items_id`,`itemtype`),
               KEY `entities_id` (`entities_id`),
               KEY `item` (`itemtype`,`items_id`),
               KEY `plugin_order_references_id` (`plugin_order_references_id`),
               KEY `plugin_order_deliverystates_id` (`plugin_order_deliverystates_id`),
               KEY `states_id` (`states_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die ($DB->error());
         } else {
            //Upgrade
            $migration->displayMessage("Upgrading $table");

            //1.1.2
            $migration->addField("glpi_plugin_order_detail","delivery_status", "int(1) NOT NULL default '0'");
            $migration->addField("glpi_plugin_order_detail","delivery_comments", "TEXT");
            $migration->migrationOneTable("glpi_plugin_order_detail");

            //1.2.0
            $migration->renameTable("glpi_plugin_order_detail", $table);
            
            $migration->changeField($table, "ID", "id",  "int(11) NOT NULL AUTO_INCREMENT");
            $migration->changeField($table, "FK_order", "plugin_order_orders_id", 
                                    "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)'");
            $migration->changeField($table, "device_type", "itemtype", 
                                    "varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file'");
            $migration->changeField($table, "FK_device",  "items_id", 
                                    "int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)'");
            $migration->changeField($table, "FK_reference", "plugin_order_references_id", 
                                    "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)'");
            $migration->changeField($table, "delivery_status",  "plugin_order_deliverystates_id", 
                                    "int (11)  NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_deliverystates (id)'");
            $migration->changeField($table, "deliverynum",  "delivery_number", 
                                    "varchar(255) collate utf8_unicode_ci default NULL");
            $migration->changeField($table, "delivery_comments",  "delivery_comment", 
                                    "text collate utf8_unicode_ci");
            $migration->changeField($table, "status", "states_id",  "int(11) NOT NULL default 1");
            $migration->changeField($table, "date", "delivery_date",  "date default NULL");
            $migration->addKey($table, array("items_id", "itemtype"), "FK_device" );
            $migration->addKey($table, array("itemtype", "items_id"), "item");
            $migration->addKey($table, "plugin_order_references_id");
            $migration->addKey($table, "plugin_order_deliverystates_id");
            $migration->addKey($table, "states_id");
            $migration->migrationOneTable($table);
            
            Plugin::migrateItemType(array(), array(), array($table));


            //1.4.0
            $migration->addField($table, "plugin_order_ordertaxes_id", 
                                 "INT (11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)'");
            $migration->migrationOneTable($table);
            
            /* Migrate VAT */
            foreach ($DB->request("glpi_plugin_order_orders") as $data) {
               $query  = "UPDATE `glpi_plugin_order_orders_items`
                          SET `plugin_order_ordertaxes_id` = '" . $data["plugin_order_ordertaxes_id"] . "'
                          WHERE `plugin_order_orders_id` = '" . $data["id"] . "'";
               $result = $DB->query($query) or die($DB->error());
            }

            //1.5.0
            $migration->addField($table, "entities_id",  "INT( 11 ) NOT NULL DEFAULT '0'");
            $migration->addField($table, "is_recursive",  "TINYINT( 1 ) NOT NULL DEFAULT '0'");
            $migration->addField($table, "bills_id",  "INT( 11 ) NOT NULL DEFAULT '0'");
            $migration->addKey("glpi_plugin_order_orders_items", "entities_id", "entities_id", "INDEX");
            $migration->migrationOneTable($table);

            //Forward entities_id and is_recursive into table glpi_plugin_order_orders_items
            $query = "SELECT `go`.`entities_id` as entities_id , `go`.`is_recursive` as is_recursive, `goi`.`id` as items_id
                      FROM `glpi_plugin_order_orders` as go, `$table` as `goi` 
                      WHERE `goi`.`plugin_order_orders_id`=`go`.`id`";
            foreach($DB->request($query) as $data) {
               $update = "UPDATE `$table` 
                          SET `entities_id`='".$data['entities_id']."' 
                             AND `is_recursive`='".$data['is_recursive']."' 
                          WHERE `id`='".$data['items_id']."'";
               $DB->query($update)  or die($DB->error());
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
}

?>