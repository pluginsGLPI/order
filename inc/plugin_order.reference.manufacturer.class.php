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
class PluginOrderReferenceManufacturer extends CommonDBTM {
	function __construct() {
		$this->table = "glpi_plugin_order_references_manufacturers";
		$this->type = PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE;
		$this->entity_assign=true;
		$this->may_be_recursive=false;
		$this->dohistory=true;
	}

	function defineTabs($ID, $withtemplate) {
		global $LANG;
		/* principal */
		$ong[1] = $LANG['title'][26];
		if (haveRight("document", "r"))
			$ong[4] = $LANG['Menu'][27];
		$ong[12] = $LANG['title'][38];

		return $ong;
	}

	function showForm($target, $ID) {
		global $LANG, $DB, $CFG_GLPI, $INFOFORM_PAGES;

			$this->getFromDB($ID);
			$this->showTabs($ID, false, $_SESSION['glpi_tab'], array (), "FK_reference=".$this->fields["FK_reference"]);
			echo "<form method='post' name='show_ref_manu' id='show_ref_manu' action=\"$target\">";
			echo "<input type='hidden' name='FK_entities' value='".$this->fields["FK_entities"]."'>";
			echo "<input type='hidden' name='ID' value='" . $ID . "'>";
			echo "<input type='hidden' name='FK_reference' value='" . $this->fields["FK_reference"] . "'>";
			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			$this->showFormHeader($ID);

			echo "<tr><th>" . $LANG['financial'][26] . "</th><th>" . $LANG['plugin_order']['detail'][4] . "</th></tr>";
			echo "<td class='tab_bg_1' align='center'><a href=\"" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[ENTERPRISE_TYPE] . "?ID=" . $this->fields["FK_enterprise"] . "\">" . getDropdownName("glpi_enterprises", $this->fields["FK_enterprise"]) . "</a></td>";
			echo "<td class='tab_bg_1' align='center'>";
			autocompletionTextField("price", "glpi_plugin_order_references_manufacturers", "price", $this->fields["price"], 7);
			echo "</td></tr>";
			echo "</tr>";
			echo "<td class='tab_bg_1'align='center' colspan='2'>";
			echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit' >";
			echo "</td>";
			echo "</tr>";
			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";
		return true;
	}
}
?>