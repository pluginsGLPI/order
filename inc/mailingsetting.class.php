<?php
/*
 * @version $Id: HEADER 1 2010-03-03 21:49 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

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
// Original Author of file: NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier
// Purpose of file: plugin order v1.2.0 - GLPI 0.78
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderMailingSetting extends CommonDBTM {

	public $table = 'glpi_plugin_order_mailingsettings';

	function showMailingForm($target) {
		global $DB, $LANG, $CFG_GLPI;
		if (!haveRight("config", "w"))
			return false;

		echo "<form action=\"$target\" method=\"post\">";
		echo "<input type='hidden' name='id' value='" . $CFG_GLPI["id"] . "'>";

		$profiles = $this->getMailingSenderList();

		echo "<div align='center'>";
		echo "<input type='hidden' name='update_notifications' value='1'>";
		// ADMIN

		$alerts = array (
			"ask" => $LANG['plugin_order']['validation'][1],
			"validation" => $LANG['plugin_order']['validation'][2],
			"cancel" => $LANG['plugin_order']['profile'][2],
			"undovalidation" => $LANG['plugin_order']['profile'][3]
		);

		echo "<table class='tab_cadre_fixe'>";

		foreach ($alerts as $value => $label) {
			echo "<tr><th colspan='3'>" . $label . "</th></tr>";
			echo "<tr class='tab_bg_2'>";
			$this->showFormMailingType($value, $profiles);
			echo "</tr>";
		}

		echo "</table>";
		echo "</div>";

		echo "</form>";

	}
	function showFormMailingType($type, $profiles) {
		global $LANG, $DB;
	
		echo "<td align='right'>";
	
		echo "<select name='mailing_to_add_" . $type . "[]' multiple size='5'>";
	
		foreach ($profiles as $key => $val) {
			list ($itemtype, $item) = explode("_", $key);
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
		$query = "SELECT `items_id` AS item, `id`
				FROM `".$this->getTable()."` 
				WHERE `type` = '$type' 
				AND `item_type` = '" . USER_MAILING_TYPE . "' 
				ORDER BY `FK_item`;";
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
				$options.= "<option value='" . $data["id"] . "'>" . $name . "</option>";
			}
		// Get Profile mailing
		$query = "SELECT `".$this->getTable()."`.`id`, `glpi_profiles`.`name` AS `prof`
			FROM `".$this->getTable()."`
			LEFT JOIN `glpi_profiles` ON (`".$this->getTable()."`.`items_id` = `glpi_profiles`.`id`)
			WHERE `".$this->getTable()."`.`type` = '$type'
			AND `".$this->getTable()."`.`itemtype` = '" . PROFILE_MAILING_TYPE . "'
			ORDER BY `glpi_profiles`.`name` ";
		$result = $DB->query($query);
		if ($DB->numrows($result))
			while ($data = $DB->fetch_assoc($result)) {
				$options.= "<option value='" . $data["id"] . "'>" . $LANG['profiles'][22] . " " . $data["prof"] . "</option>";
			}
	
		// Get Group mailing
		$query = "SELECT `".$this->getTable()."`.`id`, `glpi_groups`.`name`
			FROM `".$this->getTable()."`
			LEFT JOIN `glpi_groups` ON (`".$this->getTable()."`.`items_id` = `glpi_groups`.`id`)
			WHERE `".$this->getTable()."`.`type` = '$type'
			AND `".$this->getTable()."`.`itemtype` = '" . GROUP_MAILING_TYPE . "'
			ORDER BY `glpi_groups`.`name` ";
		$result = $DB->query($query);
		if ($DB->numrows($result))
			while ($data = $DB->fetch_assoc($result)) {
				$options.= "<option value='" . $data["id"] . "'>" . $LANG['common'][35] . " " . $data["name"] . "</option>";
			}
		if (!empty($options)) {
			echo "<select name='mailing_to_delete_" . $type . "[]' multiple size='5'>";
			echo $options;
			echo "</select>";
		} else {
			echo "&nbsp;";
		}
		echo "</td>";
	
	}
	
   function getMailingSenderList(){
      global $DB,$LANG;
       
      $profiles[USER_MAILING_TYPE . "_" . ADMIN_MAILING] = $LANG['setup'][237];
      $query = "SELECT `id`, `name` 
               FROM `glpi_profiles` 
               ORDER BY `name`";
      $result = $DB->query($query);
      while ($data = $DB->fetch_assoc($result))
         $profiles[PROFILE_MAILING_TYPE .
           "_" . $data["id"]] = $LANG['profiles'][22] . " " . $data["name"];

      $query = "SELECT `id`, `name` 
               FROM `glpi_groups` 
               ORDER BY `name`";
      $result = $DB->query($query);
      while ($data = $DB->fetch_assoc($result))
         $profiles[GROUP_MAILING_TYPE .
           "_" . $data["id"]] = $LANG['common'][35] . " " . $data["name"];

      asort($profiles);
     return $profiles;	
   }
   
   function updateMailNotifications($input) {
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
                  list ($itemtype, $item) = explode("_", $val);
                  $query = "INSERT INTO `".$this->getTable()."` (`type`,`items_id`,`itemtype`)
                  VALUES ('$type','$item','$itemtype')";
                  $DB->query($query);
               break;
               case "delete" :
                  $query = "DELETE
                  FROM `".$this->getTable()."`
                  WHERE `id` = '$val'";
                  $DB->query($query);
               break;
            }
         }
      }
   }
}

?>