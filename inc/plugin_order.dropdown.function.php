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

/*order dropdown selection */
function plugin_order_dropdownorder($myname, $entity_restrict = '', $used = array ()) {
	global $DB, $LANG, $CFG_GLPI;

	$rand = mt_rand();
	$where = " WHERE glpi_plugin_order.deleted='0' ";
	$where .= getEntitiesRestrictRequest("AND", "glpi_plugin_order", '', $entity_restrict, true);
	if (count($used)) {
		$where .= " AND ID NOT IN (0";
		foreach ($used as $ID)
			$where .= ",$ID";
		$where .= ")";
	}
	$query = "SELECT * 
					FROM glpi_dropdown_plugin_order_taxes 
					WHERE ID IN (
						SELECT DISTINCT taxes 
						FROM glpi_plugin_order 
						$where) 
					GROUP BY name ORDER BY name";
	$result = $DB->query($query);

	echo "<select name='_taxes' id='taxes_order'>\n";
	echo "<option value='0'>------</option>\n";
	while ($data = $DB->fetch_assoc($result)) {
		echo "<option value='" . $data['ID'] . "'>" . $data['name'] . "</option>\n";
	}
	echo "</select>\n";

	$params = array (
		'taxes_order' => '__VALUE__',
		'entity_restrict' => $entity_restrict,
		'rand' => $rand,
		'myname' => $myname,
		'used' => $used
	);

	ajaxUpdateItemOnSelectEvent("taxes_order", "show_$myname$rand", $CFG_GLPI["root_doc"] . "/plugins/order/ajax/dropdownTypeorder.php", $params);

	echo "<span id='show_$myname$rand'>";
	$_POST["entity_restrict"] = $entity_restrict;
	$_POST["taxes_order"] = 0;
	$_POST["myname"] = $myname;
	$_POST["rand"] = $rand;
	$_POST["used"] = $used;
	include (GLPI_ROOT . "/plugins/order/ajax/dropdownTypeorder.php");
	echo "</span>\n";

	return $rand;
}

function plugin_order_dropdownAllItems($myname, $ajax = false, $value = 0, $orderID = 0, $supplier = 0, $entity = 0, $ajax_page = '') {
	global $LANG, $CFG_GLPI, $ORDER_AVAILABLE_TYPES;

	$ci = new CommonItem();

	echo "<select name=\"$myname\" id='$myname'>";
	echo "<option value='0' selected>------</option>\n";

	foreach ($ORDER_AVAILABLE_TYPES as $tmp => $type) {
		$ci->setType($type);
		echo "<option value='$type' " . ($type == $value ? " selected" : '') . ">" . $ci->getType() . "</option>\n";
	}
	echo "</select>";

	if ($ajax) {
		$params = array (
			'type' => '__VALUE__',
			'FK_enterprise' => $supplier,
			'entity_restrict' => $entity,
			'orderID' => $orderID,

			
		);

		ajaxUpdateItemOnSelectEvent($myname, "show_reference", $ajax_page, $params);
	}
}

function plugin_order_dropdownTemplate($name, $entity, $table, $value = 0) {
	global $DB;
	$result = $DB->query("SELECT tplname, ID FROM " . $table .
	" WHERE FK_entities=" . $entity . " AND is_template=1 AND tplname <> '' GROUP BY tplname ORDER BY tplname");

	$option[0] = '-------------';
	while ($data = $DB->fetch_array($result))
		$option[$data["ID"]] = $data["tplname"];
	return dropdownArrayValues($name, $option, $value);
}

function plugin_order_getTemplateName($type, $ID) {
	$commonitem = new CommonItem;
	$commonitem->getFromDB($type, $ID);
	return $commonitem->getField("tplname");
}

function plugin_order_dropdownReferencesByEnterprise($name, $type, $enterpriseID) {
	$references = plugin_order_getAllReferencesByEnterpriseAndType($type, $enterpriseID);
	$references[0] = '-----';
	return dropdownArrayValues($name, $references, 0);
}

