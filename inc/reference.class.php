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

	public $dohistory=true;
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['reference'][1];
   }
   
   function canCreate() {
      return plugin_order_haveRight('reference', 'w');
   }

   function canView() {
      return plugin_order_haveRight('reference', 'r');
   }
   
   function cleanDBonPurge() {
		global $DB;

		$temp = new PluginOrderReference_Manufacturer();
      $temp->clean(array('plugin_order_references_id' => $this->fields['id']));

	}
	
	function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['reference'][1];

		$tab[1]['table'] = $this->getTable();
		$tab[1]['field'] = 'name';
		$tab[1]['linkfield'] = 'name';
		$tab[1]['name'] = $LANG['plugin_order']['detail'][2];
		$tab[1]['datatype'] = 'itemlink';

		$tab[2]['table'] = $this->getTable();
		$tab[2]['field'] = 'comment';
		$tab[2]['linkfield'] = 'comment';
		$tab[2]['name'] = $LANG['common'][25];
      $tab[2]['datatype'] = 'text';

		$tab[3]['table'] = $this->getTable();
		$tab[3]['field'] = 'itemtype';
		$tab[3]['linkfield'] = '';
		$tab[3]['name'] = $LANG['state'][6];

		$tab[4]['table'] = $this->getTable();
		$tab[4]['field'] = 'models_id';
		$tab[4]['linkfield'] = '';
		$tab[4]['name'] = $LANG['common'][13];

		$tab[5]['table'] = 'glpi_manufacturers';
		$tab[5]['field'] = 'name';
		$tab[5]['linkfield'] = 'manufacturers_id';
		$tab[5]['name'] = $LANG['common'][5];

		$tab[6]['table'] = $this->getTable();
		$tab[6]['field'] = 'types_id';
		$tab[6]['linkfield'] = '';
		$tab[6]['name'] = $LANG['common'][17];

		$tab[7]['table'] = $this->getTable();
		$tab[7]['field'] = 'models_id';
		$tab[7]['linkfield'] = '';
		$tab[7]['name'] = $LANG['common'][22];

      $tab[30]['table'] = $this->getTable();
		$tab[30]['field'] = 'id';
		$tab[30]['linkfield'] = '';
		$tab[30]['name']=$LANG['common'][2];

		/* entity */
		$tab[80]['table'] = 'glpi_entities';
		$tab[80]['field'] = 'completename';
		$tab[80]['linkfield'] = 'entities_id';
		$tab[80]['name'] = $LANG['entity'][0];
		
		return $tab;
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

	function prepareInputForAdd($input){
		global $DB,$LANG;

		if (!isset($input["name"]) || $input["name"] == '')
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][8], false, ERROR);
			return false;
		}

		if (!$input["itemtype"])
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][9], false, ERROR);
			return false;
		}

		$query = "SELECT COUNT(*) AS cpt FROM `".$this->getTable()."` " .
				 "WHERE `name` = '".$input["name"]."' AND `entities_id` = '".$input["entities_id"]."' ";
		$result = $DB->query($query);
		if ($DB->result($result,0,"cpt") > 0)
		{
			addMessageAfterRedirect($LANG['plugin_order']['reference'][6],false,ERROR);
			return false;
		}
		else
			return $input;
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

		$query = "SELECT COUNT(*) AS cpt FROM `glpi_plugin_order_orders_items` " .
				"WHERE `plugin_order_references_id` = '".$this->fields["id"]."' ";
		$result = $DB->query($query);
		if ($DB->result($result,0,"cpt") > 0)
			return true;
		else
			return false;
	}

	function getReceptionReferenceLink($data) {
      
      $link=getItemTypeFormURL($this->getType());
      
      if (plugin_order_haveRight("reference","r"))
         return "<a href=\"".$link."?id=".$data["id"]."\">" . $data["name"] . "</a>";
      else
         return $name;
   }

	function canDelete()
	{
		return (!$this->referenceInUse());
	}

	function showForm($target, $ID, $withtemplate = '') {
		global $CFG_GLPI, $LANG, $DB,$ORDER_TEMPLATE_TABLES,$ORDER_TYPE_CLASSES,$ORDER_MODEL_CLASSES;
      
      if (!plugin_order_haveRight("reference","r")) return false;
		
		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $canedit=$this->can($ID,'w');
		$canrecu = $this->can($ID, 'recursive');
		
      $this->showTabs($ID, $withtemplate);
      $this->showFormHeader($target,$ID,$withtemplate,1);
  
		$reference_in_use = (!$ID?false:$this->referenceInUse());

      echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order']['reference'][1] . ": </td>";
      echo "<td>";
      if ($canedit)
         autocompletionTextField($this,"name",array('size' => "70"));
      else
         echo $this->fields["name"];
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][5] . ": </td>";
      echo "<td>";
      if ($canedit && !$reference_in_use)
         Dropdown::show('Manufacturer', array('name' => "manufacturers_id",'value' => $this->fields["manufacturers_id"]));
      else
         echo Dropdown::getDropdownName("glpi_manufacturers",$this->fields["manufacturers_id"]);
      echo "</td></tr>";
      
      if ($ID > 0) {
         $itemtype = $this->fields["itemtype"];
         
         if (!class_exists($itemtype)) {
            continue;
         } 
         
         $item = new $itemtype();
      }
      echo "<tr class='tab_bg_2'><td>" . $LANG['state'][6] . ": </td>";
      echo "<td>";
      if ($ID > 0)
         echo $item->getTypeName();
      else {
         $this->dropdownAllItems("itemtype", true, $this->fields["itemtype"], 0, 0, $_SESSION["glpiactive_entity"], $CFG_GLPI["root_doc"] .
         "/plugins/order/ajax/reference.php");
         echo "<span id='show_reference'></span></td></tr>";
      }

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][17] . ": </td>";
      echo "<td><span id='show_types_id'>";
      if (isset($ORDER_TYPE_CLASSES[$this->fields["itemtype"]])) {
         if ($canedit && !$reference_in_use)
            Dropdown::show($ORDER_TYPE_CLASSES[$this->fields["itemtype"]], array('name' => "types_id",'value' => $this->fields["types_id"]));
         else
            echo Dropdown::getDropdownName($ORDER_TYPE_CLASSES[$this->fields["itemtype"]], $this->fields["types_id"]);
      }

      echo "</span></td></tr>";
      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][22] . ": </td>";
      echo "<td><span id='show_models_id'>";
      if (isset($ORDER_MODEL_CLASSES[$this->fields["itemtype"]])) {
         if ($canedit)
            Dropdown::show($ORDER_MODEL_CLASSES[$this->fields["itemtype"]], array('name' => "models_id",'value' => $this->fields["models_id"]));
         else
            echo Dropdown::getDropdownName($ORDER_MODEL_CLASSES[$this->fields["itemtype"]], $this->fields["models_id"]);
      }
      echo "</span></td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][13] . ": </td>";
      echo "<td><span id='show_templates_id'>";
      
      $table = getTableForItemType($this->fields["itemtype"]);
      
      if ($canedit && in_array($this->fields["itemtype"],$ORDER_TEMPLATE_TABLES))
            $this->dropdownTemplate("templates_id", $this->fields["entities_id"], $table, $this->fields["templates_id"]);
         else
            echo $this->getTemplateName($this->fields["itemtype"], $this->fields["templates_id"]);

      echo "</span></td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][25] . ": </td>";

      echo "<td colspan='3'>";
      if ($canedit)
         echo "<textarea cols='50' rows='4' name='comment' >" . $this->fields["comment"] . "</textarea>";
      else
         echo $this->fields["comment"];
      echo "</td></tr>";

      $this->showFormButtons($ID,$withtemplate,1);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";
   
      return true;
	}

	function dropdownTemplate($name, $entity, $table, $value = 0) {
      global $DB;

      $result = $DB->query("SELECT `template_name`, `id` FROM `" . $table .
      "` WHERE `entities_id` = '" . $entity . "' AND `is_template` = '1' AND `template_name` <> '' GROUP BY `template_name` ORDER BY `template_name`");

      $option[0] = '-------------';
      while ($data = $DB->fetch_array($result))
         $option[$data["id"]] = $data["template_name"];
      return Dropdown::showFromArray($name, $option, array('value'  => $value));
   }

	function getTemplateName($itemtype, $ID) {
      
      if ($ID) {
         if (!class_exists($itemtype)) {
               continue;
            } 
            
         $item = new $itemtype();

         $item->getFromDB($ID);
         return $item->getField("template_name");
      } else {
         return false;
      }
   }

   function checkIfTemplateExistsInEntity($detailID, $itemtype, $entity) {
      global $DB;

      $query = "SELECT `".$this->getTable()."`.`templates_id` " .
            "FROM `glpi_plugin_order_orders_items`, `".$this->getTable()."` " .
            "WHERE `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `".$this->getTable()."`.`id` " .
            "AND `glpi_plugin_order_orders_items`.`id` = '$detailID' ;";
      $result = $DB->query($query);
      if (!$DB->numrows($result))
         return 0;
      else {
         $item = new $itemtype();
         $item->getFromDB($DB->result($result, 0, "templates_id"));
         if ($item->getField('entities_id') == $entity)
            return $item->getField('id');
         else
            return 0;
      }
   }

	function dropdownAllItems($myname, $ajax = false, $value = 0, $orders_id = 0, $suppliers_id = 0, $entity = 0, $ajax_page = '',$filter=false) {
      global $DB;
      
      $types=PluginOrderOrder_Item::getClasses();

      echo "<select name=\"$myname\" id='$myname'>";
      echo "<option value='0' selected>------</option>\n";

      if ($filter){

         $used=array();
         $query = "SELECT itemtype FROM `".$this->getTable()."`
                 LEFT JOIN `glpi_plugin_order_references_manufacturers` ON (`".$this->getTable()."`.`id` = `glpi_plugin_order_references_manufacturers`.`plugin_order_references_id`)
                 WHERE `glpi_plugin_order_references_manufacturers`.`suppliers_id` = '".$suppliers_id."' ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         if ($number){
            while ($data=$DB->fetch_array($result)){
               $used[]=$data["itemtype"];
            }
         }
         
         foreach ($types as $tmp => $itemtype) {
            $result=in_array($itemtype, $used);
            if(!$result) {
               unset($types[$tmp]);
            }
         }
      }
      
      foreach ($types as $type) {
         
         $item = new $type();
         echo "<option value='".$type."'>".$item->getTypeName()."</option>\n";
      }

      echo "</select>";

      if ($ajax) {
         $params = array (
            'itemtype' => '__VALUE__',
            'suppliers_id' => $suppliers_id,
            'entity_restrict' => $entity,
            'orders_id' => $orders_id,
         );

         ajaxUpdateItemOnSelectEvent($myname, "show_reference", $ajax_page, $params);
      }
   }

   function getAllItemsByType($itemtype, $entity, $types_id = 0, $models_id = 0) {
      global $DB, $LINK_ID_TABLE, $ORDER_TYPE_TABLES, $ORDER_MODEL_TABLES, $ORDER_TEMPLATE_TABLES;

      $and = "";
      
      if ($itemtype == 'Contract')
         $field = "contract_type";
      else 
         $field = "type";
      if (isset ($ORDER_TYPE_TABLES[$itemtype]))
         $and .= ($types_id != 0 ? " AND `$field` = '$types_id' " : "");
      if (isset ($ORDER_MODEL_TABLES[$itemtype]))
         $and .= ($models_id != 0 ? " AND `model` ='$models_id' " : "");
      if (in_array($itemtype, $ORDER_TEMPLATE_TABLES))
         $and .= " AND `is_template` = 0 AND `is_deleted` = 0 ";

      switch ($itemtype) {
         default :
            $query = "SELECT `id`, `name` 
                     FROM `" . $LINK_ID_TABLE[$itemtype] . "` 
                     WHERE `entities_id` = '" . $entity ."' ". $and . " 
                     AND `id` NOT IN (SELECT `items_id` FROM `glpi_plugin_order_orders_items`)";
            break;
         case 'ConsumableItemType' :
            $query = "SELECT `id`, `name` FROM `glpi_consumableitems`
                     WHERE `entities_id` = '" . $entity . "'
                     AND `consumableitemtypes_id` = '$types_id' 
                     ORDER BY `name`";
            break;
         case 'CartridgeItemType' :
            $query = "SELECT `id`, `name` FROM `glpi_cartridgeitems`
                     WHERE `entities_id` = '" . $entity . "'
                     AND `cartridgeitemtypes_id` = '$types_id'
                     ORDER BY `name` ASC";
            break;
      }
      $result = $DB->query($query);

      $device = array ();
      while ($data = $DB->fetch_array($result)) {
         $device[$data["id"]] = $data["name"];
      }

      return $device;
   }

   function dropdownAllItemsByType($name, $itemtype, $entity=0,$types_id=0,$models_id=0) {

      $items = $this->getAllItemsByType($itemtype,$entity,$types_id,$models_id);
      $items[0] = '-----';
      asort($items);
      return Dropdown::showFromArray($name, $items);
   }

   function getAllReferencesByEnterpriseAndType($itemtype,$enterpriseID){
      global $DB;

      $query = "SELECT `gr`.`name`, `gr`.`id`, `grm`.`reference_code`
               FROM `".$this->getTable()."` AS gr, `glpi_plugin_order_references_manufacturers` AS grm" .
            " WHERE `gr`.`itemtype` = '$itemtype'
               AND `grm`.`suppliers_id` = '$enterpriseID'
               AND `grm`.`plugin_order_references_id` = `gr`.`id` ";

      $result = $DB->query($query);
      $references = array();
      while ($data = $DB->fetch_array($result)) {
         $references[$data["id"]] = $data["name"];
         if ($data['reference_code']) {
            $references[$data["id"]] .= ' ('.$data['reference_code'].')';
         }
      }

      return $references;
   }

   function dropdownReferencesByEnterprise($name, $itemtype, $enterpriseID) {

      $references = $this->getAllReferencesByEnterpriseAndType($itemtype, $enterpriseID);
      $references[0] = '-----';
      return Dropdown::showFromArray($name, $references);
   }

	function showReferencesFromSupplier($ID){
      global $LANG, $DB, $CFG_GLPI,$INFOFORM_PAGES;

      $query = "SELECT `gr`.`id`, `gr`.`manufacturers_id`, `gr`.`entities_id`, `gr`.`itemtype`, `gr`.`name`, `grm`.`price_taxfree` " .
            "FROM `glpi_plugin_order_references_manufacturers` AS grm, `".$this->getTable()."` AS gr " .
            "WHERE `grm`.`suppliers_id` = '$ID' AND `grm`.`plugin_order_references_id` = `gr`.`id`";
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
            echo Dropdown::getDropdownName("glpi_entities",$this->fields["entities_id"]);
            echo "</td>";

            echo "<td>";
            echo Dropdown::getDropdownName("glpi_manufacturers",$this->fields["manufacturers_id"]);
            echo "</td>";

            echo "<td>";
            $PluginOrderReference = new PluginOrderReference();
            echo $PluginOrderReference->getReceptionReferenceLink($data["id"], $data["name"]);
            echo "</td>";
            echo "<td>";
            $commonitem->setType($data["itemtype"]);
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