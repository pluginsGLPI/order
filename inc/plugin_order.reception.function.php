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

	$plugin_order = new plugin_order();
	$canedit = $plugin_order->can($orderID, 'w');
	$query_ref = "SELECT glpi_plugin_order_detail.ID, glpi_plugin_order_detail.FK_reference AS ref, name, type FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references` WHERE FK_order=$orderID AND glpi_plugin_order_detail.FK_reference=glpi_plugin_order_references.ID  GROUP BY glpi_plugin_order_detail.FK_reference ORDER BY glpi_plugin_order_detail.ID";
	$result_ref = $DB->query($query_ref);
	$numref = $DB->numrows($result_ref);
	$j = 0;
	while ($j < $numref || $j == 0) {
		if ($numref != 0) {
			$refID = $DB->result($result_ref, $j, 'ref');
			$typeRef = $DB->result($result_ref, $j, 'type');
			$query = "	SELECT glpi_plugin_order_detail.ID AS IDD, glpi_plugin_order_references.ID AS IDR, status, date, price_taxfree, price_ati, price_discounted,  FK_manufacturer, name, type, FK_device
								FROM glpi_plugin_order_detail, glpi_plugin_order_references
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
			$rand=mt_rand();
			echo "<tr><th><ul><li>";
			echo "<a href=\"javascript:showHideDiv('reception$rand','reception$rand','" . $CFG_GLPI["root_doc"] . "/pics/plus.png','" . $CFG_GLPI["root_doc"] . "/pics/moins.png');\">";
			echo "<img alt='' name='reception$rand' src=\"" . $CFG_GLPI["root_doc"] . "/pics/plus.png\">";
			echo "</a></li></ul></th>";
			echo "<th>" . $LANG['plugin_order']['reference'][1] . "</th>";
			echo "<th>" . $LANG['plugin_order']['delivery'][5] . "</th>";
			echo "<th>" . $LANG['plugin_order']['item'][0] . "</th>";
			echo "<th>".$LANG['plugin_order']['detail'][4]."</th>";
			echo "<th>".$LANG['plugin_order']['detail'][8]."</th>";
			echo "<th>".$LANG['plugin_order']['detail'][18]."</th></tr>";
			echo "<tr><td class='tab_bg_1' width='15'></td><td align='center' class='tab_bg_1'>" . getReceptionReferenceLink($refID, $DB->result($result_ref, $j, 'name')) . "</td>";
			echo "<td align='center' class='tab_bg_1'>". getDelivredQuantity($orderID, $refID) . " / " . getQuantity($orderID, $refID) . "</td>";
			echo "<td align='center' class='tab_bg_1'>". getNumberOfLinkedMaterial($orderID, $refID) . " / " . getQuantity($orderID, $refID) . "</td>";
			echo "<td align='center' class='tab_bg_1'>". sprintf("%01.2f", $DB->result($result,$j,"price_taxfree")) . "</td>";
			echo "<td align='center' class='tab_bg_1'>". sprintf("%01.2f", $DB->result($result,$j,"price_ati")) . "</td>";
			echo "<td align='center' class='tab_bg_1'>". sprintf("%01.2f", $DB->result($result,$j,"price_discounted")) . "</td></tr></table>";
			echo "<div class='center' id='reception$rand' style='display:none'>";
			echo "<form method='post' name='order_reception_form$rand' id='order_reception_form$rand'  action=\"" . $CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.reception.form.php\">";
			echo "<table class='tab_cadre_fixe'>";
			echo "<tr>";
			if ($canedit) 
				echo "<th width='15'></th>";
			echo "<th>" . $LANG['plugin_order']['detail'][1] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][11] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][2] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][19] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][21] . "</th>";
			echo "<th>" . $LANG['plugin_order']['detail'][22] . "</th></tr>";
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
				echo "<td align='center'>".getReceptionType($detailID)."</td>";
				echo "<td align='center'>".getReceptionManufacturer($detailID)."</td>";
				echo "<td align='center'>".getReceptionReferenceLink($DB->result($result,$i,'IDR'), $DB->result($result,$i,'name'))."</td>";
				echo "<td align='center'>".getReceptionStatus($detailID)."</td>";
				echo "<td align='center'>".getReceptionDate($detailID)."</td>";
				echo "<td align='center'>".getReceptionDeviceName($DB->result($result,$i,'FK_device'), $DB->result($result,$i,'type'));
				if($DB->result($result,$i,'FK_device')!=0) {
					echo "<img alt='' src='".$CFG_GLPI["root_doc"]."/pics/aide.png' onmouseout=\"cleanhide('comments_$random')\" onmouseover=\"cleandisplay('comments_$random')\" ";
					echo "<span class='over_link' id='comments_$random'>".nl2br(getReceptionMaterialInfo($DB->result($result,$i,'type'), $DB->result($result,$i,'FK_device')))."</span>";
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
				echo "</table></form></div>";
			}
		}
		echo "<br>";
		$j++;
	}
}