function plugin_order_dropdownAllItemsByType($name, $type, $entity=0,$item_type=0,$item_model=0) {
	$items = getAllItemsByType($type, $entity,$item_type,$item_model);
	$items[0] = '-----';
	asort($items);
	return dropdownArrayValues($name, $items, 0);
}

function plugin_order_dropdownReceptionActions($type,$referenceID,$orderID) {
	global $LANG, $CFG_GLPI;
	$rand = mt_rand();
	echo "<td width='5%'>";
	echo "<select name='receptionActions$rand' id='receptionActions$rand'>";
	echo "<option value='0' selected>-----</option>";
	if (!plugin_order_allItemsAlreadyDelivered($orderID, $referenceID))
		echo "<option value='reception'>" . $LANG['plugin_order']['delivery'][2] . "</option>";
		
	if ($type != CONSUMABLE_ITEM_TYPE && $type != CARTRIDGE_ITEM_TYPE)
		echo "<option value='generation'>" . $LANG['plugin_order']['delivery'][3] . "</option>";
	echo "<option value='createLink'>" . $LANG['plugin_order']['delivery'][11] . "</option>";
	echo "<option value='deleteLink'>" . $LANG['plugin_order']['delivery'][12] . "</option>";
	echo "</select>";
	$params = array (
		'action' => '__VALUE__',
		'type' => $type,
		'referenceID'=>$referenceID
	);
	ajaxUpdateItemOnSelectEvent("receptionActions$rand", "show_receptionActions$rand", $CFG_GLPI["root_doc"] . "/plugins/order/ajax/receptionactions.php", $params);
	echo "</td>";
	echo "<td valign=middle><span id='show_receptionActions$rand'>&nbsp;</span></td>";
}

function plugin_order_dropdownStatus($name, $value = 0) {
	global $LANG;
	$status[ORDER_STATUS_DRAFT] = $LANG['plugin_order']['status'][9];
	$status[ORDER_STATUS_WAITING_APPROVAL] = $LANG['plugin_order']['status'][7];
	$status[ORDER_STATUS_PARTIALLY_DELIVRED] = $LANG['plugin_order']['status'][1];
	$status[ORDER_STATUS_COMPLETLY_DELIVERED] = $LANG['plugin_order']['status'][2];
	$status[ORDER_STATUS_CANCELED] = $LANG['plugin_order']['status'][10];

	return dropdownArrayValues($name, $status, $value);
}

function plugin_order_getDropdownStatus($value) {
	global $LANG;
	switch ($value) {
		case ORDER_STATUS_DRAFT :
			return $LANG['plugin_order']['status'][9];
		case ORDER_STATUS_APPROVED :
			return $LANG['plugin_order']['status'][12];
		case ORDER_STATUS_WAITING_APPROVAL :
			return $LANG['plugin_order']['status'][7];
		case ORDER_STATUS_PARTIALLY_DELIVRED :
			return $LANG['plugin_order']['status'][1];
		case ORDER_STATUS_COMPLETLY_DELIVERED :
			return $LANG['plugin_order']['status'][2];
		case ORDER_STATUS_CANCELED :
			return $LANG['plugin_order']['status'][10];
		default :
			return "";
	}
}
function plugin_order_templateExistsInEntity($detailID, $type, $entity) {
	global $DB;
	$query = "SELECT glpi_plugin_order_references.template AS templateID " .
			"FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references` " .
			"WHERE glpi_plugin_order_detail.FK_reference=glpi_plugin_order_references.ID " .
			"AND glpi_plugin_order_detail.ID=" . $detailID;
	$result = $DB->query($query);
	if (!$DB->numrows($result))
		return 0;
	else {
		$commonitem = new CommonItem;
		$commonitem->getFromDB($type, $DB->result($result, 0, "templateID"));
		if ($commonitem->getField('FK_entities') == $entity)
			return $commonitem->getField('ID');
		else
			return 0;
	}

}
?>