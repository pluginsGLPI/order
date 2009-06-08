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
$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

if(isset($_POST["reception"])) 
{
	if(isset($_POST["item"]))
	{
		 foreach ($_POST["item"] as $key => $val)
		 {
			if ($val==1) 
			{
				$DB = new DB;
				$query=" UPDATE glpi_plugin_order_detail
						SET status=1
						WHERE ID=$key";
				$DB->query($query);
			}
		}
	}
	glpi_header($_SERVER["HTTP_REFERER"]);
} 
if(isset($_POST["showGeneration"])) {
	commonHeader($LANG['plugin_order'][4],$_SERVER["PHP_SELF"],"plugins","order","order");
	echo "<div class='center'>";
	echo "<table class='tab_cadre'>";
	if(isset($_POST["item"])) 
	{
		echo "<form method='post' name='order_materialGeneration' id='order_materialGeneration'  action=".$_SERVER["PHP_SELF"].">";
		echo "<tr><th colspan='4'>".$LANG['plugin_order']['delivery'][3]."</tr></th>";
		echo "<tr><th>".$LANG['plugin_order']['reference'][1]."</th>";
		echo "<th>".$LANG['plugin_order']['delivery'][6]."</th>";
		echo "<th>".$LANG['plugin_order']['delivery'][7]."</th>";
		echo "<th>".$LANG['plugin_order']['delivery'][8]."</th></tr>";
		echo "<input type='hidden' name='FK_order' value=".$_POST["orderID"].">";
		$i=0;
		foreach ($_POST["item"] as $key => $val)
		{
			if ($val==1) 
			{
				if($_POST["status"][$key]==ORDER_STATUS_NOT_DELIVERED)
				{
					addMessageAfterRedirect($LANG['plugin_order']['delivery'][10]);
					glpi_header($_SERVER["HTTP_REFERER"]);
				}
				echo "<tr><td><a href=".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.reference.form.php?ID=".$key.">".$_POST["name"][$key]."</a></td>";
				echo "<td><input type='text' size='20' name='serial[$i]'></td>";
				echo "<td><input type='text' size='20' name='inventory[$i]'></td>";
				echo "<td><input type='text' size='20' name='name[$i]'></td></tr>";
				echo "<input type='hidden' name='type[$i]' value=".$_POST['type'][$key].">";
				echo "<input type='hidden' name='ID[$i]' value=".$_POST['ID'][$key].">";
				$i++;
			}
		}
		echo "<tr><td align='center' colspan='4' class='tab_bg_2'><input type='submit' name='generation' class='submit' value=".$LANG['plugin_order']['delivery'][9]."></td></tr>";
	} else 
		glpi_header($_SERVER["HTTP_REFERER"]);
	echo "</table>";
	echo "</div>";
	commonFooter();
} 
if(isset($_POST["generation"])) 
{
	$i=0;
	while(isset($_POST["serial"][$i])) 
	{
		plugin_order_generateAssociatedMaterial($_POST["type"][$i], $_POST["serial"][$i], $_POST["inventory"][$i], $_POST["name"][$i]);
		plugin_order_createLinkWithAssociatedMaterial($_POST["type"][$i], $_POST["serial"][$i], $_POST["FK_order"], $_POST["ID"][$i]);
		$i++;
	}
	glpi_header("".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.form.php?ID=".$_POST["FK_order"]."");
}
function plugin_order_generateAssociatedMaterial($type, $serial, $numinv, $name) 
{
	global $DB, $LINK_ID_TABLE;
	
	$entity=$_SESSION["glpiactive_entity"];
	$query=" INSERT INTO ".$LINK_ID_TABLE[$type]." (serial, otherserial, name, FK_entities) 
			values ('$serial', '$numinv', '$name', $entity)";
	$DB->query($query);
}
function plugin_order_createLinkWithAssociatedMaterial($type, $serial, $FK_order, $IDD)
{
	global $DB, $LINK_ID_TABLE;
	
	$query=" SELECT ID FROM ".$LINK_ID_TABLE[$type]." WHERE serial='$serial'"; 
	$result=$DB->query($query);
	$ID=$DB->result($result,0,'ID');
	$query=" INSERT INTO glpi_plugin_order_device (FK_order, FK_device, device_type) 
			values ('$FK_order', '$ID', '$type')";
	$DB->query($query);
	$query=" SELECT ID FROM glpi_plugin_order_device WHERE FK_order='$FK_order' AND FK_device='$ID' AND device_type='$type'"; 
	$result=$DB->query($query);
	$ID_device=$DB->result($result,0,'ID');
	$query=" UPDATE glpi_plugin_order_detail
			SET FK_device=$ID_device
			WHERE ID=$IDD";
	$DB->query($query);
}
 ?>