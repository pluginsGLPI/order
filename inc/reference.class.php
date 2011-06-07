<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
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
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: plugin order v1.4.0 - GLPI 0.80
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

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

      $temp = new PluginOrderReference_Supplier();
      $temp->deleteByCriteria(array('plugin_order_references_id' => $this->fields['id']));

   }
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['reference'][1];

      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'name';
      $tab[1]['name'] = $LANG['plugin_order']['detail'][2];
      $tab[1]['datatype'] = 'itemlink';

      $tab[2]['table'] = $this->getTable();
      $tab[2]['field'] = 'comment';
      $tab[2]['name'] = $LANG['common'][25];
      $tab[2]['datatype'] = 'text';

      $tab[3]['table'] = $this->getTable();
      $tab[3]['field'] = 'itemtype';
      $tab[3]['name'] = $LANG['state'][6];
      $tab[3]['datatype'] = 'itemtypename';

      $tab[4]['table'] = $this->getTable();
      $tab[4]['field'] = 'models_id';
      $tab[4]['name'] = $LANG['common'][22];

      $tab[5]['table'] = 'glpi_manufacturers';
      $tab[5]['field'] = 'name';
      $tab[5]['name'] = $LANG['common'][5];

      $tab[6]['table'] = $this->getTable();
      $tab[6]['field'] = 'types_id';
      $tab[6]['name'] = $LANG['common'][17];
      
      $tab[7]['table'] = $this->getTable();
      $tab[7]['field'] = 'templates_id';
      $tab[7]['name'] = $LANG['common'][13];
      
      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['name']=$LANG['common'][2];

      /* entity */
      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name'] = $LANG['entity'][0];
      
      return $tab;
   }
   
   /*define header form */
   function defineTabs($options=array()) {
      global $LANG;
      /* principal */
      $ong[1] = $LANG['title'][26];
      if ($this->fields['id'] > 0) {
         $ong[2] = $LANG['plugin_order'][11];
         $ong[3] = $LANG['title'][37];
         if (haveRight("document", "r"))
            $ong[4] = $LANG['Menu'][27];
         $ong[12] = $LANG['title'][38];
      }

      return $ong;
   }

   function prepareInputForAdd($input){
      global $DB,$LANG;

      if (!isset($input["name"]) || $input["name"] == '') {
         addMessageAfterRedirect($LANG['plugin_order']['reference'][8], false, ERROR);
         return false;
      }

      if (!$input["itemtype"]) {
         addMessageAfterRedirect($LANG['plugin_order']['reference'][9], false, ERROR);
         return false;
      }
      
       if (!isset($input["transfert"])) {
         $query = "SELECT COUNT(*) AS cpt FROM `".$this->getTable()."` " .
                "WHERE `name` = '".$input["name"]."' 
                    AND `entities_id` = '".$input["entities_id"]."' ";
         $result = $DB->query($query);
         if ($DB->result($result,0,"cpt") > 0) {
            addMessageAfterRedirect($LANG['plugin_order']['reference'][6],false,ERROR);
            return false;
         }
      }
      
      return $input;
   }

   function pre_deleteItem(){
      global $LANG;

      if (!$this->referenceInUse()) {
         return true;
      } else {
         addMessageAfterRedirect($LANG['plugin_order']['reference'][7],true,ERROR);
         return false;
      }

   }

   function referenceInUse(){
      global $DB;

      $query = "SELECT COUNT(*) AS cpt FROM `glpi_plugin_order_orders_items` " .
            "WHERE `plugin_order_references_id` = '".$this->fields["id"]."' ";
      $result = $DB->query($query);
      if ($DB->result($result,0,"cpt") > 0) {
         return true;
      } else {
         return false;
      }
   }

   function getReceptionReferenceLink($data) {
      
      $link=getItemTypeFormURL($this->getType());
      
      if ($this->canView()) {
         return "<a href=\"".$link."?id=".$data["id"]."\">" . $data["name"] . "</a>";
      } else {
         return $data['name'];
      }
   }

   function canDelete() {
      return (!$this->referenceInUse());
   }

   function showForm ($ID, $options=array()) {
      global $CFG_GLPI, $LANG;
      
      if (!$this->canView()) {
         return false;
      }
      
      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $this->showTabs($options);
      $this->showFormHeader($options);
  
      $reference_in_use = (!$ID?false:$this->referenceInUse());
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . $LANG['plugin_order']['reference'][1] . ": </td>";
      echo "<td>";
      autocompletionTextField($this,"name",array('size' => "60"));
      echo "</td>";

      echo "<td>" . $LANG['common'][5] . ": </td>";
      echo "<td>";
      if (!$reference_in_use) {
         Dropdown::show('Manufacturer', array('name'  => "manufacturers_id",
                                              'value' => $this->fields["manufacturers_id"]));
      }
      else {
         echo Dropdown::getDropdownName("glpi_manufacturers",
                                        $this->fields["manufacturers_id"]);
      }
      echo "</td></tr>";
      
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . $LANG['state'][6] . ": </td>";
      echo "<td>";
      
      if ($ID > 0) {
         $itemtype = $this->fields["itemtype"];
         $item = new $itemtype();
         echo $item->getTypeName();
      } else {
         $this->dropdownAllItems("itemtype", true, $this->fields["itemtype"], 0, 0,
                                 $_SESSION["glpiactive_entity"], $CFG_GLPI["root_doc"] .
         "/plugins/order/ajax/reference.php");
         echo "<span id='show_reference'></span></td>";
      }

      echo "<td>" . $LANG['common'][17] . ": </td>";
      echo "<td><span id='show_types_id'>";
      if ($this->fields["itemtype"]) {
         if ($this->fields["itemtype"] == 'PluginOrderOther') {
            $file = 'other'; 
         } else {
            $file = $this->fields["itemtype"];
         }
         if (file_exists(GLPI_ROOT."/inc/".strtolower($file)."type.class.php") 
               || file_exists(GLPI_ROOT."/plugins/order/inc/".strtolower($file)."type.class.php")) {
            if (!$reference_in_use)
               Dropdown::show($this->fields["itemtype"]."Type", 
                              array('name' => "types_id", 'value' => $this->fields["types_id"]));
            else
               echo Dropdown::getDropdownName(getTableForItemType($this->fields["itemtype"]."Type"),
                                                                  $this->fields["types_id"]);
         }
      }

      echo "</span></td>";
      
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . $LANG['common'][22] . ": </td>";
      echo "<td><span id='show_models_id'>";
      if ($this->fields["itemtype"]) {
         if (file_exists(GLPI_ROOT."/inc/".strtolower($this->fields["itemtype"])."model.class.php")) {
            Dropdown::show($this->fields["itemtype"]."Model",
                           array('name'  => "models_id",
                                 'value' => $this->fields["models_id"]));
         }
      }
      echo "</span></td>";

      echo "<td>" . $LANG['common'][13] . ": </td>";
      echo "<td><span id='show_templates_id'>";
      
      $table = getTableForItemType($this->fields["itemtype"]);
      
      if ($this->fields["itemtype"] && $item->maybeTemplate()) {
            $this->dropdownTemplate("templates_id", $this->fields["entities_id"], $table, 
                                    $this->fields["templates_id"]);
      } else {
            echo $this->getTemplateName($this->fields["itemtype"], $this->fields["templates_id"]);
      }

      echo "</span></td>";
      
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      
      echo "<td></td>";
      echo "<td></td>";
      
      echo "<td>" . $LANG['common'][25] . ": </td>";
      
      echo "<td>";
      echo "<textarea cols='50' rows='4' name='comment' >" . $this->fields["comment"] . 
         "</textarea>";
      echo "</td>";
      
      echo "</tr>";
      
      $this->showFormButtons($options);
      $this->addDivForTabs();
   
      return true;
   }

   function dropdownTemplate($name, $entity, $table, $value = 0) {
      global $DB;

      $result = $DB->query("SELECT `template_name`, `id` FROM `" . $table .
      "` WHERE `entities_id` = '" . $entity . "' 
            AND `is_template` = '1' 
               AND `template_name` <> '' GROUP BY `template_name` ORDER BY `template_name`");

      $option[0] = DROPDOWN_EMPTY_VALUE;
      while ($data = $DB->fetch_array($result)) {
         $option[$data["id"]] = $data["template_name"];
      }
      return Dropdown::showFromArray($name, $option, array('value'  => $value));
   }

   function getTemplateName($itemtype, $ID) {
      
      if ($ID) {
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
            "WHERE `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `".
               $this->getTable()."`.`id` " .
            "AND `glpi_plugin_order_orders_items`.`id` = '$detailID' ;";
      $result = $DB->query($query);
      if (!$DB->numrows($result)) {
         return 0;
      } else {
         $item = new $itemtype();
         $item->getFromDB($DB->result($result, 0, "templates_id"));
         if ($item->getField('entities_id') == $entity) {
            return $item->getField('id');
         } else {
            return 0;
         }
      }
   }

   function dropdownAllItems($myname, $ajax = false, $value = 0, $orders_id = 0, $suppliers_id = 0,
                             $entity = 0, $ajax_page = '',$filter=false) {
      global $DB;
      
      $types=PluginOrderOrder_Item::getClasses();

      echo "<select name=\"$myname\" id='$myname'>";
      echo "<option value='0' selected>".DROPDOWN_EMPTY_VALUE."</option>\n";

      if ($filter){

         $used=array();
         $query = "SELECT itemtype FROM `".$this->getTable()."`
                 LEFT JOIN `glpi_plugin_order_references_suppliers` ON (`".
                  $this->getTable()."`.`id` = `glpi_plugin_order_references_suppliers`.`plugin_order_references_id`)
                 WHERE `glpi_plugin_order_references_suppliers`.`suppliers_id` = '".$suppliers_id."' ".
                 getEntitiesRestrictRequest("AND",$this->table,'',$entity,true);
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
         $params = array ('itemtype' => '__VALUE__', 'suppliers_id' => $suppliers_id,
                          'entity_restrict' => $entity, 'plugin_order_orders_id' => $orders_id);

         ajaxUpdateItemOnSelectEvent($myname, "show_reference", $ajax_page, $params);
      }
   }

   function getAllItemsByType($itemtype, $entity, $types_id = 0, $models_id = 0) {
      global $DB;

      $and = "";
      $item = new $itemtype();
      
      if (file_exists(GLPI_ROOT."/inc/".strtolower($itemtype)."type.class.php")) {
         $and .= ($types_id != 0 ? " AND `".
            getForeignKeyFieldForTable(getTableForItemType($itemtype."Type"))."` = '$types_id' ":"");
      }
      if (file_exists(GLPI_ROOT."/inc/".strtolower($itemtype)."model.class.php")) {
         $and .= ($models_id != 0 ? " AND `".
            getForeignKeyFieldForTable(getTableForItemType($itemtype."Model"))."` ='$models_id' ":"");
      }
      if ($item->maybeTemplate()) {
         $and .= " AND `is_template` = 0 AND `is_deleted` = 0 ";
      }
      
      $used = "AND `id` NOT IN (SELECT `items_id` FROM `glpi_plugin_order_orders_items`)";
      if ($itemtype == 'SoftwareLicense'
            || $itemtype == 'ConsumableItem'
               || $itemtype == 'CartridgeItem')
         $used = "";

      switch ($itemtype) {
         default :
            $query = "SELECT `id`, `name` 
                     FROM `" . getTableForItemType($itemtype) . "` 
                     WHERE `entities_id` = '" . $entity ."' ". $and . " 
                     $used ";
            break;
         case 'ConsumableItem' :
            $query = "SELECT `id`, `name` FROM `glpi_consumableitems`
                     WHERE `entities_id` = '" . $entity . "'
                     AND `consumableitemtypes_id` = '$types_id' 
                     ORDER BY `name`";
            break;
         case 'CartridgeItem' :
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

      $items    = $this->getAllItemsByType($itemtype,$entity,$types_id,$models_id);
      $items[0] = DROPDOWN_EMPTY_VALUE;
      asort($items);
      return Dropdown::showFromArray($name, $items);
   }

   function getAllReferencesByEnterpriseAndType($itemtype,$enterpriseID){
      global $DB;

      $query = "SELECT `gr`.`name`, `gr`.`id`, `grm`.`reference_code`
                FROM `".$this->getTable()."` AS gr, `glpi_plugin_order_references_suppliers` AS grm 
                WHERE `gr`.`itemtype` = '$itemtype'
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
      global $LANG, $DB, $CFG_GLPI;

      $query = "SELECT `gr`.`id`, `gr`.`manufacturers_id`, `gr`.`entities_id`, `gr`.`itemtype`,
                       `gr`.`name`, `grm`.`price_taxfree` 
               FROM `glpi_plugin_order_references_suppliers` AS grm, `".$this->getTable()."` AS gr 
               WHERE `grm`.`suppliers_id` = '$ID' 
                  AND `grm`.`plugin_order_references_id` = `gr`.`id`"
               .getEntitiesRestrictRequest(" AND ","gr",'','',true);
      $result = $DB->query($query);

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='5'>".$LANG['plugin_order']['reference'][3]."</th></tr>";
      echo "<tr>";
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['common'][5]."</th>";
      echo "<th>".$LANG['plugin_order']['reference'][1]."</th>";
      echo "<th>". $LANG['common'][17]."</th><th>".$LANG['plugin_order']['detail'][4]."</th></tr>";

      if ($DB->numrows($result) > 0) {
         while ($data = $DB->fetch_array($result)) {
            echo "<tr class='tab_bg_1' align='center'>";
            echo "<td>";
            echo Dropdown::getDropdownName("glpi_entities",$data["entities_id"]);
            echo "</td>";

            echo "<td>";
            echo Dropdown::getDropdownName("glpi_manufacturers",$data["manufacturers_id"]);
            echo "</td>";

            echo "<td>";
            $PluginOrderReference = new PluginOrderReference();
            echo $PluginOrderReference->getReceptionReferenceLink($data);
            echo "</td>";
            echo "<td>";
            $item = new $data["itemtype"]();
            echo $item->getTypeName();
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
   
   function getAllOrdersByReference($plugin_order_references_id){
      global $DB,$LANG;
      
      $query = "SELECT `glpi_plugin_order_orders`.* 
               FROM `glpi_plugin_order_orders_items`
               LEFT JOIN `glpi_plugin_order_orders` 
                  ON (`glpi_plugin_order_orders`.`id` = `glpi_plugin_order_orders_items`.`plugin_order_orders_id`)
               WHERE `plugin_order_references_id` = '".$plugin_order_references_id."'
               GROUP BY `glpi_plugin_order_orders`.`id`
               ORDER BY `entities_id`, `name` ";
      $result = $DB->query($query);

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='5'>".$LANG['plugin_order'][11]."</th></tr>";
      echo "<tr>"; 
      echo "<th>".$LANG['common'][16]."</th>";
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "</tr>";
 
      while ($data = $DB->fetch_array($result)) {
         echo "<tr class='tab_bg_1' align='center'>"; 
         echo "<td>";

         $link=getItemTypeFormURL('PluginOrderOrder');
         if ($this->canView()) {
            echo "<a href=\"".$link."?id=".$data["id"]."\">".$data["name"]."</a>";
         } else {
            echo $data["name"];  
         }
         echo "</td>";

         echo "<td>";
         echo Dropdown::getDropdownName("glpi_entities",$data["entities_id"]);
         echo "</td>";

         echo "</tr>"; 
      }
      
      echo "</table></div>";
   }
   
   function transfer($ID, $entity) {
      global $DB;
      
      if ($ID<=0 || !$this->getFromDB($ID)) {
         return 0;
      }

      $input                = $this->fields;
      $input['entities_id'] = $entity;
      $oldref               = $input['id'];
      unset($input['id']);
      $input['transfert']   = 1;
      $newid=$this->add($input);
      
      $PluginOrderReference_Supplier       = new PluginOrderReference_Supplier();
      $PluginOrderReference_Supplier->getFromDBByReference($oldref);
      $input = $PluginOrderReference_Supplier->fields;
      $input['entities_id']                = $entity;
      $input['plugin_order_references_id'] = $newid;
      unset($input['id']);
      $PluginOrderReference_Supplier->add($input);
      
      $PluginOrderOrder_Item = new PluginOrderOrder_Item();
      
      $query="SELECT `id` FROM `glpi_plugin_order_orders_items`
               WHERE `plugin_order_references_id` = '$oldref' ";
      
      $result=$DB->query($query);
      $num=$DB->numrows($result);
      if ($num) {
         while ($dataref=$DB->fetch_array($result)) {
            $values["id"] = $dataref['id'];
            $values["plugin_order_references_id"] = $newid;
            $PluginOrderOrder_Item->update($values);
         }
      }
   }
   
   /**
    * Display entities of the loaded profile
    *
   * @param $myname select name
    * @param $target target for entity change action
    */
   static function showSelector($target) {
      global $CFG_GLPI,$LANG;

      $rand=mt_rand();
      Plugin::loadLang('order');
      echo "<div class='center' ><span class='b'>".$LANG['plugin_order']['reference'][12].
         "</span><br>";
      echo "<a style='font-size:14px;' href='".$target."?reset=reset' title=\"".
             $LANG['buttons'][40]."\">".str_replace(" ","&nbsp;",$LANG['buttons'][40])."</a></div>";

      echo "<div class='left' style='width:100%'>";

      echo "<script type='javascript'>";
      echo "var Tree_Category_Loader$rand = new Ext.tree.TreeLoader({
         dataUrl:'".$CFG_GLPI["root_doc"]."/plugins/order/ajax/referencetreetypes.php'
      });";

      echo "var Tree_Category$rand = new Ext.tree.TreePanel({
         collapsible      : false,
         animCollapse     : false,
         border           : false,
         id               : 'tree_projectcategory$rand',
         el               : 'tree_projectcategory$rand',
         autoScroll       : true,
         animate          : false,
         enableDD         : true,
         containerScroll  : true,
         height           : 320,
         width            : 770,
         loader           : Tree_Category_Loader$rand,
         rootVisible     : false
      });";

      // SET the root node.
      echo "var Tree_Category_Root$rand = new Ext.tree.AsyncTreeNode({
         text     : '',
         draggable   : false,
         id    : '-1'                  // this IS the id of the startnode
      });
      Tree_Category$rand.setRootNode(Tree_Category_Root$rand);";

      // Render the tree.
      echo "Tree_Category$rand.render();
            Tree_Category_Root$rand.expand();";

      echo "</script>";

      echo "<div id='tree_projectcategory$rand' ></div>";
      echo "</div>";
   }

   function title() {
      global $LANG, $CFG_GLPI;

      echo "<div align='center'><script type='text/javascript'>";
      echo "cleanhide('modal_reference_content');";
      echo "var order_window=new Ext.Window({
         layout:'fit',
         width:800,
         height:400,
         closeAction:'hide',
         modal: true,
         autoScroll: true,
         title: \"".$LANG['plugin_order']['reference'][11]."\",
         autoLoad: '".$CFG_GLPI['root_doc']."/plugins/order/ajax/referencetree.php'
      });";
      echo "</script>";

      echo "<a onclick='order_window.show();' href='#modal_reference_content' title='".
             $LANG['plugin_order']['reference'][11]."'>".
             $LANG['plugin_order']['reference'][11]."</a>";
      echo "</div>";

   }
}

?>