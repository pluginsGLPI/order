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

class PluginOrderDetail extends CommonDBTM {

   function __construct () 
   {
      $this->table="glpi_plugin_order_detail";                
   }
	
	/*clean order if items are deleted */
	function cleanItems($ID,$type) {
		global $DB;
		
      $query=" DELETE FROM `".$this->table."`
               WHERE `FK_device` = '$ID' 
               AND `device_type` = '$type' ";
      $DB->query($query);
 
	}
	
	function getPricesATI($priceHT, $taxes) {
      if (!$priceHT)
         return 0;
      else
         return $priceHT + (($priceHT * $taxes) / 100);
   }
   
   function checkIFReferenceExistsInOrder($orderID, $referenceID) {
      global $DB;
      
      $query = "SELECT `ID` 
               FROM `".$this->table."` 
               WHERE `FK_order` = '$orderID' 
               AND `FK_reference` = '$referenceID' ";
      $result = $DB->query($query);
      if ($DB->numrows($result))
         return true;
      else
         return false;
   }

   function addDetails($referenceID, $device_type, $orderID, $quantity, $price, $discounted_price, $taxes) {
      global $LANG;
      
      //if ($this->checkIFReferenceExistsInOrder($orderID, $referenceID))
         //addMessageAfterRedirect($LANG['plugin_order']['detail'][28], false, ERROR);
      //else {
      if ($quantity > 0) {
         for ($i = 0; $i < $quantity; $i++) {
            $input["FK_order"] = $orderID;
            $input["FK_reference"] = $referenceID;
            $input["device_type"] = $device_type;
            $input["price_taxfree"] = $price;
            $input["price_discounted"] = $price - ($price * ($discounted_price / 100));
            $input["status"] = ORDER_STATUS_DRAFT;
            $input["price_ati"] = $this->getPricesATI($input["price_discounted"], getDropdownName("glpi_dropdown_plugin_order_taxes", $taxes));
            $input["deliverynum"] = "";
            $input["discount"] = $discounted_price;
            
            $this->add($input);
         }
      }
      //}
   }
   
   function deleteDetails($ID) {
      global $DB;

      $query = " DELETE FROM `".$this->table."`
                  WHERE `ID` = '$ID' ";
      $DB->query($query);
   }
	
	/* show details of orders */
   function showDetail($target, $ID) {

      $this->showFormDetail($target, $ID);
      $this->showAddForm($target, $ID);
   }

