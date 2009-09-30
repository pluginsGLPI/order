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
class PluginOrderDetail extends CommonDBTM {
   function __construct () 
   {
      $this->table="glpi_plugin_order_detail";                
   }
	
	/*clean order if items are deleted */
	function cleanItems($ID,$type) {
		global $DB;
		$query=" SELECT glpi_plugin_order_detail.ID AS detailID, glpi_plugin_order_references.ID
							FROM glpi_plugin_order_detail, glpi_plugin_order_references
							WHERE FK_reference=glpi_plugin_order_references.ID
							AND glpi_plugin_order_references.type=$type
							AND glpi_plugin_order_detail.FK_device=$ID";
		if($DB->query($query))
		{
			$result=$DB->query($query);
			if($DB->result($result,0,'detailID'))
				$detailID=$DB->result($result,0,'detailID');
		}
		$query=" UPDATE glpi_plugin_order_detail
							SET FK_device=0
							WHERE ID=$detailID";
		$DB->query($query);
		$query=" DELETE FROM glpi_plugin_order_detail
							WHERE FK_device = '$ID' 
							AND device_type= '$type'";
		$DB->query($query);
	}
	function showAddForm($target, $orderID)
	{
       global  $CFG_GLPI, $LANG,$DB;

		if (plugin_order_canUpdateOrder($orderID))
		{
			$order=new PluginOrder();
			$canedit=$order->can($orderID,'w');
	
			if ($canedit)
			{
				echo "<form method='post' name='order_detail_form' id='order_detail_form'  action=\"$target\">";
				echo "<input type='hidden' name='FK_order' value=\"$orderID\">";
				echo "<div class='center'>"; 
				echo"<table class='tab_cadrehov'>";
				echo "<tr><th colspan='7'>".$LANG['plugin_order']['detail'][5]."</th></tr>";
		
				if ($order->fields["FK_enterprise"])
				{
					echo "<tr>"; 
					echo "<th align='center'>".$LANG['common'][17]."</th>"; 
					echo "<th align='center'>".$LANG['plugin_order']['reference'][1]."</th>";
					echo "<th align='center'>".$LANG['plugin_order']['detail'][7]."</th>";
					echo "<th align='center'>".$LANG['plugin_order']['detail'][4]."</th>";
					echo "<th align='center'>".$LANG['plugin_order']['detail'][25]."</th>";
					echo "<th align='center'>".$LANG['plugin_order'][25]."</th>";
					echo "<th></th>";
					echo"</tr>";
					echo "<tr>";
					echo "<td class='tab_bg_1' align='center'>";
					plugin_order_dropdownAllItems("device_type", true, 0, $order->fields["ID"], $order->fields["FK_enterprise"], $order->fields["FK_entities"], $CFG_GLPI["root_doc"]."/plugins/order/ajax/detail.php",true);	
					echo "</td>";
					echo "<td class='tab_bg_1' align='center'><span id='show_reference'>&nbsp;</span></td>";
					echo "<td class='tab_bg_1' align='center'><span id='show_quantity'>&nbsp;</span></td>";
					echo "<td class='tab_bg_1' align='center'><span id='show_priceht'>&nbsp;</span></td>";
					echo "<td class='tab_bg_1' align='center'><span id='show_pricediscounted'>&nbsp;</span></td>";
					echo "<td  class='tab_bg_1' align='center'><span id='show_taxes'>&nbsp;</span></td>";
					echo "<td class='tab_bg_1' align='center'><span id='show_validate'>&nbsp;</span></td>";
					echo "</tr>";
				}
				else
					echo "<tr><td align='center'>".$LANG['plugin_order']['detail'][27]."</td></tr>";
		
				echo "</table></div></form>";
			}
		}
	}
	
