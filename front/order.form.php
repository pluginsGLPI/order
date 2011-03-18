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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if (!isset ($_GET["id"]))
	$_GET["id"] = "";
if (!isset ($_GET["withtemplate"]))
	$_GET["withtemplate"] = "";

$PluginOrderOrder = new PluginOrderOrder();
$PluginOrderConfig = new PluginOrderConfig();
$PluginOrderOrder_Item = new PluginOrderOrder_Item();
$PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier();

/* add order */
if (isset ($_POST["add"])) {
	$PluginOrderOrder->check(-1,'w',$_POST);
   $newID = $PluginOrderOrder->add($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
}
/* delete order */
else if (isset ($_POST["delete"])) {
   $PluginOrderOrder->check($_POST['id'],'w');
   $PluginOrderOrder->delete($_POST);
   glpi_header(getItemTypeSearchURL('PluginOrderOrder'));
}
/* restore order */
else if (isset ($_POST["restore"])) {
   $PluginOrderOrder->check($_POST['id'],'w');
   $PluginOrderOrder->restore($_POST);
   glpi_header(getItemTypeSearchURL('PluginOrderOrder'));
}
/* purge order */
else if (isset ($_POST["purge"])) {
   $PluginOrderOrder->check($_POST['id'],'w');
   $PluginOrderOrder->delete($_POST, 1);
   glpi_header(getItemTypeSearchURL('PluginOrderOrder'));
}
/* update order */
else if (isset ($_POST["update"])) {
   $PluginOrderOrder->check($_POST['id'],'w');
   $PluginOrderOrder->update($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);
} 
//Status update & order workflow
/* validate order */
else if (isset ($_POST["validate"])) {
   
   $config = $PluginOrderConfig->getConfig();
		
   if ($PluginOrderOrder->canCreate() && ( $PluginOrderOrder->canValidate() || !$config["use_validation"]))
   {
      $PluginOrderOrder->updateOrderStatus($_POST["id"],PluginOrderOrder::ORDER_STATUS_APPROVED,$_POST["comment"]);
      $PluginOrderOrder_Item->updateDelivryStatus($_POST["id"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][10]);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset ($_POST["waiting_for_approval"])) {
   if ($PluginOrderOrder->canCreate())
   {
      $PluginOrderOrder->updateOrderStatus($_POST["id"],PluginOrderOrder::ORDER_STATUS_WAITING_APPROVAL,$_POST["comment"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][7]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset ($_POST["cancel_waiting_for_approval"])) {
   if ($PluginOrderOrder->canCreate() && $PluginOrderOrder->canCancel())
   {
      $PluginOrderOrder->updateOrderStatus($_POST["id"],PluginOrderOrder::ORDER_STATUS_DRAFT,$_POST["comment"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][14]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset ($_POST["cancel_order"])) {
   if ($PluginOrderOrder->canCreate() && $PluginOrderOrder->canCancel())
   {
      $PluginOrderOrder->updateOrderStatus($_POST["id"],PluginOrderOrder::ORDER_STATUS_CANCELED,$_POST["comment"]);
      $PluginOrderOrder->deleteAllLinkWithItem($_POST["id"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][5]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset ($_POST["undovalidation"])) {
   if ($PluginOrderOrder->canCreate() && $PluginOrderOrder->canUndo())
   {
      $PluginOrderOrder->updateOrderStatus($_POST["id"],PluginOrderOrder::ORDER_STATUS_DRAFT,$_POST["comment"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][8]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
}
//Details management
else if (isset ($_POST["add_item"])) {
   if ($_POST["discount"] < 0 || $_POST["discount"] > 100)
      addMessageAfterRedirect($LANG['plugin_order']['detail'][33],false,ERROR);
   else
   {
      $PluginOrderOrder->getFromDB($_POST["plugin_order_orders_id"]);
      $taxes = $PluginOrderOrder->fields["plugin_order_ordertaxes_id"];
      $new_value = $LANG['plugin_order']['detail'][34]." ".Dropdown::getDropdownName("glpi_plugin_order_references",$_POST["plugin_order_references_id"]);
      $new_value.= " (".$LANG['plugin_order']['detail'][7]." : ".$_POST["quantity"];
      $new_value.= " ".$LANG['plugin_order']['detail'][25]." : ".$_POST["discount"].")";
      $PluginOrderOrder->addHistory("PluginOrderOrder","",$new_value,$_POST["plugin_order_orders_id"]);
      $PluginOrderOrder_Item->addDetails($_POST["plugin_order_references_id"], $_POST["itemtype"], $_POST["plugin_order_orders_id"], $_POST["quantity"], $_POST["price"], $_POST["discount"], $taxes);
   }
      
   glpi_header($_SERVER['HTTP_REFERER']);
} 
else if (isset ($_POST["delete_item"])) {
   
   if (isset($_POST["plugin_order_orders_id"]) && $_POST["plugin_order_orders_id"] > 0 && isset($_POST["item"]))
   {
      foreach ($_POST["item"] as $ID => $val)
         if ($val==1)
         {
            $PluginOrderOrder_Item->getFromDB($ID);
            
            if ($PluginOrderOrder_Item->fields["itemtype"] == 'SoftwareLicense') {
               $result=$PluginOrderOrder_Item->queryRef($_POST["plugin_order_orders_id"],$PluginOrderOrder_Item->fields["plugin_order_references_id"],$PluginOrderOrder_Item->fields["price_taxfree"],$PluginOrderOrder_Item->fields["discount"]);
               $nb = $DB->numrows($result);

               if ($nb) {
                  for ($i = 0; $i < $nb; $i++) {
                     $ID = $DB->result($result, $i, 'id');
                     $items_id = $DB->result($result, $i, 'items_id');
                     
                     if ($items_id) {
                        $lic = new SoftwareLicense;
                        $lic->getFromDB($items_id);
                        $values["id"] = $lic->fields["id"];
                        $values["number"] = $lic->fields["number"]-1;
                        $lic->update($values);
                     }
                     $input["id"] = $ID;
                     
                     $PluginOrderOrder_Item->delete(array('id'=>$input["id"]));
                  }
                  $new_value = $LANG['plugin_order']['detail'][35]." ".Dropdown::getDropdownName("glpi_plugin_order_references",$ID);
                  $PluginOrderOrder->addHistory("PluginOrderOrder","",$new_value,$_POST["plugin_order_orders_id"]);
               }
            } else {
            
               $new_value = $LANG['plugin_order']['detail'][35]." ".Dropdown::getDropdownName("glpi_plugin_order_references",$ID);
               $PluginOrderOrder->addHistory("PluginOrderOrder","",$new_value,$_POST["plugin_order_orders_id"]);
               $PluginOrderOrder_Item->delete(array('id'=>$ID));
            }
         }
   } else if (!isset($_POST["item"]))
      addMessageAfterRedirect($LANG['plugin_order']['detail'][29],false,ERROR);
      
   glpi_header($_SERVER['HTTP_REFERER']);
}
else 
{
	$PluginOrderOrder->checkGlobal("r");

	commonHeader($LANG['plugin_order']['title'][1], '', "plugins", "order", "order");
	
	$PluginOrderOrder->showForm($_GET["id"]);
	
	commonFooter();
}

?>