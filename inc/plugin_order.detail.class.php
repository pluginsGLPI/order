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
        
        function showFormDetail ($FK_order, $target, $mode) {
                GLOBAL  $CFG_GLPI, $LANG,$DB;
			
                        $query=" SELECT * FROM glpi_plugin_order_detail WHERE FK_order=$FK_order";
                        $result=$DB->query($query);
                        $num=$DB->numrows($result);
                        $rand=mt_rand();
                        $plugin_order=new plugin_order();
                        $canedit=$plugin_order->can($FK_order,'w');

				echo "<form method='post' name='order_detail_form$rand' id='order_detail_form$rand'  action=\"".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order_detail.form.php\">";
				echo "<input type='hidden' name='FK_order' value=\"$FK_order\">";
                        if ($num>0)
                        {
                                echo "<div class='center'><table class='tab_cadrehov'>";
                                echo "<tr><th colspan='10'>".$LANG['plugin_order']['detail'][17].":</th></tr>";
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
                                echo "<th>".$LANG['plugin_order']['detail'][9]."</th>";
                                echo "<th>".$LANG['plugin_order']['detail'][10]."</th></tr>";
                                $i=0;
                                while ($i<$num){
                                        /* type */
                                        $type=$DB->result($result, $i, "type");
                                        /* reference */
                                        $ref=$DB->result($result,$i,"reference");
                                        /* quantity */
                                        $quantity=$DB->result($result,$i,"quantity");
                                        /* delivred quantity */
                                        $delivredquantity=$DB->result($result,$i,"delivredquantity");
                                        /* unit price */
                                        $unitprice=$DB->result($result,$i,"unitprice");
                                        /* taxes */
                                        $idtaxes=$DB->result($result,$i,"taxes");
                                        $query=" SELECT * FROM glpi_dropdown_plugin_order_taxes WHERE ID=$idtaxes";
                                        $res=$DB->query($query);
                                        $taxes=$DB->result($res,0,"name");
                                        $vtaxes=$DB->result($res,0,"value");
                                        /* manufacturer */
                                        $idman=$DB->result($result,$i,"manufacturer");
                                        $query=" SELECT * FROM glpi_dropdown_manufacturer WHERE ID=$idman";
                                        $res=$DB->query($query);
                                        $manufacturer=$DB->result($res,0,"name");
                                        /* total price (without taxes) */
                                        $totalprice=$quantity*$unitprice;
                                        $totalprice=number_format($totalprice, 2);
                                        /* total price (with taxes) */
                                        $totalpricet=$totalprice*$vtaxes;
                                        $totalpricet=number_format($totalpricet, 2);
                                        /* idd */
                                        $idd=$DB->result($result,$i,"ID");
                                        $this->getFromDB($idd);
                                        if($delivredquantity==$quantity)
                                                echo "<tr class='tab_bg_2'>";
                                        else
                                                echo "<tr class='tab_bg_4'>";
                                        if ($canedit && $mode==1){
                                                 echo "<td width='10'>";
                                                $sel="";
                                                if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
                                                echo "<input type='checkbox' name='item[".$this->fields["ID"]."]' value='1' $sel>";
                                                echo "</td>";
                                        }
                                        $ci=new CommonItem();
                                        $ci->setType($type);
                                        echo "<td align='center'>".$ci->getType()."</td>";
                                        echo "<td align='center'>".$manufacturer."</td>";
                                        echo "<td align='center'>".$ref."</td>";
                                        echo "<td align='center'>".$quantity."</td>";
                                        echo "<td align='center'>".$delivredquantity."</td>";
                                        echo "<td align='center'>".$unitprice."</td>";
                                        echo "<td align='center'>".$taxes."</td>";
                                        echo "<td align='center'>".$totalprice."</td>";
                                        echo "<td align='center'>".$totalpricet."</td></tr>";
                                        $i++;
                                }
				echo "</table>";
				if ($canedit && $mode==1){
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
			if ($canedit && $mode==1){
				/* table creation */
				echo "<table class='tab_cadre_fixe' cellpadding='1'>";
				/* title */
				echo "<tr><th colspan='7' align='center'>".$LANG['plugin_order']['detail'][5].":</th></tr>";
				echo "<tr><th align='center'>".$LANG['plugin_order']['detail'][1]."</th>";
				echo "<th align='center'>".$LANG['plugin_order']['detail'][11]."</th>";
				echo "<th align='center'>".$LANG['plugin_order']['detail'][2]."</th>";
				echo "<th align='center'>".$LANG['plugin_order']['detail'][7]."*</th>";
				echo "<th align='center'>".$LANG['plugin_order']['detail'][4]."*</th>";
				echo "<th align='center'>".$LANG['plugin_order']['detail'][8]."*</th>";
				echo "<th align='center'>&nbsp;</th></tr>";
				/* type */
				echo "<tr class='tab_bg_2'><td align='center'>";
				$types[]=COMPUTER_TYPE;
				$types[]=NETWORKING_TYPE;
				$types[]=PRINTER_TYPE;
				$types[]=MONITOR_TYPE;
				$types[]=PERIPHERAL_TYPE;
				$types[]=CARTRIDGE_ITEM_TYPE;
				$types[]=CONSUMABLE_ITEM_TYPE;
				 plugin_order_dropdownAllItems("type",0,'',-1,$types); 
				echo "</td>";
				/* manufacturer */
				echo "<td align='center'>";
				dropdownValue("glpi_dropdown_manufacturer", "manufacturer", 0);
				echo "</td>";
				/* reference */
				echo "<td align='center'>";
				autocompletionTextField("reference","glpi_plugin_order_detail","reference",'',15);
				echo "</td>";
				/* quantity */
				echo "<td align='center'>";
				autocompletionTextField("quantity","glpi_plugin_order_detail","quantity",'',15);
				echo "</td>";
				/* unit price */
				echo "<td align='center'>";
				autocompletionTextField("unitprice","glpi_plugin_order_detail","unitprice",'',15);
				echo "</td align='center'>";
				/* taxes */
				echo "<td align='center' width=10%>";
				dropdownValue("glpi_dropdown_plugin_order_taxes","taxes",2);
				echo "</td>";
				echo "<td>";
				echo "<input type='submit' name='add' value=\"".$LANG['plugin_order']['detail'][6]."\" class='submit' >";
				echo "</td></tr>";
				echo "</table>";
			}	
		}
	}
?>