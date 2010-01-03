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

function plugin_order_getAllItemsByType($type, $entity, $item_type = 0, $item_model = 0) {
	global $DB, $LINK_ID_TABLE, $ORDER_TYPE_TABLES, $ORDER_MODEL_TABLES, $ORDER_TEMPLATE_TABLES, $ORDER_RESTRICTED_TYPES, $LANG;

	$and = "";
	
	if ($type == CONTRACT_TYPE)
      $field = "contract_type";
   else 
      $field = "type";
	if (isset ($ORDER_TYPE_TABLES[$type]))
		$and .= ($item_type != 0 ? " AND `$field` = '$item_type' " : "");
	if (isset ($ORDER_MODEL_TABLES[$type]))
		$and .= ($item_model != 0 ? " AND `model` ='$item_model' " : "");
	if (in_array($type, $ORDER_TEMPLATE_TABLES))
		$and .= " AND `is_template` = 0 AND `deleted` = 0 ";

	switch ($type) {
		default :
			$query = "SELECT `ID`, `name` 
                  FROM `" . $LINK_ID_TABLE[$type] . "` 
                  WHERE `FK_entities` = '" . $entity ."' ". $and . " 
                  AND `ID` NOT IN (SELECT `FK_device` FROM `glpi_plugin_order_detail`)";
			break;
		case CONSUMABLE_ITEM_TYPE :
			$query = "SELECT `ID`, `name` FROM `glpi_consumables_type`
                  WHERE `FK_entities` = '" . $entity . "'
                  AND `type` = '$item_type' 
                  ORDER BY `name`";
			break;
		case CARTRIDGE_ITEM_TYPE :
			$query = "SELECT `ID`, `name` FROM `glpi_cartridges_type`
                  WHERE `FK_entities` = '" . $entity . "'
                  AND `type` = '$item_type'
                  ORDER BY `name` ASC";
			break;
	}
	$result = $DB->query($query);

	$device = array ();
	while ($data = $DB->fetch_array($result)) {
		$device[$data["ID"]] = $data["name"];
	}

	return $device;
}

function plugin_order_dropdownAllItemsByType($name, $type, $entity=0,$item_type=0,$item_model=0) {

	$items = plugin_order_getAllItemsByType($type,$entity,$item_type,$item_model);
	$items[0] = '-----';
	asort($items);
	return dropdownArrayValues($name, $items, 0);
}

?>