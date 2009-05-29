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
class plugin_order extends CommonDBTM {
	function __construct () {
		$this->table="glpi_plugin_order";
		$this->type=PLUGIN_ORDER_TYPE;
		$this->entity_assign=true;
		$this->may_be_recursive=true;
		$this->dohistory=true;
	}
	
	/*clean if order are deleted */
	function cleanDBonPurge($ID) {
		global $DB;

		$query = "DELETE FROM glpi_plugin_order_device 
						WHERE FK_order = '$ID'";
		$DB->query($query);
		$query = "DELETE FROM glpi_doc_device 
						WHERE FK_device = '$ID' 
						AND device_type= '".PLUGIN_ORDER_TYPE."' ";
		$DB->query($query);
		$query=	"DELETE FROM glpi_plugin_order_detail
						WHERE FK_order='$ID'";
		$DB->query($query);
	}
	
	/*clean order if items are deleted */
	function cleanItems($ID,$type) {
		global $DB;
		
		$query = "DELETE FROM glpi_plugin_order_device
						WHERE FK_device = '$ID' 
						AND device_type= '$type'";
		$DB->query($query);
	}
	
	/*define header form */
	function defineTabs($ID,$withtemplate){
		global $LANG;
		/* principal */
		$ong[1]=$LANG['title'][26];
		if ($ID > 0){
			$plugin = new Plugin();
			/* detail */
			$ong[4]=$LANG['plugin_order']['detail'][0];
			/* delivery */
			$ong[5]=$LANG['plugin_order']['delivery'][1];
			/* item */
			$ong[2]=$LANG['plugin_order']['item'][0];
			/* documents */
			if (haveRight("document","r"))
				$ong[3]=$LANG['Menu'][27];
			/* all */
			$ong[12]=$LANG['title'][38];
		}
		return $ong;
	}

