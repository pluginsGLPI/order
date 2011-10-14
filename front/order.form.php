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
   $pluginOrderOrder->check(-1,'w',$_POST);

   /* FORCE Status */
   $_POST['plugin_order_orderstates_id'] = $config->getDraftState();

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
         || !$config->useValidation())) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"], 
                                           $config->getApprovedState(),
                                           $_POST["comment"]);
      $pluginOrderOrder_Item->updateDelivryStatus($_POST["id"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][10]);
      
   }
   glpi_header($_SERVER['HTTP_REFERER']);
   
} else if (isset ($_POST["waiting_for_approval"])) {
   if ($pluginOrderOrder->canCreate()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config->getWaitingForApprovalState(),
                                           $_POST["comment"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][7]);
      
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
   
} else if (isset ($_POST["cancel_waiting_for_approval"])) {
   if ($pluginOrderOrder->canCreate() && $pluginOrderOrder->canCancel()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config->getDraftState(),
                                           $_POST["comment"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][14]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
} else if (isset ($_POST["cancel_order"])) {
   if ($pluginOrderOrder->canCreate() && $pluginOrderOrder->canCancel()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config->getCanceledState(),
                                           $_POST["comment"]);
      $pluginOrderOrder->deleteAllLinkWithItem($_POST["id"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][5]);
      
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
   
}
else if (isset ($_POST["undovalidation"])) {
   if ($pluginOrderOrder->canCreate() && $pluginOrderOrder->canUndo()) {
      $pluginOrderOrder->updateOrderStatus($_POST["id"],
                                           $config->getDraftState(),
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
   if(isset($_POST['quantity'])) {
      $pluginOrderOrder_Item->updateQuantity($_POST);
   }

   if(isset($_POST['price_taxfree'])) {
      $datas = $pluginOrderOrder_Item->queryRef( $_POST['plugin_order_orders_id'], 
                                                 $_POST['old_plugin_order_references_id'], 
                                                 $_POST['old_price_taxfree'], 
                                                 $_POST['old_discount']);
      while ($item=$DB->fetch_array($datas)){
         $input = array( 'item_id'        => $item['id'],
                         'price_taxfree'  => $_POST['price_taxfree']);
         $pluginOrderOrder_Item->updatePrice_taxfree($input);
      }
   }

   if(isset($_POST['discount'])) {
      if ($_POST["discount"] < 0 || $_POST["discount"] > 100) {
         addMessageAfterRedirect($LANG['plugin_order']['detail'][33], false, ERROR);
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

   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset ($_POST["update_detail_item"])) {

   if(isset($_POST['detail_price_taxfree'])) {
      foreach($_POST['detail_price_taxfree'] as $item_id => $price) {
         $input = array( 'item_id'        => $item_id,
                         'price_taxfree'  => $price);
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
   
   glpi_header($_SERVER['HTTP_REFERER']);
} else if (isset ($_GET['unlink_order'])) {
   $pluginOrderOrder->check($_GET['id'], 'w');
   $pluginOrderOrder->unlinkBudget($_GET['id']);
   glpi_header($_SERVER['HTTP_REFERER']);
} else {
   $pluginOrderOrder->checkGlobal("r");
   commonHeader($LANG['plugin_order']['title'][1], '', "plugins", "order", "order");
   $pluginOrderOrder->showForm($_GET["id"]);
   commonFooter();
}

?>