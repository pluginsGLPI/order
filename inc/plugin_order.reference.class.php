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
			$ong[2]=$LANG['Menu'][23];
			$ong[3]=$LANG['title'][37];
			if (haveRight("document","r"))
				$ong[4]=$LANG['Menu'][27];
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
			autocompletionTextField("name","glpi_plugin_order_references","name",$this->fields["name"],70,$this->fields["FK_entities"]);	
			echo "</td></tr>";

			echo "<tr class='tab_bg_2'><td>".$LANG['common'][5].": </td>";
			echo "<td>";
			dropdownValue("glpi_dropdown_manufacturer","FK_manufacturer",$this->fields["FK_manufacturer"]);
			echo "</td></tr>";

			$commonitem = new CommonItem;
			$commonitem->setType($this->fields["type"],true);

			echo "<tr class='tab_bg_2'><td>".$LANG['plugin_order']['reference'][4].": </td>";
			echo "<td>";
			if ($ID > 0)
				echo $commonitem->getType();				
			else
			{
				plugin_order_dropdownAllItems("type",
				true,
				$this->fields["type"],
				0,0,$_SESSION["glpiactive_entity"],$CFG_GLPI["root_doc"]."/plugins/order/ajax/reference.php");
				echo "<span id='show_reference'></span></td></tr>";
			}			
			
			$exclusion_types = array(0, CONSUMABLE_ITEM_TYPE, CARTRIDGE_ITEM_TYPE);
			echo "<tr class='tab_bg_2'><td>".$LANG['common'][17].": </td>";
			echo "<td><span id='show_type'>";
			if (!in_array($this->fields["type"], $exclusion_types) )
				dropdownValue(plugin_order_getTypeTable($this->fields["type"]), "FK_type",$this->fields["FK_type"]);
			echo "</span></td></tr>";
			echo "<tr class='tab_bg_2'><td>".$LANG['common'][22].": </td>";
			echo "<td><span id='show_model'>";
			if (!in_array($this->fields["type"], $exclusion_types) )
				dropdownValue(plugin_order_getModelTable($this->fields["type"]), "FK_model",$this->fields["FK_model"]);
			echo "</span></td></tr>";

			echo "<tr class='tab_bg_2'><td>".$LANG['common'][13].": </td>";
			echo "<td><span id='show_template'>";
			if (!in_array($this->fields["type"], $exclusion_types) )
				plugin_order_dropdownTemplate("template", $this->fields["FK_entities"], $commonitem->obj->table,$this->fields["template"]);
			echo "</span></td></tr>";

			echo "<tr class='tab_bg_2'><td>".$LANG['common'][25].": </td>";
			echo "<td colspan='3'><textarea cols='50' rows='4' name='comments' >".$this->fields["comments"]."</textarea>";
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