function getNumberOfLinkedMaterial($orderID, $refID) {
	global $DB;
	$query = "SELECT count(*) AS result FROM glpi_plugin_order_detail
						WHERE FK_order = " . $orderID . "
						AND FK_reference = " . $refID . "
						AND FK_device != '0' ";
	if($result=$DB->query($query)) {
		if($DB->result($result,0,'result') != 0) {
			return ($DB->result($result,0,'result'));
		} else 
			return('0');
	}
}


function getReceptionMaterialInfo($deviceType, $deviceID) {
	global $DB, $LINK_ID_TABLE, $LANG;
	$comments="";
	switch ($deviceType) 
	{
		case COMPUTER_TYPE :
		case MONITOR_TYPE : 
		case NETWORKING_TYPE :
		case PERIPHERAL_TYPE :
		case PHONE_TYPE :
		case PRINTER_TYPE :
			$ci= new CommonItem();
			$ci->getFromDB($deviceType,$deviceID);
			if (isset($ci->obj->fields["name"]))
				$comments ="<strong>".$LANG['plugin_order']['delivery'][8].":</strong> ".$ci->obj->fields["name"];
			if (isset($ci->obj->fields["serial"]) && $ci->obj->fields["serial"] != '')
				$comments .="<br><strong>".$LANG['plugin_order']['delivery'][6].":</strong> ".$ci->obj->fields["serial"];
			if (isset($ci->obj->fields["otherserial"]) && $ci->obj->fields["otherserial"] != '')
				$comments .="<br><strong>".$LANG['plugin_order']['delivery'][7].":</strong> ".$ci->obj->fields["otherserial"];
			if (isset($ci->obj->fields["location"]) && $ci->obj->fields["location"] != 0) 
				$comments .="<br><strong>".$LANG['plugin_order']['detail'][26].":</strong> ".getDropdownName('glpi_dropdown_location', $ci->obj->fields["location"]);
			if (isset($ci->obj->fields["FK_users"]) && $ci->obj->fields["FK_users"] != 0) 
				$comments .="<br><strong>".$LANG['plugin_order']['detail'][24].":</strong> ".getDropdownName('glpi_users', $ci->obj->fields["FK_users"]);
		break;
		case CONSUMABLE_ITEM_TYPE :
			$ci= new CommonItem();
			$ci->getFromDB(CONSUMABLE_TYPE,$deviceID);
			if (isset($ci->obj->fields["name"]))
				$comments ="<strong>".$LANG['plugin_order']['delivery'][8].":</strong> ".$ci->obj->fields["name"];
		break;
		case CARTRIDGE_ITEM_TYPE :
			$ci= new CommonItem();
			$ci->getFromDB(CARTRIDGE_TYPE,$deviceID);
			if (isset($ci->obj->fields["name"]))
				$comments ="<strong>".$LANG['plugin_order']['delivery'][8].":</strong> ".$ci->obj->fields["name"];
		break;
	}	

	return($comments);
}	
					
