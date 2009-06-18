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
function showReceptionForm($orderID) {
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
		if ($numref != 0) {
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
		if ($numref == 0)
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
			echo "<td align='center' class='tab_bg_1'>" . getDelivredQuantity($orderID, $refID) . " / " . getQuantity($orderID, $refID) . "</td>";
			echo "<td align='center' class='tab_bg_1'>" . getNumberOfLinkedMaterial($orderID, $refID) . " / " . getQuantity($orderID, $refID) . "</td>";
			echo "<td align='center' class='tab_bg_1'>" . sprintf("%01.2f", $DB->result($result, $j, "price_taxfree")) . "</td>";
			echo "<td align='center' class='tab_bg_1'>" . sprintf("%01.2f", $DB->result($result, $j, "price_ati")) . "</td>";
			echo "<td align='center' class='tab_bg_1'>" . sprintf("%01.2f", $DB->result($result, $j, "price_discounted")) . "</td></tr></table>";
			
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
			echo "<th>" . $LANG['plugin_order']['item'][0] . "</th></tr>";
			$i = 0;
			while ($i < $num) {
				$random = mt_rand();
				$detailID = $DB->result($result, $i, 'IDD');
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
				echo "<td align='center'>" . getReceptionDate($detailID) . "</td>";
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
				plugin_order_dropdownReceptionActions($typeRef);
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
	if ($result = $DB->query($query)) {
		if ($DB->result($result, 0, 'result') != 0) {
			return ($DB->result($result, 0, 'result'));
		} else
			return 0;
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
			$ci = new CommonItem();
			$ci->getFromDB($deviceType, $deviceID);
			if (isset ($ci->obj->fields["name"]))
				$comments = "<strong>" . $LANG['common'][16] . ":</strong> " . $ci->obj->fields["name"];
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
			$ci = new CommonItem();
			$ci->getFromDB(CONSUMABLE_TYPE, $deviceID);
			if (isset ($ci->obj->fields["name"]))
				$comments = "<strong>" . $LANG['plugin_order']['delivery'][8] . ":</strong> " . $ci->obj->fields["name"];
			break;
		case CARTRIDGE_ITEM_TYPE :
			$ci = new CommonItem();
			$ci->getFromDB(CARTRIDGE_TYPE, $deviceID);
			if (isset ($ci->obj->fields["name"]))
				$comments = "<strong>" . $LANG['plugin_order']['delivery'][8] . ":</strong> " . $ci->obj->fields["name"];
			break;
	}

	return ($comments);
}

function getReceptionReferenceLink($ID, $name) {
	global $CFG_GLPI, $INFOFORM_PAGES;
	return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[PLUGIN_ORDER_REFERENCE_TYPE] . "?ID=" . $ID . "'>" . $name . "</a>");
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
	$query = " SELECT glpi_plugin_order_detail.ID, FK_manufacturer
									FROM glpi_plugin_order_detail, glpi_plugin_order_references
									WHERE glpi_plugin_order_detail.ID=$ID
									AND glpi_plugin_order_detail.FK_reference=glpi_plugin_order_references.ID";
	$result = $DB->query($query);
	if ($DB->result($result, 0, 'FK_manufacturer') != NULL) {
		return (getDropdownName("glpi_dropdown_manufacturer", $DB->result($result, 0, 'FK_manufacturer')));
	} else
		return (-1);
}

function getReceptionDate($ID) {
	global $DB, $LANG;
	$query = " SELECT date, status
								FROM glpi_plugin_order_detail
								WHERE ID=$ID";
	$result = $DB->query($query);
	if (getReceptionStatus($ID) != $LANG['plugin_order']['status'][11]) {
		return (convDate($DB->result($result, 0, 'date')));
	} else
		return ($LANG['plugin_order']['detail'][23]);
}

function getReceptionType($ID) {
	global $DB, $LINK_ID_TABLE;
	$query = " SELECT glpi_plugin_order_detail.ID, type 
									FROM glpi_plugin_order_detail, glpi_plugin_order_references
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
				$ci = new CommonItem();
				$ci->getFromDB(CONSUMABLE_TYPE, $deviceID);
				return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[CONSUMABLE_TYPE] . "?ID=" . $deviceID . ">" . $ci->obj->fields["name"] . "</a>");
				break;
			case CARTRIDGE_ITEM_TYPE :
				$ci = new CommonItem();
				$ci->getFromDB(CARTRIDGE_TYPE, $deviceID);
				return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[CARTRIDGE_TYPE] . "?ID=" . $deviceID . ">" . $ci->obj->fields["name"] . "</a>");
				break;
		}
	}
}