	function showAddForm($target, $orderID){
      global  $CFG_GLPI, $LANG,$DB;
      
      $order=new PluginOrder();
      $reference=new PluginOrderReference();
      
		if ($order->canUpdateOrder($orderID))
		{
			
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
					$reference->dropdownAllItems("device_type", true, 0, $order->fields["ID"], $order->fields["FK_enterprise"], $order->fields["FK_entities"], $CFG_GLPI["root_doc"]."/plugins/order/ajax/detail.php",true);	
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
      global  $CFG_GLPI, $LANG,$DB,$INFOFORM_PAGES,$ORDER_MODEL_TABLES,$ORDER_TYPE_TABLES;
		
			$query="SELECT `".$this->table."`.`ID` AS IDD, `glpi_plugin_order_references`.`ID`, 
					`glpi_plugin_order_references`.`type`,`glpi_plugin_order_references`.`FK_type`,`glpi_plugin_order_references`.`FK_model`, `glpi_plugin_order_references`.`FK_glpi_enterprise`, `glpi_plugin_order_references`.`name`, 
					`".$this->table."`.`price_taxfree`, `".$this->table."`.`price_ati`, `".$this->table."`.`price_discounted`, 
               `".$this->table."`.`discount`,
					`".$this->table."`.`price_discounted` AS totalpriceHT, 
					`".$this->table."`.`price_ati` AS totalpriceTTC 
					FROM `".$this->table."`, `glpi_plugin_order_references`
					WHERE `".$this->table."`.`FK_reference` = `glpi_plugin_order_references`.`ID`
					AND `".$this->table."`.`FK_order` = '$FK_order'
					ORDER BY `glpi_plugin_order_references`.`name` ";
					
			$result=$DB->query($query);
			$num=$DB->numrows($result);
			$rand=mt_rand();
			
			$PluginOrder=new PluginOrder();
			$PluginOrderReference = new PluginOrderReference();
			
			$canedit=$PluginOrder->can($FK_order,'w') && $PluginOrder->canUpdateOrder($FK_order);
			echo "<form method='post' name='order_detail_form$rand' id='order_detail_form$rand'  action=\"$target\">";
			echo "<input type='hidden' name='FK_order' value=\"$FK_order\">";
			if ($num>0) {
				echo "<div class='center'><table class='tab_cadrehov'>";
				echo "<tr><th colspan='13'>".$LANG['plugin_order']['detail'][17].":</th></tr>";
				echo "<tr>";
				if($canedit)
					echo "<th></th>";
				echo "<th>".$LANG['plugin_order']['detail'][1]."</th>";
				echo "<th>".$LANG['common'][5]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][6]."</th>";
				echo "<th>".$LANG['common'][22]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][4]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][18]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][25]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][9]."</th>";
				echo "<th>".$LANG['plugin_order']['detail'][10]."</th></tr>";
				
				while ($data=$DB->fetch_array($result)){
					
					echo "<tr class='tab_bg_1'>";
					if ($canedit){
						echo "<td width='10'>";
						$sel="";
						if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
						echo "<input type='checkbox' name='detail[".$data["IDD"]."]' value='1' $sel>";
						echo "</td>";
					}
					/* type */
					$ci=new CommonItem();
					$ci->setType($data["type"]);
					echo "<td align='center'>".$ci->getType()."</td>";
					/* manufacturer */
					echo "<td align='center'>".getDropdownName("glpi_dropdown_manufacturer",$data["FK_glpi_enterprise"])."</td>";
					/* reference */
					echo "<td align='center'>";
					echo $PluginOrderReference->getReceptionReferenceLink($data);
					echo "</td>";
					/* type */
					echo "<td align='center'>";
					if (isset($ORDER_TYPE_TABLES[$data["type"]]))
                  echo getDropdownName($ORDER_TYPE_TABLES[$data["type"]], $data["FK_type"]);
					echo "</td>";
					/* modele */
					echo "<td align='center'>";
					if (isset($ORDER_MODEL_TABLES[$data["type"]]))
                  echo getDropdownName($ORDER_MODEL_TABLES[$data["type"]], $data["FK_model"]);
					echo "</td>";
					echo "<td align='center'>".formatNumber($data["price_taxfree"])."</td>";
					/* price with reduction */
					echo "<td align='center'>".formatNumber($data["price_discounted"])."</td>";
					/* price with taxes */
					echo "<td align='center'>".formatNumber($data["discount"])."</td>";
					/* total price */
					echo "<td align='center'>".formatNumber($data["totalpriceHT"])."</td>";
					/* total price with taxes  */
					echo "<td align='center'>".formatNumber($data["totalpriceTTC"])."</td>";
					
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
		$query = "SELECT `ID` 
               FROM `" . $this->table . "` 
               WHERE `device_type` = '$device_type' 
               AND `FK_device` = '$deviceID' ";
      $result = $DB->query($query);
		if ($DB->numrows($result))
			return true;
		else
			return false;
	}
	
	function getTotalQuantity($FK_order, $FK_reference) {
      global $DB;
      
      $query = "SELECT COUNT(*) AS quantity 
               FROM `".$this->table."`
               WHERE `FK_order` = '$FK_order'
               AND `FK_reference` = '$FK_reference' ";
      $result = $DB->query($query);
      return ($DB->result($result, 0, 'quantity'));
   }

   function getDeliveredQuantity($FK_order, $FK_reference) {
      global $DB;
      
      $query = "	SELECT COUNT(*) AS deliveredquantity 
                  FROM `".$this->table."`
                  WHERE `FK_order` = '$FK_order'
                  AND `FK_reference` = '$FK_reference'
                  AND `status` = '".ORDER_STATUS_WAITING_APPROVAL."' ";
      $result = $DB->query($query);
      return ($DB->result($result, 0, 'deliveredquantity'));
   }
   
   function updateDelivryStatus($orderID) {
      global $DB;

      $order = new PluginOrder;
      $order->getFromDB($orderID);

      $query = "SELECT `status` 
               FROM `".$this->table."` 
               WHERE `FK_order` = '$orderID'";
      $result = $DB->query($query);
      $all_delivered = true;

      while ($data = $DB->fetch_array($result))
         if (!$data["status"])
            $all_delivered = false;

      if ($all_delivered && $order->fields["status"] != ORDER_STATUS_COMPLETLY_DELIVERED)
         $order->updateOrderStatus($orderID, ORDER_STATUS_COMPLETLY_DELIVERED);
      else if ($order->fields["status"] != ORDER_STATUS_PARTIALLY_DELIVRED) 
         $order->updateOrderStatus($orderID, ORDER_STATUS_PARTIALLY_DELIVRED);
   }
   
   function getAllPrices($FK_order) {
      global $DB;
      
      $query = "SELECT SUM(`price_ati`) AS priceTTC, SUM(`price_discounted`) AS priceHT 
               FROM `".$this->table."` 
               WHERE `FK_order` = '$FK_order' ";
      $result = $DB->query($query);
      return $DB->fetch_array($result);
   }
   
   function showItemFromPlugin($instID, $search = '') {
      global $DB, $CFG_GLPI, $LANG, $INFOFORM_PAGES, $LINK_ID_TABLE,$ORDER_RESTRICTED_TYPES;
      
      if (!plugin_order_haveRight("order", "r"))
         return false;
      $rand = mt_rand();
      
      $PluginOrder = new PluginOrder();
      if ($PluginOrder->getFromDB($instID)) {
      
         $canedit = $PluginOrder->can($instID, 'w');
         $query = "SELECT DISTINCT `device_type` 
                 FROM `".$this->table."` 
                 WHERE `FK_order` = '$instID' 
                 ORDER BY `device_type`";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         $i = 0;
         if (isMultiEntitiesMode()) {
            $colsup = 1;
         } else {
            $colsup = 0;
         }
         echo "<form method='post' name='order_form$rand' id='order_form$rand'  action=\"" . $CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.form.php\">";
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if ($number == 0) {
            echo "<tr><th colspan='" . ($canedit ? (5 + $colsup) : (4 + $colsup)) . "'>" . $LANG['plugin_order']['item'][3] . "</th></tr></table></div></form>";
         } else {
            echo "<tr><th colspan='" . ($canedit ? (5 + $colsup) : (4 + $colsup)) . "'>" . $LANG['plugin_order']['item'][0] . ":</th></tr><tr>";

            echo "<th>" . $LANG['common'][17] . "</th>";
            echo "<th>" . $LANG['common'][16] . "</th>";
            if (isMultiEntitiesMode())
               echo "<th>" . $LANG['entity'][0] . "</th>";
            echo "<th>" . $LANG['common'][19] . "</th>";
            echo "<th>" . $LANG['common'][20] . "</th>";
            echo "</tr>";

            $ci = new CommonItem();
            while ($i < $number) {
               $type = $DB->result($result, $i, "device_type");
               if (haveTypeRight($type, "r")) {
                  $column = "name";

                  $entity_restrict = (!in_array($type,$ORDER_RESTRICTED_TYPES)?true:false);

                  switch ($type)
                  {
                     default :
                        $query = "SELECT `" . $LINK_ID_TABLE[$type] . "`.*, " .
                              "`".$this->table."`.`ID` AS IDD, " .
                              "`glpi_entities`.`ID` AS entity " .
                        " FROM`".$this->table."`, `" . $LINK_ID_TABLE[$type] .
                        "` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`ID` = `" . $LINK_ID_TABLE[$type] . "`.`FK_entities`) " .
                        " WHERE `" . $LINK_ID_TABLE[$type] . "`.`ID` = `".$this->table."`.`FK_device` 
                        AND `".$this->table."`.`device_type` = '$type' 
                        AND `".$this->table."`.`FK_order` = '$instID' " . getEntitiesRestrictRequest(" AND ", $LINK_ID_TABLE[$type], '', '', isset ($CFG_GLPI["recursive_type"][$type]));
         
                        if (in_array($LINK_ID_TABLE[$type], $CFG_GLPI["template_tables"])) {
                           $query .= " AND `" . $LINK_ID_TABLE[$type] . "`.`is_template` = '0'";
                        }
                        $query .= " ORDER BY `glpi_entities`.`completename`, `" . $LINK_ID_TABLE[$type] . "`.`$column` ";
                        break;
                     case CONSUMABLE_ITEM_TYPE:
                        $query = "SELECT `gpr`.`ID`, `gct`.`name`, `gpr`.`FK_entities` AS entity
                                  FROM `".$this->table."` AS gpd, `glpi_plugin_order_references` AS gpr,
                                       `glpi_consumables` AS gc, `glpi_consumables_type` AS gct
                                  WHERE `gpd`.`FK_order` = '$instID' 
                                  AND `gpd`.`device_type` = '$type'
                                  AND `gpd`.`FK_reference` = `gpr`.`ID`
                                  AND `gpd`.`FK_device` = `gc`.`ID` 
                                  AND `gc`.`FK_glpi_consumables_type` = `gct`.`ID` 
                                  GROUP BY `gct`.`ID` 
                                  ORDER BY `gct`.`name`";
                        break;
                     case CARTRIDGE_ITEM_TYPE:
                        $query = "SELECT `gpr`.`ID`, `gct`.`name`, `gpr`.`FK_entities` AS entity
                                  FROM `".$this->table."` AS gpd, `glpi_plugin_order_references` AS gpr,
                                       `glpi_cartridges` AS gc, `glpi_cartridges_type` AS gct
                                  WHERE `gpd`.`FK_order` = '$instID' 
                                  AND `gpd`.`device_type` = '$type'
                                  AND `gpd`.`FK_reference` = `gpr`.`ID`
                                  AND `gpd`.`FK_device` = `gc`.`ID` 
                                  AND `gc`.`FK_glpi_cartridges_type` = `gct`.`ID` 
                                  GROUP BY `gct`.`ID` 
                                  ORDER BY `gct`.`name`";
                        break;
                  }
                       
                  if ($result_linked = $DB->query($query))
                     if ($DB->numrows($result_linked)) {
                        $ci->setType($type);
                        while ($data = $DB->fetch_assoc($result_linked)) {
                           $ID = "";

                           if ($_SESSION["glpiview_ID"] || empty ($data["name"]))
                              $ID = " (" . $data["ID"] . ")";
                           
                           switch ($type)
                           {
                              default :
                                 $formpage = $INFOFORM_PAGES[$type];
                                 break;
                              case CARTRIDGE_ITEM_TYPE:
                                 $formpage = $INFOFORM_PAGES[CARTRIDGE_TYPE];
                                 break;
                              case CONSUMABLE_ITEM_TYPE:
                                 $formpage = $INFOFORM_PAGES[CONSUMABLE_TYPE];
                                 break;	 	
                           }
                           
                           if (haveTypeRight($type,'r'))
                              $name = "<a href=\"" . $CFG_GLPI["root_doc"] . "/" . $formpage . "?ID=" . $data["ID"] . "\">".$data["name"] . "$ID</a>";
                           else
                              echo $data["name"];								

                           echo "<tr class='tab_bg_1'>";
                           echo "<td class='center'>" . $ci->getType() . "</td>";

                           echo "<td class='center' " . (isset ($data['deleted']) && $data['deleted'] ? "class='tab_bg_2_2'" : "") . ">" . $name . "</td>";
                           if (isMultiEntitiesMode())
                              echo "<td class='center'>" . getDropdownName("glpi_entities", $data['entity']) . "</td>";
                           if (!in_array($type,$ORDER_RESTRICTED_TYPES))
                           {
                              echo "<td class='center'>" . (isset ($data["serial"]) ? "" . $data["serial"] . "" : "-") . "</td>";
                              echo "<td class='center'>" . (isset ($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-") . "</td>";
                           }
                           else
                           {
                              echo "<td class='center'</td>";
                              echo "<td class='center'></td>";
                           }									
                           echo "</tr>";
                        }
                     }

               }
               $i++;
            }

            echo "</table></div>";
            echo "</form>";
         }
      }
   }
   
   function getOrderInfosByDeviceID($device_type, $deviceID) {
		global $DB;
		$query = "SELECT `glpi_plugin_order`.* 
               FROM `glpi_plugin_order`, `".$this->table."` 
               WHERE `glpi_plugin_order`.`ID` = `".$this->table."`.`FK_order` 
               AND `".$this->table."`.`device_type` = '$device_type' 
               AND `".$this->table."`.`FK_device` = '$deviceID' ";
		$result = $DB->query($query);
		if ($DB->numrows($result))
			return $DB->fetch_array($result);
		else
			return false;
	}
	
   function showPluginFromItems($device_type, $ID) {
      global $LANG, $INFOFORM_PAGES, $CFG_GLPI;
      
      $infos = $this->getOrderInfosByDeviceID($device_type, $ID);
      if ($infos) {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr align='center'><th colspan='2'>" . $LANG['plugin_order'][47] . ": </th></tr>";
         echo "<tr align='center'><td class='tab_bg_2'>" . $LANG['plugin_order'][39] . "</td>";
         echo "<td class='tab_bg_2'>";
         if (plugin_order_haveRight("order", "r"))
            echo "<a href='" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[PLUGIN_ORDER_TYPE] . "?ID=" . $infos["ID"] . "'>" . $infos["name"] . "</a>";
         else
            echo $infos["name"];
         echo "</td></tr>";
         echo "<tr align='center'><td class='tab_bg_2'>" . $LANG['plugin_order']['detail'][21] . "</td>";
         echo "<td class='tab_bg_2'>" . convDate($infos["date"]) . "</td></tr>";
         echo "</table></div>";
      }
   }
}

?>