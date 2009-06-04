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
    Original Author of file: Walid Nouh
    Purpose of file:
    ----------------------------------------------------------------------*/
function plugin_order_showReferenceManufacturers($target, $ID) {
	global $LANG, $DB, $CFG_GLPI;
	$query = "SELECT * FROM `glpi_plugin_order_references_manufacturers` WHERE FK_reference='$ID'";
	$result = $DB->query($query);

	echo "<div class='center'>";
	echo "<form method='post' name=form action=\"$target\">";
	echo "<input type='hidden' name='FK_reference' value='" . $ID . "'>";
	echo "<table class='tab_cadre_fixe'>";

	echo "<tr><th></th><th>" . $LANG['financial'][26] . "</th><th>" . $LANG['plugin_order']['detail'][4] . "</th></tr>";

	$suppliers = array();
	
	if ($DB->numrows($result) > 0) {
		echo "<form method='post' name='show_ref_manu' action=\"$target\">";
		echo "<input type='hidden' name='FK_reference' value='" . $ID . "'>";

		while ($data = $DB->fetch_array($result)) {
			$suppliers[$data["ID"]] = $data["ID"];
			echo "<input type='hidden' name='item[" . $data["ID"] . "]' value='" . $ID . "'>";
			echo "<tr>";
			echo "<td class='tab_bg_1'>";
			echo "<input type='checkbox' name='check[" . $data["ID"] . "]'>";
			echo "</td>";
			echo "<td class='tab_bg_1'>" . getDropdownName("glpi_enterprises", $data["FK_enterprise"]) . "</td>";
			echo "<td class='tab_bg_1'>";
			autocompletionTextField("price[".$data["ID"]."]", "glpi_plugin_order_references_manufacturers", "price", $data["price"], 7);
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";

		echo "<table width='80%'>";
		echo "<tr>"; 
		echo "<td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td align='center'><a onclick= \"if ( markAllRows('show_ref_manu') ) return false;\" href='".$_SERVER['PHP_SELF']."?check=all'>".$LANG["buttons"][18]."</a></td>";
		echo "<td>/</td><td align='center'><a onclick= \"if ( unMarkAllRows('show_ref_manu') ) return false;\" href='".$_SERVER['PHP_SELF']."?check=none'>".$LANG["buttons"][19]."</a>";
		echo "</td><td align='left' width='80%'>"; 
		echo "<input type='submit' name='delete_reference_manufacturer' value=\"" . $LANG['buttons'][6] . "\" class='submit' >";
		echo "&nbsp;";
		echo "<input type='submit' name='update_reference_manufacturer' value=\"" . $LANG['buttons'][7] . "\" class='submit' >";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
	}
	else
		echo "</table>";
	echo "</form>";
	echo "<form method='post' name='add_ref_manu' action=\"$target\">";
	echo "<table class='tab_cadre'>";
	echo "<input type='hidden' name='FK_reference' value='" . $ID . "'>";
	echo "<tr>";
	echo "<td class='tab_bg_1'>";
	echo "<td class='tab_bg_1'>"; 
	dropdownValue("glpi_enterprises","FK_enterprise","",1,$_SESSION["glpiactive_entity"],'',$suppliers); 
	echo "</td>";
	echo "<td class='tab_bg_1'>";
	autocompletionTextField("price", "glpi_plugin_order_references_manufacturers", "price", 0, 7);
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td class='tab_bg_1' align='center' colspan='3'>";
	echo "<input type='submit' name='add_reference_manufacturer' value=\"" . $LANG['buttons'][7] . "\" class='submit' >";
	echo "</td>";
	echo "</tr>";
	echo "</table></form>";	
	echo "</div>";
}
?>