function getAllItemsByType($type, $entity) {
	global $DB, $LINK_ID_TABLE;
	switch ($type) {
		case COMPUTER_TYPE :
		case MONITOR_TYPE :
		case NETWORKING_TYPE :
		case PERIPHERAL_TYPE :
		case PHONE_TYPE :
		case PRINTER_TYPE :
			$query = "SELECT ID, name FROM `" . $LINK_ID_TABLE[$type] . "` 
								 WHERE FK_entities=" . $entity . " 
								 AND is_template=0
								 AND deleted=0
								 AND ID NOT IN (SELECT FK_device FROM glpi_plugin_order_detail)";
			break;
		case CONSUMABLE_ITEM_TYPE :
			$query = "SELECT ID, name FROM `glpi_consumables_type` 
								  WHERE FK_entities=" . $entity . "";
			break;
		case CARTRIDGE_ITEM_TYPE :
			$query = "SELECT ID, name FROM `glpi_cartridges_type` 
								  WHERE FK_entities=" . $entity . "";
			break;
	}
	$result = $DB->query($query);
	$device = array ();
	while ($data = $DB->fetch_array($result))
		$device[$data["ID"]] = $data["name"];

	return $device;
}

function plugin_order_createLinkWithDevice($detailID, $deviceID, $deviceType, $orderID) {
	$detail = new PluginOrderDetail;
	$input["ID"] = $detailID;
	$input["FK_device"] = $deviceID;
	$detail->update($input);

	$device = new PluginOrderDevice;
	$input = array ();
	$input["FK_order"] = $orderID;
	$input["FK_device"] = $deviceID;
	$input["device_type"] = $deviceType;
	$device->add($input);
}

function plugin_order_deleteLinkWithDevice($detailID, $deviceType) {
	global $DB;
	$detail = new PluginOrderDetail;
	$detail->getFromDB($detailID);

	$query = " SELECT ID FROM `glpi_plugin_order_device`
								WHERE FK_device=" . $detail->fields["FK_device"] . "
								AND device_type=" . $deviceType . "";
	if ($result = $DB->query($query)) {
		if ($DB->numrows($result) > 0)
			$deviceID = $DB->result($result, 0, 'ID');
	}
	$device = new PluginOrderDevice;
	$device->delete(array (
		"ID" => $deviceID
	));

	$input = $detail->fields;
	$input["FK_device"] = 0;
	$detail->update($input);
}

function plugin_order_deleteAllLinkWithDevice($orderID)
{
	global $DB;
	$devices = getAllDatasFromTable("glpi_plugin_order_device","FK_order=$orderID");

	$device = new PluginOrderDevice;

	foreach ($devices as $deviceID => $device)
		$device->delete(array ("ID" => $deviceID));
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
						$input["ID"] = $key;
						$input["date"] = $params["date"];
						$input["status"] = ORDER_DEVICE_DELIVRED;

						$detail->update($input);
						addMessageAfterRedirect($LANG['plugin_order']['detail'][31], true);
					} else
						addMessageAfterRedirect($LANG['plugin_order']['detail'][32], true, ERROR);
				}
			}

		plugin_order_updateDelivryStatus($orderID);
	} else
		addMessageAfterRedirect($LANG['plugin_order']['detail'][29], false, ERROR);
}

