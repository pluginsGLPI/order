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
function plugin_order_showFormMailingType($type, $profiles) {
		global $LANG, $DB;
	
		echo "<td align='right'>";
	
		echo "<select name='mailing_to_add_" . $type . "[]' multiple size='5'>";
	
		foreach ($profiles as $key => $val) {
			list ($item_type, $item) = explode("_", $key);
			echo "<option value='$key'>" . $val . "</option>";
		}
		echo "</select>";
		echo "</td>";
		echo "<td align='center'>";
		echo "<input type='submit'  class=\"submit\" name='mailing_add_$type' value='" . $LANG['buttons'][8] . " >>'><br><br>";
		echo "<input type='submit'  class=\"submit\" name='mailing_delete_$type' value='<< " . $LANG['buttons'][6] . "'>";
		echo "</td>";
		echo "<td>";

		$options="";
		// Get User mailing
		$query = "SELECT glpi_plugin_order_mailing.FK_item as item, glpi_plugin_order_mailing.ID as ID 
				FROM glpi_plugin_order_mailing 
				WHERE glpi_plugin_order_mailing.type='$type' 
				AND glpi_plugin_order_mailing.item_type='" . USER_MAILING_TYPE . "' 
				ORDER BY glpi_plugin_order_mailing.FK_item;";
		$result = $DB->query($query);
		if ($DB->numrows($result))
			while ($data = $DB->fetch_assoc($result)) {
				switch ($data["item"]) {
					case ADMIN_MAILING :
						$name = $LANG['setup'][237];
						break;
					case TECH_MAILING :
						$name = $LANG['setup'][239];
						break;
					default :
				}
				$options.= "<option value='" . $data["ID"] . "'>" . $name . "</option>";
			}
		// Get Profile mailing
		$query = "SELECT glpi_plugin_order_mailing.FK_item as item, glpi_plugin_order_mailing.ID as ID, glpi_profiles.name as prof 
				FROM glpi_plugin_order_mailing 
				LEFT JOIN glpi_profiles ON (glpi_plugin_order_mailing.FK_item = glpi_profiles.ID) 
				WHERE glpi_plugin_order_mailing.type='$type' 
				AND glpi_plugin_order_mailing.item_type='" . PROFILE_MAILING_TYPE . "' 
				ORDER BY glpi_profiles.name;";
		$result = $DB->query($query);
		if ($DB->numrows($result))
			while ($data = $DB->fetch_assoc($result)) {
				$options.= "<option value='" . $data["ID"] . "'>" . $LANG['profiles'][22] . " " . $data["prof"] . "</option>";
			}
	
		// Get Group mailing
		$query = "SELECT glpi_plugin_order_mailing.FK_item as item, glpi_plugin_order_mailing.ID as ID, glpi_groups.name as name 
				FROM glpi_plugin_order_mailing 
				LEFT JOIN glpi_groups ON (glpi_plugin_order_mailing.FK_item = glpi_groups.ID) 
				WHERE glpi_plugin_order_mailing.type='$type' 
				AND glpi_plugin_order_mailing.item_type='" . GROUP_MAILING_TYPE . "' 
				ORDER BY glpi_groups.name;";
		$result = $DB->query($query);
		if ($DB->numrows($result))
			while ($data = $DB->fetch_assoc($result)) {
				$options.= "<option value='" . $data["ID"] . "'>" . $LANG['common'][35] . " " . $data["name"] . "</option>";
			}
		if (!empty($options)){
			echo "<select name='mailing_to_delete_" . $type . "[]' multiple size='5'>";
			echo $options;
			echo "</select>";
		} else {
			echo "&nbsp;";
		}
		echo "</td>";
	
	}
	
function plugin_order_getMailingSenderList()
{
	 global $DB,$LANG;
	 
	 $profiles[USER_MAILING_TYPE . "_" . ADMIN_MAILING] = $LANG['setup'][237];
      $query = "SELECT ID, name 
            FROM glpi_profiles 
            ORDER BY name";
      $result = $DB->query($query);
      while ($data = $DB->fetch_assoc($result))
        $profiles[PROFILE_MAILING_TYPE .
        "_" . $data["ID"]] = $LANG['profiles'][22] . " " . $data["name"];

      $query = "SELECT ID, name 
            FROM glpi_groups 
            ORDER BY name";
      $result = $DB->query($query);
      while ($data = $DB->fetch_assoc($result))
        $profiles[GROUP_MAILING_TYPE .
        "_" . $data["ID"]] = $LANG['common'][35] . " " . $data["name"];

      asort($profiles);
	  return $profiles;	
}

function plugin_order_updateMailNotifications($input) {
		global $DB;
		$type = "";
		$action = "";
	
		foreach ($input as $key => $val) {
			if (!strstr($key,"mailing_to_") && strstr($key,"mailing_")) {
				if (preg_match("/mailing_([a-z]+)_([a-z]+)/", $key, $matches)) {
					$type = $matches[2];
					$action = $matches[1];
				}
			}
		}
	
		if (count($input["mailing_to_" . $action . "_" . $type]) > 0) {
			foreach ($input["mailing_to_" . $action . "_" . $type] as $val) {
				switch ($action) {
					case "add" :
						list ($item_type, $item) = explode("_", $val);
						$query = "INSERT INTO glpi_plugin_order_mailing (type,FK_item,item_type) 
									VALUES ('$type','$item','$item_type')";
						$DB->query($query);
						break;
					case "delete" :
						$query = "DELETE 
								FROM glpi_plugin_order_mailing 
								WHERE ID='$val'";
						$DB->query($query);
						break;
				}
			}
		}
	
	}

function plugin_order_sendNotification($action,$orderID,$entity=0,$userID=0,$comments='')
{
	$mailing = new PluginOrderMailing($orderID,$action,$entity,$userID,$comments);
	$mailing->mailing();
}
?>