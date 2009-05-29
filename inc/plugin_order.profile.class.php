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
class plugin_order_Profile extends CommonDBTM {
	function __construct () {
		$this->table="glpi_plugin_order_profiles";
		$this->type=-1;
	}
	/* if profile deleted */
	function cleanProfiles($ID) {
		global $DB;
		
		$query = "DELETE 
					FROM glpi_plugin_order_profiles 
					WHERE ID='$ID' ";
		$DB->query($query);
	}
	
	/* profiles modification */
	function showForm($target,$ID){
		global $LANG;

		if (!haveRight("profile","r")) return false;
		$canedit=haveRight("profile","w");
		if ($ID){
			$this->getFromDB($ID);
		}
		echo "<form action='".$target."' method='post'>";
		echo "<table class='tab_cadre_fixe'>";
		echo "<tr><th colspan='2' align='center'><strong>".$LANG['plugin_order']['profile'][0]." ".$this->fields["name"]."</strong></th></tr>";
		echo "<tr class='tab_bg_2'>";
		echo "<td>".$LANG['plugin_order']['profile'][1].":</td><td>";
		dropdownNoneReadWrite("order",$this->fields["order"],1,1,1);
		echo "</td>";
		echo "</tr>";

		echo "<tr class='tab_bg_2'>";
		echo "<td>".$LANG['plugin_order']['detail'][2].":</td><td>";
		dropdownNoneReadWrite("reference",$this->fields["reference"],1,1,1);
		echo "</td>";
		echo "</tr>";

		if ($canedit){
			echo "<tr class='tab_bg_1'>";
			echo "<td align='center' colspan='2'>";
			echo "<input type='hidden' name='ID' value=$ID>";
			echo "<input type='submit' name='update_user_profile' value=\"".$LANG['buttons'][7]."\" class='submit'>";
			echo "</td></tr>";
		}
		echo "</table></form>";
	}
}
?>