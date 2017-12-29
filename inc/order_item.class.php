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
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderOrder_Item extends CommonDBRelation {

   public static $rightname              = 'plugin_order_order';

   public $dohistory                     = true;

   // From CommonDBRelation
   public static $itemtype_1             = "PluginOrderOrder";

   public static $items_id_1             = 'plugin_order_orders_id';

   public static $checkItem_1_Rights     = self::DONT_CHECK_ITEM_RIGHTS;

   public static $itemtype_2             = 'itemtype';

   public static $items_id_2             = 'items_id';

   public static $checkItem_2_Rights     = self::DONT_CHECK_ITEM_RIGHTS;

   public static $check_entity_coherency = false;

   //TODO better right and entity menber (ex Computer_Item)


   static function canCreate() {
      return true;
   }


   static function canPurge() {
      return true;
   }


   static function canDelete() {
      return true;
   }


   public function canCreateItem() {
      return true;
   }


   public function canUpdateItem() {
      return true;
   }


   public function canDeleteItem() {
      return true;
   }


   public function canPurgeItem() {
      return true;
   }


   public function canViewItem() {
      return true;
   }


   public static function getTypeName($nb = 0) {
      return __("Order item", "order");
   }


   public function getSearchOptions() {
      $tab = [];

      $tab['common']          = __("Orders management", "order");

      /* order_number */
      $tab[1]['table']        = self::getTable();
      $tab[1]['field']        = 'price_ati';
      $tab[1]['name']         = __("Unit price tax free", "order");
      $tab[1]['datatype']     = 'decimal';

      $tab[2]['table']        = self::getTable();
      $tab[2]['field']        = 'discount';
      $tab[2]['name']         = __("Discount (%)", "order");
      $tab[2]['datatype']     = 'decimal';

      $tab[3]['table']        = self::getTable();
      $tab[3]['field']        = 'price_taxfree';
      $tab[3]['name']         = __("Discount (%)", "order");
      $tab[3]['datatype']     = 'decimal';

      /* order_number */
      $tab[4]['table']        = self::getTable();
      $tab[4]['field']        = 'delivery_number';
      $tab[4]['name']         = __("Delivery form");
      $tab[4]['checktype']    = 'text';
      $tab[4]['displaytype']  = 'text';
      $tab[4]['injectable']   = true;

      /* order_date */
      $tab[5]['table']        = self::getTable();
      $tab[5]['field']        = 'delivery_date';
      $tab[5]['name']         = __("Delivery date");
      $tab[5]['datatype']     = 'date';
      $tab[5]['checktype']    = 'date';
      $tab[5]['displaytype']  = 'date';
      $tab[5]['injectable']   = true;

      /* comments */
      $tab[16]['table']       = self::getTable();
      $tab[16]['field']       = 'comment';
      $tab[16]['name']        = __("Description");
      $tab[16]['datatype']    = 'text';
      $tab[16]['checktype']   = 'text';
      $tab[16]['displaytype'] = 'multiline_text';
      $tab[16]['injectable']  = true;

      $tab[86]['table']       = 'glpi_plugin_order_deliverystates';
      $tab[86]['field']       = 'name';
      $tab[86]['name']        = __("Delivery status", "order");
      $tab[86]['injectable']  = true;

      return $tab;
   }


   public static function updateItem($item) {

      //TO DO : Must do check same values or update infocom
      $plugin = new Plugin();
      if ($plugin->isActivated("order")) {
         if (isset ($item->fields["id"])) {

            $item->getFromDB($item->input["id"]);

            if (isset ($item->fields["itemtype"]) & isset ($item->fields["items_id"])) {
               $orderlink      = new PluginOrderLink();
               $order          = new PluginOrderOrder();
               $orderitem      = new self();
               $order_supplier = new PluginOrderOrder_Supplier();

               $detail_id      = $orderlink->isItemLinkedToOrder($item->fields["itemtype"],
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
                           if (isset ($item->input[$field])
                              && $item->input[$field] != $value[$field]) {
                              $field_set           = true;
                              $item->input[$field] = $value[$field];
                           }
                        }
                        if ($field_set && !isset($item->input['_no_warning'])) {
                           Session::addMessageAfterRedirect(__("Some fields cannont be modified because they belong to an order", "order"), true, ERROR);
                        }
                        break;
                     case 'Contract':
                        $orderitem->getFromDB($detail_id);
                        $order->getFromDB($orderitem->fields["plugin_order_orders_id"]);
                        $item->input['cost'] = $orderitem->fields["price_discounted"];
                        break;
                  }
               }
            }
         }
      }
   }


   public static function getClasses($all = false) {
      global $ORDER_TYPES;

      if ($all) {
         return $ORDER_TYPES;
      }

      $types = $ORDER_TYPES;
      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }
         if (!$type::canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }


   public function getPricesATI($priceHT, $taxes) {
      return (!$priceHT ? 0 : $priceHT + (($priceHT * $taxes) / 100));
   }


   public function checkIFReferenceExistsInOrder($orders_id, $ref_id) {
      return  (countElementsInTable($this->getTable(),
                                    "`plugin_order_orders_id` = '$orders_id'
                                       AND `plugin_order_references_id` = '$ref_id' "));
   }


   public function addDetails($ref_id, $itemtype, $orders_id, $quantity, $price, $discounted_price, $taxes_id) {
      $order = new PluginOrderOrder();
      if ($quantity > 0 && $order->getFromDB($orders_id)) {
         for ($i = 0; $i < $quantity; $i++) {
            $input["plugin_order_orders_id"]     = $orders_id;
            $input["plugin_order_references_id"] = $ref_id;
            $input["plugin_order_ordertaxes_id"] = $taxes_id;
            $input["itemtype"]                   = $itemtype;
            $input["entities_id"]                = $order->getEntityID();
            $input["is_recursive"]               = $order->isRecursive();
            $input["price_taxfree"]              = $price;
            $input["price_discounted"]           = $price - ($price * ($discounted_price / 100));
            $input["states_id"]                  = PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED;;

            $input["price_ati"]                  = $this->getPricesATI($input["price_discounted"],
                                                                       Dropdown::getDropdownName("glpi_plugin_order_ordertaxes",
                                                                                                 $taxes_id));
            $input["discount"]                   = $discounted_price;

            $this->add($input);
         }
      }
   }


   /* show details of orders */
   public function showItem($ID) {
      $this->showFormDetail($ID);
      $this->showAddForm($ID);
   }


   public function showAddForm($plugin_order_orders_id) {
      global  $CFG_GLPI,$DB;

      $order     = new PluginOrderOrder();
      $reference = new PluginOrderReference();

      if ($order->getFromDB($plugin_order_orders_id)
         && $order->canUpdateOrder()) {
         if ($order->can($plugin_order_orders_id, UPDATE)) {
            echo "<form method='post' name='order_detail_form' id='order_detail_form'  action=\"".
            Toolbox::getItemTypeFormURL('PluginOrderOrder')."\">";
            echo Html::hidden('plugin_order_orders_id', ['value' => $plugin_order_orders_id]);
            echo "<div class='center'>";
            echo "<hr>";
            echo "<h3>".__("Add to the order", "order")."</h3>";
            echo "<table class='tab_cadre_fixe tab_order_fixed tab_order_add_items'>";

            if ($order->fields["suppliers_id"]) {
               echo "<tr align='center'>";
               echo "<th>".__("Type")."</th>";
               echo "<th>".__("Product reference", "order")."</th>";
               echo "<th>".__("Quantity", "order")."</th>";
               echo "<th>".__("Unit price tax free", "order")."</th>";
               echo "<th>".__("VAT", "order")."</th>";
               echo "<th>".__("Discount (%)", "order")."</th>";
               echo "<th></th>";
               echo"</tr>";

               echo "<tr align='center'>";
               echo "<td class='tab_bg_1'>";

               $query = "SELECT DISTINCT r.`itemtype`
                         FROM `glpi_plugin_order_references` r
                         LEFT JOIN `glpi_plugin_order_references_suppliers` s
                            ON s.`plugin_order_references_id` = r.`id`
                         WHERE s.`suppliers_id` = {$order->fields["suppliers_id"]}";
               $result = $DB->query($query);

               $itemtypeArray = ['' => Dropdown::EMPTY_VALUE];
               while (list($itemtype) = $DB->fetch_array($result)) {
                  $type                     = new $itemtype();
                  $itemtypeArray[$itemtype] = $type->getTypeName();
               }
               asort($itemtypeArray);

               $rand = mt_rand();
               Dropdown::showFromArray('itemtype', $itemtypeArray, [
                  'rand' => $rand,
                  'width' => '100%'
               ]);

               Ajax::updateItemOnSelectEvent('dropdown_itemtype'.$rand,
                                             'show_reference',
                                             '../ajax/dropdownReference.php',
                                             [
                                                'itemtype'     => '__VALUE__',
                                                'fieldname'    => 'plugin_order_references_id',
                                                'suppliers_id' => $order->fields["suppliers_id"],
                                                'entities_id'  => $order->fields["entities_id"],
                                                'is_recursive' => $order->fields["is_recursive"],
                                                'rand'         => $rand,
                                             ]);
               echo "</td>";
               echo "<td class='tab_bg_1'><span id='show_reference'>";
               Dropdown::showFromArray('plugin_order_references_id', [0 => Dropdown::EMPTY_VALUE], [
                  'rand'  => $rand,
                  'width' => '100%'
               ]);
               echo "</span></td>";

               echo "<td class='tab_bg_1'><span id='show_quantity'>";
               echo "<input type='number' name='quantity' value='0' class='quantity' />";
               echo "</span></td>";

               echo "<td class='tab_bg_1'><span id='show_priceht'>";
               echo "<input type='number' step='".PLUGIN_ORDER_NUMBER_STEP."' name='price' value='0.00' class='decimal' />";
               echo "</span></td>";

               echo "<td class='tab_bg_1'><span id='show_taxe'>";
               $config = PluginOrderConfig::getConfig();
               PluginOrderOrderTax::Dropdown([
                  'name'                => "plugin_order_ordertaxes_id",
                  'value'               => $config->getDefaultTaxes(),
                  'display_emptychoice' => true,
                  'emptylabel'          => __("No VAT", "order"),
               ]);
               echo "</span></td>";

               echo "<td class='tab_bg_1'><span id='show_pricediscounted'>";
               echo "<input type='number' step='".PLUGIN_ORDER_NUMBER_STEP."' name='discount' value='0' class='smalldecimal' />";
               echo "</span></td>";

               echo "<td class='tab_bg_1'><span id='show_validate'>";
               echo "<input type='submit' name='add_item' value=\"".__("Add")."\" class='submit'>";
               echo "</span></td>";
               echo "</tr>";
            } else {
               echo "<tr class='tab_bg_1'><td align='center'>".__("Please select a supplier", "order")."</td></tr>";
            }

            echo "</table></div>";
            Html::closeForm();
         }
      }
   }


   public function queryDetail($ID) {
      global $DB;

      $table = self::getTable();
      $query = "SELECT item.`id` AS IDD,
                       ref.`id`,
                       ref.`itemtype`,
                       othertype.`name` as othertypename,
                       ref.`types_id`,
                       ref.`models_id`,
                       ref.`manufacturers_id`,
                       ref.`name`,
                       item.`price_taxfree`, item.`price_ati`,
                       item.`price_discounted`,
                       item.`discount`,
                       item.`plugin_order_ordertaxes_id`
               FROM $table item
               LEFT JOIN `glpi_plugin_order_references` ref
                  ON item.`plugin_order_references_id` = ref.`id`
               LEFT JOIN `glpi_plugin_order_othertypes` othertype
                  ON ref.`itemtype` = 'PluginOrderOther' AND ref.`types_id` = `othertype`.`id`
               WHERE item.`plugin_order_orders_id` = '$ID'
               GROUP BY ref.`id`, item.`price_taxfree`, item.`discount`
               ORDER BY ref.`name` ";
      return $DB->query($query);
   }


   public function queryBills($orders_id, $references_id) {
      global $DB;

      $table = self::getTable();
      $query = "SELECT item.`id` AS IDD,
                       ref.`id`,
                       ref.`itemtype`,
                       ref.`types_id`,
                       ref.`models_id`,
                       ref.`manufacturers_id`,
                       ref.`name`,
                       item.`plugin_order_bills_id`,
                       item.`plugin_order_billstates_id`
                FROM `$table` item, `glpi_plugin_order_references` ref
                WHERE item.`plugin_order_references_id` = `ref`.`id`
                AND item.`plugin_order_orders_id` = '$orders_id'
                AND `ref`.`id` = '$references_id'
                ORDER BY `ref`.`name` ";
      return $DB->query($query);
   }


   public function queryRef($orders_id, $ref_id, $price_taxfree, $discount, $states_id = false) {
      global $DB;

      $query = "SELECT `id`, `items_id`
                FROM `glpi_plugin_order_orders_items`
                WHERE `plugin_order_orders_id` = '$orders_id'
                AND `plugin_order_references_id` = '$ref_id '
                AND CAST(`price_taxfree` AS CHAR) = '$price_taxfree '
                AND CAST(`discount` AS CHAR) = '$discount'";

      if ($states_id) {
         $query .= "AND `states_id` = '".$states_id."' ";
      }

      return $DB->query($query);
   }


   public function showFormDetail($plugin_order_orders_id) {
      global  $CFG_GLPI,$DB;

      $order                = new PluginOrderOrder();
      $reference            = new PluginOrderReference();
      $reception            = new PluginOrderReception();
      $result_ref           = $this->queryDetail($plugin_order_orders_id);
      $numref               = $DB->numrows($result_ref);
      $rand                 = mt_rand();
      $canedit              = $order->can($plugin_order_orders_id, UPDATE)
                              && $order->canUpdateOrder();
      Session::initNavigateListItems($this->getType(),
                            __("Order", "order") ." = ". $order->getName());
      while ($data_ref = $DB->fetch_array($result_ref)) {
         $global_rand = mt_rand();
         echo "<div class='center'>";
         echo "<form method='post' name='order_updatedetail_form$rand' " .
                  "id='order_updatedetail_form$rand'  " .
                  "action='".Toolbox::getItemTypeFormURL('PluginOrderOrder')."'>";
         echo Html::hidden('plugin_order_orders_id', ['value' => $plugin_order_orders_id]);
         echo Html::hidden('plugin_order_order_items_id', ['value' => $data_ref['IDD']]);
         echo "<table class='tab_cadre_fixe'>";
         if (!$numref) {
            echo "<tr><th>".__("No item to take delivery of", "order")."</th></tr></table></div>";
         } else {
            $refID         = $data_ref["id"];
            $price_taxfree = $data_ref["price_taxfree"];
            $discount      = $data_ref["discount"];
            $rand          = mt_rand();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('detail$rand','detail_img$rand', '"
              .$CFG_GLPI['root_doc']."/pics/plus.png','"
              .$CFG_GLPI['root_doc']."/pics/moins.png');\">";
            echo "<img alt='' name='detail_img$rand' src=\"".$CFG_GLPI['root_doc']."/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>".__("Quantity", "order")."</th>";
            echo "<th>".__("Equipment", "order")."</th>";
            echo "<th>".__("Manufacturer")."</th>";
            echo "<th>".__("Reference")."</th>";
            echo "<th>".__("Type")."</th>";
            echo "<th>".__("Model")."</th>";
            echo "<th>".__("Unit price tax free", "order")."</th>";
            echo "<th>".__("Discount (%)", "order")."</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1 center'>";

            echo "<td><div id='viewaccept$rand' style='display:none;'>";
            echo "<p><input type='submit' onclick=\"return confirm('"
              .__("Do you really want to update this item ?", "order")."');\" name='update_item' value=\""
              ._sx("button", "Update")."\" class='submit'></p>";
            echo "<br /><p><input type='button' onclick=\"hideForm$rand();\" value=\""
               ._sx("button", "Cancel")."\" class='submit'></p>";
            echo "</div></td>";

            if ($canedit) {
               echo "<script type='text/javascript' >";
               echo "function hideForm$rand() {";
               echo "$('#quantity$rand').show();";
               echo "$('#pricetaxfree$rand').show();";
               echo "$('#discount$rand').show();";

               echo "$('#viewquantity$rand input').remove();";
               echo "$('#viewpricetaxfree$rand input').remove();";
               echo "$('#viewdiscount$rand input').remove();";

               echo "$('#viewaccept$rand').hide();";
               echo "}";
               echo "</script>";
            }

            /* quantity */
            $quantity = $this->getTotalQuantityByRefAndDiscount($plugin_order_orders_id, $refID,
                                                                $price_taxfree, $discount);
            if ($canedit) {
               echo "<td align='center'>";
               echo "<script type='text/javascript' >";
               echo "function showQuantity$rand() {";
               echo "$('#quantity$rand').hide();";
               echo "$('#viewaccept$rand').show();";
               Ajax::updateItemJsCode("viewquantity$rand", $CFG_GLPI["root_doc"]."/plugins/order/ajax/inputnumber.php", [
                  'maxlength'     => 15,
                  'size'          => 8,
                  'name'          => 'quantity',
                  'class'         => 'quantity',
                  'force_integer' => true,
                  'data'          => rawurlencode($quantity)
               ], false);
               echo "}";
               echo "</script>";
               echo "<div id='quantity$rand' class='center' onClick='showQuantity$rand()'>";
               echo $quantity;
               echo "</div>";
               echo "<div id='viewquantity$rand'>";
               echo "</div>";
               echo "</td>";
            } else {
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

            echo Html::hidden('old_plugin_order_references_id', ['value' => $refID]);

            echo $reference->getReceptionReferenceLink($data_ref);
            echo "</td>";
            /* type */
            echo "<td align='center'>";
            if (file_exists(GLPI_ROOT."/inc/".strtolower($data_ref["itemtype"])."type.class.php")) {
               echo Dropdown::getDropdownName(getTableForItemType($data_ref["itemtype"]."Type"),
                                              $data_ref["types_id"]);
            } else if ($data_ref["itemtype"] == "PluginOrderOther") {
               echo  $data_ref['othertypename'];
            }
            echo "</td>";
            /* modele */
            echo "<td align='center'>";
            if (file_exists(GLPI_ROOT."/inc/".strtolower($data_ref["itemtype"])."model.class.php")) {
               echo Dropdown::getDropdownName(getTableForItemType($data_ref["itemtype"]."Model"),
                                              $data_ref["models_id"]);
            }
            echo "</td>";
            if ($canedit) {
               echo "<td align='center'>";
               echo Html::hidden('old_price_taxfree', ['value' => $price_taxfree]);
               echo "<script type='text/javascript' >";
               echo "function showPricetaxfree$rand() {";
               echo "$('#pricetaxfree$rand').hide();";
               echo "$('#viewaccept$rand').show();";
               Ajax::updateItemJsCode("viewpricetaxfree$rand", $CFG_GLPI["root_doc"]."/plugins/order/ajax/inputnumber.php", [
                  'maxlength' => 15,
                  'size'      => 8,
                  'name'      => 'price_taxfree',
                  'class'     => 'decimal',
                  'data'      => rawurlencode($price_taxfree)
               ], false);
               echo "}";
               echo "</script>";
               echo "<div id='pricetaxfree$rand' class='center' onClick='showPricetaxfree$rand()'>";
               echo Html::formatNumber($price_taxfree);
               echo "</div>";
               echo "<div id='viewpricetaxfree$rand'>";
               echo "</div>";
               echo "</td>";
            } else {
               echo "<td align='center'>".Html::formatNumber($price_taxfree)."</td>";
            }
            /* reduction */
            if ($canedit) {
               echo "<td align='center'>";
               echo Html::hidden('old_discount', ['value' => $discount]);
               echo "<script type='text/javascript'>";
               echo "function showDiscount$rand() {";
               echo "$('#discount$rand').hide();";
               echo "$('#viewaccept$rand').show();";
               Ajax::updateItemJsCode("viewdiscount$rand", $CFG_GLPI["root_doc"]."/plugins/order/ajax/inputnumber.php", [
                  'maxlength' => 15,
                  'size'      => 8,
                  'name'      => 'discount',
                  'class'     => 'smalldecimal',
                  'data'      => rawurlencode($discount)
               ], false);
               echo "}";
               echo "</script>";
               echo "<div id='discount$rand' class='center' onClick='showDiscount$rand()'>";
               echo Html::formatNumber($discount);
               echo "</div>";
               echo "<div id='viewdiscount$rand'>";
               echo "</div>";
               echo "</td>";
            } else {
               echo "<td align='center'>".Html::formatNumber($discount)."</td>";
            }
            echo "</tr></table>";
            Html::closeForm();

            echo "<div class='center' id='detail$rand' style='display:none'>";
            echo "<form method='post' name='order_detail_form$rand' id='order_detail_form$rand'  " .
                  "action=\"".Toolbox::getItemTypeFormURL('PluginOrderOrder')."\">";

            if ($canedit) {
               echo "<div class='center'>";
               echo "<table width='950px' class='tab_glpi'>";
               echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left-top.png\" alt=''>";
               echo "</td><td class='center'>";
               echo "<a onclick= \"if ( markCheckboxes('order_detail_form$rand') ) return false;\" href='#'>".
               __("Check all")."</a></td>";
               echo "<td>/</td><td class='center'>";
               echo "<a onclick= \"if ( unMarkCheckboxes('order_detail_form$rand') ) " .
                " return false;\" href='#'>".__("Uncheck all")."</a>";
               echo "</td><td align='left'>";
               echo "<input type='submit' onclick=\"return confirm('" .
               __("Do you really want to delete these details ? Delivered items will not be linked to order !", "order")."')\" name='delete_item' value=\"".
               __("Delete permanently")."\" class='submit'>";
               echo "</td>";

               // Edit buttons
               echo "<td align='left' width='80%'>";
               echo "<div id='detail_viewaccept$global_rand' style='display:none;'>";

               echo "&nbsp;<input type='submit' onclick=\"return confirm('" .
                    __("Do you really want to update this item ?", "order")."');\" name='update_detail_item'
                    value=\"". _sx("button", "Update")."\" class='submit'>&nbsp;";

               echo "&nbsp;<input type='button' onclick=\"detail_hideForm$global_rand();\"
                     value=\""._sx("button", "Cancel")."\" class='submit'>";

               echo "</div>";
               echo "</td>";

               echo "</table>";
               echo "</div>";
            }

            echo "<table class='tab_cadre_fixe'>";

            echo "<tr>";
            if ($canedit) {
               echo "<th></th>";
            }
            if ($data_ref["itemtype"] != 'SoftwareLicense') {
               echo "<th>".__("ID")."</th>";
            }
            echo "<th>".__("Reference")."</th>";
            echo "<th>".__("Unit price tax free", "order")."</th>";
            echo "<th>".__("VAT", "order")."</th>";
            echo "<th>".__("Discount (%)", "order")."</th>";
            echo "<th>".__("Discounted price tax free", "order")."</th>";
            echo "<th>".__("Price ATI", "order")."</th>";
            echo "<th>".__("Status")."</th></tr>";

            $table = self::getTable();
            $query = "SELECT `$table`.`id` AS IDD, `glpi_plugin_order_references`.`id`,
                           `glpi_plugin_order_references`.`name`,
                           `$table`.`comment`,
                           `$table`.`price_taxfree`,
                           `$table`.`price_discounted`,
                           `$table`.`discount`,
                           `$table`.`plugin_order_ordertaxes_id`,
                           `$table`.`price_ati`
                     FROM `$table`, `glpi_plugin_order_references`
                     WHERE `$table`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`
                        AND `$table`.`plugin_order_references_id` = '$refID'
                        AND `$table`.`price_taxfree` LIKE '$price_taxfree'
                        AND `$table`.`discount` LIKE '$discount'
                        AND `$table`.`plugin_order_orders_id` = '$plugin_order_orders_id'";

            if ($data_ref["itemtype"] == 'SoftwareLicense') {
               $query .= " GROUP BY `glpi_plugin_order_references`.`name` ";
            }
            $query .= " ORDER BY `glpi_plugin_order_references`.`name` ";

            $result = $DB->query($query);
            $num    = $DB->numrows($result);

            // Initialize for detail_hideForm javascript function
            $hideForm = "";

            while ($data = $DB->fetch_array($result)) {
               $rand_line = mt_rand();
               Session::addToNavigateListItems($this->getType(), $data['IDD']);

               // Compute for detail_hideForm javascript function
               $hideForm .= "$('#detail_pricetaxfree$rand_line').show();";
               $hideForm .= "$('#detail_viewpricetaxfree$rand_line input').remove();";
               $hideForm .= "$('#detail_discount$rand_line').show();";
               $hideForm .= "$('#detail_viewdiscount$rand_line input').remove();";

               echo "<tr class='tab_bg_1'>";
               if ($canedit) {
                  echo "<td width='10'>";
                  $sel = "";
                  if (isset($_GET["select"]) && $_GET["select"] == "all") {
                     $sel = "checked";
                  }
                  echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
                  echo Html::hidden('plugin_order_orders_id', ['value' => $plugin_order_orders_id]);
                  echo "</td>";
               }
               if ($data_ref["itemtype"] != 'SoftwareLicense') {
                  echo "<td align='center'><a href='".
                     Toolbox::getItemTypeFormURL('PluginOrderOrder_Item')."?id=".$data['IDD']."'>".$data['IDD']."</a>";
                     echo "&nbsp;";
                     Html::showToolTip($data['comment']);
               }

               /* reference */
               echo "<td align='center'>";
               echo Html::hidden("detail_old_plugin_order_references_id[".$data["IDD"]."]",
                                 ['value' => $data["id"]]);
               echo $reference->getReceptionReferenceLink($data);
               echo "</td>";

               if ($canedit) {
                  echo "<td align='center'>";
                  echo Html::hidden("detail_old_price_taxfree[".$data["IDD"]."]", [
                     'value' => $data["price_taxfree"]
                  ]);
                  echo "<script type='text/javascript' >";
                  echo "function showDetailPricetaxfree$rand_line() {";
                  echo "$('#detail_pricetaxfree$rand_line').hide();";
                  echo "$('#detail_viewaccept$global_rand').show();";
                  Ajax::updateItemJsCode("detail_viewpricetaxfree$rand_line", $CFG_GLPI["root_doc"]."/plugins/order/ajax/inputnumber.php", [
                     'maxlength' => 15,
                     'size'      => 8,
                     'name'      => 'detail_price_taxfree['.$data["IDD"].']',
                     'class'     => 'decimal',
                     'data'      => rawurlencode($data["price_taxfree"])
                  ], false);
                  echo "}";
                  echo "</script>";
                  echo "<div id='detail_pricetaxfree$rand_line' class='center'
                             onClick='showDetailPricetaxfree$rand_line()'>";
                  echo Html::formatNumber($data["price_taxfree"]);
                  echo "</div>";
                  echo "<div id='detail_viewpricetaxfree$rand_line'>";
                  echo "</div>";
                  echo "</td>";
               } else {
                  echo "<td align='center'>".Html::formatNumber($data["price_taxfree"])."</td>";
               }

               /* taxe */
               echo "<td align='center'>";
               echo Dropdown::getDropdownName(getTableForItemType("PluginOrderOrderTax"),
                                              $data["plugin_order_ordertaxes_id"]);
               echo "</td>";
               /* reduction */
               if ($canedit) {
                  echo "<td align='center'>";
                  echo Html::hidden("detail_old_discount[".$data["IDD"]."]", [
                     'value' => $data["discount"]
                  ]);
                  echo "<script type='text/javascript' >";
                  echo "function showDetailDiscount$rand_line() {";
                  echo "$('#detail_discount$rand_line').hide();";
                  echo "$('#detail_viewaccept$global_rand').show();";
                  Ajax::updateItemJsCode("detail_viewdiscount$rand_line", $CFG_GLPI["root_doc"]."/plugins/order/ajax/inputnumber.php", [
                     'maxlength' => 15,
                     'size'      => 8,
                     'name'      => 'detail_discount['.$data["IDD"].']',
                     'class'     => 'smalldecimal',
                     'data'      => rawurlencode($data["discount"])
                  ], false);
                  echo "}";
                  echo "</script>";
                  echo "<div id='detail_discount$rand_line' class='center'
                             onClick='showDetailDiscount$rand_line()'>";
                  echo Html::formatNumber($data["discount"]);
                  echo "</div>";
                  echo "<div id='detail_viewdiscount$rand_line'>";
                  echo "</div>";
                  echo "</td>";
               } else {
                  echo "<td align='center'>".Html::formatNumber($data["discount"])."</td>";
               }
               /* price with reduction */
               echo "<td align='center'>".Html::formatNumber($data["price_discounted"])."</td>";
               /* price ati */
               echo "<td align='center'>".Html::formatNumber($data["price_ati"])."</td>";
               /* status  */
               echo "<td align='center'>".$reception->getReceptionStatus($data["IDD"]).
                  "</td></tr>";

            }
            echo "</table>";

            if ($canedit) {
               echo "<script type='text/javascript' >";
               echo "function detail_hideForm$global_rand() {";
               echo $hideForm;
               echo "$('#detail_viewaccept$global_rand').hide();";
               echo "}";
               echo "</script>";
            }

            if ($canedit) {
               echo "<div class='center'>";
               echo "<table width='950px' class='tab_glpi'>";
               echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''>";
               echo "</td><td class='center'>";
               echo "<a onclick= \"if ( markCheckboxes('order_detail_form$rand') ) return false;\" href='#'>".
                  __("Check all")."</a></td>";
               echo "<td>/</td><td class='center'>";
               echo "<a onclick= \"if ( unMarkCheckboxes('order_detail_form$rand') ) " .
                      " return false;\" href='#'>".__("Uncheck all")."</a>";
               echo "</td><td align='left'>";
               echo "<input type='submit' onclick=\"return confirm('" .
                  __("Do you really want to delete these details ? Delivered items will not be linked to order !", "order")."')\" name='delete_item' value=\"".
                     __("Delete permanently")."\" class='submit'>";
               echo "</td>";

               // Edit buttons
               echo "<td align='left' width='80%'>";
               echo "<div id='detail_viewaccept$global_rand' style='display:none;'>";

               echo "&nbsp;<input type='submit' onclick=\"return confirm('" .
                  __("Do you really want to update this item ?", "order")."');\" name='update_detail_item'
                     value=\"". _sx("button", "Update")."\" class='submit'>&nbsp;";

               echo "&nbsp;<input type='button' onclick=\"detail_hideForm$global_rand();\"
                     value=\""._sx("button", "Cancel")."\" class='submit'>";

               echo "</div>";
               echo "</td>";

               echo "</table>";
               echo "</div>";
            }
            Html::closeForm();
            echo "</div>";
         }
         echo "<br>";
      }
   }


   public function getTotalQuantityByRefAndDiscount($orders_id, $references_id, $price_taxfree, $discount) {
      global $DB;

      $query = "SELECT COUNT(*) AS quantity
                FROM `".self::getTable()."`
                WHERE  `plugin_order_orders_id` = '$orders_id'
                  AND `plugin_order_references_id` = '$references_id'
                  AND CAST(`price_taxfree` AS CHAR) = '$price_taxfree'
                  AND CAST(`discount` AS CHAR) = '$discount'";
      $result = $DB->query($query);
      return ($DB->result($result, 0, 'quantity'));
   }


   public function getTotalQuantityByRef($orders_id, $references_id) {
      global $DB;

      $query = "SELECT COUNT(*) AS quantity
                FROM `".self::getTable()."`
                WHERE `plugin_order_orders_id` = '$orders_id'
                   AND `plugin_order_references_id` = '$references_id' ";
      $result = $DB->query($query);
      return ($DB->result($result, 0, 'quantity'));
   }


   public function getDeliveredQuantity($orders_id, $references_id, $price_taxfree, $discount) {
      return countElementsInTable(self::getTable(),
                                  "`plugin_order_orders_id` = '$orders_id'
                                    AND `plugin_order_references_id` = '$references_id'
                                    AND `price_taxfree` LIKE '$price_taxfree'
                                    AND `discount` LIKE '$discount'
                                    AND `states_id` != '0' ");
   }


   public function getAllPrices($orders_id) {
      global $DB;

      $query = "SELECT SUM(`price_ati`) AS priceTTC, SUM(`price_discounted`) AS priceHT,
                     SUM(`price_ati` - `price_discounted`) as priceTVA
                FROM `".self::getTable()."`
                WHERE `plugin_order_orders_id` = '$orders_id' ";
      $result = $DB->query($query);
      return $DB->fetch_array($result);
   }


   public function getOrderInfosByItem($itemtype, $items_id) {
      global $DB;

      $table = self::getTable();
      $query = "SELECT `glpi_plugin_order_orders`.*
                FROM `glpi_plugin_order_orders`, `$table`
                WHERE `glpi_plugin_order_orders`.`id` = `$table`.`plugin_order_orders_id`
                  AND `$table`.`itemtype` = '$itemtype'
                  AND `$table`.`items_id` = '$items_id' ";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         return $DB->fetch_array($result);
      } else {
         return false;
      }
   }


   public function showPluginFromItems($itemtype, $ID) {
      global $CFG_GLPI;

      $infos = $this->getOrderInfosByItem($itemtype, $ID);
      if ($infos) {
         echo "<tr align='center'><th colspan='5'>".__("Order informations", "order")."</th></tr>";
         echo "<tr align='center'><td colspan='2' class='tab_bg_2'>".__("Order name", "order")."</td>";
         echo "<td colspan='2' class='tab_bg_2'>";
         $order = new PluginOrderOrder();
         $order->getFromDB($infos['id']);
         echo $order->getLink(PluginOrderOrder::canView());
         echo "</td>";

         $result = getAllDatasFromTable(self::getTable(),
                                        "`plugin_order_orders_id`='".$infos['id']."'
                                          AND `itemtype`='$itemtype'
                                          AND `items_id`='$ID'");
         if (!empty($result)) {
            $link = array_shift($result);
            $reference = new PluginOrderReference();
            $reference->getFromDB($link['plugin_order_references_id']);
            if (Session::haveRight('plugin_order_reference', READ)) {
               echo "<tr align='center'><td colspan='2' class='tab_bg_2'>" .
                     __("Reference")."</td>";
               echo "<td colspan='2' class='tab_bg_2'>".
                    $reference->getLink(PluginOrderReference::canView())."</td></tr>";
            }
            echo "<tr align='center'><td colspan='2' class='tab_bg_2'>" .
                  __("Delivery date")."</td>";
            echo "<td colspan='2' class='tab_bg_2'>".Html::convDate($link["delivery_date"])."</td></tr>";

         }
      }
   }


   public function defineTabs($options = array()) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   public function showForm ($ID, $options = array()) {
      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      $order_order = new PluginOrderOrder();
      $order_order->getFromDB($this->fields['plugin_order_orders_id']);

      $order_reference = new PluginOrderReference();
      $order_reference->getFromDB($this->fields["plugin_order_references_id"]);

      $canedit         = $order_order->can($this->fields['plugin_order_orders_id'], UPDATE)
                          && $order_order->canUpdateOrder() && !$order_order->isCanceled();
      $canedit_comment = $order_order->can($this->fields['plugin_order_orders_id'], UPDATE)
                          && !$order_order->isCanceled();

      echo Html::hidden('plugin_order_orders_id',
                        ['value' => $this->fields['plugin_order_orders_id']]);

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__("Order", "order").": </td>";
      echo "<td>";
      echo $order_order->getLink(true);
      echo "</td>";

      echo "<td>".__("Reference").": </td>";
      echo "<td>";
      $data         = [];
      $data["id"]   = $this->fields["plugin_order_references_id"];
      $data["name"] = $order_reference->fields["name"];
      echo $order_reference->getReceptionReferenceLink($data);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Unit price tax free", "order").": </td>";
      if ($canedit) {
         echo "<td><input type='number' step='".PLUGIN_ORDER_NUMBER_STEP."' name='price_taxfree' value='".$this->fields['price_taxfree']."' class='decimal'>";
      } else {
         echo "<td>".Html::formatNumber($this->fields['price_taxfree'])."</td>";
      }

      echo "<td>".__("VAT", "order").": </td>";
      echo "<td>";
      if ($canedit) {
         PluginOrderOrderTax::Dropdown(['value' => $this->fields['plugin_order_ordertaxes_id']]);
      } else {
         echo Dropdown::getDropdownName('glpi_plugin_order_ordertaxes',
                                        $this->fields['plugin_order_ordertaxes_id']);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Discount (%)", "order").": </td>";
      if ($canedit) {
         echo "<td><input type='number' step='".PLUGIN_ORDER_NUMBER_STEP."' name='discount'
                          value='".$this->fields['discount']."' class='decimal'>";
      } else {
         echo "<td>".Html::formatNumber($this->fields['discount'])."</td>";
      }

      echo "<td>".__("Discounted price tax free", "order").": </td>";
      echo "<td>".Html::formatNumber($this->fields['price_discounted'])."</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Price ATI", "order").": </td>";
      echo "<td>".Html::formatNumber($this->fields['price_ati'])."</td>";

      echo "<td>".__("Status").": </td>";
      echo "<td>";
      echo Dropdown::getDropdownName('glpi_plugin_order_deliverystates',
                                      $this->fields['plugin_order_deliverystates_id']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>";

      //comments of order
      echo __("Description").":  </td>";
      echo "<td colspan='3'>";
      if ($canedit_comment) {
         echo "<textarea cols='50' rows='4' name='comment'>".$this->fields["comment"]."</textarea>";
      } else {
         echo $this->fields['comment'];
      }
      echo "</td></tr>";

      $this->showFormButtons([
         'canedit' => ($canedit || $canedit_comment),
         'candel' => $canedit
      ]);

      return true;
   }


   public function updatePrices($order_items_id) {
      global $DB;
      $this->getFromDB($order_items_id);
      if (isset($this->input['price_taxfree'])
         || isset($this->input['plugin_order_ordertaxes_id'])) {
         $data = $this->queryRef($this->fields['plugin_order_orders_id'],
                                 $this->fields['plugin_order_references_id'],
                                 $this->fields['price_taxfree'],
                                 $this->fields['discount']);
         while ($item = $DB->fetch_array($data)) {
            $this->updatePrice_taxfree([
               'item_id'       => $item['id'],
               'price_taxfree'  => $this->fields['price_taxfree']
            ]);
         }
      }
   }


   public function showBillsItems(PluginOrderOrder $order) {
      global $DB, $CFG_GLPI;

      $reference  = new PluginOrderReference();

      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'><th colspan='2'>".__("Bills", "order")."</th></tr>";
      echo "<tr class='tab_bg_1'><td class='center'>".__("Payment status", "order").": </td>";
      echo "<td>";
      echo PluginOrderBillState::getState($order->fields['plugin_order_billstates_id']);
      echo "</td></tr></table>";

      $table = self::getTable();
      if (countElementsInTable($table, "`plugin_order_orders_id`='".$order->getID().
                               "' GROUP BY `plugin_order_bills_id`")) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th>".__("Name")."</th>";
         echo "<th>".__("Status")."</th>";
         echo "<th>".__("Value")."</th>";
         echo "<th>".__("Paid value", "order")."</th></tr>";

         $bill = new PluginOrderBill();
         foreach ($DB->request($table, "`plugin_order_orders_id`='".$order->getID().
                                       "' GROUP BY `plugin_order_bills_id`") as $item) {
            if (isset($item->fields['plugin_order_bills_id'])
            && $item->fields['plugin_order_bills_id']) {
               echo "<tr class='tab_bg_1'><td class='center'>";
               if ($bill->can($item->fields['plugin_order_bills_id'], READ)) {
                  echo "<td><a href='".$item->getURL()."'>".$bill->getName()."</a></td>";
               } else {
                  echo "<td>".$bill->getName()."</td>";
               }

                  echo "</td>";
                  echo "<td>";
                  echo Dropdown::getDropdownName(PluginOrderBillState::getTable(),
                                                 $bill->fields['plugin_order_billstates_id']);
                  echo "</td></tr>";

            }
         }

         echo "</tr></table>";
      }

      echo "</div>";

      //Can write orders, and order is not already paid
      $canedit = $order->can($order->getID(), UPDATE)
                 && !$order->isPaid() && !$order->isCanceled();

      $query_ref = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD,
                     `glpi_plugin_order_orders_items`.`plugin_order_references_id` AS id,
                     `glpi_plugin_order_references`.`name`,
                     `glpi_plugin_order_references`.`itemtype`,
                     `glpi_plugin_order_references`.`manufacturers_id`
                    FROM `glpi_plugin_order_orders_items`, `glpi_plugin_order_references`
                    WHERE `plugin_order_orders_id` = '".$order->getID()."'
                     AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`
                    GROUP BY `glpi_plugin_order_orders_items`.`plugin_order_references_id`
                    ORDER BY `glpi_plugin_order_references`.`name`";
      $result_ref = $DB->query($query_ref);

      while ($data_ref = $DB->fetch_array($result_ref)) {
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if (!$DB->numrows($result_ref)) {
            echo "<tr><th>".__("No item to take delivery of", "order")."</th></tr></table></div>";

         } else {
            $rand     = mt_rand();
            $itemtype = $data_ref["itemtype"];
            $item     = new $itemtype();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('generation$rand','generation_img$rand', '".
               $CFG_GLPI['root_doc']."/pics/plus.png','".$CFG_GLPI['root_doc']."/pics/moins.png');\">";
            echo "<img alt='' name='generation_img$rand' src=\"".$CFG_GLPI['root_doc']."/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>".__("Type")."</th>";
            echo "<th>".__("Manufacturer")."</th>";
            echo "<th>".__("Product reference", "order")."</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>".$item->getTypeName()."</td>";

            //Entity
            echo "<td align='center'>";
            echo Dropdown::getDropdownName('glpi_entities', $this->getEntityID());
            echo "</td>";
            echo "<td>".$reference->getReceptionReferenceLink($data_ref)."</td>";
            echo "</tr></table>";

            echo "<div class='center' id='generation$rand' style='display:none'>";
            echo "<form method='post' name='bills_form$rand' id='bills_form$rand'  " .
                     "action='".Toolbox::getItemTypeFormURL('PluginOrderBill')."'>";

            echo Html::hidden('plugin_order_orders_id', ['value' => $order->getID()]);
            echo "<table class='tab_cadre_fixe'>";

            echo "<th></th>";
            echo "<th>".__("Reference")."</th>";
            echo "<th>".__("Type")."</th>";
            echo "<th>".__("Model")."</th>";
            echo "<th>".__("Bill", "order")."</th>";
            echo "<th>".__("Bill status", "order")."</th>";
            echo "</tr>";

            $results = $this->queryBills($order->getID(), $data_ref['id']);
            while ($data = $DB->fetch_array($results)) {
               echo "<tr class='tab_bg_1'>";
               if ($canedit) {
                  echo "<td width='10'>";
                  $sel = "";
                  if (isset($_GET["select"]) && $_GET["select"] == "all") {
                     $sel = "checked";
                  }
                  echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
                  echo Html::hidden('plugin_order_orders_id', ['value' => $order->getID()]);
                  echo "</td>";
               }

               //Reference
               echo "<td align='center'>";
               echo $reference->getReceptionReferenceLink($data);
               echo "</td>";

               //Type
               echo "<td align='center'>";
               if (file_exists(GLPI_ROOT."/inc/".strtolower($data["itemtype"])."type.class.php")) {
                  echo Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Type"),
                                                                     $data["types_id"]);
               }
               echo "</td>";
               //Model
               echo "<td align='center'>";
               if (file_exists(GLPI_ROOT."/inc/".strtolower($data["itemtype"])."model.class.php")) {
                  echo Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Model"),
                                                 $data["models_id"]);
               }
               $bill = new PluginOrderBill();
               echo "<td align='center'>";
               if ($data["plugin_order_bills_id"] > 0) {
                  if ($bill->can($data['plugin_order_bills_id'], READ)) {
                     echo "<a href='".$bill->getLinkURL()."'>".$bill->getName(true)."</a>";

                  } else {
                     echo $bill->getName();
                  }

               }
               echo "</td>";
               echo "<td align='center'>";
               echo Dropdown::getDropdownName(PluginOrderBillState::getTable(),
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
                  "return false;\" href='#'>".__("Check all")."</a></td>";

            echo "<td>/</td><td class='center'>";
            echo "<a onclick= \"if ( unMarkCheckboxes('bills_form$rand') ) " .
                  "return false;\" href='#'>".__("Uncheck all")."</a>";
            echo "</td><td align='left' width='80%'>";
            echo Html::hidden('plugin_order_orders_id', ['value' => $order->getID()]);
            $this->dropdownBillItemsActions($order->getID());
            echo "</td>";
            echo "</table>";
            echo "</div>";

         }
         Html::closeForm();
         echo "</div>";
      }
      echo "<br>";
   }


   public function dropdownBillItemsActions($orders_id) {
      global $CFG_GLPI;
      $action['']     = Dropdown::EMPTY_VALUE;
      $action['bill'] = __("Bill", "order");
      $rand           = Dropdown::showFromArray('chooseAction', $action);

      Ajax::updateItemOnSelectEvent("dropdown_chooseAction$rand", "show_billsActions$rand",
                                    $CFG_GLPI["root_doc"]."/plugins/order/ajax/billactions.php",
                                    [
                                       'action'                 => '__VALUE__',
                                       'plugin_order_orders_id' => $orders_id
                                    ]);
       echo "<span id='show_billsActions$rand'>&nbsp;</span>";

   }


   public function updateQuantity($post) {
      global $DB;
      $quantity = $this->getTotalQuantityByRefAndDiscount($post['plugin_order_orders_id'],
                                                          $post['old_plugin_order_references_id'],
                                                          $post['old_price_taxfree'],
                                                          $post['old_discount']);

      if ($post['quantity'] > $quantity) {

         $data = $this->queryRef($post['plugin_order_orders_id'],
                                 $post['old_plugin_order_references_id'],
                                 $post['old_price_taxfree'],
                                 $post['old_discount']);
         $item = $DB->fetch_array($data);

         $this->getFromDB($item['id']);
         $to_add  = $post['quantity'] - $quantity;
         $this->addDetails($this->fields['plugin_order_references_id'],
                           $this->fields['itemtype'],
                           $this->fields['plugin_order_orders_id'],
                           $to_add,
                           $this->fields['price_taxfree'],
                           $this->fields['discount'],
                           $this->fields['plugin_order_ordertaxes_id']);
      }
   }


   public function updatePrice_taxfree($post) {
      global $DB;

      $this->getFromDB($post['item_id']);

      $input = $this->fields;
      $discount                   = $input['discount'];
      $plugin_order_ordertaxes_id = $input['plugin_order_ordertaxes_id'];

      $input["price_taxfree"]     = $post['price_taxfree'];
      $input["price_discounted"]  = $input["price_taxfree"] - ($input["price_taxfree"] * ($discount / 100));

      $taxe_name                  = Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", $plugin_order_ordertaxes_id);
      $input["price_ati"]         = $this->getPricesATI($input["price_discounted"], $taxe_name);
      $this->update($input);
   }


   public function updateDiscount($post) {
      global $DB;

      $this->getFromDB($post['item_id']);

      $input                        = $this->fields;
      $plugin_order_ordertaxes_id   = $input['plugin_order_ordertaxes_id'];

      $input["discount"]            = $post['discount'];
      $input["price_discounted"]    = $post['price'] - ($post['price'] * ($input['discount'] / 100));

      $taxe_name = Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", $plugin_order_ordertaxes_id);
      $input["price_ati"]  = $this->getPricesATI($input["price_discounted"], $taxe_name);
      $this->update($input);
   }


   public static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();

      if (!$DB->tableExists($table) && !$DB->tableExists("glpi_plugin_order_detail")) {
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
               `price_taxfree` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `price_discounted` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `discount` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `price_ati` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `states_id` int(11) NOT NULL default 1,
               `delivery_date` date default NULL,
               `plugin_order_bills_id` INT( 11 ) NOT NULL DEFAULT '0',
               `plugin_order_billstates_id` INT( 11 ) NOT NULL DEFAULT '0',
               `comment` text collate utf8_unicode_ci,
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
         if ($DB->tableExists("glpi_plugin_order_detail")) {
            $migration->addField("glpi_plugin_order_detail", "delivery_status",
                                 "int(1) NOT NULL default '0'");
            $migration->addField("glpi_plugin_order_detail", "delivery_comments", "TEXT");
            $migration->migrationOneTable("glpi_plugin_order_detail");

         }

         //1.2.0
         $migration->renameTable("glpi_plugin_order_detail", $table);

         $migration->changeField($table, "ID", "id", "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($table, "FK_order", "plugin_order_orders_id",
                                  "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)'");
         $migration->changeField($table, "device_type", "itemtype",
                                 "varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file'");
         $migration->changeField($table, "FK_device", "items_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)'");
         $migration->changeField($table, "FK_reference", "plugin_order_references_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)'");
         $migration->changeField($table, "delivery_status", "plugin_order_deliverystates_id",
                                 "int (11)  NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_deliverystates (id)'");
         $migration->changeField($table, "deliverynum", "delivery_number",
                                 "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->changeField($table, "delivery_comments", "delivery_comment",
                                 "text collate utf8_unicode_ci");
         $migration->changeField($table, "status", "states_id", "int(11) NOT NULL default 1");
         $migration->changeField($table, "date", "delivery_date", "date default NULL");
         $migration->addKey($table, ["items_id", "itemtype"], "FK_device" );
         $migration->addKey($table, ["itemtype", "items_id"], "item");
         $migration->addKey($table, "plugin_order_references_id");
         $migration->addKey($table, "plugin_order_deliverystates_id");
         $migration->addKey($table, "states_id");
         $migration->migrationOneTable($table);

         Plugin::migrateItemType([], [], [$table]);
          //1.4.0
         $migration->addField($table, "plugin_order_ordertaxes_id",
                              "INT (11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)'");
         $migration->migrationOneTable($table);

         /* Migrate VAT */
         foreach ($DB->request("glpi_plugin_order_orders") as $data) {
            $query  = "UPDATE `glpi_plugin_order_orders_items`
                       SET `plugin_order_ordertaxes_id` = '".$data["plugin_order_ordertaxes_id"]."'
                       WHERE `plugin_order_orders_id` = '".$data["id"]."'";
            $result = $DB->query($query) or die($DB->error());
         }
          //1.5.0
         $migration->addField($table, "entities_id", "INT( 11 ) NOT NULL DEFAULT '0'");
         $migration->addField($table, "is_recursive", "TINYINT( 1 ) NOT NULL DEFAULT '0'");
         $migration->addField($table, "plugin_order_bills_id", "INT( 11 ) NOT NULL DEFAULT '0'");
         $migration->addField($table, "plugin_order_billstates_id", "INT( 11 ) NOT NULL DEFAULT '0'");
         $migration->addKey($table, "entities_id");
         $migration->addKey($table, "plugin_order_bills_id");
         $migration->addKey($table, "plugin_order_billstates_id");
         $migration->addField($table, "comment", "text collate utf8_unicode_ci");
         $migration->migrationOneTable($table);

         //Change format for prices : from float to decimal
         $migration->changeField($table, "price_taxfree", "price_taxfree",
                                 "decimal(20,6) NOT NULL DEFAULT '0.000000'");
         $migration->changeField($table, "price_discounted", "price_discounted",
                                 "decimal(20,6) NOT NULL DEFAULT '0.000000'");
         $migration->changeField($table, "price_ati", "price_ati",
                                 "decimal(20,6) NOT NULL DEFAULT '0.000000'");
         $migration->changeField($table, "discount", "discount",
                                 "decimal(20,6) NOT NULL DEFAULT '0.000000'");

         //Drop unused fields from previous migration
         $migration->dropField($table, "price_taxfree2");
         $migration->dropField($table, "price_discounted2");
         $migration->migrationOneTable($table);

         //Forward entities_id and is_recursive into table glpi_plugin_order_orders_items
         $query = "SELECT `go`.`entities_id` as entities_id ,
                          `go`.`is_recursive` as is_recursive, `goi`.`id` as items_id
                   FROM `glpi_plugin_order_orders` as go, `$table` as `goi`
                   WHERE `goi`.`plugin_order_orders_id`=`go`.`id`";
         foreach ($DB->request($query) as $data) {
            $update = "UPDATE `$table`
                       SET `entities_id`='".$data['entities_id']."'
                          AND `is_recursive`='".$data['is_recursive']."'
                       WHERE `id`='".$data['items_id']."'";
            $DB->query($update) or die($DB->error());
         }

         $migration->executeMigration();
      }
   }


   public static function uninstall() {
      global $DB;

      //Old table name
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_order_detail`") or die ($DB->error());
      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `".self::getTable()."`") or die ($DB->error());
      self::uninstallOrderItemNotification();
   }


   public static function countForOrder(PluginOrderOrder $item) {
      return countElementsInTable('glpi_plugin_order_orders_items',
                                  "`plugin_order_orders_id` = '".$item->getID()."'");
   }


   public static function countForItem(CommonDBTM $item) {
      return countElementsInTable('glpi_plugin_order_orders_items',
                                  "`itemtype`='".$item->getType()."'
                                   AND `items_id` = '".$item->getID()."'");
   }


   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (in_array(get_class($item), PluginOrderOrder_Item::getClasses(true))) {

         $orderlink = new PluginOrderLink();
         if (!$orderlink->isItemLinkedToOrder(get_class($item), $item->getID())) {
            return '';
         }

         if ($item->getField('id') && !$withtemplate) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(__("Orders", "order"), self::countForItem($item));
            }
            return __("Orders", "order");
         }
      } else if (get_class($item) == 'PluginOrderOrder') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(__("Order item", "order"), self::countForOrder($item));
         }
         return __("Order", "order");
      }
      return '';
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if (get_class($item) == 'PluginOrderOrder') {
         if (!$item->fields['is_template']) {
            $order_item = new self();
            $order_item->showItem($item->getID());
         }
      } else if (in_array($item->getType(), PluginOrderOrder_Item::getClasses(true))) {
         $order_item = new self();
         $order_item->showPluginFromItems(get_class($item), $item->getField('id'));
      }
      return true;
   }


   public static function showForInfocom(CommonDBTM $item) {
      $order_item = new self();
      $order_item->showPluginFromItems(get_class($item), $item->getField('id'));
   }


   public static function uninstallOrderItemNotification() {
      global $DB;

      $notif   = new Notification();
      $options = [
         'itemtype' => 'PluginOrderOrder_Item',
         'event'    => 'delivered',
         'FIELDS'   => 'id'
      ];
      foreach ($DB->request('glpi_notifications', $options) as $data) {
         $notif->delete($data);
      }

      $template    = new NotificationTemplate();
      $translation = new NotificationTemplateTranslation();

      //templates
      $options = [
         'itemtype' => 'PluginOrderOrder_Item',
         'FIELDS'   => 'id'
      ];
      foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
         $options_template = [
            'notificationtemplates_id' => $data['id'],
            'FIELDS'                   => 'id'
         ];
         foreach ($DB->request('glpi_notificationtemplatetranslations',
                                  $options_template) as $data_template) {
            $translation->delete($data_template);
         }
         $template->delete($data);
      }
   }


}
