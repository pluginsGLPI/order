<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

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
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: 
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

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
               $order_item->update($input);
            }
         }
      } else {
         $order_item->update($_POST);
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);
   
} else if (isset ($_POST["delete"])) {

   $reception->deleteDelivery($_POST["id"]);
   glpi_header(getItemTypeFormURL('PluginOrderOrder')."?id=".$_POST["plugin_order_orders_id"]);
   
} else if (isset ($_POST["reception"])) {
   //A new item is delivered
   $reception->updateReceptionStatus($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);
   
} else if (isset ($_POST["bulk_reception"])) {
   //Several new items are delivered
   $reception->updateBulkReceptionStatus($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);

} else {
   
   commonHeader($LANG['plugin_order']['title'][1], '', "plugins", "order", "order");
   $reception->showForm($_GET["id"]);
   commonFooter();
   
}

?>