//TODO : change name : not explicit enough
function plugin_order_showReceptionForm($target, $params) {
	global $LANG, $CFG_GLPI;
	commonHeader($LANG['plugin_order'][4], $_SERVER["PHP_SELF"], "plugins", "order", "order");
	echo "<div class='center'>";
	echo "<table class='tab_cadre'>";

	echo "<form method='post' name='order_deviceGeneration' id='order_deviceGeneration'  action=" . $_SERVER["PHP_SELF"] . ">";
	echo "<tr><th colspan='4'>" . $LANG['plugin_order']['delivery'][3] . "</tr></th>";
	echo "<tr><th>" . $LANG['plugin_order']['reference'][1] . "</th>";
	echo "<th>" . $LANG['plugin_order']['delivery'][6] . "</th>";
	echo "<th>" . $LANG['plugin_order']['delivery'][7] . "</th>";
	echo "<th>" . $LANG['plugin_order']['delivery'][8] . "</th></tr>";
	echo "<input type='hidden' name='orderID' value=" . $params["orderID"] . ">";
	$i = 0;
	foreach ($params["item"] as $key => $val)
		if ($val == 1) {
			echo "<tr class='tab_bg_1'><td align='center'><a href=" . $CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.reference.form.php?ID=" . $key . ">" . $_POST["name"][$key] . "</a></td>";
			echo "<td><input type='text' size='20' name='serial[$i]'></td>";
			echo "<td><input type='text' size='20' name='otherserial[$i]'></td>";
			echo "<td><input type='text' size='20' name='name[$i]'></td></tr>";
			echo "<input type='hidden' name='type[$i]' value=" . $params['type'][$key] . ">";
			echo "<input type='hidden' name='ID[$i]' value=" . $params["ID"][$key] . ">";
			$i++;
		}

	echo "<tr><td align='center' colspan='4' class='tab_bg_2'><input type='submit' name='generate' class='submit' value=" . $LANG['plugin_order']['delivery'][9] . "></td></tr>";

	echo "</table>";
	echo "</div>";
	commonFooter();
}

function plugin_order_generateNewDevice($params, $entity) {
	global $DB, $LANG;
	$i = 0;

	while (isset ($params["serial"][$i])) {
		//Look for a template in the entity
		$templateID = plugin_order_templateExistsInEntity($params["ID"][$i], $params["type"][$i], $entity);

		$input["FK_entities"] = $entity;
		$input["serial"] = $params["serial"][$i];
		$input["otherserial"] = $params["otherserial"][$i];
		$input["name"] = $params["name"][$i];

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

		plugin_order_generateInfoComRelatedToOrder($entity, $params["ID"][$i], $params["type"][$i], $newID, $templateID);
		plugin_order_createLinkWithDevice($params["ID"][$i], $newID, $params["type"][$i], $params["orderID"]);
		addMessageAfterRedirect($LANG['plugin_order']['detail'][30]);

		/*
		$changes[0] = 0;
		$changes[1] = $LANG['plugin_order']['history'][1];
		$changes[2] = 0;
		historyLog($params["ID"][$i],$params['type'][$i],$changes,HISTORY_LOG_SIMPLE_MESSAGE);
		*/
		$i++;
	}
}

function plugin_order_generateInfoComRelatedToOrder($entity, $detailID, $device_type, $deviceID, $templateID) {
	global $LANG;

	$detail = new PluginOrderDetail;
	$detail->getFromDB($detailID);
	$order = new PluginOrder;
	$order->getFromDB($detail->fields["FK_order"]);

	// ADD Infocoms
	$ic = new Infocom();
	$fields = array ();
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

	$fields["device_type"] = $device_type;
	$fields["FK_device"] = $deviceID;
	$fields["num_commande"] = $order->fields["numorder"];
	$fields["bon_livraison"] = $order->fields["deliverynum"];
	$fields["budget"] = $order->fields["budget"];
	$fields["FK_enterprise"] = $order->fields["FK_enterprise"];
	$fields["facture"] = $order->fields["numbill"];
	$fields["value"] = $detail->fields["price_discounted"];
	$fields["buy_date"] = $order->fields["date"];
	$ic->add($fields);
}
?>