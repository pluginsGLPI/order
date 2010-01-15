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

class PluginOrderReception extends CommonDBChild {

	public $dohistory=true;
	public $table="glpi_plugin_order_orders_items";
   
   public $itemtype = 'PluginOrderOrder';
   public $items_id = 'plugin_order_orders_id';
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order'][6];
   }
   
   function canCreate() {
      return plugin_order_haveRight('order', 'w');
   }

   function canView() {
      return plugin_order_haveRight('order', 'r');
   }
   
   function getFromDBByOrder($plugin_order_orders_id) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->table."`
					WHERE `plugin_order_orders_id` = '" . $plugin_order_orders_id . "' ";
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
	
	function checkThisItemStatus($detailID, $states_id) {
      global $DB;
      
      $query = "SELECT `states_id` 
               FROM `glpi_plugin_order_orders_items` 
               WHERE `id` = '$detailID' ";
      $result = $DB->query($query);
      if ($DB->result($result, 0, "states_id") == $states_id)
         return true;
      else
         return false;
   }
   
   function checkItemStatus($plugin_order_orders_id, $plugin_order_references_id, $states_id) {
      global $DB;
      
      $query = "SELECT COUNT(*) AS cpt 
               FROM `glpi_plugin_order_orders_items` 
               WHERE `plugin_order_orders_id` = '$plugin_order_orders_id' 
               AND `plugin_order_references_id` = '$plugin_order_references_id' 
               AND `states_id` = '".$states_id."' ";
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
		global $LANG;
      
      if (!plugin_order_haveRight("order", "r"))
			return false;
			
		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w',$input);
      }
      $this->showTabs($ID);

      $this->showFormHeader($target,$ID,'',1);
      
      $PluginOrderOrder = new PluginOrderOrder();
      $PluginOrderOrder->getFromDB($this->fields["plugin_order_orders_id"]);

      $PluginOrderReference = new PluginOrderReference();
      $PluginOrderReference->getFromDB($this->fields["plugin_order_references_id"]);
      
      $canedit = $PluginOrderOrder->can($this->fields["plugin_order_orders_id"], 'w') && !$PluginOrderOrder->canUpdateOrder($this->fields["plugin_order_orders_id"]) && $PluginOrderOrder->fields["states_id"] != ORDER_STATUS_CANCELED;
      
      echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order']['detail'][6] . ": </td>";
      echo "<td>";
      $data = array();
      $data["id"] = $this->fields["plugin_order_references_id"];
      $data["name"]= $PluginOrderReference->fields["name"];
      echo $PluginOrderReference->getReceptionReferenceLink($data);
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order']['detail'][21] . ": </td>";
      echo "<td>";
      if ($canedit)
         showDateFormItem("delivery_date",$this->fields["delivery_date"],true,1);
      else
         echo convDate($this->fields["delivery_date"]);
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_2'><td>" . $LANG['financial'][19] . ": </td>";
      echo "<td>";
      if ($canedit)
         autocompletionTextField($this,"delivery_number");
      else
         echo $this->fields["delivery_number"];
      echo "</td></tr>";
         
      $this->showFormButtons($ID,'',1,false);
      
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

		return true;
	}
	
	function showOrderReception($plugin_order_orders_id) {
      global $DB, $CFG_GLPI, $LANG;

      $PluginOrderOrder = new PluginOrderOrder();
      $PluginOrderOrder->getFromDB($plugin_order_orders_id);
      $PluginOrderOrder_Item = new PluginOrderOrder_Item();
      $PluginOrderReference = new PluginOrderReference();
      
      initNavigateListItems($this->getType(),$LANG['plugin_order'][7] ." = ". $PluginOrderOrder->fields["name"]);
      
      $canedit = $PluginOrderOrder->can($plugin_order_orders_id, 'w') && !$PluginOrderOrder->canUpdateOrder($plugin_order_orders_id) && $PluginOrderOrder->fields["states_id"] != ORDER_STATUS_CANCELED;
      
      $query_ref = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD, `glpi_plugin_order_orders_items`.`plugin_order_references_id` AS id, `glpi_plugin_order_references`.`name`, `glpi_plugin_order_references`.`itemtype`, `glpi_plugin_order_references`.`manufacturers_id` " .
      "FROM `glpi_plugin_order_orders_items`, `glpi_plugin_order_references` " .
      "WHERE `plugin_order_orders_id` = '$plugin_order_orders_id' " .
      "AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`  " .
      "GROUP BY `glpi_plugin_order_orders_items`.`plugin_order_references_id` " .
      "ORDER BY `glpi_plugin_order_orders_items`.`id`";
      $result_ref = $DB->query($query_ref);
      $numref = $DB->numrows($result_ref);

      while ($data_ref=$DB->fetch_array($result_ref)){
         
         addToNavigateListItems($this->getType(),$data_ref['IDD']);
         
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if (!$numref)
            echo "<tr><th>" . $LANG['plugin_order']['detail'][20] . "</th></tr></table></div>";
         else {
            
            $plugin_order_references_id = $data_ref["id"];
            $typeRef = $data_ref["itemtype"];		
            $item = new $typeRef();
            $rand = mt_rand();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('reception$rand','reception$rand','" . $CFG_GLPI["root_doc"] . "/pics/plus.png','" . $CFG_GLPI["root_doc"] . "/pics/moins.png');\">";
            echo "<img alt='' name='reception$rand' src=\"" . $CFG_GLPI["root_doc"] . "/pics/plus.png\">";
            echo "</a></li></ul></th>";
            echo "<th>" . $LANG['plugin_order']['detail'][6] . "</th>";
            echo "<th>" . $LANG['common'][5] . "</th>";
            echo "<th>" . $LANG['plugin_order']['reference'][1] . "</th>";
            echo "<th>" . $LANG['plugin_order']['delivery'][5] . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>" . $item->getTypeName() . "</td>";
            echo "<td align='center'>" . Dropdown::getDropdownName("glpi_manufacturers", $data_ref["manufacturers_id"]) . "</td>";
            echo "<td>" . $PluginOrderReference->getReceptionReferenceLink($data_ref) . "</td>";
            echo "<td>" . $PluginOrderOrder_Item->getDeliveredQuantity($plugin_order_orders_id, $plugin_order_references_id) . " / " . $PluginOrderOrder_Item->getTotalQuantityByRef($plugin_order_orders_id,$plugin_order_references_id) . "</td>";
            echo "</tr></table>";

            echo "<div class='center' id='reception$rand' style='display:none'>";
            echo "<form method='post' name='order_reception_form$rand' id='order_reception_form$rand'  action=\"" . $CFG_GLPI["root_doc"] . "/plugins/order/front/reception.form.php\">";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr>";
            echo "<th width='15'></th>";
            echo "<th>" . $LANG['common'][2] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][2] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][19] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][21] . "</th>";
            echo "<th>" . $LANG['financial'][19] . "</th>";
            echo "</tr>";
            
            $query = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD, `glpi_plugin_order_references`.`id` AS id,`glpi_plugin_order_references`.`templates_id`, `glpi_plugin_order_orders_items`.`states_id`, `glpi_plugin_order_orders_items`.`delivery_date`,`glpi_plugin_order_orders_items`.`delivery_number`, `glpi_plugin_order_references`.`name`, `glpi_plugin_order_references`.`itemtype`, `glpi_plugin_order_orders_items`.`items_id`
                    FROM `glpi_plugin_order_orders_items`, `glpi_plugin_order_references`
                    WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
                    AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = '".$plugin_order_references_id."'
                    AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`
                    ORDER BY `glpi_plugin_order_orders_items`.`id`";
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
               
               echo "<td align='center'>" . $data["IDD"] . "</td>";
               echo "<td align='center'>" . $PluginOrderReference->getReceptionReferenceLink($data) . "</td>";
               echo "<td align='center'>";
               $link=getItemTypeFormURL($this->getType());
               if ($canedit && $data["states_id"]==ORDER_DEVICE_DELIVRED)
                  echo "<a href=\"" . $link . "?id=".$data["IDD"]."\">";
               echo $this->getReceptionStatus($detailID);
               if ($canedit && $data["states_id"]==ORDER_DEVICE_DELIVRED)
                  echo "</a>";
               echo "</td>";
               echo "<td align='center'>" . convDate($data["delivery_date"]) . "</td>";
               echo "<td align='center'>" . $data["delivery_number"] . "</td>";

               echo "<input type='hidden' name='id[$detailID]' value='$detailID'>";
               echo "<input type='hidden' name='name[$detailID]' value='" . $data["name"] . "'>";
               echo "<input type='hidden' name='itemtype[$detailID]' value='" . $data["itemtype"] . "'>";
               echo "<input type='hidden' name='templates_id[$detailID]' value='" . $data["templates_id"] . "'>";
               echo "<input type='hidden' name='states_id[$detailID]' value='" . $data["states_id"] . "'>";

            }
            echo "</table>";
            if ($canedit && $this->checkItemStatus($plugin_order_orders_id, $plugin_order_references_id, ORDER_DEVICE_NOT_DELIVRED)) {
               
               echo "<div class='center'>";
               echo "<table width='950px' class='tab_glpi'>";
               echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('order_reception_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?id=$plugin_order_orders_id&amp;select=all'>".$LANG['buttons'][18]."</a></td>";

               echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('order_reception_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?id=$plugin_order_orders_id&amp;select=none'>".$LANG['buttons'][19]."</a>";
               echo "</td><td align='left' width='80%'>";
               echo "<input type='hidden' name='plugin_order_orders_id' value='$plugin_order_orders_id'>";
               $this->dropdownReceptionActions($typeRef, $plugin_order_references_id, $plugin_order_orders_id);
               echo "</td>";
               echo "</table>";
               echo "</div>";
               
               $rand = mt_rand();
               
               echo "<div id='massreception" . $plugin_order_orders_id . "$rand'></div>\n";
               
               echo "<script type='text/javascript' >\n";
               echo "function viewmassreception" . $plugin_order_orders_id . "$rand(){\n";
               $params = array ('plugin_order_orders_id' => $plugin_order_orders_id,
                                'plugin_order_references_id' => $plugin_order_references_id);
               ajaxUpdateItemJsCode("massreception" . $plugin_order_orders_id . "$rand",
                                    $CFG_GLPI["root_doc"]."/plugins/order/ajax/massreception.php", $params, false);
               echo "};";
               echo "</script>\n";
               echo "<p><a href='javascript:viewmassreception".$plugin_order_orders_id."$rand();'>";
               echo $LANG['plugin_order']['delivery'][4]."</a></p><br>\n";
            }
            echo "</form></div>";
         }
         echo "<br>";
      }
   }
   
   function dropdownReceptionActions($itemtype,$plugin_order_references_id,$plugin_order_orders_id) {
      global $LANG,$CFG_GLPI;
      
      $rand = mt_rand();

      echo "<select name='receptionActions$rand' id='receptionActions$rand'>";
      echo "<option value='0' selected>-----</option>";
      echo "<option value='reception'>" . $LANG['plugin_order']['delivery'][2] . "</option>";
      echo "</select>";
      $params = array (
         'action' => '__VALUE__',
         'itemtype' => $itemtype,
         'plugin_order_references_id'=>$plugin_order_references_id,
         'plugin_order_orders_id'=>$plugin_order_orders_id
      );
      ajaxUpdateItemOnSelectEvent("receptionActions$rand", "show_receptionActions$rand", $CFG_GLPI["root_doc"] . "/plugins/order/ajax/receptionactions.php", $params);
      echo "<span id='show_receptionActions$rand'>&nbsp;</span>";
   }
   
   function showOrderGeneration($plugin_order_orders_id) {
      global $DB, $CFG_GLPI, $LANG;

      $PluginOrderOrder = new PluginOrderOrder();
      $PluginOrderOrder_Item = new PluginOrderOrder_Item();
      $PluginOrderReference = new PluginOrderReference();
      $PluginOrderOrder->getFromDB($plugin_order_orders_id);
      $canedit = $PluginOrderOrder->can($plugin_order_orders_id, 'w') && !$PluginOrderOrder->canUpdateOrder($plugin_order_orders_id) && $PluginOrderOrder->fields["states_id"] != ORDER_STATUS_CANCELED;
      
      $query_ref = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD, `glpi_plugin_order_orders_items`.`plugin_order_references_id` AS id, `glpi_plugin_order_references`.`name`, `glpi_plugin_order_references`.`itemtype`, `glpi_plugin_order_references`.`manufacturers_id` " .
      "FROM `glpi_plugin_order_orders_items`, `glpi_plugin_order_references` " .
      "WHERE `plugin_order_orders_id` = '$plugin_order_orders_id' " .
      "AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`  " .
      "AND `glpi_plugin_order_orders_items`.`states_id` = '".ORDER_DEVICE_DELIVRED."'   " .
      "GROUP BY `glpi_plugin_order_orders_items`.`plugin_order_references_id` " .
      "ORDER BY `glpi_plugin_order_orders_items`.`id`";
      $result_ref = $DB->query($query_ref);
      $numref = $DB->numrows($result_ref);

      while ($data_ref=$DB->fetch_array($result_ref)){

         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if (!$numref)
            echo "<tr><th>" . $LANG['plugin_order']['detail'][20] . "</th></tr></table></div>";
         else {
            
            $plugin_order_references_id = $data_ref["id"];
            $typeRef = $data_ref["itemtype"];		
            
            $item = new $typeRef();
            $rand = mt_rand();
            echo "<tr><th><ul><li>";
            echo "<a href=\"javascript:showHideDiv('generation$rand','generation$rand','" . $CFG_GLPI["root_doc"] . "/pics/plus.png','" . $CFG_GLPI["root_doc"] . "/pics/moins.png');\">";
            echo "<img alt='' name='generation$rand' src=\"" . $CFG_GLPI["root_doc"] . "/pics/plus.png\">";
            echo "</a></li></ul></th>";
            echo "<th>" . $LANG['plugin_order']['detail'][6] . "</th>";
            echo "<th>" . $LANG['common'][5] . "</th>";
            echo "<th>" . $LANG['plugin_order']['reference'][1] . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td></td>";
            echo "<td align='center'>" . $item->getTypeName() . "</td>";
            echo "<td align='center'>" . Dropdown::getDropdownName("glpi_manufacturers", $data_ref["manufacturers_id"]) . "</td>";
            echo "<td>" . $PluginOrderReference->getReceptionReferenceLink($data_ref) . "</td>";
            echo "</tr></table>";

            echo "<div class='center' id='generation$rand' style='display:none'>";
            echo "<form method='post' name='order_generation_form$rand' id='order_generation_form$rand'  action=\"" . $CFG_GLPI["root_doc"] . "/plugins/order/front/reception.form.php\">";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr>";
            if ($canedit)
               echo "<th width='15'></th>";
            echo "<th>" . $LANG['common'][2] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][2] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][19] . "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][21] . "</th>";
            echo "<th>" . $LANG['plugin_order']['item'][0] . "</th></tr>";
            
            $query = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD, `glpi_plugin_order_references`.`id` AS id,`glpi_plugin_order_references`.`templates_id`, `glpi_plugin_order_orders_items`.`states_id`, `glpi_plugin_order_orders_items`.`delivery_date`,`glpi_plugin_order_orders_items`.`delivery_number`, `glpi_plugin_order_references`.`name`, `glpi_plugin_order_references`.`itemtype`, `glpi_plugin_order_orders_items`.`items_id`
                    FROM `glpi_plugin_order_orders_items`, `glpi_plugin_order_references`
                    WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
                    AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = '".$plugin_order_references_id."'
                    AND `glpi_plugin_order_orders_items`.`states_id` = '".ORDER_DEVICE_DELIVRED."'
                    AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `glpi_plugin_order_references`.`id`
                    ORDER BY `glpi_plugin_order_orders_items`.`id`";
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
               
               echo "<td align='center'>" . $data["IDD"] . "</td>";
               echo "<td align='center'>" . $PluginOrderReference->getReceptionReferenceLink($data) . "</td>";
               echo "<td align='center'>" . $this->getReceptionStatus($detailID) . "</td>";
               echo "<td align='center'>" . convDate($data["delivery_date"]) . "</td>";
               echo "<td align='center'>" . $this->getReceptionDeviceName($data["items_id"], $data["itemtype"]);
               if ($data["items_id"] != 0) {
                  echo "&nbsp;<img alt='' src='" . $CFG_GLPI["root_doc"] . "/pics/aide.png' onmouseout=\"cleanhide('comments_$random')\" onmouseover=\"cleandisplay('comments_$random')\" ";
                  echo "<span class='over_link' id='comments_$random'>" . nl2br($this->getReceptionMaterialInfo($data["itemtype"], $data["items_id"])) . "</span>";
               }
               echo "<input type='hidden' name='id[$detailID]' value='$detailID'>";
               echo "<input type='hidden' name='name[$detailID]' value='" . $data["name"] . "'>";
               echo "<input type='hidden' name='itemtype[$detailID]' value='" . $data["itemtype"] . "'>";
               echo "<input type='hidden' name='templates_id[$detailID]' value='" . $data["templates_id"] . "'>";
               echo "<input type='hidden' name='states_id[$detailID]' value='" . $data["states_id"] . "'>";

            }
            echo "</table>";
            if ($canedit) {
               
               echo "<div class='center'>";
               echo "<table width='950px' class='tab_glpi'>";
               echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('order_generation_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?id=$plugin_order_orders_id&amp;select=all'>".$LANG['buttons'][18]."</a></td>";

               echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('order_generation_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?id=$plugin_order_orders_id&amp;select=none'>".$LANG['buttons'][19]."</a>";
               echo "</td><td align='left' width='80%'>";
               echo "<input type='hidden' name='plugin_order_orders_id' value='$plugin_order_orders_id'>";
               $this->dropdownGenerationActions($typeRef, $plugin_order_references_id, $plugin_order_orders_id);
               echo "</td>";
               echo "</table>";
               echo "</div>";
            }
            echo "</form></div>";
         }
         echo "<br>";
      }
   }
   
   function getReceptionMaterialInfo($itemtype, $items_id) {
      global $LANG;
      
      $comments = "";
      switch ($itemtype) {
         case 'Computer' :
         case 'Monitor' :
         case 'NetworkEquipment' :
         case 'Peripheral' :
         case 'Phone' :
         case 'Printer' :
         default :
            
            $item = new $itemtype();
            $item->getFromDB($items_id);

            if ($item->getField("name")) {
               $comments = "<strong>" . $LANG['common'][16] . ":</strong> " . $item->getField("name");
            }

            if ($item->getField("entities_id")) {
               $comments = "<strong>" . $LANG['entity'][0] . ":</strong> " . Dropdown::getDropdownName("glpi_entities", $item->getField("entities_id"));
            }

            if ($item->getField("serial") != '') {
               $comments .= "<br><strong>" . $LANG['common'][19] . ":</strong> " . $item->getField("serial");
            }

            if ($item->getField("otherserial") != '') {
               $comments .= "<br><strong>" . $LANG['common'][20] . ":</strong> " . $item->getField("otherserial");
            }
            if ($item->getField("locations_id")) {
               $comments .= "<br><strong>" . $LANG['common'][15] . ":</strong> " . Dropdown::getDropdownName('glpi_locations', $item->getField("locations_id"));
            }

            if ($item->getField("users_id")) {
               $comments .= "<br><strong>" . $LANG['common'][34] . ":</strong> " . Dropdown::getDropdownName('glpi_users', $item->getField("users_id"));
            }
            break;
         case 'ConsumableItem' :
            $ci = new Consumable();
            if ($ci->getFromDB($items_id)) {
               $ct = new ConsumableItem;
               $ct->getFromDB($ci->fields['consumableitems_id']);
               $comments = "<strong>" . $LANG['entity'][0] . ":</strong> " . Dropdown::getDropdownName("glpi_entities", $ct->fields["entities_id"]);
               $comments .= '<br><strong>' . $LANG['consumables'][0] . ' : </strong> #' . $items_id;
               $comments .= '<br><strong>' . $LANG['consumables'][12] . ' : </strong>' . $ct->fields['name'];
               $comments .= '<br><strong>' . $LANG['common'][5] . ' : </strong>' . Dropdown::getDropdownName('glpi_manufacturers', $ct->fields['manufacturers_id']);
               $comments .= '<br><strong>' . $LANG['consumables'][23] . ' : </strong>' . (!$ci->fields['id_user'] ? $LANG['consumables'][1] : $LANG['consumables'][15]);
               if ($ci->fields['id_user'])
                  $comments .= '<br><strong>' . $LANG['common'][34] . ' : </strong>' . Dropdown::getDropdownName('glpi_users', $ci->fields['id_user']);
            }
            break;
         case 'CartridgeItem' :
            $ci = new Cartridge();
            if ($ci->getFromDB($items_id)) {
               $ct = new CartridgeItem;
               $ct->getFromDB($ci->fields['cartridgeitems_id']);
               $comments = "<strong>" . $LANG['entity'][0] . ":</strong> " . Dropdown::getDropdownName("glpi_entities", $ct->fields["entities_id"]);
               $comments .= '<br><strong>' . $LANG['cartridges'][0] . ' : </strong> #' . $items_id;
               $comments .= '<br><strong>' . $LANG['cartridges'][12] . ' : </strong>' . $ct->fields['name'];
               $comments .= '<br><strong>' . $LANG['common'][5] . ' : </strong>' . Dropdown::getDropdownName('glpi_manufacturers', $ct->fields['manufacturers_id']);
            }
      }

      return ($comments);
   }
   
   function getReceptionStatus($ID) {
      global $DB, $LANG;

      $detail = new PluginOrderOrder_Item;
      $detail->getFromDB($ID);

      switch ($detail->fields["states_id"]) {
         case ORDER_DEVICE_NOT_DELIVRED :
            return $LANG['plugin_order']['status'][11];
         case ORDER_DEVICE_DELIVRED :
            return $LANG['plugin_order']['status'][8];
         default :
            return "";
      }
   }

   function getReceptionDeviceName($items_id, $itemtype) {
      global $CFG_GLPI, $LANG;
      
      if ($items_id == 0)
         return ($LANG['plugin_order']['item'][2]);
      else {
         switch ($itemtype) {
            case 'Computer' :
            case 'Monitor' :
            case 'NetworkEquipment' :
            case 'Peripheral' :
            case 'Phone' :
            case 'Printer' :
            default :
               $item = new $itemtype();
               $item->getFromDB($items_id);
               $name = $item->getField("name");
               $link=getItemTypeFormURL($item->getType());
               if ($_SESSION["glpiis_ids_visible"] || empty($name)) $name.=" (".$items_id.")";
               return ("<a href=" . $link . "?id=" . $items_id . "&itemtype=" . $itemtype . ">" . $name."</a>");
               break;
            case 'ConsumableItem' :
               $ci = new Consumable();
               $ci->getFromDB($items_id);
               $ct = new ConsumableItem;
               $link=getItemTypeFormURL($ct->getType());
               $ct->getFromDB($ci->fields['consumableitems_id']);
               return ("<a href=" . $link . "?id=" . $ct->fields['id'] . ">" . $LANG['consumables'][0] . ': #' . $items_id . ' (' . $ct->fields["name"] . ')' . "</a>");
               break;
            case 'CartridgeItem' :
               $ci = new Cartridge();
               $ci->getFromDB($items_id);
               $ct = new CartridgeItem;
               $link=getItemTypeFormURL($ct->getType());
               $ct->getFromDB($ci->fields['cartridgeitems_id']);
               return ("<a href=" . $link . "?id=" . $ct->fields['id'] . ">" . $LANG['cartridges'][0] . ': #' . $items_id . ' (' . $ct->fields["name"] . ')' . "</a>");
               break;
         }
      }
   }

   function dropdownGenerationActions($type,$plugin_order_references_id,$plugin_order_orders_id) {
      global $LANG,$CFG_GLPI;
      
      $rand = mt_rand();

      echo "<select name='generationActions$rand' id='generationActions$rand'>";
      echo "<option value='0' selected>-----</option>";
      
      $restricted = array('ConsumableItem',
                           'CartridgeItem',
                           'SoftwareLicense',
                           'Contract');
      
      if ($this->checkItemStatus($plugin_order_orders_id, $plugin_order_references_id, ORDER_DEVICE_DELIVRED)) {
         if (!in_array($type, $restricted))
            echo "<option value='generation'>" . $LANG['plugin_order']['delivery'][3] . "</option>";

         echo "<option value='createLink'>" . $LANG['plugin_order']['delivery'][11] . "</option>";
         echo "<option value='deleteLink'>" . $LANG['plugin_order']['delivery'][12] . "</option>";
      }
         
      echo "</select>";
      $params = array (
         'action' => '__VALUE__',
         'itemtype' => $type,
         'plugin_order_references_id'=>$plugin_order_references_id,
         'plugin_order_orders_id'=>$plugin_order_orders_id
      );
      ajaxUpdateItemOnSelectEvent("generationActions$rand", "show_generationActions$rand", $CFG_GLPI["root_doc"] . "/plugins/order/ajax/generationactions.php", $params);
      echo "<span id='show_generationActions$rand'>&nbsp;</span>";
   }
   
   function itemAlreadyLinkedToAnOrder($itemtype, $items_id, $plugin_order_orders_id, $detailID = 0) {
      global $DB;
      
      $restricted = array('ConsumableItem',
                           'CartridgeItem');
                           
      if (!in_array($itemtype, $restricted)) {
         $query = "SELECT COUNT(*) AS cpt 
                  FROM `glpi_plugin_order_orders_items` 
                  WHERE `plugin_order_orders_id` = '$plugin_order_orders_id' 
                  AND `items_id` = '$items_id' 
                  AND `itemtype` = '$itemtype' ";

         $result = $DB->query($query);
         if ($DB->result($result, 0, "cpt") > 0)
            return true;
         else
            return false;
      } else {
         $detail = new PluginOrderOrder_Item;
         $detail->getFromDB($detailID);
         if (!$detail->fields['items_id']) {
            return false;
         } else {
            return true;
         }
      }
   }
   
   function generateInfoComRelatedToOrder($entity, $detailID, $itemtype, $items_id, $templateID = 0) {
      global $LANG;

      $detail = new PluginOrderOrder_Item;
      $detail->getFromDB($detailID);
      $order = new PluginOrderOrder;
      $order->getFromDB($detail->fields["plugin_order_orders_id"]);
      
      $PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier;
      $PluginOrderOrder_Supplier->getFromDBByOrder($detail->fields["plugin_order_orders_id"]);
      // ADD Infocoms
      $ic = new Infocom();
      $fields = array ();

      $exists = false;

      if ($templateID) {
         if ($ic->getFromDBforDevice($itemtype, $templateID)) {
            $fields = $ic->fields;
            unset ($fields["id"]);
            if (isset ($fields["num_immo"])) {
               $fields["num_immo"] = autoName($fields["num_immo"], "num_immo", 1, 'Infocom', $entity);
            }
            if (empty ($fields['use_date'])) {
               unset ($fields['use_date']);
            }
            if (empty ($fields['buy_date'])) {
               unset ($fields['buy_date']);
            }
         }
      }

      if ($ic->getFromDBforDevice($itemtype, $items_id)) {
         $exists = true;
         $fields["id"] = $ic->fields["id"];
      }

      $fields["itemtype"] = $itemtype;
      $fields["items_id"] = $items_id;
      $fields["num_commande"] = $order->fields["num_order"];
      $fields["bon_livraison"] = $detail->fields["delivery_number"];
      $fields["budgets_id"] = $order->fields["budgets_id"];
      $fields["suppliers_id"] = $order->fields["suppliers_id"];
      if (isset($PluginOrderOrder_Supplier->fields["numbill"]))
         $fields["facture"] = $PluginOrderOrder_Supplier->fields["num_bill"];
      $fields["value"] = $detail->fields["price_discounted"];
      $fields["buy_date"] = $order->fields["order_date"];

      //DO not check infocom modifications
      $fields["_manage_by_order"] = 1;

      if (!$exists)
         $ic->add($fields);
      else
         $ic->update($fields);
   }
   
   function removeInfoComRelatedToOrder($itemtype, $items_id) {

	$infocom = new InfoCom;
	$infocom->getFromDBforDevice($itemtype, $items_id);
	$input["id"] = $infocom->fields["id"];
	$input["num_commande"] = "";
	$input["bon_livraison"] = "";
	$input["budget"] = 0;
	$input["suppliers_id"] = 0;
	$input["facture"] = "";
	$input["value"] = 0;
	$input["buy_date"] = "0000:00:00";

	//DO not check infocom modifications
	$input["_manage_by_order"] = 1;

	$infocom->update($input);
}

   function createLinkWithItem($detailID = 0, $items_id = 0, $itemtype = 0, $plugin_order_orders_id = 0, $entity = 0, $templateID = 0, $history = true, $check_link = true) {
      global $LANG;

      if (!$check_link || !$this->itemAlreadyLinkedToAnOrder($itemtype, $items_id, $plugin_order_orders_id, $detailID)) {
         $detail = new PluginOrderOrder_Item;
         
         $restricted = array('ConsumableItem',
                           'CartridgeItem');
                       
         if (in_array($itemtype, $restricted)) {
         
            if ($itemtype == 'ConsumableItem') {
               $item = new Consumable;
               $type = 'Consumable';
            } elseif ($itemtype == 'CartridgeItem') {
               $item = new Cartridge;
               $type = 'Cartridge';
            }
            $detail->getFromDB($detailID);
            $input["tID"] = $items_id;
            $input["date_in"] = $detail->fields["delivery_date"];
            $newID = $item->add($input);

            $input["id"] = $detailID;
            $input["items_id"] = $newID;
            $input["itemtype"] = $itemtype;
            $detail->update($input);

            $this->generateInfoComRelatedToOrder($entity, $detailID, $type, $newID, 0);
         } else {
            $input["id"] = $detailID;
            $input["items_id"] = $items_id;
            $input["itemtype"] = $itemtype;
            $detail->update($input);
            $detail->getFromDB($detailID);
            $this->generateInfoComRelatedToOrder($entity, $detailID, $itemtype, $items_id, $templateID);
         }
         if ($history) {
            $order = new PluginOrderOrder;
            $order->getFromDB($detail->fields["plugin_order_orders_id"]);
            $new_value = $LANG['plugin_order']['delivery'][14] . ' : ' . $order->fields["name"];
            $order->addHistory($itemtype, '', $new_value, $items_id);
         }
         addMessageAfterRedirect($LANG['plugin_order']['delivery'][14], true);
      } else
         addMessageAfterRedirect($LANG['plugin_order']['delivery'][16], true, ERROR);

   }
   
   function deleteLinkWithItem($detailID, $itemtype, $plugin_order_orders_id) {
      global $DB, $LANG;
      
      $order = new PluginOrderOrder;
      $order->getFromDB($plugin_order_orders_id);
            
      $detail = new PluginOrderOrder_Item;
      $detail->getFromDB($detailID);
      $items_id = $detail->fields["items_id"];

      $this->removeInfoComRelatedToOrder($itemtype, $items_id);

      if ($items_id != 0) {
         $input = $detail->fields;
         $input["items_id"] = 0;
         $detail->update($input);
      } else
         addMessageAfterRedirect($LANG['plugin_order'][48], TRUE, ERROR);

      $new_value = $LANG['plugin_order']['delivery'][15] . ' : ' . $order->fields["name"];
      $order->addHistory($itemtype, '', $new_value, $items_id);
      
      $item = new $itemtype();
      $item->getFromDB($items_id);
      $new_value = $LANG['plugin_order']['delivery'][15] . ' : ' . $item->getField("name");
      $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);

   }
   
   function updateBulkReceptionStatus($params) {
      global $LANG, $DB;
      
      $query = "SELECT `id` 
               FROM `glpi_plugin_order_orders_items` 
               WHERE `plugin_order_orders_id` = '" . $params["plugin_order_orders_id"] ."' 
               AND `plugin_order_references_id` = '" . $params["plugin_order_references_id"] ."' 
               AND `states_id` = 0 ";
      $result = $DB->query($query);
      $nb = $DB->numrows($result);
      if ($nb < $params['number_reception'])
         addMessageAfterRedirect($LANG['plugin_order']['detail'][37], true, ERROR);
      else {
         for ($i = 0; $i < $params['number_reception']; $i++) {
            $this->receptionOneItem($DB->result($result, $i, 0), $params['plugin_order_orders_id'], $params["delivery_date"], $params["delivery_number"]);
         }
         $detail = new PluginOrderOrder_Item;
         $detail->updateDelivryStatus($params['plugin_order_orders_id']);
      }
   }
   
   function receptionOneItem($detailID, $plugin_order_orders_id, $delivery_date, $delivery_number) {
      global $LANG;
      
      $detail = new PluginOrderOrder_Item;
      $input["id"] = $detailID;
      $input["delivery_date"] = $delivery_date;
      $input["states_id"] = ORDER_DEVICE_DELIVRED;
      $input["delivery_number"] = $delivery_number;
      $detail->update($input);
      addMessageAfterRedirect($LANG['plugin_order']['detail'][31], true);
   }
   
   function updateReceptionStatus($params) {
      global $LANG;
      
      $detail = new PluginOrderOrder_Item;
      $plugin_order_orders_id = 0;
      if (isset ($params["item"])) {
         foreach ($params["item"] as $key => $val)
            if ($val == 1) {
               if ($detail->getFromDB($key)) {
                  if (!$plugin_order_orders_id)
                     $plugin_order_orders_id = $detail->fields["plugin_order_orders_id"];

                  if ($detail->fields["states_id"] == ORDER_DEVICE_NOT_DELIVRED) {
                     $this->receptionOneItem($key, $plugin_order_orders_id, $params["delivery_date"], $params["delivery_number"]);
                  } else
                     addMessageAfterRedirect($LANG['plugin_order']['detail'][32], true, ERROR);
               }
            }

         $detail->updateDelivryStatus($plugin_order_orders_id);
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
      echo "<input type='hidden' name='plugin_order_orders_id' value=" . $params["plugin_order_orders_id"] . ">";
      echo "<input type='hidden' name='plugin_order_references_id' value=" . $params["plugin_order_references_id"] . ">";

      $order = new PluginOrderOrder;
      $order->getFromDB($params["plugin_order_orders_id"]);
      
      $PluginOrderReference = new PluginOrderReference;
      
      $i = 0;
      $found = false;

      foreach ($params["item"] as $key => $val)
         if ($val == 1) {
            $detail = new PluginOrderOrder_Item;
            $detail->getFromDB($key);

            if (!$detail->fields["items_id"]) {

               if ($use_plugin_geninventorynumber && $gen_config->fields["active"] && $fields[$params['itemtype'][$key]]['enabled'] && in_array($params['itemtype'][$key], $GENINVENTORYNUMBER_INVENTORY_TYPES)) {
                  $gen_inventorynumber = true;
               } else {
                  $gen_inventorynumber = false;
               }

               echo "<tr class='tab_bg_1'><td align='center'>" . $_POST["name"][$key] . "</td>";
               $templateID = $PluginOrderReference->checkIfTemplateExistsInEntity($params["id"][$key], $params['itemtype'][$key], $order->fields["entities_id"]);
               if ($templateID) {
                  $item = new $params['itemtype'][$key]();
                  $item->getFromDB($templateID);
                  
                  $name = $item->fields["name"];
                  $serial = $item->fields["serial"];
                  $otherserial = $item->fields["otherserial"];
               }
               if (!$templateID) {
                  echo "<td><input type='text' size='20' name='id[$i][serial]'></td>";
               } else {
                  echo "<td>".$serial."</td>";
               }
               //If geninventorynumber plugin is active, and this type is managed by the plugin
               if ($gen_inventorynumber) {
                  echo "<td align='center'>---------</td>";
               } else {
                  if (!$templateID) {
                     echo "<td><input type='text' size='20' name='id[$i][otherserial]'></td>";
                  } else {
                     echo "<td>".$otherserial."</td>";
                  }
               }
               if (!$templateID) {
                  echo "<td><input type='text' size='20' name='id[$i][name]'></td>";
                } else {
                  echo "<td>".$name."</td>";
               }
               echo "<td align='center'>";
               if ($templateID) {
                  echo $PluginOrderReference->getTemplateName($params['itemtype'][$key], $params['templates_id'][$key]);
               }   
               echo "</td>";
               
               if (isMultiEntitiesMode()) {
                  echo "<td>";
                  $entity_restrict = ($order->fields["is_recursive"] ? getEntitySons($order->fields["entities_id"]) : $order->fields["entities_id"]);
                  Dropdown::show('Entity', array('name' => "id[$i][entities_id]",'value' => $order->fields["entities_id"], 'entity' => $entity_restrict));
                  echo "</td>";
               } else {
                  echo "<input type='hidden' name='id[$i][entities_id]' value=" . $_SESSION["glpiactive_entity"] . ">";
               }
               echo "</tr>";
               echo "<input type='hidden' name='id[$i][itemtype]' value=" . $params['itemtype'][$key] . ">";
               echo "<input type='hidden' name='id[$i][id]' value=" . $params["id"][$key] . ">";
               echo "<input type='hidden' name='id[$i][plugin_order_orders_id]' value=" . $params["plugin_order_orders_id"] . ">";
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
      
      foreach ($params["id"] as $tmp => $values) {
         
         $entity = $values["entities_id"];
         //------------- Template management -----------------------//
         //Look for a template in the entity
         $templateID = $PluginOrderReference->checkIfTemplateExistsInEntity($values["id"], $values["itemtype"], $entity);
         
         $order = new PluginOrderOrder;
         $order->getFromDB($values["plugin_order_orders_id"]);

         $reference = new PluginOrderReference;
         $reference->getFromDB($params["plugin_order_references_id"]);
         
         $item = new $values["itemtype"]();
          
         if ($templateID) {

            $item->getFromDB($templateID);
            unset ($item->fields["is_template"]);
            unset ($item->fields["date_mod"]);

            $fields = array ();
            foreach ($item->fields as $key => $value) {
               if ($value != '' && (!isset ($fields[$key]) || $fields[$key] == '' || $fields[$key] == 0))
                  $input[$key] = $value;
            }
            
            $input["entities_id"] = $entity;
            $input["name"] = autoName($item->fields["name"], "name", $templateID, $values["itemtype"],$entity);
            $input["otherserial"] = autoName($item->fields["otherserial"], "otherserial", $templateID, $values["itemtype"],$entity);
            
         } else {
            $input["entities_id"] = $entity;
            $input["serial"] = $values["serial"];
            if (isset ($values["otherserial"])) {
               $input["otherserial"] = $values["otherserial"];
            }

            $input["name"] = $values["name"];

            $typefield = getForeignKeyFieldForTable(getTableForItemType($values["itemtype"]."Type"));
            $input[$typefield] = $reference->fields["types_id"];
            $modelfield = getForeignKeyFieldForTable(getTableForItemType($values["itemtype"]."Model"));
            $input[$modelfield] = $reference->fields["models_id"];

            $input["manufacturers_id"] = $reference->fields["manufacturers_id"];
            /*if ($entity == $reference->fields["entities_id"])
               $input["locations_id"] = $order->fields["locations_id"];*/
               
         }

         $newID = $item->add($input);

         //-------------- End template management ---------------------------------//
         $this->createLinkWithItem($values["id"], $newID, $values["itemtype"], $values["plugin_order_orders_id"], $entity, $templateID, false, false);

         //Add item's history
         $new_value = $LANG['plugin_order']['delivery'][13] . ' : ' . $order->fields["name"];
         $order->addHistory($values["itemtype"], '', $new_value, $newID);

         //Add order's history
         $new_value = $LANG['plugin_order']['delivery'][13] . ' : ';
         $new_value .= $item->getTypeName() . " -> " . $item->getField("name");
         $order->addHistory('PluginOrderOrder', '', $new_value, $values["plugin_order_orders_id"]);

         addMessageAfterRedirect($LANG['plugin_order']['detail'][30], true);
         $i++;
      }
   }
}

?>