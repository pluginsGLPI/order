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

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$link       = new PluginOrderLink();
$order_item = new PluginOrderOrder_Item();

if (isset($_POST["generation"])) {
   if (isset ($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($val == 1) {
            $order_item->getFromDB($_POST["id"][$key]);
            if ($order_item->fields["states_id"] == PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED) {
               Session::addMessageAfterRedirect(__("Cannot generate items not delivered", "order"), true, ERROR);
               Html::redirect($_SERVER["HTTP_REFERER"]);
            }
         }
      }
   }

   if (isset ($_POST["item"])) {
      Html::header(__("Orders", "order"), $_SERVER['PHP_SELF'], "management", "PluginOrderMenu",
                   "order"
      );

      $link->showItemGenerationForm($_POST);
      Html::footer();
   } else {
      Session::addMessageAfterRedirect(__("No item selected", "order"), false, ERROR);
      Html::redirect($_SERVER["HTTP_REFERER"]);
   }

} else if (isset($_POST["generate"])) {
   $link->generateNewItem($_POST);
   Html::redirect(Toolbox::getItemTypeFormURL('PluginOrderOrder') . "?id=" . $_POST["plugin_order_orders_id"] . "");

} else if (isset($_POST["deleteLinkWithItem"])) {
   foreach ($_POST["item"] as $key => $val) {
      if ($val == 1) {
         $link->deleteLinkWithItem($key, $_POST["itemtype"][$key], $_POST["plugin_order_orders_id"]);
      }
   }
   Html::redirect(Toolbox::getItemTypeFormURL('PluginOrderOrder') . "?id=" . $_POST["plugin_order_orders_id"] . "");

} else if (isset($_POST["createLinkWithItem"])) {
   if (isset($_POST["item"]) && $_POST['item']) {
      $i    = 0;
      $doit = 1;
      if (!in_array($_POST["itemtype"], array('SoftwareLicense', 'ConsumableItem', 'CartridgeItem'))
         && count($_POST["item"]) > 1) {
         $doit = 0;
      }

      if ($doit) {
         foreach ($_POST["item"] as $key => $val) {
            if ($val == 1) {
               $order_item->getFromDB($_POST["id"][$key]);
               if ($order_item->fields["states_id"] == PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED) {
                  Session::addMessageAfterRedirect(__("Cannot link items not delivered", "order"), true, ERROR);
                  Html::redirect($_SERVER["HTTP_REFERER"]);
               } else {
                  $link->createLinkWithItem($key, $_POST["items_id"],
                                            $_POST["itemtype"],
                                            $_POST["plugin_order_orders_id"]);
               }
            }
         }
      } else {
         Session::addMessageAfterRedirect(__("Cannot link several items to one detail line", "order"), true, ERROR);
      }
   }
   Html::redirect(Toolbox::getItemTypeFormURL('PluginOrderOrder') . "?id=" . $_POST["plugin_order_orders_id"] . "");
}
