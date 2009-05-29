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
class plugin_order_reference extends CommonDBTM {
	function __construct () {
		$this->table="glpi_plugin_order_references";
		$this->type=PLUGIN_ORDER_REFERENCE_TYPE;
		$this->entity_assign=true;
		$this->may_be_recursive=true;
		$this->dohistory=true;
	}
	
	/*define header form */
	function defineTabs($ID,$withtemplate){
		global $LANG;
		/* principal */
		$ong[1]=$LANG['title'][26];
		if ($ID > 0 )
		{
			if (haveRight("document","r"))
				$ong[3]=$LANG['Menu'][27];
			$ong[12]=$LANG['title'][38];
		}

		return $ong;
	}

	function showForm ($target,$ID,$withtemplate='') {
		global  $CFG_GLPI, $LANG,$DB;

		if (!plugin_order_haveRight("order","r")) return false;
		$spotted = false;
		if ($ID>0){
			if($this->can($ID,'r')){
				$spotted = true;
			}
		}else{
			if($this->can(-1,'w')){
				$spotted = true;
				$this->getEmpty();
			}
		}
		if ($spotted){
			$this->showTabs($ID, $withtemplate,$_SESSION['glpi_tab']);
			$canedit=$this->can($ID,'w');
			$canrecu=$this->can($ID,'recursive');
			echo "<form method='post' name=form action=\"$target\">";
			if (empty($ID)||$ID<0){
					echo "<input type='hidden' name='FK_entities' value='".$_SESSION["glpiactive_entity"]."'>";
				}
			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			$this->showFormHeader($ID,'',1);
			echo "<tr class='tab_bg_2'><td>".$LANG['plugin_order']['reference'][1].": </td>";
			echo "<td>";
			autocompletionTextField("name","glpi_plugin_order_references","name",$this->fields["name"],30,$this->fields["FK_entities"]);	
			echo "</td></tr>";

			echo "<tr class='tab_bg_2'><td>".$LANG['common'][17].": </td>";
			echo "<td>";
			plugin_order_dropdownAllItems("type",$this->fields["type"]);
			echo "</td></tr>";

			echo "<tr class='tab_bg_2'><td>".$LANG['financial'][26].": </td>";
			echo "<td>";
			dropdownValue("glpi_enterprises","FK_enterprise",$this->fields["FK_enterprise"],1,$_SESSION["glpiactive_entity"]);
			echo "</td></tr>";
			
			if ($canedit) {
				if (empty($ID)||$ID<0){
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='2'>";
					echo "<div align='center'><input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" class='submit'></div>";
					echo "</td>";
					echo "</tr>";
				} else {
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='2'><div align='center'>";
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >";
					if ($this->fields["deleted"]=='0'){
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"".$LANG['buttons'][6]."\" class='submit'></div>";
					}else {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='restore' value=\"".$LANG['buttons'][21]."\" class='submit'>";
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"".$LANG['buttons'][22]."\" class='submit'></div>";
					}
					echo "</td>";
					echo "</tr>";
				}	
			}
			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";
		} else {
			echo "<div align='center'><b>".$LANG['plugin_order'][11]."</b></div>";
			return false;
		}
		return true;
	}
}
?>