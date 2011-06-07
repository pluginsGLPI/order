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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if(!isset($_GET["id"])) {
   $_GET["id"] = "";
} 
if(!isset($_GET["withtemplate"])) { 
   $_GET["withtemplate"] = "";
}

$PluginOrderReception = new PluginOrderReception();
$PluginOrderOrder_Item = new PluginOrderOrder_Item();

if (isset ($_POST["update"])) {

   if ($PluginOrderReception->canCreate()) {
      $PluginOrderOrder_Item->getFromDB($_POST["id"]);
      if ($PluginOrderOrder_Item->fields["itemtype"] == 'SoftwareLicense') {
         $result=
            $PluginOrderOrder_Item->queryRef($PluginOrderOrder_Item->fields["plugin_order_orders_id"],
                                             $PluginOrderOrder_Item->fields["plugin_order_references_id"],
                                             $PluginOrderOrder_Item->fields["price_taxfree"],
                                             $PluginOrderOrder_Item->fields["discount"],
                                             PluginOrderOrder::ORDER_DEVICE_DELIVRED);
         $nb = $DB->numrows($result);

         if ($nb) {
            for ($i = 0; $i < $nb; $i++) {
               $ID = $DB->result($result, $i, 'id');
               $input["id"] = $ID;
               $input["delivery_date"] = $_POST["delivery_date"];
               $input["delivery_number"] = $_POST["delivery_number"];
               $input["plugin_order_deliverystates_id"] = $_POST["plugin_order_deliverystates_id"];
               $input["delivery_comment"] = $_POST["delivery_comment"];
               $PluginOrderOrder_Item->update($input);
            }
         }
      } else {
         $PluginOrderOrder_Item->update($_POST);
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);
   
} else if (isset ($_POST["delete"])) {

   $PluginOrderReception->deleteDelivery($_POST["id"]);
   glpi_header($CFG_GLPI["root_doc"] . 
      "/plugins/order/front/order.form.php?id=".$_POST["plugin_order_orders_id"]);
   
} else if (isset ($_POST["reception"])) {

/* reception d'une ligne detail */
   $PluginOrderReception->updateReceptionStatus($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);
   
} else if (isset ($_POST["bulk_reception"])) {

   $PluginOrderReception->updateBulkReceptionStatus($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);

} else {
   
   commonHeader($LANG['plugin_order']['title'][1],'',"plugins","order","order");
   $PluginOrderReception->showForm($_GET["id"]);
   commonFooter();
   
}

?>