   function showFormDetail ($target,$FK_order) {
      global  $CFG_GLPI, $LANG,$DB,$INFOFORM_PAGES;
		
			$query="SELECT glpi_plugin_order_detail.ID AS IDD, glpi_plugin_order_references.ID AS IDR, 
					glpi_plugin_order_references.type,glpi_plugin_order_references.FK_model, glpi_plugin_order_references.FK_glpi_enterprise, glpi_plugin_order_references.name, 
					glpi_plugin_order_detail.price_taxfree, glpi_plugin_order_detail.price_ati, glpi_plugin_order_detail.price_discounted, 
               glpi_plugin_order_detail.discount,
					SUM(glpi_plugin_order_detail.price_discounted) AS totalpriceHT, 
					SUM(glpi_plugin_order_detail.price_ati) AS totalpriceTTC 
					FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references`
					WHERE glpi_plugin_order_detail.FK_reference=glpi_plugin_order_references.ID
					AND glpi_plugin_order_detail.FK_order=$FK_order
					GROUP BY glpi_plugin_order_detail.FK_reference
					ORDER BY glpi_plugin_order_detail.ID";
			$result=$DB->query($query);
			$num=$DB->numrows($result);
			$rand=mt_rand();
			$plugin_order=new PluginOrder();
			$canedit=$plugin_order->can($FK_order,'w') && plugin_order_canUpdateOrder($FK_order);
			echo "<form method='post' name='order_detail_form$rand' id='order_detail_form$rand'  action=\"$target\">";
			echo "<input type='hidden' name='FK_order' value=\"$FK_order\">";
			if ($num>0)
			{
				echo "<div class='center'><table class='tab_cadrehov'>";
				echo "<tr><th colspan='12'>".$LANG['plugin_order']['detail'][17].":</th></tr>";
				echo "<tr>";
				if($canedit)
					echo "<th></th>";
				echo "<th>".$LANG['plugin_order']['detail'][1]."</th>";
				echo "<th>".$LANG['common'][5]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
				echo "<th>".$LANG['common'][22]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][7]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][3]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][4]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][18]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][25]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][9]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][10]."</th></tr>";
				$i=0;
				while ($i<$num){
					$ID=$DB->result($result,$i,"IDD");
					$IDR=$DB->result($result,$i,"IDR");
					if(plugin_order_getDelivredQuantity($FK_order, $IDR)==plugin_order_getQuantity($FK_order, $IDR))
						echo "<tr class='tab_bg_2'>";
					else
						echo "<tr class='tab_bg_1'>";
					if ($canedit){
						echo "<td width='10'>";
						$sel="";
						if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
						echo "<input type='checkbox' name='detail[".$DB->result($result,$i,"IDR")."]' value='1' $sel>";
						echo "</td>";
					}
					/* type */
					$ci=new CommonItem();
					$ci->setType($DB->result($result,$i,"type"));
					echo "<td align='center'>".$ci->getType()."</td>";
					/* manufacturer */
					echo "<td align='center'>".getDropdownName("glpi_dropdown_manufacturer",$DB->result($result,$i,"FK_glpi_enterprise"))."</td>";
					/* reference */
					echo "<td align='center'>";
					echo getReceptionReferenceLink($DB->result($result,$i,"IDR"), $DB->result($result,$i,"name"));
					echo "</td>";
					/* modele */
					echo "<td align='center'>";
					echo getDropdownName(plugin_order_getModelTable($DB->result($result,$i,"type")), $DB->result($result,$i,"FK_model"));
					echo "</td>";
					/* quantity */
					echo "<td align='center'>".plugin_order_getQuantity($FK_order, $IDR)."</td>";	
					/* delivered quantity */
					echo "<td align='center'>".plugin_order_getDelivredQuantity($FK_order, $IDR)."</td>";	
					/*price */
					echo "<td align='center'>".plugin_order_displayPrice($DB->result($result,$i,"price_taxfree"))."</td>";
					/* price with reduction */
					echo "<td align='center'>".plugin_order_displayPrice($DB->result($result,$i,"price_discounted"))."</td>";
					/* price with taxes */
					echo "<td align='center'>".formatNumber($DB->result($result,$i,"discount"))."</td>";
					/* total price */
					echo "<td align='center'>".plugin_order_displayPrice($DB->result($result,$i,"totalpriceHT"))."</td>";
					/* total price with taxes  */
					echo "<td align='center'>".plugin_order_displayPrice($DB->result($result,$i,"totalpriceTTC"))."</td>";
					$i++;
				}
				echo "</table>";

				if ($canedit) {
					echo "<div class='center'>";
					echo "<table width='80%' class='tab_glpi'>";
					echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('order_detail_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$FK_order&amp;select=all'>".$LANG['buttons'][18]."</a></td>";
					echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('order_detail_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$FK_order&amp;select=none'>".$LANG['buttons'][19]."</a>";
					echo "</td><td align='left' width='90%'>";
					echo "<input type='submit' onclick=\"return confirm('" . $LANG['plugin_order']['detail'][36] . "')\" name='delete_detail' value=\"".$LANG['buttons'][6]."\" class='submit'>";
					echo "</td>";
					echo "</table>";
					echo "</div>";
				}	
			}
	}

	function isDeviceLinkedToOrder($device_type, $deviceID) {
		global $DB;
		$query = "SELECT ID FROM `" . $this->table . "` WHERE `device_type`='$device_type' AND `FK_device`='$deviceID'";
      $result = $DB->query($query);
		if ($DB->numrows($result))
			return true;
		else
			return false;
	}

	function getOrderInfosByDeviceID($device_type, $deviceID) {
		global $DB;
		$query = "SELECT go.* FROM `glpi_plugin_order` AS go, `" . $this->table . "` AS god " .
		"WHERE go.ID=god.FK_order AND god.device_type=$device_type AND god.FK_device=$deviceID";
		$result = $DB->query($query);
		if ($DB->numrows($result))
			return $DB->fetch_array($result);
		else
			return false;
	}

}
?>