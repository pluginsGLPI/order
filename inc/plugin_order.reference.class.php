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

class PluginOrderReference extends CommonDBTM {

	function __construct() {
		$this->table = "glpi_plugin_order_references";
		$this->type = PLUGIN_ORDER_REFERENCE_TYPE;
		$this->entity_assign = true;
		$this->may_be_recursive = true;
		$this->dohistory = true;
	}

	/*define header form */
	function defineTabs($ID, $withtemplate) {
		global $LANG;
		/* principal */
		$ong[1] = $LANG['title'][26];
		if ($ID > 0) {
			$ong[3] = $LANG['title'][37];
			if (haveRight("document", "r"))
				$ong[4] = $LANG['Menu'][27];
			$ong[12] = $LANG['title'][38];
		}

		return $ong;
	}

	function prepareInputForAdd($params){
		global $DB,$LANG;

		if (!isset($params["name"]) || $params["name"] == '')
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][8], false, ERROR);
			return false;
		}
		
		if (!$params["type"])
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][9], false, ERROR);
			return false;
		}
		
		$query = "SELECT COUNT(*) AS cpt FROM `".$this->table."` " .
				 "WHERE `name` = '".$params["name"]."' AND `FK_entities` = '".$params["FK_entities"]."' ";
		$result = $DB->query($query);
		if ($DB->result($result,0,"cpt") > 0)
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][6],false,ERROR);
			return false;
		}
		else
			return $params;		
	}
	
	function pre_deleteItem($params){
		global $LANG;
		
		if (!$this->referenceInUse())
			return $params;
		else
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][7],true,ERROR);
			return false;	
		}
			
	}
	
	function referenceInUse(){
		global $DB;
		
		$query = "SELECT COUNT(*) AS cpt FROM `glpi_plugin_order_detail` " .
				"WHERE `FK_reference` = '".$this->fields["ID"]."' ";
		$result = $DB->query($query);
		if ($DB->result($result,0,"cpt") > 0)
			return true;
		else
			return false;	
	}
	
	function getReceptionReferenceLink($data) {
      global $CFG_GLPI, $INFOFORM_PAGES;
      
      if (plugin_order_haveRight("reference","r"))
         return "<a href='" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[$this->type] . "?ID=" . $data["ID"] . "'>" . $data["name"] . "</a>";
      else
         return $name;
   }
	
	function canDelete()
	{
		return (!$this->referenceInUse());
	}
	
	/**
	 * Print a good title for user pages
	 *
	 *@return nothing (display)
	 **/
	function title() {
		global $LANG, $CFG_GLPI;
		
		displayTitle($CFG_GLPI["root_doc"] . "/plugins/order/pics/reference-icon.png", $LANG['plugin_order']['reference'][1], $LANG['plugin_order']['reference'][1]);
	}

	function showForm($target, $ID, $withtemplate = '') {
		global $CFG_GLPI, $LANG, $DB,$ORDER_TEMPLATE_TABLES,$ORDER_TYPE_TABLES,$ORDER_MODEL_TABLES;

		if (!plugin_order_haveRight("reference", "r")) {
			return false;
		}

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
		
		$canedit = plugin_order_haveRight("reference", "w");
		$reference_in_use = (!$ID?false:$this->referenceInUse());
		
		if ($spotted) {

			$this->showTabs($ID, $withtemplate, $_SESSION['glpi_tab']);
			$canedit = $this->can($ID, 'w');
			$canrecu = $this->can($ID, 'recursive');
			echo "<form method='post' name=form action=\"$target\">";
			if (empty ($ID) || $ID < 0) {
				echo "<input type='hidden' name='FK_entities' value='" . $_SESSION["glpiactive_entity"] . "'>";
			}
			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			$this->showFormHeader($ID, $withtemplate, 1);
			echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order']['reference'][1] . ": </td>";
			echo "<td>";
			if ($canedit)
				autocompletionTextField("name", "glpi_plugin_order_references", "name", $this->fields["name"], 70, $this->fields["FK_entities"]);
			else
				echo $this->fields["name"];	
			echo "</td></tr>";

			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][5] . ": </td>";
			echo "<td>";
			if ($canedit && !$reference_in_use)
				dropdownValue("glpi_dropdown_manufacturer", "FK_glpi_enterprise", $this->fields["FK_glpi_enterprise"]);
			else
				echo getDropdownName("glpi_dropdown_manufacturer",$this->fields["FK_glpi_enterprise"]);	
			echo "</td></tr>";

			$commonitem = new CommonItem();
			$commonitem->setType($this->fields["type"], true);

			echo "<tr class='tab_bg_2'><td>" . $LANG['state'][6] . ": </td>";
			echo "<td>";
			if ($ID > 0)
				echo $commonitem->getType();
			else {
				$this->dropdownAllItems("type", true, $this->fields["type"], 0, 0, $_SESSION["glpiactive_entity"], $CFG_GLPI["root_doc"] .
				"/plugins/order/ajax/reference.php");
				echo "<span id='show_reference'></span></td></tr>";
			}

			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][17] . ": </td>";
			echo "<td><span id='show_type'>";
			if (isset($ORDER_TYPE_TABLES[$this->fields["type"]])) {
            if ($canedit && !$reference_in_use)
					dropdownValue($ORDER_TYPE_TABLES[$this->fields["type"]], "FK_type", $this->fields["FK_type"]);
				else
					echo getDropdownName($ORDER_TYPE_TABLES[$this->fields["type"]], $this->fields["FK_type"]);
			}

			echo "</span></td></tr>";
			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][22] . ": </td>";
			echo "<td><span id='show_model'>";
			if (isset($ORDER_MODEL_TABLES[$this->fields["type"]])) {
            if ($canedit) 
					dropdownValue($ORDER_MODEL_TABLES[$this->fields["type"]], "FK_model", $this->fields["FK_model"]);
				else
					echo getDropdownName($ORDER_MODEL_TABLES[$this->fields["type"]], $this->fields["FK_model"]);
			}
			echo "</span></td></tr>";

			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][13] . ": </td>";
			echo "<td><span id='show_template'>";
			
			if ($canedit && in_array($this->fields["type"],$ORDER_TEMPLATE_TABLES))
					$this->dropdownTemplate("template", $this->fields["FK_entities"], $commonitem->obj->table, $this->fields["template"]);
				else
					echo $this->getTemplateName($this->fields["type"], $this->fields["template"]);

			echo "</span></td></tr>";

			echo "<tr class='tab_bg_2'><td>" . $LANG['common'][25] . ": </td>";
			
			echo "<td colspan='3'>";
			if ($canedit)
				echo "<textarea cols='50' rows='4' name='comments' >" . $this->fields["comments"] . "</textarea>";
			else
				echo $this->fields["comments"];
			echo "</td></tr>";

			if ($canedit) {
				if (empty ($ID) || $ID < 0) {
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='2'>";
					echo "<div align='center'><input type='submit' name='add' value=\"" . $LANG['buttons'][8] . "\" class='submit'></div>";
					echo "</td>";
					echo "</tr>";
				} else {
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='2'><div align='center'>";
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit' >";
					if (!$this->referenceInUse())
					{
						if ($this->fields["deleted"] == '0') {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . "\" class='submit'></div>";
						} else {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='restore' value=\"" . $LANG['buttons'][21] . "\" class='submit'>";
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"" . $LANG['buttons'][22] . "\" class='submit'></div>";
						}
					}
					echo "</td>";
					echo "</tr>";
				}
			}
			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";
		} else {
			echo "<div align='center'><b>" . $LANG['plugin_order'][11] . "</b></div>";
			return false;
		}
		return true;
	}
	
	function dropdownTemplate($name, $entity, $table, $value = 0) {
      global $DB;
      
      $result = $DB->query("SELECT `tplname`, `ID` FROM `" . $table .
      "` WHERE `FK_entities` = '" . $entity . "' AND `is_template` = '1' AND `tplname` <> '' GROUP BY `tplname` ORDER BY `tplname`");

      $option[0] = '-------------';
      while ($data = $DB->fetch_array($result))
         $option[$data["ID"]] = $data["tplname"];
      return dropdownArrayValues($name, $option, $value);
   }
	
	function getTemplateName($type, $ID) {

      $commonitem = new CommonItem;
      $commonitem->getFromDB($type, $ID);
      return $commonitem->getField("tplname");
   }
   
   function checkIfTemplateExistsInEntity($detailID, $type, $entity) {
      global $DB;
      
      $query = "SELECT `".$this->table."`.`template` AS templateID " .
            "FROM `glpi_plugin_order_detail`, `".$this->table."` " .
            "WHERE `glpi_plugin_order_detail`.`FK_reference` = `".$this->table."`.`ID` " .
            "AND `glpi_plugin_order_detail`.`ID` = '$detailID' ;";
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

	function dropdownAllItems($myname, $ajax = false, $value = 0, $orderID = 0, $supplier = 0, $entity = 0, $ajax_page = '',$filter=false) {
      global $ORDER_AVAILABLE_TYPES,$DB;

      $ci = new CommonItem();

      echo "<select name=\"$myname\" id='$myname'>";
      echo "<option value='0' selected>------</option>\n";
     
     if ($filter){
         
         $used=array();
         $query = "SELECT type FROM `".$this->table."` 
                 LEFT JOIN `glpi_plugin_order_references_manufacturers` ON (`".$this->table."`.`ID` = `glpi_plugin_order_references_manufacturers`.`FK_reference`)
                 WHERE `glpi_plugin_order_references_manufacturers`.`FK_enterprise` = '".$supplier."' ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         if ($number){
            while ($data=$DB->fetch_array($result)){
               $used[]=$data["type"];
            }
         }
       
         foreach ($ORDER_AVAILABLE_TYPES as $tmp => $type) {
            $result=in_array($type, $used);
            if(!$result) {
               unset($ORDER_AVAILABLE_TYPES[$tmp]);
            }
         }
      }
      foreach ($ORDER_AVAILABLE_TYPES as $tmp => $type) {
         $ci->setType($type);
         echo "<option value='$type' " . ($type == $value ? " selected" : '') . ">".$ci->getType(). "</option>\n";
      }
      echo "</select>";

      if ($ajax) {
         $params = array (
            'device_type' => '__VALUE__',
            'FK_enterprise' => $supplier,
            'entity_restrict' => $entity,
            'orderID' => $orderID,	
         );

         ajaxUpdateItemOnSelectEvent($myname, "show_reference", $ajax_page, $params);
      }
   }
   
   function getAllItemsByType($type, $entity, $item_type = 0, $item_model = 0) {
      global $DB, $LINK_ID_TABLE, $ORDER_TYPE_TABLES, $ORDER_MODEL_TABLES, $ORDER_TEMPLATE_TABLES;

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

   function dropdownAllItemsByType($name, $type, $entity=0,$item_type=0,$item_model=0) {

      $items = $this->getAllItemsByType($type,$entity,$item_type,$item_model);
      $items[0] = '-----';
      asort($items);
      return dropdownArrayValues($name, $items, 0);
   }

   function getAllReferencesByEnterpriseAndType($type,$enterpriseID){
      global $DB;
      
      $query = "SELECT `gr`.`name`, `gr`.`ID` 
               FROM `".$this->table."` AS gr, `glpi_plugin_order_references_manufacturers` AS grm" .
            " WHERE `gr`.`type` = '$type' 
               AND `grm`.`FK_enterprise` = '$enterpriseID' 
               AND `grm`.`FK_reference` = `gr`.`ID` ";

      $result = $DB->query($query);
      $references = array();
      while ($data = $DB->fetch_array($result))
         $references[$data["ID"]] = $data["name"];

      return $references;		
   }

   function dropdownReferencesByEnterprise($name, $type, $enterpriseID) {

      $references = $this->getAllReferencesByEnterpriseAndType($type, $enterpriseID);
      $references[0] = '-----';
      return dropdownArrayValues($name, $references, 0);
   }

	function showReferencesFromSupplier($ID){
      global $LANG, $DB, $CFG_GLPI,$INFOFORM_PAGES;
      
      $query = "SELECT `gr`.`ID`, `gr`.`FK_glpi_enterprise`, `gr`.`FK_entities`, `gr`.`type`, `gr`.`name`, `grm`.`price_taxfree` " .
            "FROM `glpi_plugin_order_references_manufacturers` AS grm, `".$this->table."` AS gr " .
            "WHERE `grm`.`FK_enterprise` = '$ID' AND `grm`.`FK_reference` = `gr`.`ID`";
      $result = $DB->query($query);

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='5'>".$LANG['plugin_order']['reference'][3]."</th></tr>";
      echo "<tr>"; 
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['common'][5]."</th>";
      echo "<th>".$LANG['plugin_order']['reference'][1]."</th>";
      echo "<th>". $LANG['common'][17]."</th><th>".$LANG['plugin_order']['detail'][4]."</th></tr>";
      
      if ($DB->numrows($result) > 0)
      {
         $commonitem = new CommonItem;
         while ($data = $DB->fetch_array($result))
         {
            echo "<tr class='tab_bg_1' align='center'>";
            echo "<td>";
            echo getDropdownName("glpi_entities",$data["FK_entities"]);
            echo "</td>";

            echo "<td>";
            echo getDropdownName("glpi_dropdown_manufacturer",$data["FK_glpi_enterprise"]);
            echo "</td>";

            echo "<td>";
            $PluginOrderReference = new PluginOrderReference();
            echo $PluginOrderReference->getReceptionReferenceLink($data["ID"], $data["name"]);
            echo "</td>";
            echo "<td>"; 
            $commonitem->setType($data["type"]);
            echo $commonitem->getType();
            echo "</td>";
            echo "<td>";
            echo $data["price_taxfree"];
            echo "</td>";
            echo "</tr>";	
         }
      }
      echo "</table>";	
      echo "</div>";
      
   }
}

?>