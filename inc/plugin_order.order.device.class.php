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
class plugin_order_device extends CommonDBTM {
	function __construct() {
		$this->table = "glpi_plugin_order_device";
	}

	function isDeviceLinkedToOrder($device_type, $deviceID) {
		global $DB;
		$query = "SELECT ID FROM " . $this->table . " WHERE device_type=$device_type AND FK_device=$deviceID";
		$result = $DB->query($query);
		if ($DB->numrows($result))
			return true;
		else
			return false;
	}

	function getOrderInfosByDeviceID($device_type, $deviceID) {
		global $DB;
		$query = "SELECT go.* FROM `glpi_plugin_order` AS go, `" . $this->table . "` AS god " .
		"WHERE go.ID=god.FK_order AND god.device_type=$device_type AND god.FK_device=$deviceID";
		$result = $DB->query($query);
		if ($DB->numrows($result))
			return $DB->fetch_array($result);
		else
			return false;
	}
}
?>