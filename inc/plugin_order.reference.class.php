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
class PluginOrderReference extends CommonDBTM {
	function __construct() {
		$this->table = "glpi_plugin_order_references";
		$this->type = PLUGIN_ORDER_REFERENCE_TYPE;
		$this->entity_assign = true;
		$this->may_be_recursive = true;
		$this->dohistory = true;
	}

	/*define header form */
	function defineTabs($ID, $withtemplate) {
		global $LANG;
		/* principal */
		$ong[1] = $LANG['title'][26];
		if ($ID > 0) {
			$ong[3] = $LANG['title'][37];
			if (haveRight("document", "r"))
				$ong[4] = $LANG['Menu'][27];
			$ong[12] = $LANG['title'][38];
		}

		return $ong;
	}

	function prepareInputForAdd($params)
	{
		global $DB,$LANG;

		if (!isset($params["name"]) || $params["name"] == '')
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][8], false, ERROR);
			return false;
		}
		
		if (!$params["type"])
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][9], false, ERROR);
			return false;
		}
		
		$query = "SELECT COUNT(*) as cpt FROM `".$this->table."` " .
				 "WHERE name='".$params["name"]."' AND FK_entities=".$params["FK_entities"];
		$result = $DB->query($query);
		if ($DB->result($result,0,"cpt") > 0)
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][6],false,ERROR);
			return false;
		}
		else
			return $params;		
	}
	
	function pre_deleteItem($params)
	{
		global $LANG;
		if (!$this->referenceInUse())
			return $params;
		else
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][7],true,ERROR);
			return false;	
		}
			
	}
	
	function referenceInUse()
	{
		global $DB;
		$query = "SELECT COUNT(*) as cpt FROM `glpi_plugin_order_detail` " .
				"WHERE FK_reference=".$this->fields["ID"];
		$result = $DB->query($query);
		if ($DB->result($result,0,"cpt") > 0)
			return true;
		else
			return false;	
	}
	
	function canDelete()
	{
		return (!$this->referenceInUse());
	}
	
	function isRestrictedType()
	{
		global $ORDER_RESTRICTED_TYPES;
		return (in_array($this->fields["type"],$ORDER_RESTRICTED_TYPES));
	}
	
	/**
	 * Print a good title for user pages
	 *
	 *@return nothing (display)
	 **/
	function title() {
		global $LANG, $CFG_GLPI;
		displayTitle($CFG_GLPI["root_doc"] . "/plugins/order/pics/reference-icon.png", $LANG['plugin_order']['reference'][1], $LANG['plugin_order']['reference'][1]);
	}

	function showForm($target, $ID, $withtemplate = '') {
		global $CFG_GLPI, $LANG, $DB;

		if (!plugin_order_haveRight("reference", "r"))
			return false;
		$spotted = false;
		if ($ID > 0) {
			if ($this->can($ID, 'r')) {
				$spotted = true;
			}
		} else {
			if ($this->can(-1, 'w')) {
				$spotted = true;
				$this->getEmpty();
			}
		}
		
		$canedit = plugin_order_haveRight("reference", "w");
		$reference_in_use = (!$ID?false:$this->referenceInUse());
		
		if ($spotted) {
			$this->showTabs($ID, $withtemplate, $_SESSION['glpi_tab']);
			$canedit = $this->can($ID, 'w');
			$canrecu = $this->can($ID, 'recursive');
			echo "<form method='post' name=form action=\"$target\">";
			if (empty ($ID) || $ID < 0) {
				echo "<input type='hidden' name='FK_entities' value='" . $_SESSION["glpiactive_entity"] . "'>";
			}
			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			$this->showFormHeader($ID, '', 1);
			echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order']['reference'][1] . ": </td>";
			echo "<td>";
			if ($canedit && !$reference_in_use)
				autocompletionTextField("name", "glpi_plugin_order_references", "name", $this->fields["name"], 70, $this->fields["FK_entities"]);
			else
				echo $this->fields["name"];	
			echo "</td></tr>";

			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][5] . ": </td>";
			echo "<td>";
			if ($canedit && !$reference_in_use)
				dropdownValue("glpi_dropdown_manufacturer", "FK_manufacturer", $this->fields["FK_manufacturer"]);
			else
				echo getDropdownName("glpi_dropdown_manufacturer",$this->fields["FK_manufacturer"]);	
			echo "</td></tr>";

			$commonitem = new CommonItem;
			$commonitem->setType($this->fields["type"], true);

			echo "<tr class='tab_bg_2'><td>" . $LANG['state'][6] . ": </td>";
			echo "<td>";
			if ($ID > 0)
				echo $commonitem->getType();
			else {
				plugin_order_dropdownAllItems("type", true, $this->fields["type"], 0, 0, $_SESSION["glpiactive_entity"], $CFG_GLPI["root_doc"] .
				"/plugins/order/ajax/reference.php");
				echo "<span id='show_reference'></span></td></tr>";
			}

			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][17] . ": </td>";
			echo "<td><span id='show_type'>";
			if ($canedit && !$this->isRestrictedType() && !$reference_in_use )
					dropdownValue(plugin_order_getTypeTable($this->fields["type"]), "FK_type", $this->fields["FK_type"]);
				else
					echo getDropdownName(plugin_order_getTypeTable($this->fields["type"]), $this->fields["FK_type"]);
			

			echo "</span></td></tr>";
			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][22] . ": </td>";
			echo "<td><span id='show_model'>";
			if ($canedit && !$this->isRestrictedType() && !$reference_in_use ) 
					dropdownValue(plugin_order_getModelTable($this->fields["type"]), "FK_model", $this->fields["FK_model"]);
				else
					echo getDropdownName(plugin_order_getModelTable($this->fields["type"]), $this->fields["FK_model"]);
			

			echo "</span></td></tr>";

			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][13] . ": </td>";
			echo "<td><span id='show_template'>";
			if ($canedit && !$this->isRestrictedType() && !$reference_in_use )
					plugin_order_dropdownTemplate("template", $this->fields["FK_entities"], $commonitem->obj->table, $this->fields["template"]);
				else
					echo plugin_order_getTemplateName($this->fields["type"], $this->fields["template"]);

			echo "</span></td></tr>";

			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][25] . ": </td>";
			
			echo "<td colspan='3'>";
			if ($canedit)
				echo "<textarea cols='50' rows='4' name='comments' >" . $this->fields["comments"] . "</textarea>";
			else
				echo $this->fields["comments"];
			echo "</td></tr>";

			if ($canedit) {
				if (empty ($ID) || $ID < 0) {
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='2'>";
					echo "<div align='center'><input type='submit' name='add' value=\"" . $LANG['buttons'][8] . "\" class='submit'></div>";
					echo "</td>";
					echo "</tr>";
				} else {
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='2'><div align='center'>";
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit' >";
					if (!$this->referenceInUse())
					{
						if ($this->fields["deleted"] == '0') {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . "\" class='submit'></div>";
						} else {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='restore' value=\"" . $LANG['buttons'][21] . "\" class='submit'>";
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"" . $LANG['buttons'][22] . "\" class='submit'></div>";
						}
					}
					echo "</td>";
					echo "</tr>";
				}
			}
			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";
		} else {
			echo "<div align='center'><b>" . $LANG['plugin_order'][11] . "</b></div>";
			return false;
		}
		return true;
	}
}
?>