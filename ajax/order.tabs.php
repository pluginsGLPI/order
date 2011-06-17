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
// Purpose of file: 
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!isset ($_POST["id"])) {
   exit ();
}

if (!isset ($_POST["withtemplate"])) {
   $_POST["withtemplate"] = "";
}

$order          = new PluginOrderOrder();
$order_item     = new PluginOrderOrder_Item();
$order_supplier = new PluginOrderOrder_Supplier();
$orderreception = new PluginOrderReception();
$orderlink      = new PluginOrderLink();
$surveySupplier = new PluginOrderSurveySupplier();
$order->checkGlobal("r");

if ($_POST["id"] > 0 && $order->can($_POST["id"], 'r')) {
   switch($_REQUEST['glpi_tab']) {
      case -1 :
         $order_item->showItem($_POST["id"]);
         $order->showValidationForm($_POST["id"]);
         $order_supplier->showOrderSupplierInfos($_POST["id"]);
         if (!$order_supplier->checkIfSupplierInfosExists($_POST["id"]) 
                && $order->can($_POST["id"], 'w')) {
            $order_supplier->showForm("",  array('plugin_order_orders_id' => $_POST["id"]));

         }
         if ($order->fields['plugin_order_orderstates_id'] != PluginOrderOrderState::DRAFT) {
            $orderreception->showOrderReception($_POST["id"]);
            $orderlink->showOrderLink($_POST["id"]);
            
            if ($order->getState() == PluginOrderOrderState::DELIVERED) {
               $surveySupplier->showOrderSupplierSurvey($_POST["id"]);
               if (!$surveySupplier->checkIfSupplierSurveyExists($_POST["id"]) 
                  && $order->can($_POST["id"], 'w')) {
                  $surveySupplier->showForm("",array('plugin_order_orders_id' => $_POST["id"]));
               }

            }

         }

         Document::showAssociated($order);
         Plugin::displayAction($order,$_REQUEST['glpi_tab']);
         break;
      case 2 :
         $order->showValidationForm($_POST["id"]);
         break;
      case 3 :
         $order_supplier->showOrderSupplierInfos($_POST["id"]);
         if (!$order_supplier->checkIfSupplierInfosExists($_POST["id"]) 
                && $order->can($_POST["id"],'w')) {
            $order_supplier->showForm("", array('plugin_order_orders_id' => $_POST["id"]));
         }
         break;
      case 4 :
         if ($order->getState() == PluginOrderOrderState::DELIVERED) {
            if ($order->can($_POST["id"],'w')) {
               $order->showGenerationForm($_POST["id"]);

            }

         }
         break;
      case 5 :
         $orderreception->showOrderReception($_POST["id"]);
         break;
      case 6 :
         $orderlink->showOrderLink($_POST["id"]);
         break;
       case 7 :
         if ($order->getState() == PluginOrderOrderState::DELIVERED) {
            $surveySupplier->showOrderSupplierSurvey($_POST["id"]);
            if (!$surveySupplier->checkIfSupplierSurveyExists($_POST["id"]) 
                   && $order->can($_POST["id"], 'w')) {
               $surveySupplier->showForm("",  array('plugin_order_orders_id' => $_POST["id"]));
            }
            
         }
         break;
      case 9 :
         Document::showAssociated($order);
         break;
      case 10 :
         showNotesForm($_POST['target'],"PluginOrderOrder", $_POST["id"]);
         break;
      case 12 :
         Log::showForItem($order);
         break;
      default :
         if (!Plugin::displayAction($order, $_REQUEST['glpi_tab'])) {
            $order_item->showItem($_POST["id"]);
         }
         break;
    }
}

ajaxFooter();

?>