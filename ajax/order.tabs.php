<?php
/*
 * @version $Id: HEADER 1 2009-09-21 14:58 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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
// Original Author of file: NOUH Walid & Benjamin Fontan
// Purpose of file: plugin order v1.1.0 - GLPI 0.72
// ----------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!isset ($_POST["id"])) {
	exit ();
}

if (!isset ($_POST["withtemplate"]))
	$_POST["withtemplate"] = "";

PluginOrderProfile::checkRight("order","r");

$PluginOrderOrder = new PluginOrderOrder();
$PluginOrderOrder_Item = new PluginOrderOrder_Item();
$PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier();
$PluginOrderReception = new PluginOrderReception();

if ($_POST["id"]>0 && $PluginOrderOrder->can($_POST["id"],'r')) {
   if (!empty($_POST["withtemplate"])) {
		switch($_REQUEST['glpi_tab']) {
			default :
				break;
		}
	} else {
      switch($_REQUEST['glpi_tab']) {
         case -1 :
            $PluginOrderOrder_Item->showItem($_SERVER["HTTP_REFERER"], $_POST["id"]);
            $PluginOrderOrder->showValidationForm($_SERVER["HTTP_REFERER"], $_POST["id"]);
            $PluginOrderOrder_Supplier->showOrderSupplierInfos($_SERVER['HTTP_REFERER'],$_POST["id"]);
            $PluginOrderOrder_Supplier->addSupplierInfosToOrder($_SERVER['HTTP_REFERER'],$_POST["id"]);
            $PluginOrderReception->showOrderReception($_POST["id"]);
            $PluginOrderReception->showOrderGeneration($_POST["id"]);
            Document::showAssociated($PluginOrderOrder);
            Plugin::displayAction($PluginOrderOrder,$_POST['glpi_tab']);
            break;
         case 2 :
            $PluginOrderOrder->showValidationForm($_SERVER["HTTP_REFERER"], $_POST["id"]);
            break;
         case 3 :
            $PluginOrderOrder_Supplier->showOrderSupplierInfos($_SERVER['HTTP_REFERER'],$_POST["id"]);
            $PluginOrderOrder_Supplier->addSupplierInfosToOrder($_SERVER['HTTP_REFERER'],$_POST["id"]);
            break;
         case 4 :
            $PluginOrderOrder->showGenerationForm($_POST["id"]);
            break;
         case 5 :
            $PluginOrderReception->showOrderReception($_POST["id"]);
            break;
         case 6 :
            $PluginOrderReception->showOrderGeneration($_POST["id"]);
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
            if (!Plugin::displayAction($PluginOrderOrder,$_POST['glpi_tab'])) {
               $PluginOrderOrder_Item->showItem($_SERVER["HTTP_REFERER"], $_POST["id"]);
            }
            break;
      }
   }
}

ajaxFooter();

?>