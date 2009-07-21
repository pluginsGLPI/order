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
    Original Author of file: Benjamin Fontan
    Purpose of file:
    ----------------------------------------------------------------------*/

function plugin_order_getAllOrdersByBudget ($budget_id)
{
	global $DB,$LANG,$INFOFORM_PAGES,$CFG_GLPI;
	
	$budget = new PluginOrderBudget;
	$budget->getFromDB($budget_id);
	$query = "SELECT * FROM `glpi_plugin_order` WHERE budget=".$budget->fields["FK_budget"]." ORDER BY FK_entities, name";
	$result = $DB->query($query);

	echo "<div class='center'>";
	echo "<table class='tab_cadre_fixe'>";
	echo "<tr><th colspan='5'>".$LANG['plugin_order']['reference'][3]."</th></tr>";
	echo "<tr>"; 
	echo "<th>".$LANG['plugin_order']['budget'][1]."</th>";
	echo "<th>".$LANG['entity'][0]."</th>";
	echo "</tr>";

	while ($datas = $DB->fetch_array($result))
	{
		echo "<tr class='tab_bg_1' align='center'>"; 
		echo "<td>";
		if (plugin_order_haveRight("order","r"))
			echo "<a href='".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[PLUGIN_ORDER_TYPE]."?ID=".$datas["ID"]."'>".$datas["name"]."</a>";
		else
			echo $datas["name"];	
		echo "</td>";

		echo "<td>";
		echo getDropdownName("glpi_entities",$datas["FK_entities"]);
		echo "</td>";

		echo "</tr>"; 
	}
	
	echo "</table></div>";
}

function plugin_order_getTotalBuyByBudget($budget_id)
{
	global $DB,$LANG,$INFOFORM_PAGES,$CFG_GLPI;
	$budget = new PluginOrderBudget;
	$budget->getFromDB($budget_id);
	$query = "SELECT SUM(price_discounted) as total_price, FROM `glpi_plugin_order`, `glpi_plugin_order_detail` " .
			"WHERE budget=".$budget->fields["FK_budget"]." AND glpi_plugin_order_detail.FK_order=glpi_plugin_order.ID " .
			"GROUP BY glpi_plugin_order.budget";
	$result = $DB->query($query);
	
	while ($datas = $DB->fetch_array($result))
	{
		
		
	}	
}

?>