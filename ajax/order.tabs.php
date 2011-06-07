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
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!isset ($_POST["id"])) {
   exit ();
}

if (!isset ($_POST["withtemplate"]))
   $_POST["withtemplate"] = "";

$PluginOrderOrder = new PluginOrderOrder();
$PluginOrderOrder->checkGlobal("r");
$PluginOrderOrder_Item = new PluginOrderOrder_Item();
$PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier();
$PluginOrderReception = new PluginOrderReception();
$PluginOrderLink = new PluginOrderLink();
$PluginOrderSurveySupplier = new PluginOrderSurveySupplier();

if ($_POST["id"]>0 && $PluginOrderOrder->can($_POST["id"],'r')) {
   if (!empty($_POST["withtemplate"])) {
      switch($_REQUEST['glpi_tab']) {
         default :
            break;
      }
   } else {
      switch($_REQUEST['glpi_tab']) {
         case -1 :
            $PluginOrderOrder_Item->showItem($CFG_GLPI["root_doc"] .
         "/plugins/order/front/order.form.php", $_POST["id"]);
            $PluginOrderOrder->showValidationForm($CFG_GLPI["root_doc"] .
         "/plugins/order/front/order.form.php", $_POST["id"]);
            $PluginOrderOrder_Supplier->showOrderSupplierInfos($CFG_GLPI["root_doc"] .
         "/plugins/order/front/order_supplier.form.php",$_POST["id"]);
            if (!$PluginOrderOrder_Supplier->checkIfSupplierInfosExists($_POST["id"]) 
                                                                          && $PluginOrderOrder->can($_POST["id"],'w'))
               $PluginOrderOrder_Supplier->showForm("", 
                                                    array('plugin_order_orders_id' => $_POST["id"],
                                                          'target' => $CFG_GLPI["root_doc"] .
         "/plugins/order/front/order_supplier.form.php"));
            $PluginOrderReception->showOrderReception($_POST["id"]);
            $PluginOrderLink->showOrderLink($_POST["id"]);
            $PluginOrderSurveySupplier->showOrderSupplierSurvey($CFG_GLPI["root_doc"] .
         "/plugins/order/front/surveysupplier.form.php",$_POST["id"]);
            if (!$PluginOrderSurveySupplier->checkIfSupplierSurveyExists($_POST["id"]) 
                                                                           && $PluginOrderOrder->can($_POST["id"],'w'))
               $PluginOrderSurveySupplier->showForm("", 
                                                    array('plugin_order_orders_id' => $_POST["id"],
                                                          'target' => $CFG_GLPI["root_doc"] .
         "/plugins/order/front/surveysupplier.form.php"));
            Document::showAssociated($PluginOrderOrder);
            Plugin::displayAction($PluginOrderOrder,$_REQUEST['glpi_tab']);
            break;
         case 2 :
            $PluginOrderOrder->showValidationForm($CFG_GLPI["root_doc"] .
         "/plugins/order/front/order.form.php", $_POST["id"]);
            break;
         case 3 :
            $PluginOrderOrder_Supplier->showOrderSupplierInfos($CFG_GLPI["root_doc"] .
         "/plugins/order/front/order_supplier.form.php",$_POST["id"]);
            if (!$PluginOrderOrder_Supplier->checkIfSupplierInfosExists($_POST["id"]) 
                                                                         && $PluginOrderOrder->can($_POST["id"],'w'))
               $PluginOrderOrder_Supplier->showForm("", 
                                                    array('plugin_order_orders_id' => $_POST["id"],
                                                          'target' => $CFG_GLPI["root_doc"] .
         "/plugins/order/front/order_supplier.form.php"));
            break;
         case 4 :
            if ($PluginOrderOrder->can($_POST["id"],'w'))
               $PluginOrderOrder->showGenerationForm($_POST["id"]);
            break;
         case 5 :
            $PluginOrderReception->showOrderReception($_POST["id"]);
            break;
         case 6 :
            $PluginOrderLink->showOrderLink($_POST["id"]);
            break;
          case 7 :
            $PluginOrderSurveySupplier->showOrderSupplierSurvey($CFG_GLPI["root_doc"] .
         "/plugins/order/front/surveysupplier.form.php",$_POST["id"]);
            if (!$PluginOrderSurveySupplier->checkIfSupplierSurveyExists($_POST["id"]) 
                                                                           && $PluginOrderOrder->can($_POST["id"],'w'))
               $PluginOrderSurveySupplier->showForm("", 
                                                    array('plugin_order_orders_id' => $_POST["id"],
                                                          'target' => $CFG_GLPI["root_doc"] .
         "/plugins/order/front/surveysupplier.form.php"));
            break;
         case 9 :
            Document::showAssociated($PluginOrderOrder);
            break;
         case 10 :
            showNotesForm($_POST['target'],"PluginOrderOrder",$_POST["id"]);
            break;
         case 12 :
            Log::showForItem($PluginOrderOrder);
            break;
         default :
            if (!Plugin::displayAction($PluginOrderOrder,$_REQUEST['glpi_tab'])) {
               $PluginOrderOrder_Item->showItem($CFG_GLPI["root_doc"] .
                                                  "/plugins/order/front/order.form.php",
                                                $_POST["id"]);
            }
            break;
      }
   }
}

ajaxFooter();

?>