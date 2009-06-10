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
include (GLPI_ROOT."/plugins/order/inc/plugin_order.reception.function.php");
include (GLPI_ROOT."/plugins/order/inc/plugin_order.order.function.php");


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
						SET status=1, date='".$_POST["date"]."'
						WHERE ID=$key";
				$DB->query($query);
			}
		}
	updateOrderStatus($_POST["orderID"]);
	}
	glpi_header($_SERVER["HTTP_REFERER"]);
} 
if(isset($_POST["generation"])) 
{
	commonHeader($LANG['plugin_order'][4],$_SERVER["PHP_SELF"],"plugins","order","order");
	echo "<div class='center'>";
	echo "<table class='tab_cadre'>";
	if(isset($_POST["item"])) 
	{
		echo "<form method='post' name='order_deviceGeneration' id='order_deviceGeneration'  action=".$_SERVER["PHP_SELF"].">";
		echo "<tr><th colspan='4'>".$LANG['plugin_order']['delivery'][3]."</tr></th>";
		echo "<tr><th>".$LANG['plugin_order']['reference'][1]."</th>";
		echo "<th>".$LANG['plugin_order']['delivery'][6]."</th>";
		echo "<th>".$LANG['plugin_order']['delivery'][7]."</th>";
		echo "<th>".$LANG['plugin_order']['delivery'][8]."</th></tr>";
		echo "<input type='hidden' name='orderID' value=".$_POST["orderID"].">";
		$i=0;
		foreach ($_POST["item"] as $key => $val)
		{
			if ($val==1) 
			{
				echo "<tr><td><a href=".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.reference.form.php?ID=".$key.">".$_POST["name"][$key]."</a></td>";
				echo "<td><input type='text' size='20' name='serial[$i]'></td>";
				echo "<td><input type='text' size='20' name='otherserial[$i]'></td>";
				echo "<td><input type='text' size='20' name='name[$i]'></td></tr>";
				echo "<input type='hidden' name='type[$i]' value=".$_POST['type'][$key].">";
				echo "<input type='hidden' name='ID[$i]' value=".$_POST['ID'][$key].">";
				$i++;
			}
		}
		echo "<tr><td align='center' colspan='4' class='tab_bg_2'><input type='submit' name='generate' class='submit' value=".$LANG['plugin_order']['delivery'][9]."></td></tr>";
	} else 
		glpi_header($_SERVER["HTTP_REFERER"]);
	echo "</table>";
	echo "</div>";
	commonFooter();
} 
if(isset($_POST["generate"])) 
{
	$i=0;
	while(isset($_POST["serial"][$i])) 
	{
		$ci=new CommonItem();
		$ci->setType($_POST["type"][$i],true);
		$newID=$ci->obj->add(array(	'serial'=>$_POST["serial"][$i],
										'otherserial'=>$_POST["otherserial"][$i],
										'name'=>$_POST["name"][$i],
										'FK_entities'=>$_SESSION["glpiactive_entity"]));
		plugin_order_createLinkWithDevice($_POST["ID"][$i], $newID, $_POST["type"][$i], $_POST["orderID"]);
		$i++;
	}
	glpi_header("".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.form.php?ID=".$_POST["orderID"]."");
}
if(isset($_POST["deleteLinkWithDevice"])) 
{
	foreach ($_POST["item"] as $key => $val)
	{
		if ($val==1) 
			plugin_order_deleteLinkWithDevice($key);
	}
	glpi_header("".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.form.php?ID=".$_POST["orderID"]."");
}
if(isset($_POST["createLinkWithDevice"])) 
{
	$i=0;
	if(sizeof($_POST["item"])<=1)
	{
		foreach ($_POST["item"] as $key => $val)
		{
		
			if ($val==1) 
				plugin_order_createLinkWithDevice($key, $_POST["device"], $_POST["type"][$key], $_POST["orderID"]);
		}
	}
	else
		addMessageAfterRedirect($LANG['plugin_order'][42],false,ERROR);
	glpi_header("".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.form.php?ID=".$_POST["orderID"]."");
}
 ?>