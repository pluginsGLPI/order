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
    
function plugin_order_showReceptionForm($FK_order) {
	global $DB, $CFG_GLPI, $LANG;
	
	$plugin_order=new plugin_order();
	$canedit=$plugin_order->can($FK_order,'w');
	$query="	SELECT glpi_plugin_order_detail.ID AS IDD, price, reductedprice, taxesprice, status, date, FK_manufacturer, name, type, FK_device
			FROM glpi_plugin_order_detail, glpi_plugin_order_references
			WHERE FK_order=$FK_order
			AND glpi_plugin_order_detail.FK_ref=glpi_plugin_order_references.ID
			ORDER BY glpi_plugin_order_detail.ID";
	$result=$DB->query($query);
	$num=$DB->numrows($result);
	$rand=mt_rand();
	echo "<form method='post' name='order_reception_form$rand' id='order_reception_form$rand'  action=\"".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.reception.form.php\">";
	echo "<div class='center'><table class='tab_cadrehov'>";
	if($num==0) 
		echo "<tr><th>".$LANG['plugin_order']['detail'][20]."</th></tr></table></div>";
	else {
		echo "<tr>";
		if($canedit)
			echo "<th></th>";
		echo "<th>".$LANG['plugin_order']['detail'][1]."</th>";
		echo "<th>".$LANG['plugin_order']['detail'][11]."</th>";
		echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
		echo "<th>".$LANG['plugin_order']['detail'][19]."</th>";
		echo "<th>".$LANG['plugin_order']['detail'][21]."</th>";
		echo "<th>".$LANG['plugin_order']['detail'][22]."</th></tr>";
		$i=0;
		while($i<$num) {
			$ID=$DB->result($result,$i,'IDD');
			echo "<tr class='tab_bg_2'>";
			if ($canedit){
						echo "<td width='10'>";
						$sel="";
						if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
						echo "<input type='checkbox' name='item[".$ID."]' value='1' $sel>";
						echo "</td>";
			}
			echo "<td align='center'>".getDetailType($ID)."</td>";
			echo "<td align='center'>".getDetailManufacturer($ID)."</td>";
			$name=$DB->result($result,$i,'name');
			$ref="<a href=".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.reference.form.php?ID=".$ID.">".$DB->result($result,$i,'name')."</a>";
			echo "<td align='center'>$ref</td>";
			echo "<td align='center'>";
			if($DB->result($result,$i,'status')==1) 
				echo "".$LANG['plugin_order']['status'][8]."";
			else
				echo "".$LANG['plugin_order']['status'][7]."";
			echo "</td>";
			echo "<td align='center'>".$DB->result($result,$i,'date')."</td>";
			echo "<td align='center'>".$DB->result($result,$i,'FK_device')."</td>";
			echo "<input type='hidden' name='ID[$i]' value='$ID'>";
			echo "<input type='hidden' name='name[$i]' value='$name'>";
			$i++;
		}
		echo "</table></div>";
		if($canedit) {
			echo "<div class='center'>";
			echo "<table width='80%' class='tab_glpi'>";
			echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('order_reception_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$FK_order&amp;select=all'>".$LANG['buttons'][18]."</a></td>";
		
			echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('order_reception_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$FK_order&amp;select=none'>".$LANG['buttons'][19]."</a>";
			echo "</td><td>";
			/*echo "<select name='action'>";
			echo "<option value='reception'>-----</option>";
			echo "<option value='reception'>".$LANG['plugin_order']['delivery'][2]."</option>";
			echo "<option value='reception'>".$LANG['plugin_order']['delivery'][3]."</option>";
			echo "</select>";*/
			echo "<input type='hidden' name='FK_order' value=\"$FK_order\">";
			echo "<input type='submit' name='reception' value=\"".$LANG['plugin_order']['delivery'][2]."\" class='submit'>";
			echo "</td>";
			echo "<td align='left' width='80%'>";
			echo "<input type='submit' name='showGeneration' value=\"".$LANG['plugin_order']['delivery'][3]."\" class='submit'>";
			echo "</td>";
			echo "</table>";
			echo "</div>";
		}
	}
	
	
}

function getDetailManufacturer($ID) {
	global $DB;
	$query=" SELECT glpi_plugin_order_detail.ID, FK_manufacturer
			FROM glpi_plugin_order_detail, glpi_plugin_order_references
			WHERE glpi_plugin_order_detail.ID=$ID
			AND glpi_plugin_order_detail.FK_ref=glpi_plugin_order_references.ID";
	$result=$DB->query($query);
	if ($DB->result($result,0,'FK_manufacturer') != NULL) {
		return(getDropdownName("glpi_dropdown_manufacturer", $DB->result($result,0,'FK_manufacturer')));
	}
	else
		return(-1);
}

function getDetailType($ID) {
	global $DB;
	$query=" SELECT glpi_plugin_order_detail.ID, type 
			FROM glpi_plugin_order_detail, glpi_plugin_order_references
			WHERE glpi_plugin_order_detail.ID=$ID
			AND glpi_plugin_order_detail.FK_ref=glpi_plugin_order_references.ID";
	$result=$DB->query($query);
	if ($DB->result($result,0,'type') != NULL) {
		$ci = new CommonItem();
		$ci->setType($DB->result($result,0,'type'));
		return($ci->getType());
	}
	else
		return(-1);
}

function generateAssociatedMaterial($type, $serial) {
	global $DB, $LINK_ID_TABLE;
	$query=" INSERT INTO".$LINK_ID_TABLE[$type]." (serial) 
			values ($serial)";
	$DB->query($query);
}
	
	
	
	
	
	
	
	
	
?>