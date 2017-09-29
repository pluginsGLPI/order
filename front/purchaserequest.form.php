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

$purchase = new PluginOrderPurchaseRequest();

if (isset($_POST["add"])) {
   $purchase->check(-1, CREATE, $_POST);
   $newID = $purchase->add($_POST);
   $url   = Toolbox::getItemTypeFormURL('PluginOrderPurchaseRequest') . "?id=$newID";
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($purchase->getFormURL() . "?id=" . $newID);
   } else {
      Html::redirect($url);
   }

} else if (isset($_POST["add_tickets"])) {
   $purchase->check(-1, CREATE, $_POST);
   $newID = $purchase->add($_POST);
   Html::back();

   /* purge purchaserequest */
} else if (isset($_POST["purge"])) {
   $purchase->check($_POST['id'], PURGE);
   $purchase->delete($_POST, 1);
   $purchase->redirectToList();

   /* update order */
} else if (isset($_POST["update"]) || (isset($_POST['update_status']))) {
   $purchase->check($_POST['id'], UPDATE);
   $purchase->update($_POST);
   Html::back();
}

if (isset($_POST['action'])) {
   // Retrieve configuration for generate assets feature

   $purchase_request = new PluginOrderPurchaseRequest();
   switch ($_POST['chooseAction']) {
      case 'delete_link':
         if (isset ($_POST["item"])) {
            foreach ($_POST["item"] as $key => $val) {
               if ($val == 1) {
                  $tmp['id'] = $key;
                  $tmp['plugin_order_orders_id'] = 0;
                  $purchase_request->update($tmp);

               }
            }
         }
         break;
   }
   Html::back();
}

Html::header(PluginOrderPurchaseRequest::getTypeName(1),
             $_SERVER['PHP_SELF'],
             "management",
             "PluginOrderMenu",
             "purchaserequest"
);

$purchase->display($_GET);

Html::footer();
