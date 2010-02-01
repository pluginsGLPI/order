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
include (GLPI_ROOT."/inc/includes.php");

if(!isset($_GET["id"])) $_GET["id"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";
if(!isset($_GET["plugin_order_orders_id"])) $_GET["plugin_order_orders_id"] = "";

$PluginOrderOrder_Supplier=new PluginOrderOrder_Supplier();
$PluginOrderOrder=new PluginOrderOrder();

if (isset($_POST["add"]))
{
	if(plugin_order_HaveRight("order","w"))
	{
		if (isset($_POST["plugin_order_orders_id"]) && $_POST["plugin_order_orders_id"] > 0)
		{
			$newID=$PluginOrderOrder_Supplier->add($_POST);
			$new_value = $LANG['plugin_order']['history'][2]. " ";
         if ($_POST["num_quote"])
            $new_value.= $LANG['plugin_order'][30]." ".$_POST["num_quote"];
         if ($_POST["num_order"])
            $new_value.= " - ".$LANG['plugin_order'][31]." : ".$_POST["num_order"];
         if ($_POST["num_bill"])
            $new_value.= " - ".$LANG['plugin_order'][28]." : ".$_POST["num_bill"];
         $PluginOrderOrder->addHistory('PluginOrderOrder',"",$new_value,$_POST["plugin_order_orders_id"]);
		}
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($_POST["delete"]))
{
	if(plugin_order_HaveRight("order","w"))
	{
		foreach ($_POST["check"] as $ID => $value) {
			$PluginOrderOrder_Supplier->delete(array("id"=>$ID),0,0);
			$new_value = $LANG['plugin_order']['history'][4]. " ".$LANG['plugin_order'][4]." : ".$ID;
         $PluginOrderOrder->addHistory('PluginOrderOrder',"",$new_value,$_POST["plugin_order_orders_id"]);
      }
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($_POST["update"]))
{
	if(plugin_order_HaveRight("order","w")) {
		
      $PluginOrderOrder_Supplier->update($_POST);
   }
	glpi_header($_SERVER['HTTP_REFERER']);
}
else
{
	PluginOrderProfile::checkRight("order","r");

	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_tab']=$_GET['onglet'];
	}
	
	commonHeader($LANG['plugin_order']['title'][1],$_SERVER["PHP_SELF"],"plugins","order","order");
	
	/* load order form */
	$PluginOrderOrder_Supplier->showForm($_GET["id"], array('plugin_order_orders_id' => $_GET["plugin_order_orders_id"]));

	commonFooter();
}

?>