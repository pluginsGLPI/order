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
class PluginOrderConfig extends CommonDBTM {
	function __construct () {
		$this->table="glpi_plugin_order_config";
	}

	function showForm($target)
	{
		global $LANG;
		$this->getFromDB(1);
		echo "<div class='center'>";
		echo "<table class='tab_cadre_fixe'>";
		echo "<tr><th colspan='2'>".$LANG['plugin_order'][38]."</th></tr>";
		echo "<form method='post' name=form action='$target'>";
		
		echo "<input type='hidden' name='ID' value='1'>";
		echo "<tr class='tab_bg_1' align='center'><td>".$LANG['plugin_order']['config'][1]."</td><td>";
		dropdownValue("glpi_dropdown_plugin_order_taxes","default_taxes",$this->fields["default_taxes"]);
		echo "</td>";
		echo "</tr>";
		
		echo "<tr class='tab_bg_1' align='center'><td>".$LANG['plugin_order']['config'][2]."</td><td>";
		dropdownYesNo("use_validation",$this->fields["use_validation"]);
		echo "</td>";
		echo "</tr>";
		
		echo "<tr class='tab_bg_1' align='center'>"; 
		echo "<td colspan='2' align='center'>"; 
		echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >"; 
		echo"</td>";
		echo "</tr>";
		
		echo "</table></form></div>";
	}
	
	function showOrderGenerationForm($target)
	{
		global $LANG;
		echo "<br><table class='tab_cadre_fixe'>";
		echo "<tr><th>".$LANG['plugin_order'][37]."</th></tr>";
		echo "<tr class='tab_bg_1' align='center'><td><a href='$target?action=createorder'>".$LANG['plugin_order'][34]."</a></td></tr>";
		echo "<tr class='tab_bg_1' align='center'><td><a href='$target?action=deleteorder'>".$LANG['plugin_order'][35]."</a></td></tr>";
		echo "</table>";
	}

}