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

class PluginOrderReception extends CommonDBTM {

	function __construct() {
		$this->table = "glpi_plugin_order_detail";
		$this->type = PLUGIN_ORDER_RECEPTION_TYPE;
		$this->dohistory=true;
	}
   
   function getFromDBByOrder($FK_Order) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->table."`
					WHERE `FK_Order` = '" . $FK_Order . "' ";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result) != 1) {
				return false;
			}
			$this->fields = $DB->fetch_assoc($result);
			if (is_array($this->fields) && count($this->fields)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	function checkThisItemStatus($detailID, $status) {
      global $DB;
      
      $query = "SELECT `status` 
               FROM `glpi_plugin_order_detail` 
               WHERE `ID` = '$detailID' ";
      $result = $DB->query($query);
      if ($DB->result($result, 0, "status") == $status)
         return true;
      else
         return false;
   }
   
   function checkItemStatus($orderID, $referenceID, $status) {
      global $DB;
      
      $query = "SELECT COUNT(*) AS cpt 
               FROM `glpi_plugin_order_detail` 
               WHERE `FK_order` = '$orderID' 
               AND `FK_reference` = '$referenceID' 
               AND `status` = '".$status."' ";
      $result = $DB->query($query);
      if ($DB->result($result, 0, "cpt") > 0)
         return ($DB->result($result, 0, 'cpt'));
      else
         return false;
   }
	
	function defineTabs($ID, $withtemplate) {
		global $LANG;

		$ong[1] = $LANG['title'][26];

		return $ong;
	}

	function showForm($target, $ID) {
		global $LANG, $DB, $CFG_GLPI, $INFOFORM_PAGES;
      
      if (!plugin_order_haveRight("order", "r"))
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
		
		if ($spotted) {
         $this->showTabs($ID, false, $_SESSION['glpi_tab'],array(),"FK_order=".$this->fields["FK_order"]."");
         echo "<form method='post' name='show_reception' id='show_reception' action=\"$target\">";
         echo "<input type='hidden' name='ID' value='" . $ID . "'>";
         
         $PluginOrder = new PluginOrder();
         $PluginOrder->getFromDB($this->fields["FK_order"]);
         $this->fields["FK_entities"] = $PluginOrder->fields["FK_entities"];
         
         $PluginOrderReference = new PluginOrderReference();
         $PluginOrderReference->getFromDB($this->fields["FK_reference"]);
         
         $canedit = $PluginOrder->can($this->fields["FK_order"], 'w') && !$PluginOrder->canUpdateOrder($this->fields["FK_order"]) && $PluginOrder->fields["status"] != ORDER_STATUS_CANCELED;
         echo "<input type='hidden' name='FK_order' value='" . $this->fields["FK_order"] . "'>";
         
         echo "<div class='center' id='tabsbody'>";
         echo "<table class='tab_cadre_fixe'>";
         $this->showFormHeader($ID,"", 2);
         
         echo "<tr>";
         echo "<th>" . $LANG['plugin_order']['detail'][6]. "</th>";
         echo "<th>" . $LANG['plugin_order']['detail'][21]. "</th>";
         echo "<th>" . $LANG['financial'][19] . "</th></tr>";
         
         echo "<tr class='tab_bg_1'>";
         echo "<td align='center'>";
         $data = array();
         $data["ID"] = $this->fields["FK_reference"];
         $data["name"]= $PluginOrderReference->fields["name"];
         echo $PluginOrderReference->getReceptionReferenceLink($data);
         echo "</td>";
         echo "<td align='center'>";
         if ($canedit)
            showDateFormItem("date",$this->fields["date"],true,1);
         else
            echo convDate($this->fields["date"]);
         echo "</td>";
         echo "<td align='center'>";
         if ($canedit)
            autocompletionTextField("deliverynum", "glpi_plugin_order_detail", "deliverynum", $this->fields["deliverynum"], 20);
         else
            echo $this->fields["deliverynum"];
         echo "</td>";
         
         echo "</tr>";
         if ($canedit)
         {
            echo "<tr>";
            echo "<td class='tab_bg_1'align='center' colspan='3'>";
            echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit' >";
            echo "</td>";
            echo "</tr>";
         }
      }
      echo "</table></div></form>";
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

		return true;
	}
	
	function showOrderReception($orderID) {
      global $DB, $CFG_GLPI, $LANG, $LINK_ID_TABLE, $INFOFORM_PAGES;

      $PluginOrder = new PluginOrder();
      $PluginOrderDetail = new PluginOrderDetail();
      $PluginOrderReference = new PluginOrderReference();
      
      initNavigateListItems(PLUGIN_ORDER_RECEPTION_TYPE,$LANG['plugin_order'][7] ." = ". $orderID);
      
      $canedit = $PluginOrder->can($orderID, 'w') && !$PluginOrder->canUpdateOrder($orderID) && $PluginOrder->fields["status"] != ORDER_STATUS_CANCELED;
      
      $result_ref=$PluginOrderDetail->queryDetail($orderID);
      $numref = $DB->numrows($result_ref);

      while ($data_ref=$DB->fetch_array($result_ref)){
         
         addToNavigateListItems(PLUGIN_ORDER_RECEPTION_TYPE,$data_ref['IDD']);
         
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if (!$numref)
            echo "<tr><th>" . $LANG['plugin_order']['detail'][20] . "</th></tr></table></div>";
         else {
            
            $refID = $data_ref["ID"];
            $typeRef = $data_ref["type"];		
            $price_taxfree = $data_ref["price_taxfree"];
            $discount = $data_ref["discount"];
            
            $ci = new CommonItem();
            $ci->setType($typeRef);
            $rand = mt_rand();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('reception$rand','reception', '".GLPI_ROOT."/pics/plus.png','".GLPI_ROOT."/pics/moins.png');\">";
            echo "<img alt='' name='reception' src=\"".GLPI_ROOT."/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>" . $LANG['plugin_order']['detail'][6] . "</th>";
            echo "<th>" . $LANG['common'][5] . "</th>";
            echo "<th>" . $LANG['plugin_order']['reference'][1] . "</th>";
            echo "<th>" . $LANG['plugin_order']['delivery'][5] . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>" . $ci->getType() . "</td>";
            echo "<td align='center'>" . getDropdownName("glpi_dropdown_manufacturer", $data_ref["FK_glpi_enterprise"]) . "</td>";
            echo "<td>" . $PluginOrderReference->getReceptionReferenceLink($data_ref) . "</td>";
            echo "<td>" . $PluginOrderDetail->getDeliveredQuantity($orderID, $refID, $data_ref["price_taxfree"], $data_ref["discount"]) . " / " . $PluginOrderDetail->getTotalQuantityByRefAndDiscount($orderID,$refID, $data_ref["price_taxfree"], $data_ref["discount"]) . "</td>";
            echo "</tr></table>";

            echo "<div class='center' id='reception$rand' style='display:none'>";
            echo "<form method='post' name='order_reception_form$rand' id='order_reception_form$rand'  action=\"" . $CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.reception.form.php\">";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr>";
            echo "<th width='15'></th>";
            if ($typeRef != SOFTWARELICENSE_TYPE)
               echo "<th>" . $LANG['common'][2] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][2] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][19] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][21] . "</th>";
            echo "<th>" . $LANG['financial'][19] . "</th>";
            echo "</tr>";
            
            $query="SELECT `glpi_plugin_order_detail`.`ID` AS IDD, `glpi_plugin_order_references`.`ID` AS ID,`glpi_plugin_order_references`.`template`, `glpi_plugin_order_detail`.`status`, `glpi_plugin_order_detail`.`date`,`glpi_plugin_order_detail`.`deliverynum`, `glpi_plugin_order_references`.`name`, `glpi_plugin_order_references`.`type`, `glpi_plugin_order_detail`.`FK_device`
					FROM `".$this->table."`, `glpi_plugin_order_references`
					WHERE `".$this->table."`.`FK_reference` = `glpi_plugin_order_references`.`ID`
					AND `".$this->table."`.`FK_reference` = '".$refID."'
					AND `".$this->table."`.`price_taxfree` LIKE '".$price_taxfree."'
					AND `".$this->table."`.`discount` LIKE '".$discount."'
					AND `".$this->table."`.`FK_order` = '$orderID' ";
				if ($data_ref["type"] == SOFTWARELICENSE_TYPE)
               $query.=" GROUP BY `glpi_plugin_order_references`.`name` ";	
				$query.=" ORDER BY `glpi_plugin_order_references`.`name` ";
				
            $result = $DB->query($query);
            $num = $DB->numrows($result);
            
            while ($data=$DB->fetch_array($result)){
               $random = mt_rand();
               
               $detailID = $data["IDD"];

               echo "<tr class='tab_bg_2'>";
               if ($canedit && $this->checkThisItemStatus($detailID, ORDER_DEVICE_NOT_DELIVRED)) {
                  echo "<td width='15' align='left'>";
                  $sel = "";
                  if (isset ($_GET["select"]) && $_GET["select"] == "all")
                     $sel = "checked";
                  
                  echo "<input type='checkbox' name='item[" . $detailID . "]' value='1' $sel>";
                  echo "</td>";
               } else {
                  echo "<td width='15' align='left'></td>";
               }
               
               if ($typeRef != SOFTWARELICENSE_TYPE)
                  echo "<td align='center'>" . $data["IDD"] . "</td>";
               echo "<td align='center'>" . $PluginOrderReference->getReceptionReferenceLink($data) . "</td>";
               echo "<td align='center'>";
               if ($canedit && $data["status"]==ORDER_DEVICE_DELIVRED)
                  echo "<a href=\"" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[PLUGIN_ORDER_RECEPTION_TYPE] . "?ID=".$data["IDD"]."\">";
               echo $this->getReceptionStatus($detailID);
               if ($canedit && $data["status"]==ORDER_DEVICE_DELIVRED)
                  echo "</a>";
               echo "</td>";
               echo "<td align='center'>" . convDate($data["date"]) . "</td>";
               echo "<td align='center'>" . $data["deliverynum"] . "</td>";

               echo "<input type='hidden' name='ID[$detailID]' value='$detailID'>";
               echo "<input type='hidden' name='name[$detailID]' value='" . $data["name"] . "'>";
               echo "<input type='hidden' name='referenceID[$detailID]' value='" . $data["ID"] . "'>";
               echo "<input type='hidden' name='type[$detailID]' value='" . $data["type"] . "'>";
               echo "<input type='hidden' name='template[$detailID]' value='" . $data["template"] . "'>";
               echo "<input type='hidden' name='status[$detailID]' value='" . $data["status"] . "'>";

            }
            echo "</table>";
            if ($canedit && $this->checkItemStatus($orderID, $refID, ORDER_DEVICE_NOT_DELIVRED)) {
               
               echo "<div class='center'>";
               echo "<table width='950px' class='tab_glpi'>";
               echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('order_reception_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$orderID&amp;select=all'>".$LANG['buttons'][18]."</a></td>";

               echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('order_reception_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$orderID&amp;select=none'>".$LANG['buttons'][19]."</a>";
               echo "</td><td align='left' width='80%'>";
               echo "<input type='hidden' name='orderID' value='$orderID'>";
               $this->dropdownReceptionActions($typeRef, $refID, $orderID);
               echo "</td>";
               echo "</table>";
               echo "</div>";
               
               $rand = mt_rand();
               
               if ($typeRef != SOFTWARELICENSE_TYPE) {
                  echo "<div id='massreception" . $orderID . "$rand'></div>\n";
                  
                  echo "<script type='text/javascript' >\n";
                  echo "function viewmassreception" . $orderID . "$rand(){\n";
                  $params = array ('orderID' => $orderID,
                                   'referenceID' => $refID);
                  ajaxUpdateItemJsCode("massreception" . $orderID . "$rand",
                                       $CFG_GLPI["root_doc"]."/plugins/order/ajax/massreception.php", $params, false);
                  echo "};";
                  echo "</script>\n";
                  echo "<p><a href='javascript:viewmassreception".$orderID."$rand();'>";
                  echo $LANG['plugin_order']['delivery'][4]."</a></p><br>\n";
               }
            }
            echo "</form></div>";
         }
         echo "<br>";
      }
   }
   
   function dropdownReceptionActions($type,$referenceID,$orderID) {
      global $LANG,$CFG_GLPI,$ORDER_RESTRICTED_TYPES;
      
      $rand = mt_rand();

      echo "<select name='receptionActions$rand' id='receptionActions$rand'>";
      echo "<option value='0' selected>-----</option>";
      echo "<option value='reception'>" . $LANG['plugin_order']['delivery'][2] . "</option>";
      echo "</select>";
      $params = array (
         'action' => '__VALUE__',
         'type' => $type,
         'referenceID'=>$referenceID,
         'orderID'=>$orderID
      );
      ajaxUpdateItemOnSelectEvent("receptionActions$rand", "show_receptionActions$rand", $CFG_GLPI["root_doc"] . "/plugins/order/ajax/receptionactions.php", $params);
      echo "<span id='show_receptionActions$rand'>&nbsp;</span>";
   }
   
   function showOrderGeneration($orderID) {
      global $DB, $CFG_GLPI, $LANG, $LINK_ID_TABLE, $INFOFORM_PAGES;

      $PluginOrder = new PluginOrder();
      $PluginOrderDetail = new PluginOrderDetail();
      $PluginOrderReference = new PluginOrderReference();
      $PluginOrder->getFromDB($orderID);
      $canedit = $PluginOrder->can($orderID, 'w') && !$PluginOrder->canUpdateOrder($orderID) && $PluginOrder->fields["status"] != ORDER_STATUS_CANCELED;
      
      $query_ref = "SELECT `glpi_plugin_order_detail`.`ID` AS IDD, `glpi_plugin_order_detail`.`FK_reference` AS ID, `glpi_plugin_order_references`.`name`, `glpi_plugin_order_references`.`type`, `glpi_plugin_order_references`.`FK_glpi_enterprise`,`glpi_plugin_order_detail`.`price_taxfree`,`glpi_plugin_order_detail`.`discount` " .
      "FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references` " .
      "WHERE `FK_order` = '$orderID' " .
      "AND `glpi_plugin_order_detail`.`FK_reference` = `glpi_plugin_order_references`.`ID`  " .
      "AND `glpi_plugin_order_detail`.`status` = '".ORDER_DEVICE_DELIVRED."'   " .
      "GROUP BY `glpi_plugin_order_detail`.`FK_reference` " .
      "ORDER BY `glpi_plugin_order_references`.`name`";
      $result_ref = $DB->query($query_ref);
      $numref = $DB->numrows($result_ref);

      while ($data_ref=$DB->fetch_array($result_ref)){

         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if (!$numref)
            echo "<tr><th>" . $LANG['plugin_order']['detail'][20] . "</th></tr></table></div>";
         else {
            
            $refID = $data_ref["ID"];
            $typeRef = $data_ref["type"];		
            
            $ci = new CommonItem();
            $ci->setType($typeRef);
            $rand = mt_rand();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('generation$rand','generation', '".GLPI_ROOT."/pics/plus.png','".GLPI_ROOT."/pics/moins.png');\">";
            echo "<img alt='' name='generation' src=\"".GLPI_ROOT."/pics/plus.png\">";
            echo "</a>";
            echo "</li></ul></th>";
            echo "<th>" . $LANG['plugin_order']['detail'][6] . "</th>";
            echo "<th>" . $LANG['common'][5] . "</th>";
            echo "<th>" . $LANG['plugin_order']['reference'][1] . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>" . $ci->getType() . "</td>";
            echo "<td align='center'>" . getDropdownName("glpi_dropdown_manufacturer", $data_ref["FK_glpi_enterprise"]) . "</td>";
            echo "<td>" . $PluginOrderReference->getReceptionReferenceLink($data_ref) . "</td>";
            echo "</tr></table>";

            echo "<div class='center' id='generation$rand' style='display:none'>";
            echo "<form method='post' name='order_generation_form$rand' id='order_generation_form$rand'  action=\"" . $CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.reception.form.php\">";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr>";
            if ($canedit)
               echo "<th width='15'></th>";
            if ($typeRef != SOFTWARELICENSE_TYPE)
               echo "<th>" . $LANG['common'][2] . "</th>";
            else
               echo "<th>" . $LANG['plugin_order']['detail'][7] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][2] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][19] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][21] . "</th>";
            echo "<th>" . $LANG['plugin_order']['item'][0] . "</th></tr>";
            
            $query = "SELECT `glpi_plugin_order_detail`.`ID` AS IDD, `glpi_plugin_order_references`.`ID` AS ID,`glpi_plugin_order_references`.`template`, `glpi_plugin_order_detail`.`status`, `glpi_plugin_order_detail`.`date`,`glpi_plugin_order_detail`.`deliverynum`, `glpi_plugin_order_references`.`name`, `glpi_plugin_order_references`.`type`, `glpi_plugin_order_detail`.`FK_device`, `glpi_plugin_order_detail`.`price_taxfree`, `glpi_plugin_order_detail`.`discount`
                    FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references`
                    WHERE `FK_order` = '$orderID'
                    AND `glpi_plugin_order_detail`.`FK_reference` = '".$refID."'
                    AND `glpi_plugin_order_detail`.`status` = '".ORDER_DEVICE_DELIVRED."'
                    AND `glpi_plugin_order_detail`.`FK_reference` = `glpi_plugin_order_references`.`ID` ";
            if ($typeRef == SOFTWARELICENSE_TYPE)
               $query.=" GROUP BY `glpi_plugin_order_detail`.`price_taxfree`,`glpi_plugin_order_detail`.`discount` ";
				$query.=" ORDER BY `glpi_plugin_order_references`.`name` ";
            $result = $DB->query($query);
            $num = $DB->numrows($result);
            
            while ($data=$DB->fetch_array($result)){
               $random = mt_rand();
               
               $detailID = $data["IDD"];

               echo "<tr class='tab_bg_2'>";
               if ($canedit) {
                  echo "<td width='15' align='left'>";
                  $sel = "";
                  if (isset ($_GET["select"]) && $_GET["select"] == "all")
                     $sel = "checked";
                  
                  echo "<input type='checkbox' name='item[" . $detailID . "]' value='1' $sel>";
                  echo "</td>";
               }
               
               if ($typeRef != SOFTWARELICENSE_TYPE)
                  echo "<td align='center'>" . $data["IDD"] . "</td>";
               else
                  echo "<td align='center'>" . $PluginOrderDetail->getTotalQuantityByRefAndDiscount($orderID,$refID, $data["price_taxfree"], $data["discount"]) . "</td>";
               echo "<td align='center'>" . $PluginOrderReference->getReceptionReferenceLink($data) . "</td>";
               echo "<td align='center'>" . $this->getReceptionStatus($detailID) . "</td>";
               echo "<td align='center'>" . convDate($data["date"]) . "</td>";
               echo "<td align='center'>" . $this->getReceptionDeviceName($data["FK_device"], $data["type"]);
               if ($data["FK_device"] != 0) {
                  echo " <img src='".$CFG_GLPI["root_doc"]."/pics/aide.png' alt=\"\" onmouseout=\"setdisplay(getElementById('comments_$random'),'none')\" onmouseover=\"setdisplay(getElementById('comments_$random'),'block')\">";
                  echo "<span class='over_link' id='comments_$random'>".nl2br($this->getReceptionMaterialInfo($data["type"], $data["FK_device"])) ."</span>";
               }
               echo "<input type='hidden' name='ID[$detailID]' value='$detailID'>";
               echo "<input type='hidden' name='name[$detailID]' value='" . $data["name"] . "'>";
               echo "<input type='hidden' name='type[$detailID]' value='" . $data["type"] . "'>";
               echo "<input type='hidden' name='template[$detailID]' value='" . $data["template"] . "'>";
               echo "<input type='hidden' name='status[$detailID]' value='" . $data["status"] . "'>";

            }
            echo "</table>";
            if ($canedit) {
               
               echo "<div class='center'>";
               echo "<table width='950px' class='tab_glpi'>";
               echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('order_generation_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$orderID&amp;select=all'>".$LANG['buttons'][18]."</a></td>";

               echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('order_generation_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$orderID&amp;select=none'>".$LANG['buttons'][19]."</a>";
               echo "</td><td align='left' width='80%'>";
               echo "<input type='hidden' name='orderID' value='$orderID'>";
               $this->dropdownGenerationActions($typeRef, $refID, $orderID);
               echo "</td>";
               echo "</table>";
               echo "</div>";
            }
            echo "</form></div>";
         }
         echo "<br>";
      }
   }
   
   function getReceptionMaterialInfo($deviceType, $deviceID) {
      global $DB, $LINK_ID_TABLE, $LANG;
      
      $comments = "";
      switch ($deviceType) {
         case COMPUTER_TYPE :
         case MONITOR_TYPE :
         case NETWORKING_TYPE :
         case PERIPHERAL_TYPE :
         case PHONE_TYPE :
         case PRINTER_TYPE :
         default :
            $ci = new CommonItem();
            $ci->getFromDB($deviceType, $deviceID);
            if ($ci->getField("name")) {
               $comments = "<strong>" . $LANG['common'][16] . ":</strong> " . $ci->getField("name");
            }

            if ($ci->getField("FK_entities")) {
               $comments = "<strong>" . $LANG['entity'][0] . ":</strong> " . getDropdownName("glpi_entities", $ci->getField("FK_entities"));
            }

            if ($ci->getField("serial") != '') {
               $comments .= "<br><strong>" . $LANG['common'][19] . ":</strong> " . $ci->getField("serial");
            }

            if ($ci->getField("otherserial") != '') {
               $comments .= "<br><strong>" . $LANG['common'][20] . ":</strong> " . $ci->obj->fields["otherserial"];
            }
            if ($ci->getField("location")) {
               $comments .= "<br><strong>" . $LANG['common'][15] . ":</strong> " . getDropdownName('glpi_dropdown_locations', $ci->getField("location"));
            }

            if ($ci->getField("FK_users")) {
               $comments .= "<br><strong>" . $LANG['common'][34] . ":</strong> " . getDropdownName('glpi_users', $ci->getField("FK_users"));
            }
            break;
         case CONSUMABLE_ITEM_TYPE :
            $ci = new Consumable();
            if ($ci->getFromDB($deviceID)) {
               $ct = new ConsumableType;
               $ct->getFromDB($ci->fields['FK_glpi_consumables_type']);
               $comments = "<strong>" . $LANG['entity'][0] . ":</strong> " . getDropdownName("glpi_entities", $ct->fields["FK_entities"]);
               $comments .= '<br><strong>' . $LANG['consumables'][0] . ' : </strong> #' . $deviceID;
               $comments .= '<br><strong>' . $LANG['consumables'][12] . ' : </strong>' . $ct->fields['name'];
               $comments .= '<br><strong>' . $LANG['common'][5] . ' : </strong>' . getDropdownName('glpi_dropdown_manufacturer', $ct->fields['FK_glpi_enterprise']);
               $comments .= '<br><strong>' . $LANG['consumables'][23] . ' : </strong>' . (!$ci->fields['id_user'] ? $LANG['consumables'][1] : $LANG['consumables'][15]);
               if ($ci->fields['id_user'])
                  $comments .= '<br><strong>' . $LANG['common'][34] . ' : </strong>' . getDropdownName('glpi_users', $ci->fields['id_user']);
            }
            break;
         case CARTRIDGE_ITEM_TYPE :
            $ci = new Cartridge();
            if ($ci->getFromDB($deviceID)) {
               $ct = new CartridgeType;
               $ct->getFromDB($ci->fields['FK_glpi_cartridges_type']);
               $comments = "<strong>" . $LANG['entity'][0] . ":</strong> " . getDropdownName("glpi_entities", $ct->fields["FK_entities"]);
               $comments .= '<br><strong>' . $LANG['cartridges'][0] . ' : </strong> #' . $deviceID;
               $comments .= '<br><strong>' . $LANG['cartridges'][12] . ' : </strong>' . $ct->fields['name'];
               $comments .= '<br><strong>' . $LANG['common'][5] . ' : </strong>' . getDropdownName('glpi_dropdown_manufacturer', $ct->fields['FK_glpi_enterprise']);
            }
      }

      return ($comments);
   }
   
   function getReceptionStatus($ID) {
      global $DB, $LANG;

      $detail = new PluginOrderDetail;
      $detail->getFromDB($ID);

      switch ($detail->fields["status"]) {
         case ORDER_DEVICE_NOT_DELIVRED :
            return $LANG['plugin_order']['status'][11];
         case ORDER_DEVICE_DELIVRED :
            return $LANG['plugin_order']['status'][8];
         default :
            return "";
      }
   }

   function getReceptionDeviceName($deviceID, $device_type) {
      global $DB, $LINK_ID_TABLE, $INFOFORM_PAGES, $CFG_GLPI, $LANG;
      if ($deviceID == 0)
         return ($LANG['plugin_order']['item'][2]);
      else {
         switch ($device_type) {
            case COMPUTER_TYPE :
            case MONITOR_TYPE :
            case NETWORKING_TYPE :
            case PERIPHERAL_TYPE :
            case PHONE_TYPE :
            case PRINTER_TYPE :
            default :
               $ci = new CommonItem();
               $ci->getFromDB($device_type, $deviceID);
               $name = $ci->getField("name");
               if ($_SESSION["glpiview_ID"] || empty($name)) $name.=" (".$deviceID.")";
               return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[$device_type] . "?ID=" . $deviceID . "&device_type=" . $device_type . ">" . $name."</a>");
               break;
            case CONSUMABLE_ITEM_TYPE :
               $ci = new Consumable();
               $ci->getFromDB($deviceID);
               $ct = new ConsumableType;
               $ct->getFromDB($ci->fields['FK_glpi_consumables_type']);
               return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[CONSUMABLE_TYPE] . "?ID=" . $ct->fields['ID'] . ">" . $LANG['consumables'][0] . ': #' . $deviceID . ' (' . $ct->fields["name"] . ')' . "</a>");
               break;
            case CARTRIDGE_ITEM_TYPE :
               $ci = new Cartridge();
               $ci->getFromDB($deviceID);
               $ct = new CartridgeType;
               $ct->getFromDB($ci->fields['FK_glpi_cartridges_type']);
               return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[CARTRIDGE_TYPE] . "?ID=" . $ct->fields['ID'] . ">" . $LANG['cartridges'][0] . ': #' . $deviceID . ' (' . $ct->fields["name"] . ')' . "</a>");
               break;
         }
      }
   }

   function dropdownGenerationActions($type,$referenceID,$orderID) {
      global $LANG,$CFG_GLPI,$ORDER_RESTRICTED_TYPES;
      
      $rand = mt_rand();

      echo "<select name='generationActions$rand' id='generationActions$rand'>";
      echo "<option value='0' selected>-----</option>";

      $ORDER_RESTRICTED_TYPES[]=	SOFTWARELICENSE_TYPE;
      //$ORDER_RESTRICTED_TYPES[]=	SOFTWARE_TYPE;
      $ORDER_RESTRICTED_TYPES[]=	CONTRACT_TYPE;
      
      if ($this->checkItemStatus($orderID, $referenceID, ORDER_DEVICE_DELIVRED)) {
         if (!in_array($type, $ORDER_RESTRICTED_TYPES))
            echo "<option value='generation'>" . $LANG['plugin_order']['delivery'][3] . "</option>";

         echo "<option value='createLink'>" . $LANG['plugin_order']['delivery'][11] . "</option>";
         echo "<option value='deleteLink'>" . $LANG['plugin_order']['delivery'][12] . "</option>";
      }
         
      echo "</select>";
      $params = array (
         'action' => '__VALUE__',
         'type' => $type,
         'referenceID'=>$referenceID,
         'orderID'=>$orderID
      );
      ajaxUpdateItemOnSelectEvent("generationActions$rand", "show_generationActions$rand", $CFG_GLPI["root_doc"] . "/plugins/order/ajax/generationactions.php", $params);
      echo "<span id='show_generationActions$rand'>&nbsp;</span>";
   }
   
   function itemAlreadyLinkedToAnOrder($device_type, $deviceID, $orderID, $detailID = 0) {
      global $DB, $ORDER_RESTRICTED_TYPES;
      
      $ORDER_RESTRICTED_TYPES[]=	SOFTWARELICENSE_TYPE;
      if (!in_array($device_type, $ORDER_RESTRICTED_TYPES)) {
         $query = "SELECT COUNT(*) AS cpt 
                  FROM `glpi_plugin_order_detail` 
                  WHERE `FK_order` = '$orderID' 
                  AND `FK_device` = '$deviceID' 
                  AND `device_type` = '$device_type' ";

         $result = $DB->query($query);
         if ($DB->result($result, 0, "cpt") > 0)
            return true;
         else
            return false;
      } else {
         $detail = new PluginOrderDetail;
         $detail->getFromDB($detailID);
         if (!$detail->fields['FK_device']) {
            return false;
         } else {
            return true;
         }
      }
   }
   
   function generateInfoComRelatedToOrder($entity, $detailID, $device_type, $deviceID, $templateID = 0) {
      global $LANG;

      $detail = new PluginOrderDetail;
      $detail->getFromDB($detailID);
      $order = new PluginOrder;
      $order->getFromDB($detail->fields["FK_order"]);
      
      $PluginOrderSupplier = new PluginOrderSupplier;
      $PluginOrderSupplier->getFromDBByOrder($detail->fields["FK_order"]);
      // ADD Infocoms
      $ic = new Infocom();
      $fields = array ();

      $exists = false;

      if ($templateID) {
         if ($ic->getFromDBforDevice($device_type, $templateID)) {
            $fields = $ic->fields;
            unset ($fields["ID"]);
            if (isset ($fields["num_immo"])) {
               $fields["num_immo"] = autoName($fields["num_immo"], "num_immo", 1, INFOCOM_TYPE, $entity);
            }
            if (empty ($fields['use_date'])) {
               unset ($fields['use_date']);
            }
            if (empty ($fields['buy_date'])) {
               unset ($fields['buy_date']);
            }
         }
      }

      if ($ic->getFromDBforDevice($device_type, $deviceID)) {
         $exists = true;
         $fields["ID"] = $ic->fields["ID"];
      }

      $fields["device_type"] = $device_type;
      $fields["FK_device"] = $deviceID;
      $fields["num_commande"] = $order->fields["numorder"];
      $fields["bon_livraison"] = $detail->fields["deliverynum"];
      $fields["budget"] = $order->fields["budget"];
      $fields["FK_enterprise"] = $order->fields["FK_enterprise"];
      if (isset($PluginOrderSupplier->fields["numbill"]))
         $fields["facture"] = $PluginOrderSupplier->fields["numbill"];
      $fields["value"] = $detail->fields["price_discounted"];
      $fields["buy_date"] = $order->fields["date"];

      //DO not check infocom modifications
      $fields["_manage_by_order"] = 1;

      if (!$exists)
         $ic->add($fields);
      else
         $ic->update($fields);
   }
   
   function removeInfoComRelatedToOrder($device_type, $deviceID) {

      $infocom = new InfoCom;
      $infocom->getFromDBforDevice($device_type, $deviceID);
      $input["ID"] = $infocom->fields["ID"];
      $input["num_commande"] = "";
      $input["bon_livraison"] = "";
      $input["budget"] = 0;
      $input["FK_enterprise"] = 0;
      $input["facture"] = "";
      $input["value"] = 0;
      $input["buy_date"] = "0000:00:00";

      //DO not check infocom modifications
      $input["_manage_by_order"] = 1;

      $infocom->update($input);
   }

   function createLinkWithDevice($detailID = 0, $deviceID = 0, $device_type = 0, $orderID = 0, $entity = 0, $templateID = 0, $history = true, $check_link = true) {
      global $LANG, $ORDER_RESTRICTED_TYPES,$DB;

      if (!$check_link || !$this->itemAlreadyLinkedToAnOrder($device_type, $deviceID, $orderID, $detailID)) {
         $detail = new PluginOrderDetail;
         
         if ($device_type == SOFTWARELICENSE_TYPE) {
            
            $detail->getFromDB($detailID);
            
            $query = "SELECT `ID` 
               FROM `glpi_plugin_order_detail` 
               WHERE `FK_order` = '" . $orderID."' 
               AND `FK_reference` = '" . $detail->fields["FK_reference"] ."' 
               AND `price_taxfree` LIKE '" . $detail->fields["price_taxfree"] ."'
               AND `discount` LIKE '" . $detail->fields["discount"] ."'
               AND `status` = 1 ";
            $result = $DB->query($query);
            $nb = $DB->numrows($result);

            if ($nb) {
               for ($i = 0; $i < $nb; $i++) {
                  $ID = $DB->result($result, $i, 'ID');
                  $input["ID"] = $ID;
                  $input["FK_device"] = $deviceID;
                  $input["device_type"] = SOFTWARELICENSE_TYPE;
                  $detail->update($input);
                  $this->generateInfoComRelatedToOrder($entity, $ID, $device_type, $deviceID, $templateID);
                  
                  $lic = new SoftwareLicense;
                  $lic->getFromDB($deviceID);
                  $values["ID"] = $lic->fields["ID"];
                  $values["number"] = $lic->fields["number"]+1;
                  $lic->update($values);
                  
               }
               
               if ($history) {
                  $order = new PluginOrder;
                  $commonitem = new CommonItem;
                  $commonitem->getFromDB($device_type, $deviceID);
                  $new_value = $LANG['plugin_order']['delivery'][14] . ' : ' . $commonitem->getField("name");
                  $order->addHistory(PLUGIN_ORDER_TYPE, '', $new_value, $orderID);
               }
            }
            
         } else if (in_array($device_type, $ORDER_RESTRICTED_TYPES)) {
            $commonitem = new CommonItem;
            $commonitem->setType($device_type, true);

            $detail->getFromDB($detailID);

            $input["tID"] = $deviceID;
            $input["date_in"] = $detail->fields["date"];
            $newID = $commonitem->obj->add($input);

            $input["ID"] = $detailID;
            $input["FK_device"] = $newID;
            $input["device_type"] = $device_type;
            $detail->update($input);

            $this->generateInfoComRelatedToOrder($entity, $detailID, $device_type, $newID, 0);
            
         } else  {
            $input["ID"] = $detailID;
            $input["FK_device"] = $deviceID;
            $input["device_type"] = $device_type;
            $detail->update($input);
            $detail->getFromDB($detailID);
            $this->generateInfoComRelatedToOrder($entity, $detailID, $device_type, $deviceID, $templateID);
            
            if ($history) {
               $order = new PluginOrder;
               $commonitem = new CommonItem;
               $commonitem->getFromDB($device_type, $deviceID);
               $new_value = $LANG['plugin_order']['delivery'][14] . ' : ' . $commonitem->getField("name");
               $order->addHistory(PLUGIN_ORDER_TYPE, '', $new_value, $orderID);
            }
         }

         if ($history) {
            $order = new PluginOrder;
            $order->getFromDB($detail->fields["FK_order"]);
            $new_value = $LANG['plugin_order']['delivery'][14] . ' : ' . $order->fields["name"];
            $order->addHistory($device_type, '', $new_value, $deviceID);
         }
         addMessageAfterRedirect($LANG['plugin_order']['delivery'][14], true);
      } else
         addMessageAfterRedirect($LANG['plugin_order']['delivery'][16], true, ERROR);

   }
   
   function deleteLinkWithDevice($detailID, $device_type) {
      global $DB, $LANG;
      
      if ($device_type == SOFTWARELICENSE_TYPE) {
            
         $detail = new PluginOrderDetail;
         $detail->getFromDB($detailID);
         $license = $detail->fields["FK_device"];
         
         $this->removeInfoComRelatedToOrder($device_type, $license);
         
         $query = "SELECT `ID` 
            FROM `glpi_plugin_order_detail` 
            WHERE `FK_order` = '" . $detail->fields["FK_order"]."' 
            AND `FK_reference` = '" . $detail->fields["FK_reference"] ."' 
            AND `price_taxfree` LIKE '" . $detail->fields["price_taxfree"] ."'
            AND `discount` LIKE '" . $detail->fields["discount"] ."'
            AND `status` = 1 ";
         $result = $DB->query($query);
         $nb = $DB->numrows($result);

         if ($nb) {
            for ($i = 0; $i < $nb; $i++) {
               $ID = $DB->result($result, $i, 'ID');
               $input["ID"] = $ID;
               $input["FK_device"] = 0;
               $detail->update($input);
               
               $lic = new SoftwareLicense;
               $lic->getFromDB($license);
               $values["ID"] = $lic->fields["ID"];
               $values["number"] = $lic->fields["number"]-1;
               $lic->update($values);
            }
            
            //TODO : check history
            $order = new PluginOrder;
            $order->getFromDB($detail->fields["FK_order"]);
            $new_value = $LANG['plugin_order']['delivery'][15] . ' : ' . $order->fields["name"];
            $order->addHistory($device_type, '', $new_value, $license);

            $commonitem = new CommonItem;
            $commonitem->getFromDB($device_type, $license);
            $new_value = $LANG['plugin_order']['delivery'][15] . ' : ' . $commonitem->getField("name");
            $order->addHistory(PLUGIN_ORDER_TYPE, '', $new_value, $order->fields["ID"]);
         }
      } else {
         $detail = new PluginOrderDetail;
         $detail->getFromDB($detailID);
         $deviceID = $detail->fields["FK_device"];

         $query = "SELECT `ID`, `FK_order` 
                  FROM `glpi_plugin_order_detail` 
                  WHERE `FK_device` = '" . $deviceID ."' 
                  AND `device_type` = '" . $device_type."' ";

         if ($result = $DB->query($query)) {
            if ($DB->numrows($result) > 0) {
               $orderDeviceID = $DB->result($result, 0, 'ID');

               $this->removeInfoComRelatedToOrder($device_type, $deviceID);

               if ($detail->fields["FK_device"] != 0) {
                  $input = $detail->fields;
                  $input["FK_device"] = 0;
                  $detail->update($input);
               } else
                  addMessageAfterRedirect($LANG['plugin_order'][48], TRUE, ERROR);

               $order = new PluginOrder;
               $order->getFromDB($DB->result($result, 0, "FK_order"));
               $new_value = $LANG['plugin_order']['delivery'][15] . ' : ' . $order->fields["name"];
               $order->addHistory($device_type, '', $new_value, $deviceID);

               $commonitem = new CommonItem;
               $commonitem->getFromDB($device_type, $deviceID);
               $new_value = $LANG['plugin_order']['delivery'][15] . ' : ' . $commonitem->getField("name");
               $order->addHistory(PLUGIN_ORDER_TYPE, '', $new_value, $order->fields["ID"]);
            }
         }
      }
   }
   
   function updateBulkReceptionStatus($params) {
      global $LANG, $DB;
      
      $query = "SELECT `ID` 
               FROM `glpi_plugin_order_detail` 
               WHERE `FK_order` = '" . $params["orderID"] ."' 
               AND `FK_reference` = '" . $params["referenceID"] ."' 
               AND `status` = 0 ";
      $result = $DB->query($query);
      $nb = $DB->numrows($result);
      if ($nb < $params['number_reception'])
         addMessageAfterRedirect($LANG['plugin_order']['detail'][37], true, ERROR);
      else {
         for ($i = 0; $i < $params['number_reception']; $i++) {
            $this->receptionOneItem($DB->result($result, $i, 0), $params['orderID'], $params["date"], $params["deliverynum"]);
         }
         $detail = new PluginOrderDetail;
         $detail->updateDelivryStatus($params['orderID']);
      }
   }
   
   function receptionOneItem($detailID, $orderID, $date, $deliverynum) {
      global $LANG;
      
      $detail = new PluginOrderDetail;
      $input["ID"] = $detailID;
      $input["date"] = $date;
      $input["status"] = ORDER_DEVICE_DELIVRED;
      $input["deliverynum"] = $deliverynum;
      $detail->update($input);
      addMessageAfterRedirect($LANG['plugin_order']['detail'][31], true);
   }
   
   function receptionAllItem($detailID, $referenceID, $orderID, $date, $deliverynum) {
      global $LANG, $DB;
      
      $detail = new PluginOrderDetail;
      $detail->getFromDB($detailID);
         
      $query = "SELECT `ID` 
               FROM `glpi_plugin_order_detail` 
               WHERE `FK_order` = '" . $orderID."' 
               AND `FK_reference` = '" . $referenceID ."' 
               AND `price_taxfree` LIKE '" . $detail->fields["price_taxfree"] ."'
               AND `discount` LIKE '" . $detail->fields["discount"] ."'
               AND `status` = 0 ";
      $result = $DB->query($query);
      $nb = $DB->numrows($result);

      if ($nb) {
         for ($i = 0; $i < $nb; $i++) {
            $detailID = $DB->result($result, $i, 'ID');
            $detail = new PluginOrderDetail;
            $input["ID"] = $detailID;
            $input["date"] = $date;
            $input["status"] = ORDER_DEVICE_DELIVRED;
            $input["deliverynum"] = $deliverynum;
            $detail->update($input);
         }
      }
      addMessageAfterRedirect($LANG['plugin_order']['detail'][31], true);
   }
   
   function updateReceptionStatus($params) {
      global $LANG;
      
      $detail = new PluginOrderDetail;
      $orderID = $params["orderID"];
      if (isset ($params["item"])) {
         foreach ($params["item"] as $key => $val)
            if ($val == 1) {
            
               if ($params["type"][$key] == SOFTWARELICENSE_TYPE) {
                  $this->receptionAllItem($key,$params["referenceID"][$key], $params["orderID"], $params["date"], $params["deliverynum"]);
            
               } else {
                  if ($detail->getFromDB($key)) {
                     if ($detail->fields["status"] == ORDER_DEVICE_NOT_DELIVRED) {
                        $this->receptionOneItem($key, $orderID, $params["date"], $params["deliverynum"]);
                     } else
                        addMessageAfterRedirect($LANG['plugin_order']['detail'][32], true, ERROR);
                  }
               }
            }

         $detail->updateDelivryStatus($orderID);
      } else
         addMessageAfterRedirect($LANG['plugin_order']['detail'][29], false, ERROR);
   }
   
   function showItemGenerationForm($target, $params) {
      global $LANG, $CFG_GLPI, $GENINVENTORYNUMBER_INVENTORY_TYPES;
      
      echo "<div class='center'>";

      //If plugin geninventorynumber is installed, activated and version >= 1.1.0
      $plugin = new Plugin;
      if ($plugin->isInstalled("geninventorynumber") && $plugin->isActivated("geninventorynumber")) {
         usePlugin("geninventorynumber", true);
         $infos = plugin_version_geninventorynumber();
         if ($infos['version'] >= '1.1.0') {
            $fields = plugin_geninventorynumber_getFieldInfos("otherserial");
            $gen_config = plugin_geninventorynumber_getConfig();
            $use_plugin_geninventorynumber = true;
         } else {
            $use_plugin_geninventorynumber = false;
         }
      } else {
         $use_plugin_geninventorynumber = false;
      }

      echo "<a href='" . $_SERVER["HTTP_REFERER"] . "'>" . $LANG['buttons'][13] . "</a></br><br>";

      echo "<form method='post' name='order_deviceGeneration' id='order_deviceGeneration'  action=" . $_SERVER["PHP_SELF"] . ">";
      
      echo "<table class='tab_cadre_fixe'>";
      $colspan = "5";
      if (isMultiEntitiesMode())
         $colspan = "6";
      echo "<tr><th colspan='$colspan'>" . $LANG['plugin_order']['delivery'][3] . "</tr></th>";
      echo "<tr><th>" . $LANG['plugin_order']['reference'][1] . "</th>";
      echo "<th>" . $LANG['common'][19] . "</th>";
      echo "<th>" . $LANG['common'][20] . "</th>";
      echo "<th>" . $LANG['common'][16] . "</th>";
      echo "<th>" . $LANG['common'][13] . "</th>";
      if (isMultiEntitiesMode())
         echo "<th>" . $LANG['entity'][0] . "</th>";
      echo "</tr>";
      echo "<input type='hidden' name='orderID' value=" . $params["orderID"] . ">";
      echo "<input type='hidden' name='referenceID' value=" . $params["referenceID"] . ">";

      $order = new PluginOrder;
      $order->getFromDB($params["orderID"]);
      
      $PluginOrderReference = new PluginOrderReference;
      
      $i = 0;
      $found = false;

      foreach ($params["item"] as $key => $val)
         if ($val == 1) {
            $detail = new PluginOrderDetail;
            $detail->getFromDB($key);

            if (!$detail->fields["FK_device"]) {

               if ($use_plugin_geninventorynumber && $gen_config->fields["active"] && $fields[$params['type'][$key]]['enabled'] && in_array($params['type'][$key], $GENINVENTORYNUMBER_INVENTORY_TYPES)) {
                  $gen_inventorynumber = true;
               } else {
                  $gen_inventorynumber = false;
               }

               echo "<tr class='tab_bg_1'><td align='center'>" . $_POST["name"][$key] . "</td>";
               $templateID = $PluginOrderReference->checkIfTemplateExistsInEntity($params["ID"][$key], $params['type'][$key], $order->fields["FK_entities"]);
               if ($templateID) {
                  $commonitem = new CommonItem;
                  $commonitem->setType($params['type'][$key], true);
                  $commonitem->getFromDB($params['type'][$key], $templateID);
                  $name = $commonitem->obj->fields["name"];
                  $serial = $commonitem->obj->fields["serial"];
                  $otherserial = $commonitem->obj->fields["otherserial"];
               }
               
               echo "<td><input type='text' size='20' name='ID[$i][serial]'></td>";

               //If geninventorynumber plugin is active, and this type is managed by the plugin
               if ($gen_inventorynumber) {
                  echo "<td align='center'>---------</td>";
               } else {
                  echo "<td><input type='text' size='20' name='ID[$i][otherserial]'></td>";
               }
               
               echo "<td><input type='text' size='20' name='ID[$i][name]'></td>";

               echo "<td align='center'>";
               if ($templateID) {
                  echo $PluginOrderReference->getTemplateName($params['type'][$key], $params['template'][$key]);
               }   
               echo "</td>";
               
               if (isMultiEntitiesMode()) {
                  echo "<td>";
                  $entity_restrict = ($order->fields["recursive"] ? getEntitySons($order->fields["FK_entities"]) : $order->fields["FK_entities"]);
                  dropdownValue("glpi_entities", "ID[$i][FK_entities]", $order->fields["FK_entities"], 1, $entity_restrict);
                  echo "</td>";
               } else {
                  echo "<input type='hidden' name='ID[$i][FK_entities]' value=" . $_SESSION["glpiactive_entity"] . ">";
               }
               echo "</tr>";
               echo "<input type='hidden' name='ID[$i][type]' value=" . $params['type'][$key] . ">";
               echo "<input type='hidden' name='ID[$i][ID]' value=" . $params["ID"][$key] . ">";
               echo "<input type='hidden' name='ID[$i][orderID]' value=" . $params["orderID"] . ">";
               $found = true;
            }
            $i++;
         }

      if ($found)
         echo "<tr><td align='center' colspan='$colspan' class='tab_bg_2'><input type='submit' name='generate' class='submit' value=" . $LANG['plugin_order']['delivery'][9] . "></td></tr>";
      else
         echo "<tr><td align='center' colspan='$colspan' class='tab_bg_2'>" . $LANG['plugin_order']['delivery'][17] . "</td></tr>";

      echo "</table>";
      echo "</form></div>";
   }
   
   function generateNewItem($params) {
      global $DB, $LANG;
      
      $i = 0;
      
      $PluginOrderReference = new PluginOrderReference;
      
      foreach ($params["ID"] as $tmp => $values) {
         
         $entity = $values["FK_entities"];
         //------------- Template management -----------------------//
         //Look for a template in the entity
         $templateID = $PluginOrderReference->checkIfTemplateExistsInEntity($values["ID"], $values["type"], $entity);
         
         $commonitem = new CommonItem;
         $commonitem->setType($values["type"], true);
         
         $order = new PluginOrder;
         $order->getFromDB($values["orderID"]);

         $reference = new PluginOrderReference;
         $reference->getFromDB($params["referenceID"]);
            
         if ($templateID) {
            
            $commonitem->getFromDB($values["type"], $templateID);
            unset ($commonitem->obj->fields["is_template"]);
            unset ($commonitem->obj->fields["date_mod"]);

            $fields = array ();
            foreach ($commonitem->obj->fields as $key => $value) {
               if ($value != '' && (!isset ($fields[$key]) || $fields[$key] == '' || $fields[$key] == 0))
                  $input[$key] = $value;
            }
            
            $input["FK_entities"] = $entity;
            $input["serial"] = $values["serial"];
            if ($values["name"])
               $input["name"] = $values["name"];
            else
                $input["name"] = autoName($commonitem->obj->fields["name"], "name", $templateID, $values["type"],$entity);
               
            if ($values["otherserial"])
               $input["otherserial"] = $values["otherserial"];
            else
               $input["otherserial"] = autoName($commonitem->obj->fields["otherserial"], "otherserial", $templateID, $values["type"],$entity);
               
            
         } else {
            $input["FK_entities"] = $entity;
            $input["serial"] = $values["serial"];
            if (isset ($values["otherserial"])) {
               $input["otherserial"] = $values["otherserial"];
            }

            $input["name"] = $values["name"];
            $input["type"] = $reference->fields["FK_type"];
            $input["model"] = $reference->fields["FK_model"];
            $input["FK_glpi_enterprise"] = $reference->fields["FK_glpi_enterprise"];
            /*if ($entity == $reference->fields["FK_entities"])
               $input["location"] = $order->fields["location"];*/
               
         }

         $newID = $commonitem->obj->add($input);

         //-------------- End template management ---------------------------------//
         $this->createLinkWithDevice($values["ID"], $newID, $values["type"], $values["orderID"], $entity, $templateID, false, false);

         //Add item's history
         $new_value = $LANG['plugin_order']['delivery'][13] . ' : ' . $order->fields["name"];
         $order->addHistory($values["type"], '', $new_value, $newID);

         //Add order's history
         $new_value = $LANG['plugin_order']['delivery'][13] . ' : ';
         $new_value .= $commonitem->getType() . " -> " . $commonitem->getField("name");
         $order->addHistory(PLUGIN_ORDER_TYPE, '', $new_value, $values["orderID"]);

         addMessageAfterRedirect($LANG['plugin_order']['detail'][30], true);
         $i++;
      }
   }
}

?>