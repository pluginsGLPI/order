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
 along with GLPI; along with Behaviors. If not, see <http://www.gnu.org/licenses/>.
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
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!isset ($_POST["id"])) {
   exit ();
}

if (!isset ($_POST["withtemplate"])) {
   $_POST["withtemplate"] = "";
}

$config         = PluginOrderConfig::getConfig();
$order          = new PluginOrderOrder();
$order_item     = new PluginOrderOrder_Item();
$order_supplier = new PluginOrderOrder_Supplier();
$orderreception = new PluginOrderReception();
$orderlink      = new PluginOrderLink();
$surveySupplier = new PluginOrderSurveySupplier();
$order->checkGlobal("r");

if ($_POST["id"] > 0 && $order->can($_POST["id"], 'r') && !$order->fields['is_template']) {
   switch($_REQUEST['glpi_tab']) {
      case -1 :
         $order_item->showItem($_POST["id"]);
         if (plugin_order_haveRight('order','w')) {
            $order->showValidationForm($_POST["id"]);
         }
         if ($config->canUseSupplierInformations() && $order->fields['suppliers_id']) {
            $order_supplier->showForm("", array('plugin_order_orders_id' => $_POST["id"]));
         }
         
         if ($config->canGenerateOrderPDF()
            && $order->getState() > PluginOrderOrderState::DRAFT
               && plugin_order_haveRight('order','w') && $order->can($_POST["id"],'w')) {
            $order->showGenerationForm($_POST["id"]);
         }
         
         if (plugin_order_haveRight('delivery', 'r')
            && $order->getState() > PluginOrderOrderState::DRAFT) {
            $orderreception->showOrderReception($_POST["id"]);
            
            if ($order->checkIfDetailExists($order->getID(), true)) {
               $orderlink->showOrderLink($_POST["id"]);
            }
         }

         if ($config->canUseSupplierSatisfaction()
            && $order->getState() == PluginOrderOrderState::DELIVERED) {
            $surveySupplier->showOrderSupplierSurvey($_POST["id"]);
            if (!$surveySupplier->checkIfSupplierSurveyExists($_POST["id"])
                   && $order->can($_POST["id"], 'w')) {
               $surveySupplier->showForm("",  array('plugin_order_orders_id' => $_POST["id"]));
            }
         }

         if (plugin_order_haveRight("bill", "r")) {
            $order_item->showBillsItems($order);
         }
         if (haveRight("document", "r")) {
            Document::showAssociated($order);
         }
         Plugin::displayAction($order,$_REQUEST['glpi_tab']);
         break;
      case 2 :
        if (plugin_order_haveRight('order','w')) {
           $order->showValidationForm($_POST["id"]);
        }
        break;
      case 3 :
         if ($config->canUseSupplierInformations() && $order->fields['suppliers_id']) {
            $order_supplier->showForm("", array('plugin_order_orders_id' => $_POST["id"]));
         }
         break;
      case 4 :
         if ($config->canGenerateOrderPDF()
            && $order->getState() > PluginOrderOrderState::DRAFT
               && plugin_order_haveRight('order','w') && $order->can($_POST["id"],'w')) {
            $order->showGenerationForm($_POST["id"]);
         }
         break;
      case 5 :
         if (plugin_order_haveRight('delivery', 'r')
            && $order->getState() > PluginOrderOrderState::DRAFT) {
            $orderreception->showOrderReception($_POST["id"]);
         }
         break;
      case 6 :
         if (plugin_order_haveRight('delivery', 'r')
            && $order->getState() > PluginOrderOrderState::DRAFT
               && $order->checkIfDetailExists($order->getID(), true)) {
            $orderlink->showOrderLink($_POST["id"]);
         }
         break;
       case 7 :
         if ($config->canUseSupplierSatisfaction()
            && $order->getState() == PluginOrderOrderState::DELIVERED) {
            $surveySupplier->showOrderSupplierSurvey($_POST["id"]);
            if (!$surveySupplier->checkIfSupplierSurveyExists($_POST["id"])
                   && $order->can($_POST["id"], 'w')) {
               $surveySupplier->showForm("",  array('plugin_order_orders_id' => $_POST["id"]));
            }
         }
         break;
      case 8:
         if (plugin_order_haveRight("bill", "r")) {
            $order_item->showBillsItems($order);
         }
         break;
      case 9 :
         if (haveRight('document', 'r')) {
            Document::showAssociated($order);
         }
         break;
      case 10 :
         showNotesForm($_POST['target'], "PluginOrderOrder", $_POST["id"]);
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