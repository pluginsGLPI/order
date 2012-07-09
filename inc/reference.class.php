<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Behaviors. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderReference extends CommonDropdown {

   public $dohistory         = true;
   public $first_level_menu  = "plugins";
   public $second_level_menu = "order";
   public $forward_entity_to = array('PluginOrderReference_Supplier');
   
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

      $tab[86]['table']    = $this->getTable();
      $tab[86]['field']    = 'is_recursive';
      $tab[86]['name']     = $LANG['entity'][9];
      $tab[86]['datatype'] = 'bool';
      $tab[86]['massiveaction'] = false;

      return $tab;
   }
   
   function prepareInputForAdd($input){
      global $DB,$LANG;

      if (!isset($input["name"]) || $input["name"] == '') {
         Session::addMessageAfterRedirect($LANG['plugin_order']['reference'][8], false, ERROR);
         return false;
      }

      if (!$input["itemtype"]) {
         Session::addMessageAfterRedirect($LANG['plugin_order']['reference'][9], false, ERROR);
         return false;
      }
      
       if (!isset($input["transfert"])
            && countElementsInTable($this->getTable(), 
                                    "`name` = '".$input["name"]."' 
                                       AND `entities_id` = '".$input["entities_id"]."'")) {
         Session::addMessageAfterRedirect($LANG['plugin_order']['reference'][6], false, ERROR);
         return false;
      }
      
      return $input;
   }

   function pre_deleteItem(){
      global $LANG;

      if (!$this->referenceInUse()) {
         return true;
      } else {
         Session::addMessageAfterRedirect($LANG['plugin_order']['reference'][7], true, ERROR);
         return false;
      }

   }

   function referenceInUse(){
      global $DB;
      
      $number = countElementsInTable("glpi_plugin_order_orders_items", 
                                   "`plugin_order_references_id` = '".
                                      $this->fields["id"]."'");
      if ($number > 0) {
         return true;
      } else {
         return false;
      }
   }

   function getReceptionReferenceLink($data) {
      $link=Toolbox::getItemTypeFormURL($this->getType());
      if ($this->canView()) {
         return "<a href=\"".$link."?id=".$data["id"]."\">" . $data["name"] . "</a>";
      } else {
         return $data['name'];
      }
   }

   function canDelete() {
      return (!$this->referenceInUse());
   }

   function getAdditionalFields() {
      global $LANG;

      return array(array('name'  => 'manufacturers_id',
                         'label' => $LANG['common'][5],
                         'type'  => 'dropdownValue'),
                   array('name'  => 'itemtype',
                         'label' => $LANG['state'][6],
                         'type'  => 'reference_itemtype'),
                   array('name'  => 'types_id',
                         'label' => $LANG['common'][17],
                         'type'  => 'reference_types_id'),
                   array('name'  => 'models_id',
                         'label' => $LANG['common'][22],
                         'type'  => 'reference_models_id'),
                   array('name'  => 'templates_id',
                         'label' => $LANG['common'][13],
                         'type'  => 'reference_templates_id'));
   }

   /**
    * Display specific fields for FieldUnicity
    *
    * @param $ID
    * @param $field array
   **/
   function displaySpecificTypeField($ID, $field=array()) {
      global $CFG_GLPI;
      
      $reference_in_use = (!$ID?false:$this->referenceInUse());
       
      switch ($field['type']) {
         case 'reference_itemtype' :
            if ($ID > 0) {
               $itemtype = $this->fields["itemtype"];
               $item     = new $itemtype();
               echo $item->getTypeName();
               echo "<input type='hidden' name='itemtype' value='$itemtype'>";
            } else {
               $params = array('myname'       => 'itemtype', 'ajax' => true, 
                               'value'        => $this->fields["itemtype"],
                               'entity'       => $_SESSION["glpiactive_entity"], 
                               'ajax_page'    => GLPI_ROOT.'/plugins/order/ajax/referencespecifications.php',
                               'class'        => __CLASS__);
                               
               $this->dropdownAllItems($params);
            }
            break;

         case 'reference_types_id' :
            echo "<span id='show_types_id'>";
            if ($this->fields["itemtype"]) {
               if ($this->fields["itemtype"] == 'PluginOrderOther') {
                  $file = 'other'; 
               } else {
                  $file = $this->fields["itemtype"];
               }
               $core_typefilename   = GLPI_ROOT."/inc/".strtolower($file)."type.class.php";
               $plugin_typefilename = GLPI_ROOT."/plugins/order/inc/".strtolower($file)."type.class.php";
               $itemtypeclass       = $this->fields["itemtype"]."Type";
               
               if (file_exists($core_typefilename) 
                     || file_exists($plugin_typefilename)) {
                  if (!$reference_in_use) {
                     Dropdown::show($itemtypeclass, 
                                    array('name'  => "types_id", 
                                          'value' => $this->fields["types_id"]));
                  } else{
                     echo Dropdown::getDropdownName(getTableForItemType($itemtypeclass),
                                                                        $this->fields["types_id"]);
                  }
                }
            }
      
            echo "</span>";
            break;
            
         case 'reference_models_id' :
            echo "<span id='show_models_id'>";
            if ($this->fields["itemtype"]) {
               if (file_exists(GLPI_ROOT."/inc/".strtolower($this->fields["itemtype"])."model.class.php")) {
                  Dropdown::show($this->fields["itemtype"]."Model",
                                 array('name'  => "models_id",
                                       'value' => $this->fields["models_id"]));
               }
            }
            echo "</span>";
            break;

         case 'reference_templates_id' :
            echo "<span id='show_templates_id'>";
            if ($this->fields['itemtype'] != '' 
               && FieldExists(getTableForItemType($this->fields['itemtype']), 'is_template')) {
               $this->dropdownTemplate('templates_id', $this->fields['entities_id'], 
                                       getTableForItemType($this->fields['itemtype']), 
                                       $this->fields['templates_id']);
            }
            echo "</span>";

            break;

      }
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;
      
      if (!$withtemplate) {

         if ($item->getType()=='Supplier') {
         
            return $LANG['plugin_order']['title'][1];
         
         } else if ($item->getType()==__CLASS__) {

            return $LANG['plugin_order'][11];
         
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;
      
      $self=new self();
      $order_supplier = new PluginOrderOrder_Supplier();
      $surveysupplier = new PluginOrderSurveySupplier();
      
      if ($item->getType()=='Supplier') {
      
         $self->showReferencesFromSupplier($item->getField('id'));
         $order_supplier->showDeliveries($item->getField('id'));
         $surveysupplier->showGlobalNotation($item->getField('id'));
         
      } else if ($item->getType()==__CLASS__) {
         $self->getAllOrdersByReference($item->getID());

      }
      return true;
   }
   
   function defineTabs($options=array()) {
      global $LANG;
      $tabs = array();
      $this->addStandardTab('PluginOrderReference_Supplier', $tabs, $options);
      $this->addStandardTab(__CLASS__,$tabs,$options);
      $this->addStandardTab('Document',$tabs,$options);
      $this->addStandardTab('Note',$tabs,$options);
      $this->addStandardTab('Log',$tabs,$options);
      return $tabs;
   }
   
   
   function dropdownTemplate($name, $entity, $table, $value = 0) {
      global $DB;

      $query = "SELECT `template_name`, `id` FROM `$table`
                WHERE `entities_id` = ' $entity' 
                   AND `is_template` = '1' 
                      AND `template_name` <> '' GROUP BY `template_name` ORDER BY `template_name`";
      $result = $DB->query($query);

      $option[0] = Dropdown::EMPTY_VALUE;
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

      $query = "SELECT `".$this->getTable()."`.`templates_id` 
               FROM `glpi_plugin_order_orders_items`, `".$this->getTable()."` 
               WHERE `glpi_plugin_order_orders_items`.`plugin_order_references_id` = `".
                   $this->getTable()."`.`id`
                      AND `glpi_plugin_order_orders_items`.`id` = '$detailID' ;";
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

   function dropdownAllItems($options = array()) {

      global $DB;
      
      $p['myname']       = '';
      $p['ajax']         = false;
      $p['value']        = 0;
      $p['orders_id']    = 0;
      $p['suppliers_id'] = 0;
      $p['entity']       = 0;
      $p['ajax_page']    = '';
      $p['filter']       = '';
      $p['class']       = '';
      foreach ($options as $key => $value) {
         $p[$key] = $value;
      }
      
      $types = PluginOrderOrder::getTypes();

      echo "<select name='".$p['myname']."' id='".$p['myname']."'>";
      echo "<option value='0' selected>".Dropdown::EMPTY_VALUE."</option>\n";

      if ($p['filter']){

         $used  = array();
         $query = "SELECT `itemtype` FROM `".$this->getTable()."` as t
                   LEFT JOIN `glpi_plugin_order_references_suppliers` as s ON (
                      `t`.`id` = `s`.`plugin_order_references_id`)
                  WHERE `s`.`suppliers_id` = '".$p['suppliers_id']."' ".
                 getEntitiesRestrictRequest("AND", 't', '', $p['entity'], true);
         $result = $DB->query($query);

         $number = $DB->numrows($result);
         if ($number) {
            while ($data=$DB->fetch_array($result)) {
               $used[]=$data["itemtype"];
            }
         }
         
         foreach ($types as $tmp => $itemtype) {
            if(!in_array($itemtype, $used)) {
               unset($types[$tmp]);
            }
         }
      }
      
      foreach ($types as $type) {
         $item = new $type();
         echo "<option value='".$type."'>".$item->getTypeName()."</option>\n";
      }

      echo "</select>";

      if ($p['ajax']) {
         $params = array ('itemtype' => '__VALUE__', 'suppliers_id' => $p['suppliers_id'],
                          'entity_restrict' => $p['entity'], 
                          'plugin_order_orders_id' => $p['orders_id']);

         if ($p['class'] != 'PluginOrderOrder_Item') {
            foreach (array("types_id", "models_id", "templates_id") as $field) {
               $params['field'] = $field;
               $params['plugin_order_references_id'] = $p['value'];
               Ajax::updateItemOnSelectEvent($p['myname'], "show_$field", 
                                           $p['ajax_page'], 
                                           $params);
            }
         } else {
               Ajax::updateItemOnSelectEvent($p['myname'], "show_reference", 
                                           $p['ajax_page'], 
                                           $params);
         }
      }
   }

   function getAllItemsByType($itemtype, $entity, $types_id = 0, $models_id = 0) {
      global $DB;

      $and  = "";
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

      $items    = $this->getAllItemsByType($itemtype, $entity, $types_id, $models_id);
      $items[0] = Dropdown::EMPTY_VALUE;
      asort($items);
      return Dropdown::showFromArray($name, $items);
   }

   function dropdownReferencesByEnterprise($options = array()) {
      global $DB;
      $query = "SELECT `gr`.`name`, `gr`.`id`, `grm`.`reference_code`
                FROM `".$this->getTable()."` AS gr, `glpi_plugin_order_references_suppliers` AS grm 
                WHERE `gr`.`itemtype` = '".$options['itemtype']."'
                   AND `grm`.`suppliers_id` = '".$options['suppliers_id']."'
                     AND `grm`.`plugin_order_references_id` = `gr`.`id`  ORDER BY `gr`.`name` ASC";

      $result     = $DB->query($query);
      $references = array();
      while ($data = $DB->fetch_array($result)) {
         $references[] = $data["id"];
      }

      if (!empty($references)) {
         $condition = "`id` IN (".implode(',', $references).")";
         return Dropdown::show(__CLASS__, array('condition'           => $condition, 
                                                'name'                => 'reference', 
                                                'display_emptychoice' => true,
                                                'entity'              => $options['entity_restrict']));
      } else {
         return Dropdown::EMPTY_VALUE;
      }
   }
   
   function showReferences($itemtype, $options=array()) {
      global $DB,$CFG_GLPI,$LANG;

      if ($itemtype && !($item = getItemForItemtype($itemtype))) {
         return false;
      }

      $table = $item->getTable();

      $params['name']        = $item->getForeignKeyField();
      $params['value']       = ($itemtype=='Entity' ? $_SESSION['glpiactive_entity'] : '');
      $params['comments']    = true;
      $params['entity']      = -1;
      $params['entity_sons'] = false;
      $params['toupdate']    = '';
      $params['used']        = array();
      $params['toadd']       = array();
      $params['on_change']   = '';
      $params['condition']   = '';
      $params['rand']        = mt_rand();
      $params['displaywith'] = array();
      //Parameters about choice 0
      //Empty choice's label
      $params['emptylabel'] = Dropdown::EMPTY_VALUE;
      //Display emptychoice ?
      $params['display_emptychoice'] = true;
      //In case of Entity dropdown, display root entity ?
      $params['display_rootentity']  = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $name         = $params['emptylabel'];
      $comment      = "";
      $limit_length = $_SESSION["glpidropdown_chars_limit"];

      // Check default value for dropdown : need to be a numeric
      if (strlen($params['value'])==0 || !is_numeric($params['value'])) {
         $params['value'] = 0;
      }

      if ($params['value'] > 0
         || ($itemtype == "Entity" && $params['value'] >= 0)) {
         $tmpname = self::getDropdownName($table, $params['value'], 1);

         if ($tmpname["name"] != "&nbsp;") {
            $name    = $tmpname["name"];
            $comment = $tmpname["comment"];

            if (Toolbox::strlen($name) > $_SESSION["glpidropdown_chars_limit"]) {
               if ($item instanceof CommonTreeDropdown) {
                  $pos          = strrpos($name, ">");
                  $limit_length = max(Toolbox::strlen($name) - $pos,
                                      $_SESSION["glpidropdown_chars_limit"]);

                  if (Toolbox::strlen($name)>$limit_length) {
                     $name = "&hellip;".Toolbox::substr($name, -$limit_length);
                  }

               } else {
                  $limit_length = Toolbox::strlen($name);
               }

            } else {
               $limit_length = $_SESSION["glpidropdown_chars_limit"];
            }
         }
      }

      // Manage entity_sons
      if (!($params['entity']<0) && $params['entity_sons']) {
         if (is_array($params['entity'])) {
            echo "entity_sons options is not available with array of entity";
         } else {
            $params['entity'] = getSonsOf('glpi_entities',$params['entity']);
         }
      }

      $use_ajax = false;
      if ($CFG_GLPI["use_ajax"]) {
         $nb = 0;

         if ($item->isEntityAssign()) {
            if (!($params['entity']<0)) {
               $nb = countElementsInTableForEntity($table, $params['entity'], $params['condition']);
            } else {
               $nb = countElementsInTableForMyEntities($table, $params['condition']);
            }

         } else {
            $nb = countElementsInTable($table, $params['condition']);
         }

         $nb -= count($params['used']);
         
         //TODO : temporarily disable ajax for display references
         //MUST to find other solution
         /*if ($nb>$CFG_GLPI["ajax_limit_count"]) {
            $use_ajax = true;
         }*/
      }

      $param = array('searchText'           => '__VALUE__',
                      'value'               => $params['value'],
                      'itemtype'            => $itemtype,
                      'myname'              => $params['name'],
                      'limit'               => $limit_length,
                      'toadd'               => $params['toadd'],
                      'comment'             => $params['comments'],
                      'rand'                => $params['rand'],
                      'entity_restrict'     => $params['entity'],
                      'update_item'         => $params['toupdate'],
                      'used'                => $params['used'],
                      'on_change'           => $params['on_change'],
                      'condition'           => $params['condition'],
                      'emptylabel'          => $params['emptylabel'],
                      'display_emptychoice' => $params['display_emptychoice'],
                      'displaywith'         => $params['displaywith'],
                      'display_rootentity'  => $params['display_rootentity']);

      $default  = "<select name='".$params['name']."' id='dropdown_".$params['name'].
                    $params['rand']."'>";
      $default .= "<option value='".$params['value']."'>$name</option></select>";
      Ajax::dropdown($use_ajax, "/ajax/dropdownValue.php", $param, $default, $params['rand']);

      // Display comment
      if ($params['comments']) {
         $options_tooltip = array('contentid' => "comment_".$params['name'].$params['rand']);

         if ($item->canView()
            && $params['value'] && $item->getFromDB($params['value'])
            && $item->canViewItem()) {

            $options_tooltip['link']       = $item->getLinkURL();
            $options_tooltip['linktarget'] = '_blank';
         }

         Html::showToolTip($comment,$options_tooltip);

         if (($item instanceof CommonDropdown)
              && $item->canCreate()
              && !isset($_GET['popup'])) {

               echo "<img alt='' title=\"".$LANG['buttons'][8]."\" src='".$CFG_GLPI["root_doc"].
                     "/pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'
                     onClick=\"var w = window.open('".$item->getFormURL()."?popup=1&amp;rand=".
                     $params['rand']."' ,'glpipopup', 'height=400, ".
                     "width=1000, top=100, left=100, scrollbars=yes' );w.focus();\">";
         }

      }

      return $params['rand'];
   }

   function showReferencesFromSupplier($ID){
      global $LANG, $DB, $CFG_GLPI;

      $query = "SELECT `gr`.`id`, `gr`.`manufacturers_id`, `gr`.`entities_id`, `gr`.`itemtype`,
                       `gr`.`name`, `grm`.`price_taxfree` 
               FROM `glpi_plugin_order_references_suppliers` AS grm, `".$this->getTable()."` AS gr 
               WHERE `grm`.`suppliers_id` = '$ID' 
                  AND `grm`.`plugin_order_references_id` = `gr`.`id`"
               .getEntitiesRestrictRequest(" AND ", "gr", '', '', true);
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
            echo Dropdown::getDropdownName("glpi_entities", $data["entities_id"]);
            echo "</td>";

            echo "<td>";
            echo Dropdown::getDropdownName("glpi_manufacturers", $data["manufacturers_id"]);
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

         $link = Toolbox::getItemTypeFormURL('PluginOrderOrder');
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

      
      //If reference is not visible in the target entity : transfer it!
      if(!countElementsInTableForEntity($this->getTable(), $entity, "`id`='".$this->getID()."'")) {
         $input                = $this->fields;
         $input['entities_id'] = $entity;
         $oldref               = $input['id'];
         unset($input['id']);
         $input['transfert']   = 1;
         $newid=$this->add($input);
         
         $reference_supplier       = new PluginOrderReference_Supplier();
         $reference_supplier->getFromDBByReference($oldref);
         $input = $reference_supplier->fields;
         $input['entities_id']                = $entity;
         $input['plugin_order_references_id'] = $newid;
         unset($input['id']);
         $reference_supplier->add($input);
         
         $PluginOrderOrder_Item = new PluginOrderOrder_Item();
         
         $query = "SELECT `id` FROM `glpi_plugin_order_orders_items`
                   WHERE `plugin_order_references_id` = '$oldref' ";
         
         $result = $DB->query($query);
         $num    = $DB->numrows($result);
         if ($num) {
            while ($dataref=$DB->fetch_array($result)) {
               $values["id"] = $dataref['id'];
               $values["plugin_order_references_id"] = $newid;
               $PluginOrderOrder_Item->update($values);
            }
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
      echo "<div align='center'>";
      echo self::getPerTypeJavascriptCode();
      echo "<a onclick='order_window.show();' href='#modal_reference_content' title='".
             $LANG['plugin_order']['reference'][11]."'>".
             $LANG['plugin_order']['reference'][11]."</a>";
      echo "</div>";

   }

   static function getPerTypeJavascriptCode() {
      global $LANG, $CFG_GLPI;
      
      $out = "<script type='text/javascript'>";
      $out.= "cleanhide('modal_reference_content');";
      $out.= "var order_window=new Ext.Window({
         layout:'fit',
         width:800,
         height:400,
         closeAction:'hide',
         modal: true,
         autoScroll: true,
         title: \"".$LANG['plugin_order']['reference'][11]."\",
         autoLoad: '".$CFG_GLPI['root_doc']."/plugins/order/ajax/referencetree.php'
      });";
      $out.= "</script>";
      return $out;
   }
   
   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         //Install
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references` (
               `id` int(11) NOT NULL auto_increment,
               `entities_id` int(11) NOT NULL default '0',
               `is_recursive` tinyint(1) NOT NULL default '0',
               `name` varchar(255) collate utf8_unicode_ci default NULL,
               `manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
               `types_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtypes tables (id)',
               `models_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemmodels tables (id)',
               `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
               `templates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
               `comment` text collate utf8_unicode_ci,
               `is_deleted` tinyint(1) NOT NULL default '0',
               `notepad` longtext collate utf8_unicode_ci,
               PRIMARY KEY  (`id`),
               KEY `name` (`name`),
               KEY `entities_id` (`entities_id`),
               KEY `manufacturers_id` (`manufacturers_id`),
               KEY `types_id` (`types_id`),
               KEY `models_id` (`models_id`),
               KEY `templates_id` (`templates_id`),
               KEY `is_deleted` (`is_deleted`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die ($DB->error());
            
      } else {
         //Upgrade
         $migration->displayMessage("Upgrading $table");
         
         //1.1.0
         $migration->changeField($table, "FK_manufacturer", "FK_glpi_enterprise", "int(11) NOT NULL DEFAULT '0'");
         
         ///1.2.0
         $migration->changeField($table, "ID", "id", "int(11) NOT NULL auto_increment");
         $migration->changeField($table, "FK_entities", "entities_id", 
                                 "int(11) NOT NULL default '0'");
         $migration->changeField($table, "recursive", "is_recursive", 
                                 "tinyint(1) NOT NULL default '0'");
         $migration->changeField($table, "name", "name", 
                                 "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->changeField($table, "FK_glpi_enterprise", "manufacturers_id", 
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)'");
         $migration->changeField($table, "FK_type", "types_id", 
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtypes tables (id)'");
         $migration->changeField($table, "FK_model", "models_id", 
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemmodels tables (id)'");
         $migration->changeField($table, "type", "itemtype", 
                                 "varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file'");
         $migration->changeField($table, "template", "templates_id", 
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)'");
         $migration->changeField($table, "comments", "comment",
                                 "text collate utf8_unicode_ci");
         $migration->changeField($table, "deleted", "is_deleted",
                                 "tinyint(1) NOT NULL default '0'");
         $migration->addField($table, "notepad", "longtext collate utf8_unicode_ci");

         $migration->addKey($table, "name");
         $migration->addKey($table, "entities_id");
         $migration->addKey($table, "manufacturers_id");
         $migration->addKey($table, "types_id");
         $migration->addKey($table, "models_id");
         $migration->addKey($table, "templates_id");
         $migration->addKey($table, "is_deleted");
         $migration->migrationOneTable($table);

         Plugin::migrateItemType(array(3151 => 'PluginOrderReference'),
                                 array("glpi_bookmarks", "glpi_bookmarks_users", 
                                       "glpi_displaypreferences", "glpi_documents_items", 
                                       "glpi_infocoms", "glpi_logs", "glpi_tickets"));

         Plugin::migrateItemType(array(), array(), array($table));

         //1.3.0
         $DB->query("UPDATE `glpi_plugin_order_references`
                    SET `itemtype`='ConsumableItem' 
                    WHERE `itemtype` ='Consumable'") or die ($DB->error());
         $DB->query("UPDATE `glpi_plugin_order_references`
                    SET `itemtype`='CartridgeItem' 
                    WHERE `itemtype` ='Cartridge'") or die ($DB->error());
                    
         //Displayprefs
            
         $prefs = array(1 => 1, 2 => 4, 4 => 5, 5 => 9, 6 => 6, 7 => 7);
         foreach ($prefs as $num => $rank) {
            if (!countElementsInTable("glpi_displaypreferences", 
                                       "`itemtype`='PluginOrderReference' AND `num`='$num' 
                                          AND `rank`='$rank' AND `users_id`='0'")) {
               $DB->query("INSERT INTO glpi_displaypreferences 
                           VALUES (NULL,'PluginOrderReference','$num','$rank','0');") 
                  or die($DB->error());
            }
         }
      }
   }
   
   static function uninstall() {
      global $DB;

      $table  = getTableForItemType(__CLASS__);
      foreach (array ("glpi_displaypreferences", "glpi_documents_items", "glpi_bookmarks",
                       "glpi_logs") as $t) {
         $query = "DELETE FROM `$t` WHERE `itemtype`='".__CLASS__."'";
         $DB->query($query);
      }

      $DB->query("DROP TABLE IF EXISTS `$table`") or die ($DB->error());
   }
}

?>