	function showForm ($target,$ID,$withtemplate='') {
		GLOBAL  $CFG_GLPI, $LANG,$DB;

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
			$this->showFormHeader($ID,'',2);
			echo "<tr><td class='tab_bg_1' valign='top'>";
	
			echo "<table cellpadding='2' cellspacing='2' border='0'>\n";
			
			/* num order */
			echo "<tr><td>".$LANG['plugin_order'][0]."*: </td>";
			echo "<td>";
			if ($canedit)
				autocompletionTextField("name","glpi_plugin_order","name",$this->fields["name"],30,$this->fields["FK_entities"]);	
			else
				echo "".$this->fields["name"]."";
			echo "</td></tr>";
			
			/* num order supplier */
			echo "<tr><td>".$LANG['plugin_order'][31].": </td><td>";
			if ($canedit)
				autocompletionTextField("numordersupplier","glpi_plugin_order","numordersupplier",$this->fields["numordersupplier"],30,$this->fields["FK_entities"]);	
			else
				echo "".$this->fields["numordersupplier"]."";
			echo "</td></tr>";
			
			/* number of bill */
			echo "<tr><td>".$LANG['plugin_order'][28].": </td><td>";
			if ($canedit)
				autocompletionTextField("numbill","glpi_plugin_order","numbill",$this->fields["numbill"],30,$this->fields["FK_entities"]);	
			else
				echo "".$this->fields["numbill"]."";
			echo "</td></tr>";
			
			/* delivery number */
			echo "<tr><td>".$LANG['plugin_order'][12].": </td>";
			echo "<td>";
			if ($canedit)
				autocompletionTextField("deliverynum","glpi_plugin_order","deliverynum",$this->fields["deliverynum"],30,$this->fields["FK_entities"]);	
			else
				echo "".$this->fields["deliverynum"]."";
			echo "</td></tr>";
			
			echo "</table>";
			echo "</td>";	
			echo "<td class='tab_bg_1' valign='top'>";
			echo "<table cellpadding='2' cellspacing='2' border='0'>";
			
			/* date of order */
			$editcalendar=($withtemplate!=2);
			echo "<tr><td>".$LANG['plugin_order'][1] ."*:	</td><td>";
			if ($canedit)
				if($this->fields["date"]==NULL)
					showDateFormItem("date",date("Y-m-d"),true,$editcalendar);
				else
					showDateFormItem("date",$this->fields["date"],true,$editcalendar);
			else
				echo "".$this->fields["date"]."";
			echo "</td>";
			echo "</tr>";
			
			/* budget */
			echo "<tr><td>".$LANG['plugin_order'][3].": </td><td>";
			if ($canedit)
				dropdownValue("glpi_dropdown_budget", "budget", $this->fields["budget"],1,$this->fields["FK_entities"]);
			else
				echo getdropdownname("glpi_dropdown_budget",$this->fields["budget"]);
			echo "</td></tr>";
			
			/* payment */
			echo "<tr><td>".$LANG['plugin_order'][32].": </td><td>";
			if ($canedit)
				dropdownValue("glpi_dropdown_plugin_order_payment","payment",$this->fields["payment"],1,$this->fields["FK_entities"]);
			else
				echo getdropdownname("glpi_dropdown_plugin_order_payment",$this->fields["payment"]);
			echo "</td>";
			echo "</tr>";
			
			/* supplier of order */
			echo "<tr><td>".$LANG['plugin_order']['setup'][14].": </td>";
			echo "<td>";
			if ($canedit)
				dropdownValue("glpi_enterprises","FK_enterprise",$this->fields["FK_enterprise"],1,$this->fields["FK_entities"]);
			else
			echo getdropdownname("glpi_enterprises",$this->fields["FK_enterprise"]);
			echo "</td></tr>";
			
			echo "</table>";
			echo "</td>";	
			echo "<td class='tab_bg_1' valign='top'>";
			echo "<table cellpadding='2' cellspacing='2' border='0'>";
			
			/* total price (without taxes) */
			if($this->fields["price"]!=NULL){
			echo "<tr><td>".$LANG['plugin_order'][13].": </td>";
			echo "<td>";
			echo "".$this->fields["price"]."&euro;";
			echo "</td></tr>";
			}
		
			/* total price (without taxes) */
			if(!empty($this->fields["price"])){
				$query=" SELECT sum(totalpricetaxes) AS sum FROM glpi_plugin_order_detail 
					WHERE FK_order=$ID";
				$result=$DB->query($query);
				$price=$DB->result($result,0,'sum');
				echo "<tr><td>".$LANG['plugin_order'][14].": </td>";
				echo "<td>";
				if($price!=NULL)
					echo "".$price."&euro;";
				else
					echo "0.00&euro;";
				echo "</td></tr>";		
			}
			
			/* status */
			if($this->fields["status"]!=NULL){
			echo "<td valign='top'>".$LANG['plugin_order']['status'][0].": </td>";
			echo "<td valign='top'>";
			$query=" SELECT name FROM glpi_dropdown_plugin_order_status WHERE ID=".$this->fields["status"]."";
			$result=$DB->query($query);
			$status=$DB->result($result,0,'name');
			echo "".$status."";
			echo "</td></tr>";
			}
			
			echo "</table>";
	
			echo "</td></tr>";
			
			echo "<tr><td class='tab_bg_1' valign='top' colspan='3'>";
			//comments of order
			echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
			echo $LANG['plugin_order'][2].":	</td>";
			echo "<td><textarea cols='50' rows='4' name='comment' >".$this->fields["comment"]."</textarea>";
			echo "</td>";
			echo "</table>";
			
			echo "</td>";
			echo "</tr>";
			
			if ($canedit) {
				if (empty($ID)||$ID<0){
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='3'>";
					echo "<div align='center'><input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" class='submit'></div>";
					echo "</td>";
					echo "</tr>";
				} else {
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='3'><div align='center'>";
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