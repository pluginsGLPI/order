<?php
/*----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------
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
   ----------------------------------------------------------------------*/
/*----------------------------------------------------------------------
    Original Author of file:
    Purpose of file:
    ----------------------------------------------------------------------*/
function updateOrderStatus($orderID)
{
	global $DB;
	
	$config = plugin_order_getConfig(); 
	
	$order = new plugin_order;
	$order->getFromDB($orderID);
	
	$input["ID"] = $orderID;
	$input["status"] = (!$order->fields["status"]?$config->fields["status_nodelivered"]:$config->fields["status_delivered"]);
	$order->update($input);	
}

function plugin_order_canUpdateOrder($orderID)
{
	$config = plugin_order_getConfig(); 

	$order = new plugin_order;
	$order->getFromDB($orderID);
	if ($order->fields["status"] == $config->fields["status_not"]);		
}

function plugin_order_showOrderInfoByDeviceID($device_type,$deviceID)
{
	global $LANG,$INFOFORM_PAGES,$CFG_GLPI;
	$device =  new plugin_order_device;
	$infos = $device->getOrderInfosByDeviceID($device_type,$deviceID);
	if ($infos)
	{
		echo "<div class='center'>";
		echo "<table class='tab_cadre_fixe'>";
		echo "<tr align='center'><th colspan='2'>" . $LANG['plugin_order'][47] . ": </th></tr>";
		echo "<tr align='center'><td class='tab_bg_2'>".$LANG['plugin_order'][39]."</td>";
		echo "<td class='tab_bg_2'>"; 
		if (plugin_order_haveRight("order","r"))
			echo "<a href='".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[PLUGIN_ORDER_TYPE]."?ID=".$infos["ID"]."'>".$infos["name"]."</a>";
		else
			echo $infos["name"];	 
		echo "</td></tr>";
		echo "<tr align='center'><td class='tab_bg_2'>".$LANG['plugin_order']['detail'][21]."</td>";
		echo "<td class='tab_bg_2'>".convDate($infos["date"])."</td></tr>";
		echo "</table></div>";
	}
}
?>