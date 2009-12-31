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

$NEEDED_ITEMS = array (
	"computer",	"printer",	"networking",	"monitor",	"software",
	"peripheral",	"phone",	"tracking",	"document",	"user",
	"enterprise",	"contract",	"infocom",	"group",	"cartridge",
	"consumable");
   
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

useplugin('order', true);

if (!isset ($_GET["ID"]))
	$_GET["ID"] = "";
if (!isset ($_GET["withtemplate"]))
	$_GET["withtemplate"] = "";

$plugin_order = new PluginOrder();

/* add order */
if (isset ($_POST["add"])) {
	if (plugin_order_HaveRight("order", "w"))
      $newID = $plugin_order->add($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
}
/* delete order */
else if (isset ($_POST["delete"])) {
   if (plugin_order_HaveRight("order", "w"))
      $plugin_order->delete($_POST);
   glpi_header($CFG_GLPI["root_doc"] . "/plugins/order/index.php");
}
/* restore order */
else if (isset ($_POST["restore"])) {
   if (plugin_order_HaveRight("order", "w"))
      $plugin_order->restore($_POST);
   glpi_header($CFG_GLPI["root_doc"] . "/plugins/order/index.php");
}
/* purge order */
else if (isset ($_POST["purge"])) {
   if (plugin_order_HaveRight("order", "w"))
      $plugin_order->delete($_POST, 1);
   glpi_header($CFG_GLPI["root_doc"] . "/plugins/order/index.php");
}
/* update order */
else if (isset ($_POST["update"])) {
   if (plugin_order_HaveRight("order", "w"))
      $plugin_order->update($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);
} 
//Status update & order workflow
/* validate order */
else if (isset ($_POST["validate"])) {
   
   $PluginOrderConfig = new PluginOrderConfig;
   $config = $PluginOrderConfig->getConfig();
		
   if (plugin_order_HaveRight("order", "w") && ( plugin_order_HaveRight("validation", "w") || !$config["use_validation"]))
   {
      plugin_order_updateOrderStatus($_POST["ID"],ORDER_STATUS_APPROVED,$_POST["comments"]);
      plugin_order_updateDelivryStatus($_POST["ID"]);
      $plugin_order->getFromDB($_POST["ID"]);
      plugin_order_sendNotification("validation",$_POST["ID"],$plugin_order->fields["FK_entities"],$_SESSION["glpiID"],$_POST["comments"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][10]);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
	}
else if (isset ($_POST["waiting_for_approval"])) {
   if (plugin_order_HaveRight("order", "w"))
   {
      plugin_order_updateOrderStatus($_POST["ID"],ORDER_STATUS_WAITING_APPROVAL,$_POST["comments"]);
      $plugin_order->getFromDB($_POST["ID"]);
      plugin_order_sendNotification("ask",$_POST["ID"],$plugin_order->fields["FK_entities"],$_SESSION["glpiID"],$_POST["comments"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][7]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset ($_POST["cancel_waiting_for_approval"])) {
   if (plugin_order_HaveRight("order", "w") && plugin_order_HaveRight("cancel", "w"))
   {
      plugin_order_updateOrderStatus($_POST["ID"],ORDER_STATUS_DRAFT,$_POST["comments"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][14]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset ($_POST["cancel_order"])) {
   if (plugin_order_HaveRight("order", "w") && plugin_order_HaveRight("cancel", "w"))
   {
      plugin_order_updateOrderStatus($_POST["ID"],ORDER_STATUS_CANCELED,$_POST["comments"]);
      plugin_order_deleteAllLinkWithDevice($_POST["ID"]);
      $plugin_order->getFromDB($_POST["ID"]);
      plugin_order_sendNotification("cancel",$_POST["ID"],$plugin_order->fields["FK_entities"],$_SESSION["glpiID"],$_POST["comments"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][5]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset ($_POST["undovalidation"])) {
   if (plugin_order_HaveRight("order", "w") && plugin_order_HaveRight("undo_validation", "w"))
   {
      plugin_order_updateOrderStatus($_POST["ID"],ORDER_STATUS_DRAFT,$_POST["comments"]);
      $plugin_order->getFromDB($_POST["ID"]);
      plugin_order_sendNotification("undovalidation",$_POST["ID"],$plugin_order->fields["FK_entities"],$_SESSION["glpiID"],$_POST["comments"]);
      addMessageAfterRedirect($LANG['plugin_order']['validation'][16]);
   }
   
   glpi_header($_SERVER['HTTP_REFERER']);
}
//Details management
else if (isset ($_POST["add_detail"])) {
   if ($_POST["discount"] < 0 || $_POST["discount"] > 100)
      addMessageAfterRedirect($LANG['plugin_order']['detail'][33],false,ERROR);
   else
   {
      $new_value = $LANG['plugin_order']['detail'][34]." ".getDropdownName("glpi_plugin_order_references",$_POST["FK_reference"]);
      $new_value.= " (".$LANG['plugin_order']['detail'][7]." : ".$_POST["quantity"];
      $new_value.= " ".$LANG['plugin_order']['detail'][25]." : ".$_POST["discount"].")";
      plugin_order_addHistory(PLUGIN_ORDER_TYPE,"",$new_value,$_POST["FK_order"]);
      plugin_order_addDetails($_POST["FK_reference"], $_POST["device_type"], $_POST["FK_order"], $_POST["quantity"], $_POST["price"], $_POST["discount"], $_POST["taxes"],$_POST["discount"]);
   }
      
   glpi_header($_SERVER['HTTP_REFERER']);
} 
else if (isset ($_POST["delete_detail"])) {
   plugin_order_checkRight("order", "w");
   if (isset($_POST["FK_order"]) && $_POST["FK_order"] > 0 && isset($_POST["detail"]))
   {
      foreach ($_POST["detail"] as $ID => $val)
         if ($val==1)
         {
            $new_value = $LANG['plugin_order']['detail'][35]." ".getDropdownName("glpi_plugin_order_references",$ID);
            plugin_order_addHistory(PLUGIN_ORDER_TYPE,"",$new_value,$_POST["FK_order"]);
            plugin_order_deleteDetails($ID);
         }
   }elseif(!isset($_POST["detail"]))
      addMessageAfterRedirect($LANG['plugin_order']['detail'][29],false,ERROR);
      
   glpi_header($_SERVER['HTTP_REFERER']);
} 
else 
{
	plugin_order_checkRight("order", "r");

	if (!isset ($_SESSION['glpi_tab']))
		$_SESSION['glpi_tab'] = 1;
	if (isset ($_GET['onglet'])) 
		$_SESSION['glpi_tab'] = $_GET['onglet'];

	commonHeader($LANG['plugin_order']['title'][1], $_SERVER["PHP_SELF"], "plugins", "order", "order");
	
	$plugin_order->title();
	echo "<br>";
	$plugin_order->showForm($_SERVER["PHP_SELF"], $_GET["ID"]);
	
	commonFooter();
}
?>