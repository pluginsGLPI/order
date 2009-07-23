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
function plugin_order_plugin_order_showDetailReceptionForm($orderID) {
	global $DB, $CFG_GLPI, $LANG, $LINK_ID_TABLE, $INFOFORM_PAGES;

	$plugin_order = new PluginOrder();
	$canedit = $plugin_order->can($orderID, 'w') && !plugin_order_canUpdateOrder($orderID) && $plugin_order->fields["status"] != ORDER_STATUS_CANCELED;
	$query_ref = "SELECT glpi_plugin_order_detail.ID, glpi_plugin_order_detail.FK_reference AS ref, name, type " .
	"FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references` " .
	"WHERE FK_order=$orderID " .
	"AND glpi_plugin_order_detail.FK_reference=glpi_plugin_order_references.ID  " .
	"GROUP BY glpi_plugin_order_detail.FK_reference " .
	"ORDER BY glpi_plugin_order_detail.ID";
	$result_ref = $DB->query($query_ref);
	$numref = $DB->numrows($result_ref);
	$j = 0;

	while ($j < $numref || $j == 0) {
		if ($numref) {
			$refID = $DB->result($result_ref, $j, 'ref');
			$typeRef = $DB->result($result_ref, $j, 'type');
			$query = "SELECT glpi_plugin_order_detail.ID AS IDD, glpi_plugin_order_references.ID AS IDR, status, date, price_taxfree,
								  price_ati, price_discounted,  FK_manufacturer, name, type, FK_device
								  FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references`
								  WHERE FK_order=$orderID
								  AND glpi_plugin_order_detail.FK_reference=$refID
								  AND glpi_plugin_order_detail.FK_reference=glpi_plugin_order_references.ID
								  ORDER BY glpi_plugin_order_detail.ID";
			$result = $DB->query($query);
			$num = $DB->numrows($result);
		}

		echo "<div class='center'><table class='tab_cadre_fixe'>";
		if (!$numref)
			echo "<tr><th>" . $LANG['plugin_order']['detail'][20] . "</th></tr></table></div>";
		else {
			$rand = mt_rand();
			echo "<tr><th><ul><li>";
			echo "<a href=\"javascript:showHideDiv('reception$rand','reception$rand','" . $CFG_GLPI["root_doc"] . "/pics/plus.png','" . $CFG_GLPI["root_doc"] . "/pics/moins.png');\">";
			echo "<img alt='' name='reception$rand' src=\"" . $CFG_GLPI["root_doc"] . "/pics/plus.png\">";
			echo "</a></li></ul></th>";
			echo "<th>" . $LANG['plugin_order']['reference'][1] . "</th>";
			echo "<th>" . $LANG['plugin_order']['delivery'][5] . "</th>";
			echo "<th>" . $LANG['plugin_order']['item'][0] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][4] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][8] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][18] . "</th></tr>";
			echo "<tr><td class='tab_bg_1' width='15'></td><td align='center' class='tab_bg_1'>" . getReceptionReferenceLink($refID, $DB->result($result_ref, $j, 'name')) . "</td>";
			echo "<td align='center' class='tab_bg_1'>" . plugin_order_getDelivredQuantity($orderID, $refID) . " / " . plugin_order_getQuantity($orderID, $refID) . "</td>";
			echo "<td align='center' class='tab_bg_1'>" . getNumberOfLinkedMaterial($orderID, $refID) . " / " . plugin_order_getQuantity($orderID, $refID) . "</td>";
			echo "<td align='center' class='tab_bg_1'>" . plugin_order_displayPrice($DB->result($result, $j, "price_taxfree")) . "</td>";
			echo "<td align='center' class='tab_bg_1'>" . plugin_order_displayPrice($DB->result($result, $j, "price_ati")) . "</td>";
			echo "<td align='center' class='tab_bg_1'>" . plugin_order_displayPrice($DB->result($result, $j, "price_discounted")) . "</td></tr></table>";

			echo "<div class='center' id='reception$rand' style='display:none'>";
			echo "<form method='post' name='order_reception_form$rand' id='order_reception_form$rand'  action=\"" . $CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.reception.form.php\">";
			echo "<table class='tab_cadre_fixe'>";

			echo "<tr>";
			if ($canedit)
				echo "<th width='15'></th>";
			echo "<th>" . $LANG['plugin_order']['detail'][1] . "</th>";
			echo "<th>" . $LANG['common'][5] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][2] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][19] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][21] . "</th>";
			echo "<th>" . $LANG['financial'][19] . "</th>";
			echo "<th>" . $LANG['plugin_order']['item'][0] . "</th></tr>";
			$i = 0;
			while ($i < $num) {
				$random = mt_rand();
				$detailID = $DB->result($result, $i, 'IDD');
				$mydetail = new PluginOrderDetail;
				$mydetail->getFromDB($detailID);

				echo "<tr class='tab_bg_2'>";
				if ($canedit) {
					echo "<td width='15' align='left'>";
					$sel = "";
					if (isset ($_GET["select"]) && $_GET["select"] == "all")
						$sel = "checked";
					echo "<input type='checkbox' name='item[" . $detailID . "]' value='1' $sel>";
					echo "</td>";
				}

				echo "<td align='center'>" . getReceptionType($detailID) . "</td>";
				echo "<td align='center'>" . getReceptionManufacturer($detailID) . "</td>";
				echo "<td align='center'>" . getReceptionReferenceLink($DB->result($result, $i, 'IDR'), $DB->result($result, $i, 'name')) . "</td>";
				echo "<td align='center'>" . getReceptionStatus($detailID) . "</td>";
				echo "<td align='center'>" . convDate($mydetail->fields["date"]) . "</td>";
				echo "<td align='center'>" . $mydetail->fields["deliverynum"] . "</td>";
				echo "<td align='center'>" . getReceptionDeviceName($DB->result($result, $i, 'FK_device'), $DB->result($result, $i, 'type'));
				if ($DB->result($result, $i, 'FK_device') != 0) {
					echo "<img alt='' src='" . $CFG_GLPI["root_doc"] . "/pics/aide.png' onmouseout=\"cleanhide('comments_$random')\" onmouseover=\"cleandisplay('comments_$random')\" ";
					echo "<span class='over_link' id='comments_$random'>" . nl2br(getReceptionMaterialInfo($DB->result($result, $i, 'type'), $DB->result($result, $i, 'FK_device'))) . "</span>";
				}
				echo "<input type='hidden' name='ID[$detailID]' value='$detailID'>";
				echo "<input type='hidden' name='name[$detailID]' value='" . $DB->result($result, $i, 'name') . "'>";
				echo "<input type='hidden' name='type[$detailID]' value='" . $DB->result($result, $i, 'type') . "'>";
				echo "<input type='hidden' name='status[$detailID]' value='" . $DB->result($result, $i, 'status') . "'>";
				$i++;
			}
			echo "</table>";
			if ($canedit) {
				echo "<table class='tab_cadre_fixe'>";
				echo "<tr><td width='5%'><img src=\"" . $CFG_GLPI["root_doc"] . "/pics/arrow-left.png\" alt=''></td><td class='center' width='5%'><a onclick= \"if ( markCheckboxes('order_reception_form$rand') ) return false;\" href='" . $_SERVER['PHP_SELF'] . "?ID=$orderID&amp;select=all'>" . $LANG['buttons'][18] . "</a></td>";
				echo "<td width='1%'>/</td><td class='center' width='5%'><a onclick= \"if ( unMarkCheckboxes('order_reception_form$rand') ) return false;\" href='" . $_SERVER['PHP_SELF'] . "?ID=$orderID&amp;select=none'>" . $LANG['buttons'][19] . "</a>";
				echo "</td>";
				echo "<input type='hidden' name='orderID' value='$orderID'>";
				plugin_order_dropdownReceptionActions($typeRef, $refID, $mydetail->fields["FK_order"]);
				echo "</td></tr>";
				echo "</table>";
			}
			echo "</form></div>";
		}
		echo "<br>";
		$j++;
	}
}

function getNumberOfLinkedMaterial($orderID, $refID) {
	global $DB;
	$query = "SELECT count(*) AS result FROM `glpi_plugin_order_detail`
				  WHERE FK_order = " . $orderID . "
				  AND FK_reference = " . $refID . "
				  AND FK_device != '0' ";
	$result = $DB->query($query);
	return ($DB->result($result, 0, 'result'));
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
			$ci = new CommonItem();
			$ci->getFromDB($deviceType, $deviceID);
			if (isset ($ci->obj->fields["name"]))
				$comments = "<strong>" . $LANG['common'][16] . ":</strong> " . $ci->obj->fields["name"];
			if (isset ($ci->obj->fields["FK_entities"]))
				$comments = "<strong>" . $LANG['entity'][0] . ":</strong> " . getDropdownName("glpi_entities", $ci->obj->fields["FK_entities"]);
			if (isset ($ci->obj->fields["serial"]) && $ci->obj->fields["serial"] != '')
				$comments .= "<br><strong>" . $LANG['common'][19] . ":</strong> " . $ci->obj->fields["serial"];
			if (isset ($ci->obj->fields["otherserial"]) && $ci->obj->fields["otherserial"] != '')
				$comments .= "<br><strong>" . $LANG['common'][10] . ":</strong> " . $ci->obj->fields["otherserial"];
			if (isset ($ci->obj->fields["location"]) && $ci->obj->fields["location"] != 0)
				$comments .= "<br><strong>" . $LANG['consumables'][36] . ":</strong> " . getDropdownName('glpi_dropdown_locations', $ci->obj->fields["location"]);
			if (isset ($ci->obj->fields["FK_users"]) && $ci->obj->fields["FK_users"] != 0)
				$comments .= "<br><strong>" . $LANG['common'][34] . ":</strong> " . getDropdownName('glpi_users', $ci->obj->fields["FK_users"]);
			break;
		case CONSUMABLE_ITEM_TYPE :
			$ci = new Consumable();
			if ($ci->getFromDB($deviceID)) {
            $ct = new ConsumableType;
            $ct->getFromDB($ci->fields['FK_glpi_consumables_type']);
				$comments = "<strong>" . $LANG['entity'][0] . ":</strong> " . getDropdownName("glpi_entities", $ct->fields["FK_entities"]);
            $comments .= '<br><strong>'.$LANG['consumables'][0].' : </strong> #'.$deviceID;
            $comments .= '<br><strong>'.$LANG['consumables'][12].' : </strong>'.$ct->fields['name'];
            $comments .= '<br><strong>'.$LANG['common'][5].' : </strong>'.getDropdownName('glpi_dropdown_manufacturer',$ct->fields['FK_glpi_enterprise']);
            $comments .= '<br><strong>'.$LANG['consumables'][23].' : </strong>'.(!$ci->fields['id_user']?$LANG['consumables'][1]:$LANG['consumables'][15]);
            if ($ci->fields['id_user'])
               $comments .= '<br><strong>'.$LANG['common'][34].' : </strong>'.getDropdownName('glpi_users',$ci->fields['id_user']);
         }
			break;
		case CARTRIDGE_ITEM_TYPE :
		$ci = new Cartridge();
			if ($ci->getFromDB($deviceID)) {
            $ct = new CartridgeType;
            $ct->getFromDB($ci->fields['FK_glpi_cartridges_type']);
				$comments = "<strong>" . $LANG['entity'][0] . ":</strong> " . getDropdownName("glpi_entities", $ct->fields["FK_entities"]);
            $comments .= '<br><strong>'.$LANG['cartridges'][0].' : </strong> #'.$deviceID;
            $comments .= '<br><strong>'.$LANG['cartridges'][12].' : </strong>'.$ct->fields['name'];
            $comments .= '<br><strong>'.$LANG['common'][5].' : </strong>'.getDropdownName('glpi_dropdown_manufacturer',$ct->fields['FK_glpi_enterprise']);
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

function getReceptionManufacturer($ID) {
	global $DB;
	$query = "SELECT glpi_plugin_order_detail.ID, FK_manufacturer
				  FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references`
				  WHERE glpi_plugin_order_detail.ID=$ID
				  AND glpi_plugin_order_detail.FK_reference=glpi_plugin_order_references.ID";
	$result = $DB->query($query);
	if ($DB->result($result, 0, 'FK_manufacturer') != null) {
		return (getDropdownName("glpi_dropdown_manufacturer", $DB->result($result, 0, 'FK_manufacturer')));
	} else
		return -1;
}

function getReceptionDate($ID) {
	global $DB, $LANG;

	$detail = new PluginOrderDetail;
	$detail->getFromDB($ID);
	if ($detail->fields["status"] == ORDER_DEVICE_NOT_DELIVRED)
		return $LANG['plugin_order']['detail'][23];
	else
		return convDate($detail->fields["date"]);
}

function getReceptionType($ID) {
	global $DB, $LINK_ID_TABLE;
	$query = "SELECT glpi_plugin_order_detail.ID, type 
				  FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references`
				  WHERE glpi_plugin_order_detail.ID=$ID
				  AND glpi_plugin_order_detail.FK_reference=glpi_plugin_order_references.ID";
	$result = $DB->query($query);
	if ($DB->result($result, 0, 'type') != NULL) {
		$ci = new CommonItem();
		$ci->setType($DB->result($result, 0, 'type'));
		return ($ci->getType());
	} else
		return (-1);
}

function getReceptionDeviceName($deviceID, $deviceType) {
	global $DB, $LINK_ID_TABLE, $INFOFORM_PAGES, $CFG_GLPI, $LANG;
	if ($deviceID == 0)
		return ($LANG['plugin_order']['item'][2]);
	else {
		switch ($deviceType) {
			case COMPUTER_TYPE :
			case MONITOR_TYPE :
			case NETWORKING_TYPE :
			case PERIPHERAL_TYPE :
			case PHONE_TYPE :
			case PRINTER_TYPE :
				$ci = new CommonItem();
				$ci->getFromDB($deviceType, $deviceID);
				return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[$deviceType] . "?ID=" . $deviceID . ">" . $ci->obj->fields["name"] . "</a>");
				break;
			case CONSUMABLE_ITEM_TYPE :
				$ci = new Consumable();
				$ci->getFromDB($deviceID);
            $ct = new ConsumableType;
            $ct->getFromDB($ci->fields['FK_glpi_consumables_type']);
				return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[CONSUMABLE_TYPE] . "?ID=" . $ct->fields['ID'] . ">" . $LANG['consumables'][0].': #'.$deviceID.' ('.$ct->fields["name"].')' . "</a>");
				break;
			case CARTRIDGE_ITEM_TYPE :
				$ci = new Cartridge();
				$ci->getFromDB($deviceID);
            $ct = new CartridgeType;
            $ct->getFromDB($ci->fields['FK_glpi_cartridges_type']);
				return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[CARTRIDGE_TYPE] . "?ID=" . $ct->fields['ID'] . ">" . $LANG['cartridges'][0].': #'. $deviceID. ' ('.$ct->fields["name"].')' . "</a>");
				break;
		}
	}
}

function getAllItemsByType($type, $entity, $item_type = 0, $item_model = 0) {
	global $DB, $LINK_ID_TABLE, $ORDER_TYPE_TABLES, $ORDER_MODEL_TABLES, $ORDER_TEMPLATE_TABLES,
         $ORDER_RESTRICTED_TYPES,$LANG;
	
	$and = "";
	if (isset($ORDER_TYPE_TABLES[$type]))
		$and .= ($item_type != 0 ? " AND type=$item_type" : "");
	if (isset($ORDER_MODEL_TABLES[$type]))
		$and .= ($item_model != 0 ? " AND model=$item_model" : "");
	if (in_array($type,$ORDER_TEMPLATE_TABLES))	
		$and = " AND is_template=0 AND deleted=0 ";
	
	switch ($type) {
			default:
			$query = "SELECT ID, name FROM `" . $LINK_ID_TABLE[$type] . "` 
								 WHERE FK_entities=" . $entity . $and . " 
								 AND ID NOT IN (SELECT FK_device FROM glpi_plugin_order_detail)";
			break;
		case CONSUMABLE_ITEM_TYPE :
			$query = "SELECT ID, name FROM `glpi_consumables_type`
											  WHERE FK_entities=" . $entity."
                                     AND type=$item_type 
                                     ORDER BY name";
			break;
		case CARTRIDGE_ITEM_TYPE :
			$query = "SELECT ID, name FROM `glpi_cartridges_type`
											  WHERE FK_entities=" . $entity."
                                     AND type=$item_type
                                     ORDER BY name ASC";
			break;
	}
	$result = $DB->query($query);
	
	$device = array ();
	while ($data = $DB->fetch_array($result)) {
         $device[$data["ID"]] = $data["name"];
   }

	return $device;
}

function plugin_order_createLinkWithDevice($detailID = 0, $deviceID = 0, $device_type = 0, $orderID = 0, $entity = 0, $templateID = 0, $history = true, $check_link = true) {
	global $LANG,$ORDER_RESTRICTED_TYPES;

	if (!$check_link || !plugin_order_itemAlreadyLinkedToAnOrder($device_type, $deviceID, $orderID,$detailID)) {
		$detail = new PluginOrderDetail;

		if (in_array($device_type,$ORDER_RESTRICTED_TYPES))
		{
			$commonitem = new CommonItem;
			$commonitem->setType($device_type,true);

         $detail->getFromDB($detailID);

			$input["tID"] = $deviceID;
			$input["date_in"] = $detail->fields["date"];
			$newID = $commonitem->obj->add($input);

     		$input["ID"] = $detailID;
         $input["FK_device"] = $newID;
         $input["device_type"] = $device_type;
         $detail->update($input);

         plugin_order_generateInfoComRelatedToOrder($entity, $detailID, $device_type, $newID,0);
		}
		else {
     		$input["ID"] = $detailID;
         $input["FK_device"] = $deviceID;
         $input["device_type"] = $device_type;
         $detail->update($input);
         $detail->getFromDB($detailID);
			plugin_order_generateInfoComRelatedToOrder($entity, $detailID, $device_type, $deviceID, $templateID);
      }
		if ($history) {
			$order = new PluginOrder;
			$order->getFromDB($detail->fields["FK_order"]);
			$new_value = $LANG['plugin_order']['delivery'][14] . ' : ' . $order->fields["name"];
			plugin_order_addHistory($device_type, '', $new_value, $deviceID);
		}
        addMessageAfterRedirect($LANG['plugin_order']['delivery'][14],true);
	} else
		addMessageAfterRedirect($LANG['plugin_order']['delivery'][16], true, ERROR);

}

function plugin_order_deleteLinkWithDevice($detailID, $device_type) {
	global $DB, $LANG;
	$detail = new PluginOrderDetail;
	$detail->getFromDB($detailID);
	$deviceID = $detail->fields["FK_device"];

	$query = "SELECT ID, FK_order FROM `glpi_plugin_order_detail` " .
	" WHERE FK_device=" . $deviceID .
	" AND device_type=" . $device_type;

	if ($result = $DB->query($query)) {
		if ($DB->numrows($result) > 0) {
			$orderDeviceID = $DB->result($result, 0, 'ID');
			
			plugin_order_removeInfoComRelatedToOrder($device_type, $deviceID);

			if($detail->fields["FK_device"]!=0) 
			{
			$input = $detail->fields;
			$input["FK_device"] = 0;
			$detail->update($input);
			} else
				addMessageAfterRedirect($LANG['plugin_order'][48],TRUE, ERROR);

			$order = new PluginOrder;
			$order->getFromDB($DB->result($result, 0, "FK_order"));
			$new_value = $LANG['plugin_order']['delivery'][15] . ' : ' . $order->fields["name"];
			plugin_order_addHistory($device_type, '', $new_value, $deviceID);

			$commonitem = new CommonItem;
			$commonitem->getFromDB($device_type, $deviceID);
			$new_value = $LANG['plugin_order']['delivery'][15] . ' : ' . $commonitem->getField("name");
			plugin_order_addHistory(PLUGIN_ORDER_TYPE, '', $new_value, $order->fields["ID"]);
		}
	}
}

function plugin_order_deleteAllLinkWithDevice($orderID) {
	global $DB;

	$detail = new PluginOrderDetail;
	$devices = getAllDatasFromTable("glpi_plugin_order_detail", "FK_order=$orderID");
	foreach ($devices as $deviceID => $device) 
		$detail->delete(array (
			"ID" => $deviceID
		));
}

function plugin_order_updateBulkReceptionStatus($params) {
   global $LANG,$DB;
   $query = "SELECT ID FROM `glpi_plugin_order_detail` WHERE FK_order=".$params["orderID"].
               " AND FK_reference=".$params["referenceID"].
               " AND status=0";
   $result = $DB->query($query);
   $nb = $DB->numrows($result);
   if ($nb < $params['number_reception'])
      addMessageAfterRedirect($LANG['plugin_order']['detail'][37], true, ERROR);
   else {
      for ($i=0;$i < $params['number_reception']; $i++) {
         plugin_order_receptionOneItem($DB->result($result,$i,0),$params['orderID'],$params["date"],$params["deliverynum"]);
      }
  		plugin_order_updateDelivryStatus($params['orderID']);
   }
}

function plugin_order_updateReceptionStatus($params) {
	global $LANG;
	$detail = new PluginOrderDetail;
	$orderID = 0;
	if (isset ($params["item"])) {
		foreach ($params["item"] as $key => $val)
			if ($val == 1) {
				if ($detail->getFromDB($key)) {
					if (!$orderID)
						$orderID = $detail->fields["FK_order"];

					if ($detail->fields["status"] == ORDER_DEVICE_NOT_DELIVRED) {
						plugin_order_receptionOneItem($key,$orderID,$params["date"],$params["deliverynum"]);
                 /*
                 $input["ID"] = $key;
						$input["date"] = $params["date"];
						$input["status"] = ORDER_DEVICE_DELIVRED;
						$input["deliverynum"] = $params["deliverynum"];

						$detail->update($input);
						addMessageAfterRedirect($LANG['plugin_order']['detail'][31], true);
                  
                  */
					} else
						addMessageAfterRedirect($LANG['plugin_order']['detail'][32], true, ERROR);
				}
			}

		plugin_order_updateDelivryStatus($orderID);
	} else
		addMessageAfterRedirect($LANG['plugin_order']['detail'][29], false, ERROR);
}

function plugin_order_receptionOneItem($detailID,$orderID,$date,$deliverynum)
{
	global $LANG;
   $detail = new PluginOrderDetail;
   $input["ID"] = $detailID;
	$input["date"] = $date;
	$input["status"] = ORDER_DEVICE_DELIVRED;
	$input["deliverynum"] = $deliverynum;
	$detail->update($input);
	addMessageAfterRedirect($LANG['plugin_order']['detail'][31], true);
}

function plugin_order_plugin_order_showItemGenerationForm($target, $params) {
	global $LANG, $CFG_GLPI;
	commonHeader($LANG['plugin_order']['title'][1], $_SERVER["PHP_SELF"], "plugins", "order", "order");
	echo "<div class='center'>";
	echo "<table class='tab_cadre'>";

	echo "<a href='" . $_SERVER["HTTP_REFERER"] . "'>" . $LANG['buttons'][13] . "</a></br><br>";

	echo "<form method='post' name='order_deviceGeneration' id='order_deviceGeneration'  action=" . $_SERVER["PHP_SELF"] . ">";
	echo "<tr><th colspan='5'>" . $LANG['plugin_order']['delivery'][3] . "</tr></th>";
	echo "<tr><th>" . $LANG['plugin_order']['reference'][1] . "</th>";
	echo "<th>" . $LANG['common'][19] . "</th>";
	echo "<th>" . $LANG['common'][20] . "</th>";
	echo "<th>" . $LANG['common'][16] . "</th>";
	echo "<th>" . $LANG['entity'][0] . "</th>";
	echo "</tr>";
	echo "<input type='hidden' name='orderID' value=" . $params["orderID"] . ">";
	echo "<input type='hidden' name='referenceID' value=" . $params["referenceID"] . ">";

	$order = new PluginOrder;
	$order->getFromDB($params["orderID"]);

	$i = 0;
	$found = false;
	foreach ($params["item"] as $key => $val)
		if ($val == 1) {
			$detail = new PluginOrderDetail;
			$detail->getFromDB($key);
			
			if (!$detail->fields["FK_device"])
			{
				echo "<tr class='tab_bg_1'><td align='center'>" . $_POST["name"][$key] . "</td>";
				echo "<td><input type='text' size='20' name='serial[$i]'></td>";
				echo "<td><input type='text' size='20' name='otherserial[$i]'></td>";
				echo "<td><input type='text' size='20' name='name[$i]'></td>";
				echo "<td>";
				$entity_restrict = ($order->fields["recursive"] ? getEntitySons($order->fields["FK_entities"]) : $order->fields["FK_entities"]);
				dropdownValue("glpi_entities", "FK_entities", $order->fields["FK_entities"], 1, $entity_restrict);
				echo "</td></tr>";
				echo "<input type='hidden' name='type[$i]' value=" . $params['type'][$key] . ">";
				echo "<input type='hidden' name='ID[$i]' value=" . $params["ID"][$key] . ">";
				echo "<input type='hidden' name='orderID' value=" . $params["orderID"] . ">";
				$found = true;
			}
			$i++;
		}

	if ($found)
		echo "<tr><td align='center' colspan='5' class='tab_bg_2'><input type='submit' name='generate' class='submit' value=" . $LANG['plugin_order']['delivery'][9] . "></td></tr>";
	else
		echo "<tr><td align='center' colspan='5' class='tab_bg_2'>".$LANG['plugin_order']['delivery'][17]."</td></tr>";	

	echo "</table>";
	echo "</div>";
	commonFooter();
}

function plugin_order_generateNewDevice($params) {
	global $DB, $LANG;
	$i = 0;
	$entity = $params["FK_entities"];
	while (isset ($params["serial"][$i])) {

         //------------- Template management -----------------------//
         //Look for a template in the entity
			$templateID = plugin_order_templateExistsInEntity($params["ID"][$i], $params["type"][$i], $entity);
	
			$input["FK_entities"] = $entity;
			$input["serial"] = $params["serial"][$i];
			$input["otherserial"] = $params["otherserial"][$i];
			$input["name"] = $params["name"][$i];
	
			$order = new PluginOrder;
			$order->getFromDB($params["orderID"]);
	
			$reference = new PluginOrderReference;
			$reference->getFromDB($params["referenceID"]);
			$input["type"] = $reference->fields["FK_type"];
			$input["model"] = $reference->fields["FK_model"];
			if ($entity == $reference->fields["FK_entities"])
				$input["location"] = $order->fields["location"];
	
			$commonitem = new CommonItem;
			$commonitem->setType($params["type"][$i], true);
			$newID = $commonitem->obj->add($input);
	
			$commonitem_template = new CommonItem;
			$commonitem_template->getFromDB($params["type"][$i], $templateID);
	
			//Unset fields from template
			unset ($commonitem_template->obj->fields["ID"]);
			unset ($commonitem_template->obj->fields["date_mod"]);
			unset ($commonitem_template->obj->fields["is_template"]);
			unset ($commonitem_template->obj->fields["FK_entities"]);
	
			$fields = array ();
			foreach ($commonitem_template->obj->fields as $key => $value) {
				if ($value != '' && (!isset ($fields[$key]) || $fields[$key] == '' || $fields[$key] == 0))
					$fields[$key] = $value;
			}
			$fields["ID"] = $newID;
			$commonitem->obj->update($fields);

   			// ADD Contract
			$query="SELECT FK_contract
				FROM glpi_contract_device
				WHERE FK_device='".$templateID."' AND device_type='".$params["type"][$i]."';";
			$result=$DB->query($query);
			if ($DB->numrows($result)>0){
				while ($data=$DB->fetch_array($result))
					addDeviceContract($data["FK_contract"],$params["type"][$i],$newID);
			}

			// ADD Documents
			$query="SELECT FK_doc
				FROM glpi_doc_device
				WHERE FK_device='".$templateID."' AND device_type='".$params["type"][$i]."';";
			$result=$DB->query($query);
			if ($DB->numrows($result)>0){
				while ($data=$DB->fetch_array($result))
					addDeviceDocument($data["FK_doc"],$params["type"][$i],$newID);
			}

			// ADD Ports
			$query="SELECT ID
				FROM glpi_networking_ports
				WHERE on_device='".$templateID."' AND device_type='".$params["type"][$i]."';";
			$result=$DB->query($query);
			if ($DB->numrows($result)>0){
				while ($data=$DB->fetch_array($result)){
					$np= new Netport();
					$np->getFromDB($data["ID"]);
					unset($np->fields["ID"]);
					unset($np->fields["ifaddr"]);
					unset($np->fields["ifmac"]);
					unset($np->fields["netpoint"]);
					$np->fields["on_device"]=$newID;
					$np->addToDB();
				}
			}

			// Add connected devices
			$query="SELECT *
				FROM glpi_connect_wire
				WHERE end2='".$templateID."';";

			$result=$DB->query($query);
			if ($DB->numrows($result)>0){
				while ($data=$DB->fetch_array($result)){
					Connect($data["end1"],$newID,$data["type"]);
				}
			}
		


         //-------------- End template management ---------------------------------//
			plugin_order_createLinkWithDevice($params["ID"][$i], $newID, $params["type"][$i], $params["orderID"], $entity, $templateID, false, false);
	
			//Add item's history
			$new_value = $LANG['plugin_order']['delivery'][13] . ' : ' . $order->fields["name"];
			plugin_order_addHistory($params["type"][$i], '', $new_value, $newID);
	
			//Add order's history
			$new_value = $LANG['plugin_order']['delivery'][13] . ' : ';
			$new_value .= $commonitem->getType() . " -> " . $commonitem->getField("name");
			plugin_order_addHistory(PLUGIN_ORDER_TYPE, '', $new_value, $params["orderID"]);
	
			addMessageAfterRedirect($LANG['plugin_order']['detail'][30], true);
			$i++;
		}
}

function plugin_order_itemAlreadyLinkedToAnOrder($device_type, $deviceID, $orderID,$detailID=0) {
	global $DB,$ORDER_RESTRICTED_TYPES;
		if (!in_array($device_type,$ORDER_RESTRICTED_TYPES)) {
         $query = "SELECT COUNT(*) as cpt FROM `glpi_plugin_order_detail`" .
                  " WHERE FK_order=$orderID " .
                  " AND FK_device>0 " .
                  " AND device_type=$device_type";
         $result = $DB->query($query);
         if ($DB->result($result, 0, "cpt") > 0)
            return true;
         else
            return false;
      }
      else {
         $detail = new PluginOrderDetail;
         $detail->getFromDB($detailID);
         if (!$detail->fields['FK_device']) {
            return false;
         }
         else {
            return true;
         }
      }
}

function plugin_order_allItemsAlreadyDelivered($orderID, $referenceID) {
	global $DB;
	$query = "SELECT COUNT(*)  as cpt FROM `glpi_plugin_order_detail` " .
	"WHERE FK_order=$orderID AND FK_reference=$referenceID AND status=" . ORDER_DEVICE_NOT_DELIVRED;
	$result = $DB->query($query);
	if ($DB->result($result, 0, "cpt") > 0)
		return false;
	else
		return true;
}

function plugin_order_generateInfoComRelatedToOrder($entity, $detailID, $device_type, $deviceID, $templateID=0) {
	global $LANG;

	$detail = new PluginOrderDetail;
	$detail->getFromDB($detailID);
	$order = new PluginOrder;
	$order->getFromDB($detail->fields["FK_order"]);

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
	$fields["facture"] = $order->fields["numbill"];
	$fields["value"] = $detail->fields["price_discounted"];
	$fields["buy_date"] = $order->fields["date"];

	//DO not check infocom modifications
	$fields["_manage_by_order"] = 1;
	
	if (!$exists)
		$ic->add($fields);
	else
		$ic->update($fields);	
}

function plugin_order_removeInfoComRelatedToOrder($device_type, $deviceID) {
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

?>