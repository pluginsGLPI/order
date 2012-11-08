<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if(!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if(!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$reception  = new PluginOrderReception();
$order_item = new PluginOrderOrder_Item();

if (isset ($_POST["update"])) {

   if ($reception->canCreate()) {
      $order_item->getFromDB($_POST["id"]);
      if ($order_item->fields["itemtype"] == 'SoftwareLicense') {
         $result=
            $order_item->queryRef($order_item->fields["plugin_order_orders_id"],
                                             $order_item->fields["plugin_order_references_id"],
                                             $order_item->fields["price_taxfree"],
                                             $order_item->fields["discount"],
                                             PluginOrderOrder::ORDER_DEVICE_DELIVRED);
         $nb = $DB->numrows($result);

         if ($nb) {
            for ($i = 0; $i < $nb; $i++) {
               $ID = $DB->result($result, $i, 'id');
               $input["id"]                             = $ID;
               $input["delivery_date"]                  = $_POST["delivery_date"];
               $input["delivery_number"]                = $_POST["delivery_number"];
               $input["plugin_order_deliverystates_id"] = $_POST["plugin_order_deliverystates_id"];
               $input["delivery_comment"]               = $_POST["delivery_comment"];
               $reception->update($input);
            }
         }
      } else {
         $reception->update($_POST);
      }
   }
   $reception->updateReceptionStatus(array('item' => array($_POST['id'] => 'on')));
   Html::redirect($_SERVER['HTTP_REFERER']);
   
} elseif (isset ($_POST["delete"])) {
   $reception->deleteDelivery($_POST["id"]);
   $reception->updateReceptionStatus(array('item' => array($_POST['id'] => 'on')));
   Html::redirect(Toolbox::getItemTypeFormURL('PluginOrderOrder')."?id=".$_POST["plugin_order_orders_id"]);
   
} elseif (isset ($_POST["reception"])) {
   //A new item is delivered
   $reception->updateReceptionStatus($_POST);
   Html::redirect($_SERVER["HTTP_REFERER"]);
   
} elseif (isset ($_POST["bulk_reception"])) {
   //Several new items are delivered
   $reception->updateBulkReceptionStatus($_POST);
   Html::redirect($_SERVER["HTTP_REFERER"]);

} else {
   Html::header($LANG['plugin_order']['title'][1], '', "plugins", "order", "order");
   $reception->showForm($_GET["id"]);
   Html::footer();
   
}

?>