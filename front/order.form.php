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

include ("../../../inc/includes.php");

if (!isset ($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset ($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$pluginOrderOrder          = new PluginOrderOrder();
$config                    = new PluginOrderConfig();
$pluginOrderOrder_Item     = new PluginOrderOrder_Item();
$pluginOrderOrder_Supplier = new PluginOrderOrder_Supplier();

/* add order */
if (isset ($_POST["add"])) {
   $pluginOrderOrder->check(-1, CREATE, $_POST);

    if(isset($_POST['payment_locations_id']) && $_POST['payment_locations_id'] <= 0){
      Session::addMessageAfterRedirect(__("Please select an invoice location", "order"),false,ERROR);
      Html::back();
    }


   /* FORCE Status */
   $_POST['plugin_order_orderstates_id'] = $config->getDraftState();

   $newID = $pluginOrderOrder->add($_POST);

   $url = Toolbox::getItemTypeFormURL('PluginOrderOrder')."?id=$newID";
   if (isset($_REQUEST['is_template'])) {
      $url .= "&withtemplate=1";
   }
   Html::redirect($url);

/* delete order */
} elseif (isset($_POST["delete"])) {
   $pluginOrderOrder->check($_POST['id'], DELETE);
   $pluginOrderOrder->delete($_POST);
   $pluginOrderOrder->redirectToList();

/* restore order */
} elseif (isset($_POST["restore"])) {
   $pluginOrderOrder->check($_POST['id'], CREATE);
   $pluginOrderOrder->restore($_POST);
   $pluginOrderOrder->redirectToList();

/* purge order */
} elseif (isset($_REQUEST["purge"])) {
   if (isset($_POST['id'])) {
      $id = $_POST['id'];
   } else {
      $id = $_GET['id'];
   }
   $pluginOrderOrder->check($id, DELETE);
   $pluginOrderOrder->delete(array('id' => $id), 1);
   $pluginOrderOrder->redirectToList();

/* update order */
} elseif (isset($_POST["update"])) {

   if(isset($_POST['payment_locations_id']) && $_POST['payment_locations_id'] <= 0){
      Session::addMessageAfterRedirect(__("Please select an invoice location", "order"),false,ERROR);
      Html::back();
   }

   $pluginOrderOrder->check($_POST['id'], UPDATE);
   $pluginOrderOrder->update($_POST);
   Html::back();

//Status update & order workflow
/* validate order */
} elseif (isset($_POST["validate"])) {
   if (PluginOrderOrder::canView() && (PluginOrderOrder::canValidate() || !$config->useValidation())) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"], $config->getApprovedState(), $_POST["comment"]);
      PluginOrderReception::updateDelivryStatus($_POST["id"]);
      Session::addMessageAfterRedirect(__("Order is validated", "order"));
   }
   Html::back();

} elseif (isset($_POST["waiting_for_approval"])) {
   if (pluginOrderOrder::canCreate()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config->getWaitingForApprovalState(),
                                           $_POST["comment"]);
      Session::addMessageAfterRedirect(__("Order validation successfully requested", "order"));

   }
   Html::back();

} elseif (isset($_POST["cancel_waiting_for_approval"])) {
   if (pluginOrderOrder::canView() && pluginOrderOrder::canCancel()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config->getDraftState(),
                                           $_POST["comment"]);
      Session::addMessageAfterRedirect(__("Validation query is now canceled", "order"));
   }

   Html::back();
} elseif (isset($_POST["cancel_order"])) {
   if (pluginOrderOrder::canView() && pluginOrderOrder::canCancel()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config->getCanceledState(),
                                           $_POST["comment"]);
      $pluginOrderOrder->deleteAllLinkWithItem($_POST["id"]);
      Session::addMessageAfterRedirect(__("Order canceled", "order"));

   }

   Html::back();

} elseif (isset($_POST["undovalidation"])) {
   if (pluginOrderOrder::canView() && pluginOrderOrder::canUndo()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config->getDraftState(),
                                           $_POST["comment"]);
      Session::addMessageAfterRedirect(__("Order currently edited", "order"));

   }

   Html::back();

} elseif (isset($_POST["add_item"])) {
   //Details management
   if ($_POST["discount"] < 0 || $_POST["discount"] > 100) {
      Session::addMessageAfterRedirect(__("The discount pourcentage must be between 0 and 100", "order"), false, ERROR);

   } else {
      $pluginOrderOrder->getFromDB($_POST["plugin_order_orders_id"]);
      $new_value  = __("Add reference", "order")." ";
      $new_value .= Dropdown::getDropdownName("glpi_plugin_order_references",
                                             $_POST["plugin_order_references_id"]);
      $new_value .= " (" . __("Quantity", "order") . " : " . $_POST["quantity"];
      $new_value .= " " . __("Discount (%)", "order") . " : " . $_POST["discount"] . ")";
      $pluginOrderOrder->addHistory("PluginOrderOrder", "",
                                    $new_value,$_POST["plugin_order_orders_id"]);
      $pluginOrderOrder_Item->addDetails($_POST["plugin_order_references_id"], $_POST["itemtype"],
                                         $_POST["plugin_order_orders_id"], $_POST["quantity"],
                                         $_POST["price"], $_POST["discount"],
                                         $_POST["plugin_order_ordertaxes_id"]);
   }
   Html::back();

} elseif (isset($_POST["delete_item"])) {
   if (isset($_POST["plugin_order_orders_id"]) 
       && ($_POST["plugin_order_orders_id"] > 0) 
       && isset($_POST["item"])) {
      
      foreach ($_POST["item"] as $ID => $val) {
         if ($val == 1) {
            $pluginOrderOrder_Item->getFromDB($ID);

            if ($pluginOrderOrder_Item->fields["itemtype"] == 'SoftwareLicense') {
               $result=$pluginOrderOrder_Item->queryRef($_POST["plugin_order_orders_id"],
                                                        $pluginOrderOrder_Item->fields["plugin_order_references_id"],
                                                        $pluginOrderOrder_Item->fields["price_taxfree"],
                                                        $pluginOrderOrder_Item->fields["discount"]);

               if ($nb = $DB->numrows($result)) {
                  for ($i = 0; $i < $nb; $i++) {
                     $ID       = $DB->result($result, $i, 'id');
                     $items_id = $DB->result($result, $i, 'items_id');

                     if ($items_id) {
                        $lic = new SoftwareLicense;
                        $lic->getFromDB($items_id);
                        $values["id"]     = $lic->fields["id"];
                        $values["number"] = $lic->fields["number"]-1;
                        $lic->update($values);

                     }
                     $input["id"] = $ID;

                     $pluginOrderOrder_Item->delete(array('id'=>$input["id"]));
                  }
                  $new_value  = __("Remove reference", "order") . " ";
                  $new_value .= Dropdown::getDropdownName("glpi_plugin_order_references", $ID);
                  $pluginOrderOrder->addHistory("PluginOrderOrder", "", $new_value,
                                                $_POST["plugin_order_orders_id"]);
               }
            } else {
               $new_value  = __("Remove reference", "order") . " ";
               $new_value .= Dropdown::getDropdownName("glpi_plugin_order_references", $ID);
               $pluginOrderOrder->addHistory("PluginOrderOrder", "",
                                                $new_value, $_POST["plugin_order_orders_id"]);
               $pluginOrderOrder_Item->delete(array('id' => $ID));
            }
         }
      }
   } elseif (!isset($_POST["item"])) {
      Session::addMessageAfterRedirect(__("No item selected", "order"), false, ERROR);
   }

   Html::back();

} elseif (isset($_POST["update_item"])) {
   if(isset($_POST['quantity'])) {
      $pluginOrderOrder_Item->updateQuantity($_POST);
   }

   if(isset($_POST['price_taxfree'])) {
      $datas = $pluginOrderOrder_Item->queryRef( $_POST['plugin_order_orders_id'],
                                                 $_POST['old_plugin_order_references_id'],
                                                 $_POST['old_price_taxfree'],
                                                 $_POST['old_discount']);
      while ($item=$DB->fetch_array($datas)){
         $input = array(
            'item_id'       => $item['id'],
            'price_taxfree' => $_POST['price_taxfree'],
         );
         $pluginOrderOrder_Item->updatePrice_taxfree($input);
      }
   }

   if(isset($_POST['discount'])) {
      if ($_POST["discount"] < 0 || $_POST["discount"] > 100) {
         Session::addMessageAfterRedirect(__("The discount pourcentage must be between 0 and 100", "order"), false, ERROR);
      } else {

         $price = (isset($_POST['price_taxfree'])) ? $_POST['price_taxfree'] : $_POST['old_price_taxfree'];

         $datas = $pluginOrderOrder_Item->queryRef( $_POST['plugin_order_orders_id'],
                                                    $_POST['old_plugin_order_references_id'],
                                                    $price,
                                                    $_POST['old_discount']);
         while ($item=$DB->fetch_array($datas)){
            $input = array( 'item_id'  => $item['id'],
                            'discount' => $_POST['discount'],
                            'price'    => $price);
            $pluginOrderOrder_Item->updateDiscount($input);
         }
      }
   }

   Html::back();

} elseif (isset($_POST["update_detail_item"])) {
   if(isset($_POST['detail_price_taxfree'])) {
      foreach($_POST['detail_price_taxfree'] as $item_id => $price) {
         $input = array(
            'item_id'       => $item_id,
            'price_taxfree' => $price,
         );
         $pluginOrderOrder_Item->updatePrice_taxfree($input);
      }
   }

   if(isset($_POST['detail_discount'])) {
      foreach($_POST['detail_discount'] as $item_id => $discount) {

         $price = (isset($_POST['detail_price_taxfree']))
                     ? $_POST['detail_price_taxfree'][$item_id]
                     : $_POST['detail_old_price_taxfree'][$item_id];

         $input = array( 'item_id'  => $item_id,
                         'discount' => $discount,
                         'price'    => $price);
         $pluginOrderOrder_Item->updateDiscount($input);
      }
   }

   Html::back();
} elseif (isset($_GET['unlink_order'])) {
   $pluginOrderOrder->check($_GET['id'], UPDATE);
   $pluginOrderOrder->unlinkBudget($_GET['id']);
   Html::back();

} else {
   $pluginOrderOrder->checkGlobal(READ);

   Html::header(
      __("Orders", "order"),
      $_SERVER['PHP_SELF'],
      "management",
      "PluginOrderMenu",
      "order"
   );

   if ($_GET['id'] == "") {
      $pluginOrderOrder->showForm(-1, array('withtemplate' => $_GET["withtemplate"]));
   } else {
      $pluginOrderOrder->display($_GET);
   }
   Html::footer();
}