function getReceptionReferenceLink($ID, $name) {
	global $CFG_GLPI, $INFOFORM_PAGES;
	return ("<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[PLUGIN_ORDER_REFERENCE_TYPE] . "?ID=" . $ID . "'>" . $name . "</a>");
}

function getReceptionStatus($ID) {
	global $DB, $LANG;
	
	$detail = new plugin_order_detail;
	$detail->getFromDB($ID);

	switch ($detail->fields["status"])
	{
		case ORDER_DEVICE_NOT_DELIVRED:
		   return $LANG['plugin_order']['status'][11];
		case ORDER_DEVICE_DELIVRED:
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
	$query=" SELECT date, status
			FROM glpi_plugin_order_detail
			WHERE ID=$ID";
	$result=$DB->query($query);
	if (getReceptionStatus($ID)!=$LANG['plugin_order']['status'][11]) {
		return(convDate($DB->result($result,0,'date')));
	}
	else
		return($LANG['plugin_order']['detail'][23]);
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

function getReceptionDeviceName($deviceID, $deviceType) 
{
	global $DB, $LINK_ID_TABLE, $INFOFORM_PAGES, $CFG_GLPI, $LANG;
	if($deviceID==0)
		return($LANG['plugin_order']['item'][2]);
	else
	{
		switch($deviceType)
		{
			case COMPUTER_TYPE :
			case MONITOR_TYPE : 
			case NETWORKING_TYPE :
			case PERIPHERAL_TYPE :
			case PHONE_TYPE :
			case PRINTER_TYPE :
				$ci= new CommonItem();
				$ci->getFromDB($deviceType,$deviceID);
				return("<a href=".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$deviceType]."?ID=".$deviceID.">".$ci->obj->fields["name"]."</a>");
			break;
			case CONSUMABLE_ITEM_TYPE :
				$ci= new CommonItem();
				$ci->getFromDB(CONSUMABLE_TYPE,$deviceID);
				return("<a href=".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[CONSUMABLE_TYPE]."?ID=".$deviceID.">".$ci->obj->fields["name"]."</a>");
			break;
			case CARTRIDGE_ITEM_TYPE :
				$ci= new CommonItem();
				$ci->getFromDB(CARTRIDGE_TYPE, $deviceID);
				return("<a href=".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[CARTRIDGE_TYPE]."?ID=".$deviceID.">".$ci->obj->fields["name"]."</a>");
			break;
		}
	}
}

function getAllItemsByType($type, $entity) {
	global $DB, $LINK_ID_TABLE;
	switch($type)
	{
		case COMPUTER_TYPE :
		case MONITOR_TYPE : 
		case NETWORKING_TYPE :
		case PERIPHERAL_TYPE :
		case PHONE_TYPE :
		case PRINTER_TYPE :
			$query = "SELECT ID, name FROM ".$LINK_ID_TABLE[$type]." 
									WHERE FK_entities=".$entity." 
									AND is_template=0
									AND deleted=0
									AND ID NOT IN (SELECT FK_device FROM glpi_plugin_order_detail)";
		break;
		case CONSUMABLE_ITEM_TYPE :
			$query = "SELECT ID, name FROM glpi_consumables_type 
									WHERE FK_entities=".$entity."";
		break;
		case CARTRIDGE_ITEM_TYPE :
			$query = "SELECT ID, name FROM glpi_cartridges_type 
									WHERE FK_entities=".$entity."";
		break;
	}
	$result = $DB->query($query);
	$device = array ();
	while ($data = $DB->fetch_array($result))
		$device[$data["ID"]] = $data["name"];

	return $device;
}

function plugin_order_createLinkWithDevice($detailID, $deviceID, $deviceType, $orderID) {
	global $DB;
	$detail = new plugin_order_detail;
	$input["ID"] = $detailID;
	$input["FK_device"] = $deviceID;
	$detail->update($input);
	$query = "INSERT INTO glpi_plugin_order_device (FK_order, FK_device, device_type)
				values (" . $orderID . "," . $deviceID . ", " . $deviceType . ")";
	$DB->query($query);
}

function plugin_order_deleteLinkWithDevice($detailID) {
	global $DB;
	$query = "SELECT FK_device 
				FROM glpi_plugin_order_detail 
				WHERE ID=" . $detailID . "";
	$result = $DB->query($query);
	$deviceID = $DB->result($result, 0, 'FK_device');
	$query = "DELETE 
				FROM glpi_plugin_order_device 
				WHERE FK_device=" . $deviceID . "";
	$DB->query($query);
	$query = "UPDATE glpi_plugin_order_detail
				SET FK_device=0
				WHERE ID=" . $detailID . "";
	$DB->query($query);
}
?>