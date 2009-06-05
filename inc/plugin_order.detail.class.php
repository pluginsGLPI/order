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
class plugin_order_detail extends CommonDBTM {
   function __construct () 
   {
      $this->table="glpi_plugin_order_detail";                
   }
	
	function showAddForm($target, $orderID)
	{
       global  $CFG_GLPI, $LANG,$DB;

		$order=new plugin_order();
		$canedit=$order->can($orderID,'w');

		if ($canedit)
		{
			echo "<form method='post' name='order_detail_form' id='order_detail_form'  action=\"".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.detail.form.php\">";
			echo "<input type='hidden' name='FK_order' value=\"$orderID\">";
	
			echo "<div class='center'>"; 
			echo"<table class='tab_cadre_fixe'>";
			echo "<tr><th colspan='7'>".$LANG['plugin_order']['detail'][17]."</th></tr>";
			echo "<tr>"; 
			echo "<th align='center'>".$LANG['common'][17]."</th>"; 
			echo "<th align='center'>".$LANG['plugin_order']['reference'][1]."</th>";
			echo "<th align='center'>".$LANG['plugin_order']['detail'][7]."</th>";
			echo "<th align='center'>".$LANG['plugin_order']['detail'][4]."</th>";
			echo "<th align='center'>".$LANG['plugin_order']['detail'][18]."</th>";
			echo "<th align='center'>".$LANG['plugin_order']['detail'][8]."*</th>";
			echo "<th></th>";
			echo"</tr>";
			echo "<tr>";
			echo "<td class='tab_bg_1'>";
			plugin_order_dropdownAllItems("type",true,0,$order->fields["ID"],$order->fields["FK_enterprise"],$order->fields["FK_entities"]);	
			echo "</td>";
			echo "<td class='tab_bg_1' align='center'><span id='show_reference'>&nbsp;</span></td>";
			echo "<td class='tab_bg_1' align='center'><span id='show_quantity'>&nbsp;</span></td>";
			echo "<td class='tab_bg_1' align='center'><span id='show_priceht'>&nbsp;</span></td>";
			echo "<td class='tab_bg_1' align='center'><span id='show_pricediscounted'>&nbsp;</span></td>";
			echo "<td  class='tab_bg_1' align='center'>";
			dropdownValue("glpi_dropdown_plugin_order_taxes","taxes",2);
			echo "</td>";
			echo "<td class='tab_bg_1' align='center'><span id='show_validate'>&nbsp;</span></td>";
			echo "</tr>";
			echo "</table></div></form>";
		}
			
	}
	
   function showFormDetail ($FK_order, $target, $mode) {
      global  $CFG_GLPI, $LANG,$DB;
		
			$query="	SELECT glpi_plugin_order_detail.ID AS IDD, glpi_plugin_order_references.ID AS IDR, 
								glpi_plugin_order_references.type, glpi_plugin_order_references.FK_manufacturer, glpi_plugin_order_references.name, 
								glpi_plugin_order_detail.price, glpi_plugin_order_detail.taxesprice, glpi_plugin_order_detail.reductedprice, 
								SUM(glpi_plugin_order_detail.reductedprice) AS totalprice
								FROM glpi_plugin_order_detail, glpi_plugin_order_references
								WHERE glpi_plugin_order_detail.FK_ref=glpi_plugin_order_references.ID
								AND glpi_plugin_order_detail.FK_order=$FK_order
								GROUP BY glpi_plugin_order_detail.FK_ref
								ORDER BY glpi_plugin_order_detail.ID";
			$result=$DB->query($query);
			$num=$DB->numrows($result);
			$rand=mt_rand();
			$plugin_order=new plugin_order();
			$canedit=$plugin_order->can($FK_order,'w');
			echo "<form method='post' name='order_detail_form$rand' id='order_detail_form$rand'  action=\"".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.detail.form.php\">";
			echo "<input type='hidden' name='FK_order' value=\"$FK_order\">";
			if ($num>0)
			{
				echo "<div class='center'><table class='tab_cadrehov'>";
				echo "<tr><th colspan='11'>".$LANG['plugin_order']['detail'][17].":</th></tr>";
				echo "<tr>";
				if($canedit && $mode==1)
					echo "<th></th>";
				echo "<th>".$LANG['plugin_order']['detail'][1]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][11]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][7]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][3]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][4]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][8]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][18]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][9]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][10]."</th></tr>";
				$i=0;
				while ($i<$num){
					$ID=$DB->result($result,$i,"IDD");
					$IDR=$DB->result($result,$i,"IDR");
					if(getDelivredQuantity($FK_order, $IDR)==getQuantity($FK_order, $IDR))
						echo "<tr class='tab_bg_2'>";
					else
						echo "<tr class='tab_bg_4'>";
					if ($canedit && $mode==1){
						echo "<td width='10'>";
						$sel="";
						if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
						echo "<input type='checkbox' name='' value='1' $sel>";
						echo "</td>";
					}
					/* type */
					$ci=new CommonItem();
					$ci->setType($DB->result($result,$i,"type"));
					echo "<td align='center'>".$ci->getType()."</td>";
					/* manufacturer */
					echo "<td align='center'>".getDropdownName("glpi_dropdown_manufacturer",$DB->result($result,$i,"FK_manufacturer"))."</td>";
					/* reference */
					echo "<td align='center'>".$DB->result($result,$i,"name")."</td>";
					/* quantity */
					echo "<td align='center'>".getQuantity($FK_order, $IDR)."</td>";	
					/* delivered quantity */
					echo "<td align='center'>".getDelivredQuantity($FK_order, $IDR)."</td>";	
					/*price */
					echo "<td align='center'>".$DB->result($result,$i,"price")."</td>";
					/* price with taxes */
					echo "<td align='center'>".$DB->result($result,$i,"taxesprice")."</td>";
					/* price with reduction */
					echo "<td align='center'>".$DB->result($result,$i,"reductedprice")."</td>";
					/* total price */
					echo "<td align='center'>".$DB->result($result,$i,"totalprice")."</td>";
					/* total price with taxes  */
					echo "<td align='center'>".sprintf("%01.2f", $DB->result($result,$i,"totalprice")*getTaxes($FK_order, $IDR))."</td>";
					$i++;
				}
				echo "</table>";
				if ($canedit && $mode==1) {
					echo "<div class='center'>";
					echo "<table width='80%' class='tab_glpi'>";
					echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('order_detail_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$FK_order&amp;select=all'>".$LANG['buttons'][18]."</a></td>";
					echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('order_detail_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$FK_order&amp;select=none'>".$LANG['buttons'][19]."</a>";
					echo "</td><td align='left' width='80%'>";
					echo "<input type='submit' name='delete' value=\"".$LANG['buttons'][6]."\" class='submit'>";
					echo "</td>";
					echo "</table>";
					echo "</div>";
				}	
			}
	}
}
?>