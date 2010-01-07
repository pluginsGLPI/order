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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderSupplier extends CommonDBTM {

	function __construct() {
		$this->table = "glpi_plugin_order_suppliers";
		$this->type = PLUGIN_ORDER_SUPPLIER_TYPE;
	}
   
   function getFromDBByOrder($FK_Order) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->table."`
					WHERE `FK_Order` = '" . $FK_Order . "' ";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result) != 1) {
				return false;
			}
			$this->fields = $DB->fetch_assoc($result);
			if (is_array($this->fields) && count($this->fields)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	function showForm($target, $ID) {
		global $LANG, $DB, $CFG_GLPI, $INFOFORM_PAGES;

      $canedit = plugin_order_haveRight("order","w");
      
      $supplierid = -1;
      if($this->getFromDBByOrder($ID))
         $supplierid = $this->fields["ID"];

		if ($ID > 0 & $supplierid > 0) {
			$this->check($supplierid,'r');
		} else {
			// Create item 
			$this->check(-1,'w');
			$this->getEmpty();
			$supplierid=0;
		} 

      echo "<form method='post' name='show_supplier' id='show_supplier' action=\"$target\">";
      echo "<input type='hidden' name='ID' value='" . $supplierid . "'>";
      echo "<input type='hidden' name='FK_order' value='" . $ID . "'>";
      
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th></th>";
      echo "<th><div align='left'>";
      if ($supplierid>0){
         echo " ID $supplierid:";
      }		
      echo "</div></th></tr>";

      /* number of bill */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][30] . ": </td><td>";
      if ($canedit)
         autocompletionTextField("numquote", "glpi_plugin_order_suppliers", "numquote", $this->fields["numquote"], 30, $_SESSION["glpiactive_entity"]);
      else
         echo $this->fields["numbill"];
      echo "</td>";
		echo "</tr>";
		
      /* num order supplier */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][31] . ": </td><td>";
      if ($canedit)
         autocompletionTextField("numorder", "glpi_plugin_order_suppliers", "numorder", $this->fields["numorder"], 30, $_SESSION["glpiactive_entity"]);
      else
         echo $this->fields["numorder"];
      echo "</td>";
      echo "</tr>";
      
      /* number of bill */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][28] . ": </td><td>";
      if ($canedit)
         autocompletionTextField("numbill", "glpi_plugin_order_suppliers", "numbill", $this->fields["numbill"], 30, $_SESSION["glpiactive_entity"]);
      else
         echo $this->fields["numbill"];
      echo "</td>";
		echo "</tr>";
		
		if ($canedit) {
			
         if (empty($supplierid)||$supplierid<0){

            echo "<tr>";
            echo "<td class='tab_bg_2' valign='top' colspan='3'>";
            echo "<div align='center'><input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" class='submit'></div>";
            echo "</td>";
            echo "</tr>";
   
         } else {
   
            echo "<tr>";
            echo "<td class='tab_bg_2' valign='top' colspan='3'><div align='center'>";
            echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"".$LANG['buttons'][6]."\" class='submit'></div>";
            echo "</td>";
            echo "</tr>";
   
         }
      }
      echo "</table></div></form>";

		return true;
	}
}

?>