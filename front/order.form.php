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

if (!isset ($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset ($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$pluginOrderOrder          = new PluginOrderOrder();
$pluginOrderConfig         = new PluginOrderConfig();
$pluginOrderOrder_Item     = new PluginOrderOrder_Item();
$pluginOrderOrder_Supplier = new PluginOrderOrder_Supplier();

$config = $pluginOrderConfig->getConfig();
   
/* add order */
if (isset ($_POST["add"])) {
   $pluginOrderOrder->check(-1,'w',$_POST);

   /* FORCE Status */
   $_POST['plugin_order_orderstates_id'] = $config['order_status_draft'];

   $newID = $pluginOrderOrder->add($_POST);
   glpi_header($_SERVER['HTTP_REFERER']."?id=$newID");
}
/* delete order */
else if (isset ($_POST["delete"])) {
   $pluginOrderOrder->check($_POST['id'], 'w');
   $pluginOrderOrder->delete($_POST);
   $pluginOrderOrder->redirectToList();
}
/* restore order */
else if (isset ($_POST["restore"])) {
   $pluginOrderOrder->check($_POST['id'], 'w');
   $pluginOrderOrder->restore($_POST);
   $pluginOrderOrder->redirectToList();
}
/* purge order */
else if (isset ($_POST["purge"])) {
   $pluginOrderOrder->check($_POST['id'], 'w');
   $pluginOrderOrder->delete($_POST, 1);
   $pluginOrderOrder->redirectToList();
}
/* update order */
else if (isset ($_POST["update"])) {
   $pluginOrderOrder->check($_POST['id'], 'w');
   $pluginOrderOrder->update($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);
} 
//Status update & order workflow
/* validate order */
else if (isset ($_POST["validate"])) {
   if ($pluginOrderOrder->canCreate() 
      && ( $pluginOrderOrder->canValidate() 
         || !$config["use_validation"])) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"], 
                                           $config['order_status_approved'],
                                           $_POST["comment"]);
      $pluginOrderOrder_Item->updateDelivryStatus($_POST["id"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][10]);
      
   }
   glpi_header($_SERVER['HTTP_REFERER']);
   
} else if (isset ($_POST["waiting_for_approval"])) {
   if ($pluginOrderOrder->canCreate()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config['order_status_waiting_approval'],
                                           $_POST["comment"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][7]);
      
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
   
} else if (isset ($_POST["cancel_waiting_for_approval"])) {
   if ($pluginOrderOrder->canCreate() && $pluginOrderOrder->canCancel()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config['order_status_draft'],
                                           $_POST["comment"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][14]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
} else if (isset ($_POST["cancel_order"])) {
   if ($pluginOrderOrder->canCreate() && $pluginOrderOrder->canCancel()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config['order_status_canceled'],
                                           $_POST["comment"]);
      $pluginOrderOrder->deleteAllLinkWithItem($_POST["id"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][5]);
      
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
   
}
else if (isset ($_POST["undovalidation"])) {
   if ($pluginOrderOrder->canCreate() && $pluginOrderOrder->canUndo()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config['order_status_draft'],
                                           $_POST["comment"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][8]);
      
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
   
} else if (isset ($_POST["add_item"])) {
   //Details management
   if ($_POST["discount"] < 0 || $_POST["discount"] > 100) {
      addMessageAfterRedirect($LANG['plugin_order']['detail'][33], false, ERROR);
      
   } else {
      $pluginOrderOrder->getFromDB($_POST["plugin_order_orders_id"]);
      $new_value = $LANG['plugin_order']['detail'][34]." ";
      $new_value.= Dropdown::getDropdownName("glpi_plugin_order_references",
                                             $_POST["plugin_order_references_id"]);
      $new_value.= " (".$LANG['plugin_order']['detail'][7]." : ".$_POST["quantity"];
      $new_value.= " ".$LANG['plugin_order']['detail'][25]." : ".$_POST["discount"].")";
      $pluginOrderOrder->addHistory("PluginOrderOrder", "", 
                                    $new_value,$_POST["plugin_order_orders_id"]);
      $pluginOrderOrder_Item->addDetails($_POST["plugin_order_references_id"], $_POST["itemtype"], 
                                         $_POST["plugin_order_orders_id"], $_POST["quantity"], 
                                         $_POST["price"], $_POST["discount"], 
                                         $_POST["plugin_order_ordertaxes_id"]);
                                         
   }
      
   glpi_header($_SERVER['HTTP_REFERER']);
   
} else if (isset ($_POST["delete_item"])) {
   if (isset($_POST["plugin_order_orders_id"]) 
         && $_POST["plugin_order_orders_id"] > 0 
            && isset($_POST["item"])) {
      foreach ($_POST["item"] as $ID => $val) {
         if ($val==1) {
            $pluginOrderOrder_Item->getFromDB($ID);
            
            if ($pluginOrderOrder_Item->fields["itemtype"] == 'SoftwareLicense') {
               $result=$pluginOrderOrder_Item->queryRef($_POST["plugin_order_orders_id"],
                                                        $pluginOrderOrder_Item->fields["plugin_order_references_id"],
                                                        $pluginOrderOrder_Item->fields["price_taxfree"],
                                                        $pluginOrderOrder_Item->fields["discount"]);
               $nb = $DB->numrows($result);

               if ($nb) {
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
                  $new_value = $LANG['plugin_order']['detail'][35]." ";
                  $new_value.= Dropdown::getDropdownName("glpi_plugin_order_references", $ID);
                  $pluginOrderOrder->addHistory("PluginOrderOrder", "", $new_value, 
                                                $_POST["plugin_order_orders_id"]);
                                                
               } 
            } else {
            
               $new_value = $LANG['plugin_order']['detail'][35]." ";
               $new_value.= Dropdown::getDropdownName("glpi_plugin_order_references", $ID);
               $pluginOrderOrder->addHistory("PluginOrderOrder", "", 
                                                $new_value, $_POST["plugin_order_orders_id"]);
               $pluginOrderOrder_Item->delete(array('id' => $ID));
            }
         }
      }
   } else if (!isset($_POST["item"])) {
      addMessageAfterRedirect($LANG['plugin_order']['detail'][29], false, ERROR);

   }
      
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset ($_POST["update_item"])) {

   $datas = $pluginOrderOrder_Item->queryRef($_POST['plugin_order_orders_id'], 
                                             $_POST['old_plugin_order_references_id'], 
                                             $_POST['old_price_taxfree'], 
                                             $_POST['old_discount']);

   // Update quantity
   if (isset($_POST['quantity']) && $_POST['quantity'] > $quantity) {
      
      $item    = $DB->fetch_array($datas);
      $pluginOrderOrder_Item = new PluginOrderOrder_Item();
      $pluginOrderOrder_Item->getFromDB($item['id']);

      $quantity = $pluginOrderOrder_Item->getTotalQuantityByRefAndDiscount($_POST['plugin_order_orders_id'], 
                                                            $_POST['old_plugin_order_references_id'],
                                                            $_POST['old_price_taxfree'],
                                                            $_POST['old_discount']);
      $to_add  = $_POST['quantity'] - $quantity;
      
      $pluginOrderOrder_Item->addDetails( $pluginOrderOrder_Item->fields['plugin_order_references_id'], 
                                          $pluginOrderOrder_Item->fields['itemtype'], 
                                          $pluginOrderOrder_Item->fields['plugin_order_orders_id'], 
                                          $to_add, 
                                          $pluginOrderOrder_Item->fields['price_taxfree'], 
                                          $pluginOrderOrder_Item->fields['discount'], 
                                          $pluginOrderOrder_Item->fields['plugin_order_ordertaxes_id']);
   }
   
   // Update price
   if (isset($_POST['price_taxfree'])) {
      while ($item=$DB->fetch_array($datas)){
         $pluginOrderOrder_Item = new PluginOrderOrder_Item();
         $pluginOrderOrder_Item->getFromDB($item['id']);

         $input                        = $pluginOrderOrder_Item->fields;
         $discount                     = $input['discount'];
         $plugin_order_ordertaxes_id   = $input['plugin_order_ordertaxes_id'];

         $input["price_taxfree"]       = $_POST['price_taxfree'];
         $input["price_discounted"]    = $input["price_taxfree"] - ($input["price_taxfree"] * ($discount / 100));
         
         $taxe_name = Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", $plugin_order_ordertaxes_id);
         $input["price_ati"]  = $pluginOrderOrder_Item->getPricesATI($input["price_discounted"], $taxe_name);
         $pluginOrderOrder_Item->update($input);
      }
   }
   
   // Update discount
   if (isset($_POST['discount'])) {
      if ($_POST["discount"] < 0 || $_POST["discount"] > 100) {
         addMessageAfterRedirect($LANG['plugin_order']['detail'][33], false, ERROR);
      } else {
         while ($item=$DB->fetch_array($datas)){
            $pluginOrderOrder_Item = new PluginOrderOrder_Item();
            $pluginOrderOrder_Item->getFromDB($item['id']);

            $input                        = $pluginOrderOrder_Item->fields;
            $price                        = $input['price_taxfree'];
            $plugin_order_ordertaxes_id   = $input['plugin_order_ordertaxes_id'];

            $input["discount"]            = $_POST['discount'];
            $input["price_discounted"]    = $price - ($price * ($_POST['discount'] / 100));

            $taxe_name = Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", $plugin_order_ordertaxes_id);
            $input["price_ati"]  = $pluginOrderOrder_Item->getPricesATI($input["price_discounted"], $taxe_name);
            $pluginOrderOrder_Item->update($input);
         }
      }
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
} else {
   $pluginOrderOrder->checkGlobal("r");
   commonHeader($LANG['plugin_order']['title'][1], '', "plugins", "order", "order");
   $pluginOrderOrder->showForm($_GET["id"]);
   commonFooter();
}

?>