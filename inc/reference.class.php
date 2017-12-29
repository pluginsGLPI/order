<?php
/*
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
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderReference extends CommonDBTM {

   public static $rightname         = 'plugin_order_reference'; //'plugin_order_reference'; //TODO : A développer

   public $dohistory                = true;

   public static $forward_entity_to = ['PluginOrderReference_Supplier'];


   public static function getTypeName($nb = 0) {
      return __("Product reference", "order");
   }


   public function cleanDBonPurge() {
      $temp = new PluginOrderReference_Supplier();
      $temp->deleteByCriteria([
         'plugin_order_references_id' => $this->fields['id']
      ]);
   }


   public function getSearchOptions() {
      $tab['common']            = __("Product reference", "order");

      $tab[1]['table']          = self::getTable();
      $tab[1]['field']          = 'name';
      $tab[1]['name']           = __("Reference");
      $tab[1]['datatype']       = 'itemlink';
      $tab[1]['checktype']      = 'text';
      $tab[1]['displaytype']    = 'text';
      $tab[1]['injectable']     = true;

      $tab[2]['table']          = self::getTable();
      $tab[2]['field']          = 'comment';
      $tab[2]['name']           = __("Comments");
      $tab[2]['datatype']       = 'text';
      $tab[2]['checktype']      = 'text';
      $tab[2]['displaytype']    = 'multiline_text';
      $tab[2]['injectable']     = true;

      $tab[3]['table']          = self::getTable();
      $tab[3]['field']          = 'itemtype';
      $tab[3]['name']           = __("Item type");
      $tab[3]['datatype']       = 'itemtypename';
      $tab[3]['massiveaction']  = false;
      $tab[3]['itemtype_list']  = 'plugin_order_types';
      $tab[3]['checktype']      = 'itemtype';
      $tab[3]['searchtype']     = ['equals'];
      $tab[3]['injectable']     = true;

      $tab[4]['table']          = self::getTable();
      $tab[4]['field']          = 'models_id';
      $tab[4]['name']           = __("Model");
      $tab[4]['massiveaction']  = false;
      $tab[4]['checktype']      = 'text';
      $tab[4]['displaytype']    = 'reference_model';
      $tab[4]['injectable']     = true;
      $tab[4]['massiveaction']  = false;
      $tab[4]['nosearch']       = true;

      $tab[5]['table']          = 'glpi_manufacturers';
      $tab[5]['field']          = 'name';
      $tab[5]['name']           = __("Manufacturer");
      $tab[5]['datatype']       = 'dropdown';
      $tab[5]['checktype']      = 'text';
      $tab[5]['displaytype']    = 'dropdown';
      $tab[5]['injectable']     = true;

      $tab[6]['table']          = self::getTable();
      $tab[6]['field']          = 'types_id';
      $tab[6]['name']           = __("Type");
      $tab[6]['massiveaction']  = false;
      $tab[6]['checktype']      = 'text';
      $tab[6]['injectable']     = true;
      $tab[6]['searchtype']     = ['equals'];
      $tab[6]['nosearch']       = true;

      $tab[7]['table']          = self::getTable();
      $tab[7]['field']          = 'templates_id';
      $tab[7]['name']           = __("Template name");
      $tab[7]['massiveaction']  = false;
      $tab[7]['checktype']      = 'text';
      $tab[7]['displaytype']    = 'dropdown';
      $tab[7]['injectable']     = true;
      $tab[7]['massiveaction'] = false;
      $tab[7]['nosearch']       = true;

      $tab[30]['table']         = self::getTable();
      $tab[30]['field']         = 'id';
      $tab[30]['name']          = __("ID");
      $tab[30]['massiveaction'] = false;
      $tab[30]['injectable']    = false;

      $tab[31]['table']         = self::getTable();
      $tab[31]['field']         = 'is_active';
      $tab[31]['name']          = __("Active");
      $tab[31]['datatype']      = 'bool';
      $tab[31]['checktype']     = 'bool';
      $tab[31]['displaytype']   = 'bool';
      $tab[31]['injectable']    = true;
      $tab[31]['searchtype']    = ['equals'];

      $tab[32]['table']         = 'glpi_plugin_order_references_suppliers';
      $tab[32]['field']         = 'price_taxfree';
      $tab[32]['name']          = __("Unit price tax free", "order");
      $tab[32]['forcegroupby']  = true;
      $tab[32]['usehaving']     = true;
      $tab[32]['massiveaction'] = false;
      $tab[32]['joinparams']    = ['jointype' => 'child'];
      $tab[32]['datatype']      = 'decimal';

      $tab[33]['table']         = 'glpi_plugin_order_references_suppliers';
      $tab[33]['field']         = 'reference_code';
      $tab[33]['name']          = __("Manufacturer's product reference", "order");
      $tab[33]['forcegroupby']  = true;
      $tab[33]['usehaving']     = true;
      $tab[33]['massiveaction'] = false;
      $tab[33]['joinparams']    = ['jointype' => 'child'];

      $tab[34]['table']         = 'glpi_suppliers';
      $tab[34]['field']         = 'name';
      $tab[34]['name']          = __("Supplier");
      $tab[34]['datatype']      = 'itemlink';
      $tab[34]['itemlink_type'] = 'Supplier';
      $tab[34]['forcegroupby']  = true;
      $tab[34]['usehaving']     = true;
      $tab[34]['massiveaction'] = false;
      $tab[34]['joinparams']    = [
         'beforejoin' => [
            'table'      => 'glpi_plugin_order_references_suppliers',
            'joinparams' => ['jointype' => 'child']
         ]
      ];

      $tab[35]['table']         = self::getTable();
      $tab[35]['field']         = 'date_mod';
      $tab[35]['massiveaction'] = false;
      $tab[35]['name']          = __("Last update");
      $tab[35]['datatype']      = 'datetime';

      /* entity */
      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';
      $tab[80]['injectable']    = false;

      $tab[86]['table']         = self::getTable();
      $tab[86]['field']         = 'is_recursive';
      $tab[86]['name']          = __("Child entities");
      $tab[86]['datatype']      = 'bool';
      $tab[86]['checktype']     = 'text';
      $tab[86]['displaytype']   = 'dropdown';
      $tab[86]['injectable']    = true;
      $tab[86]['searchtype']    = ['equals'];

      return $tab;
   }


   public function post_getEmpty() {
      $this->fields['is_active'] = 1;
   }


   public function prepareInputForAdd($input) {
      global $DB;

      if (!isset($input["name"]) || $input["name"] == '') {
         Session::addMessageAfterRedirect(__("Cannot create reference without a name", "order"), false, ERROR);
         return false;
      }

      if (!$input["itemtype"]) {
         Session::addMessageAfterRedirect(__("Cannot create reference without a type", "order"), false, ERROR);
         return false;
      }

      if (!isset($input["transfert"])
            && countElementsInTable(self::getTable(),
                                    "`name` = '".$input["name"]."'
                                     AND `entities_id` = '".$input["entities_id"]."'")) {
         Session::addMessageAfterRedirect(__("A reference with the same name still exists", "order"), false, ERROR);
         return false;
      }

      return $input;
   }


   public function pre_deleteItem() {
      if (!$this->referenceInUse()) {
         return true;
      } else {
         Session::addMessageAfterRedirect(__("Reference(s) in use", "order"), true, ERROR);
         return false;
      }
   }


   public function referenceInUse() {
      global $DB;

      $number = countElementsInTable("glpi_plugin_order_orders_items",
                                     "`plugin_order_references_id` = '".$this->fields["id"]."'");
      if ($number > 0) {
         return true;
      } else {
         return false;
      }
   }


   public function getReceptionReferenceLink($data) {
      $link = Toolbox::getItemTypeFormURL($this->getType());

      if (self::canView()) {
         return "<a href=\"".$link."?id=".$data["id"]."\">".$data["name"]."</a>";
      } else {
         return $data['name'];
      }
   }


   public function defineTabs($options = array()) {
      $ong = [];
      if (!$this->isNewItem()) {
         $this->addDefaultFormTab($ong);
         $this->addStandardTab('PluginOrderReference_Supplier', $ong, $options);
         // $this->addStandardTab('PluginOrderReference', $ong,$options);
         $this->addStandardTab('Document_Item', $ong, $options);
         $this->addStandardTab('Note', $ong, $options);
         $this->addStandardTab('Log', $ong, $options);
      }
      return $ong;
   }


   public function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if (get_class($item) == __CLASS__) {
         return [1 => __("Linked orders", "order")];
      }
      return '';
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == __CLASS__) {
         $item->showOrders($item);
      }
      return true;
   }


   public function dropdownTemplate($name, $entity, $table, $value = 0) {
      global $DB;

      $query = "SELECT `template_name`, `id`
                FROM `$table`
                WHERE `entities_id` = '$entity'
                AND `is_template` = '1'
                AND `template_name` <> ''
                GROUP BY `template_name`
                ORDER BY `template_name`";

      $option[0] = Dropdown::EMPTY_VALUE;
      foreach ($DB->request($query) as $data) {
         $option[$data["id"]] = $data["template_name"];
      }
      return Dropdown::showFromArray($name, $option, ['value'  => $value]);
   }


   public function getTemplateName($itemtype, $ID) {
      if ($ID) {
         $item = new $itemtype();
         $item->getFromDB($ID);
         return $item->getField("template_name");
      } else {
         return false;
      }
   }


   public function checkIfTemplateExistsInEntity($detailID, $itemtype, $entity) {
      global $DB;

      $table = self::getTable();
      $query = "SELECT ref.`templates_id`
                FROM `glpi_plugin_order_orders_items` item, `$table` ref
                WHERE item.`plugin_order_references_id` = ref.`id`
                AND item.`id` = '$detailID';";
      $result = $DB->query($query);

      if (!$DB->numrows($result)) {
         return 0;
      } else {
         $item = new $itemtype();
         $item->getFromDB($DB->result($result, 0, "templates_id"));
         if ($item->getField('entities_id') == $entity
               || ($item->maybeRecursive()
               && $item->fields['is_recursive']
               && Session::haveAccessToEntity($entity, true))) {
            return $item->getField('id');
         } else {
            //Workaround when templates are not recursive (ie computers, monitors, etc.)
            //If templates have the same name in several entities : search for a template with
            //the same name
            if ($item->getField('template_name') != NOT_AVAILABLE) {
               $query = "SELECT `id` FROM `".$item->getTable()."`
                         WHERE `entities_id`='$entity'
                            AND `template_name`='".$item->fields['template_name']."'
                               AND `is_template`='1'";
               $result_template = $DB->query($query);
               if ($DB->numrows($result_template) >= 1) {
                  return $DB->result($result_template, 0, "id");
               } else {
                  return 0;
               }
            } else {
               return 0;
            }
         }
      }
   }


   public function dropdownAllItems($options = array()) {
      global $DB, $CFG_GLPI;

      $p['myname']       = '';
      $p['value']        = "";
      $p['orders_id']    = 0;
      $p['suppliers_id'] = 0;
      $p['entity']       = 0;
      $p['ajax_page']    = '';
      $p['filter']       = '';
      $p['class']        = '';
      $p['span']         = '';

      foreach ($options as $key => $value) {
         $p[$key] = $value;
      }

      $types = PluginOrderOrder_Item::getClasses();

      echo "<select name='".$p['myname']."' id='".$p['myname']."'>";
      echo "<option value='0' selected>".Dropdown::EMPTY_VALUE."</option>\n";

      if ($p['filter']) {
         $used  = [];
         $table = self::getTable();
         $query = "SELECT `itemtype`
                   FROM `$table` as t
                   LEFT JOIN `glpi_plugin_order_references_suppliers` as s
                     ON (`t`.`id` = `s`.`plugin_order_references_id`)
                  WHERE `s`.`suppliers_id` = '{$p['suppliers_id']}'"
                 .getEntitiesRestrictRequest("AND", 't', '', $p['entity'], true);
         $result = $DB->query($query);

         $number = $DB->numrows($result);
         if ($number) {
            while ($data = $DB->fetch_array($result)) {
               $used[] = $data["itemtype"];
            }
         }

         foreach ($types as $tmp => $itemtype) {
            if (!in_array($itemtype, $used)) {
               unset($types[$tmp]);
            }
         }
      }

      foreach ($types as $type) {
         $item = new $type();
         echo "<option value='".$type."' ";
         if (isset($p['value']) && $p['value'] == $type) {
            echo "selected";
         }
         echo " >".$item->getTypeName()."</option>\n";
      }

      echo "</select>";

      $params = [
         'itemtype'        => '__VALUE__',
         'suppliers_id'    => $p['suppliers_id'],
         'entity_restrict' => $p['entity'],
         'orders_id'       => $p['orders_id'],
         'span'            => 'show_quantity',
      ];

      if ($p['class'] != 'PluginOrderOrder_Item') {
         foreach (["types_id", "models_id", "templates_id"] as $field) {
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


   public function showForm($id, $options = array()) {
      global $CFG_GLPI, $DB;

      $this->initForm($id, $options);
      $reference_in_use = !$id ? false : $this->referenceInUse();

      // $this->showTabs($options);
      $this->showFormHeader($options);

      if (isset($options['popup'])) {
         echo Html::hidden('popup', ['value' => $options['popup']]);
      }
      if (!isset($options['item']) || empty($options['item'])) {
         $options['item'] = $this->fields["itemtype"];
      }

      echo "<tr class='tab_bg_1'><td>".__("Name")."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td rowspan='2'>".__("Comments")."</td>";
      echo "<td rowspan='2'>";
      echo "<textarea cols='50' rows='3' name='comment'>".$this->fields["comment"] .
            "</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__("Active")."</td>";
      echo "<td>";
      Dropdown::showYesNo('is_active', $this->fields['is_active']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__("Manufacturer")."</td>";
      echo "<td>";
      Manufacturer::Dropdown(['value' => $this->fields['manufacturers_id']]);
      echo "</td>";
      echo "<td>".__("Manufacturer reference", "order")."</td>";
      echo "<td>";
      echo Html::autocompletionTextField($this, 'manufacturers_reference');
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__("Item type");
      // Mandatory dropdown :
      if ($id <= 0) {
         echo " <span class='red'>*</span>";
      }
      echo "</td>";
      echo "<td>";
      if ($id > 0) {
         $itemtype = $this->fields["itemtype"];
         $item     = new $itemtype();
         echo $item->getTypeName();
         echo Html::hidden('itemtype', ['value' => $itemtype]);
      } else {
         $this->dropdownAllItems([
            'myname'    => 'itemtype',
            'value'     => $options["item"],
            'entity'    => $_SESSION["glpiactive_entity"],
            'ajax_page' => $CFG_GLPI["root_doc"].'/plugins/order/ajax/referencespecifications.php',
            'class'     => __CLASS__,
         ]);
      }
      echo "</td>";

      echo "<td>".__("Type")."</td>";
      echo "<td>";
      echo "<span id='show_types_id'>";
      if ($options['item']) {
         if ($options['item'] == 'PluginOrderOther') {
            $file = 'other';
         } else {
            $file = $options['item'];
         }

         $itemtypeclass = $options['item']."Type";
         if (class_exists($itemtypeclass)) {
            if (!$reference_in_use) {
               Dropdown::show($itemtypeclass, [
                  'name'  => "types_id",
                  'value' => $this->fields["types_id"],
               ]);
            } else {
               echo Dropdown::getDropdownName($itemtypeclass::getTable(), $this->fields["types_id"]);
            }
         }
      }
      echo "</span>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__("Model")."</td>";
      echo "<td>";
      echo "<span id='show_models_id'>";
      if ($options['item']) {
         if (class_exists($itemtypeclass)) {
            Dropdown::show($options['item']."Model", [
               'name'  => "models_id",
               'value' => $this->fields["models_id"],
            ]);
         }
      }
      echo "</span>";
      echo "</td>";

      echo "<td>".__("Template name")."</td>";
      echo "<td>";
      echo "<span id='show_templates_id'>";
      if (!empty($options['item'])
         && $DB->fieldExists($options['item']::getTable(), 'is_template')) {
         $this->dropdownTemplate('templates_id', $this->fields['entities_id'],
                                 $options['item']::getTable(),
                                 $this->fields['templates_id']);
      }
      echo "</span>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__("Last update")."</td>";
      echo "<td>";
      echo Html::convDateTime($this->fields["date_mod"]);
      echo "</td>";
      echo "<td colspan='2'></td></tr>";

      $options['canedit'] = true;
      $this->showFormButtons($options);
      Html::closeForm();
      return true;
   }


   /**
    * Permet l'affichage dynamique d'une liste d�roulante imbriquee
    *
    * @static
    * @param array ($itemtype,$options)
    */
   public static function dropdownReferencesByEnterprise($itemtype, $options = array()) {
      global $DB,$CFG_GLPI;

      if ($itemtype && !($item = getItemForItemtype($itemtype))) {
         return false;
      }

      $table = $item->getTable();

      $params['comments']    = true;
      $params['condition']   = '';
      $params['entity']      = -1;
      $params['name']        = "reference";
      $params['value']       = 0;
      $params['entity_sons'] = false;
      $params['rand']        = mt_rand();
      $params['used']        = [];
      $params['table']       = $table;
      $params['emptylabel']  = Dropdown::EMPTY_VALUE;

      //specific
      $params['action']       = "";
      $params['itemtype']     = "";
      $params['span']         = "";
      $params['orders_id']    = 0;
      $params['suppliers_id'] = 0;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $name         = $params['emptylabel'];
      $comment      = "";
      $limit_length = $_SESSION["glpidropdown_chars_limit"];

      if (strlen($params['value']) == 0 || !is_numeric($params['value'])) {
         $params['value'] = 0;
      }

      if ($params['value'] > 0) {
         $tmpname = Dropdown::getDropdownName($table, $params['value'], 1);

         if ($tmpname["name"] != "&nbsp;") {
            $name    = $tmpname["name"];
            $comment = $tmpname["comment"];

            if (Toolbox::strlen($name) > $_SESSION["glpidropdown_chars_limit"]) {
               if ($item instanceof CommonTreeDropdown) {
                  $pos          = strrpos($name, ">");
                  $limit_length = max(Toolbox::strlen($name) - $pos,
                                      $_SESSION["glpidropdown_chars_limit"]);

                  if (Toolbox::strlen($name) > $limit_length) {
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
      if (!($params['entity'] < 0) && $params['entity_sons']) {
         if (is_array($params['entity'])) {
            echo "entity_sons options is not available with array of entity";
         } else {
            $params['entity'] = getSonsOf('glpi_entities', $params['entity']);
         }
      }

      $use_ajax = false;
      if ($CFG_GLPI["use_ajax_autocompletion"]) {
         $nb = 0;

         $query = "SELECT COUNT(*) AS cpt
                   FROM `$table` as t
                   LEFT JOIN `glpi_plugin_order_references_suppliers` as s
                      ON (`t`.`id` = `s`.`plugin_order_references_id`)
                   WHERE `s`.`suppliers_id` = '{$params['suppliers_id']}'
                   AND `t`.`itemtype` = '{$params['itemtype']}'";

         if ($item->isEntityAssign()) {
            if (!($params['entity'] < 0)) {
               $query .= getEntitiesRestrictRequest("AND", 't', '', $params['entity'], true);
            } else {
               $query .= getEntitiesRestrictRequest("AND", 't', '', '', true);
            }
         }

         $result = $DB->query($query);
         if ($DB->numrows($result) == 1) {
            $nb = $DB->result($result, 0, "cpt");
         }
         $nb -= count($params['used']);

         if ($nb > $CFG_GLPI["ajax_limit_count"]) {
            $use_ajax = true;
         }
      }

      $param = [
         'searchText'      => '__VALUE__',
         'value'           => $params['value'],
         'itemtype'        => $params['itemtype'],
         'myname'          => $params['name'],
         'limit'           => $limit_length,
         'comment'         => $params['comments'],
         'rand'            => $params['rand'],
         'entity_restrict' => $params['entity'],
         'used'            => $params['used'],
         'condition'       => $params['condition'],
         'table'           => $params['table'],
         //specific
         'action'          => $params['action'],
         'span'            => $params['span'],
         'orders_id'       => $params['orders_id'],
         'suppliers_id'    => $params['suppliers_id'],
      ];

      $default  = "<select name='".$params['name']."' id='dropdown_".$params['name'].$params['rand']."'>";
      $default .= "<option value='".$params['value']."'>$name</option></select>";

      Ajax::Dropdown($use_ajax, "/plugins/order/ajax/dropdownValue.php", $param, $default, $params['rand']);

      // Display comment
      if ($params['comments']) {
         $options_tooltip = [
            'contentid' => "comment_".$param['myname'].$params['rand']
         ];

         if ($params['value'] && $item->getFromDB($params['value'])) {
            $options_tooltip['link']       = $item->getLinkURL();
            $options_tooltip['linktarget'] = '_blank';
         }

         Html::showToolTip($comment, $options_tooltip);

         if ($itemtype::canCreate() && !isset($_GET['popup'])) {
            echo "<img alt='' title=\"".__("Add")."\" src='".$CFG_GLPI["root_doc"]."
               /pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'
               onClick=\"var w = window.open('".$item->getFormURL()."?popup=1&amp;rand=" .
               $params['rand']."&amp;itemtype=".$params['itemtype']."&amp;entities_id=" .
               $params['entity']."', "."'glpipopup', 'height=400,width=1000, top=100, left=100,
               scrollbars=yes' );w.focus();\">";
         }
      }

      return $params['rand'];
   }


   public function dropdownAllItemsByType($name, $itemtype, $entity = 0, $types_id = 0,
                                          $models_id = 0) {
      switch ($itemtype) {
         case 'CartridgeItem':
         case 'ConsumableItem':
         case 'SoftwareLicense':
            $fk        = getForeignkeyFieldForItemType($itemtype."Type");
            $condition = "`$fk` = '$types_id'";
            $rand      = Dropdown::show($itemtype, [
               'condition'   => $condition,
               'name'        => $name,
               'entity'      => $entity,
               'displaywith' => ['ref'],
            ]);
            break;

         default:
            $item = new $itemtype();
            $and  = "";
            if (class_exists($itemtype."Type", false)) {
               $and .= $types_id != 0 ? " AND `".
                    getForeignKeyFieldForTable(getTableForItemType($itemtype."Type"))."` = '$types_id' " : "";
            }
            if (class_exists($itemtype."Model", false)) {
               $and .= $models_id != 0 ? " AND `".
                    getForeignKeyFieldForTable(getTableForItemType($itemtype."Model"))."` = '$models_id' " : "";
            }
            if ($item->maybeTemplate()) {
               $and .= " AND `is_template` = 0 ";
            }
            if ($item->maybeDeleted()) {
               $and .= " AND `is_deleted` = 0 ";
            }

            $table = $itemtype::getTable();
            $condition  = "1 $and AND `$table`.`id` NOT IN ";
            $condition .= "(SELECT `items_id` FROM `glpi_plugin_order_orders_items`
                           WHERE `itemtype`='$itemtype' AND `items_id`!='0')";
            $rand = Dropdown::show($itemtype, [
               'condition'   => $condition,
               'name'        => $name,
               'entity'      => $entity,
               'comments'    => true,
               'displaywith' => ['serial', 'otherserial'],
            ]);
            break;
      }
      return $rand;
   }


   public function showOrders($ref) {
      global $DB;

      $order = new PluginOrderOrder();
      $query = "SELECT `glpi_plugin_order_orders_items`.*
                FROM `glpi_plugin_order_orders_items`
                LEFT JOIN `glpi_plugin_order_references`
                   ON (`glpi_plugin_order_references`.`id` = `glpi_plugin_order_orders_items`.`plugin_order_references_id`)
                WHERE `plugin_order_references_id` = '".$ref->getID()."'";
      $query .= getEntitiesRestrictRequest(" AND ", "glpi_plugin_order_references", "entities_id", $ref->fields["entities_id"], true);
      $query .= " GROUP BY `glpi_plugin_order_orders_items`.`plugin_order_orders_id`
               ORDER BY `entities_id`, `name` ";

      $result = $DB->query($query);
      $nb     = $DB->numrows($result);

      echo "<div class='center'>";
      if ($nb) {
         if (isset($_REQUEST["start"])) {
            $start = $_REQUEST["start"];
         } else {
            $start = 0;
         }
         $query_limit = $query." LIMIT ".intval($start).",".intval($_SESSION['glpilist_limit']);

         Html::printAjaxPager(__("Linked orders", "order"), $start, $nb);
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th>".__("Name")."</a></th>";
         echo "<th>".__("Entity")."</th>";
         echo "</tr>";

         foreach ($DB->request($query_limit) as $data) {
            echo "<tr class='tab_bg_1' align='center'>";
            echo "<td>";
            $order->getFromDB($data['plugin_order_orders_id']);
            echo $order->getLink(PluginOrderOrder::canView());
            echo "</td>";

            echo "<td>";
            echo Dropdown::getDropdownName("glpi_entities", $order->fields["entities_id"]);
            echo "</td>";

            echo "</tr>";
         }
         echo "</table>";
      } else {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><td class='center'>".__("No item to display")."</td></tr>";
         echo "</table>";
      }

      echo "</div>";
   }


   public function transfer($ID, $entity) {
      global $DB;

      if ($ID <= 0 || !$this->getFromDB($ID)) {
         return 0;
      }

      //If reference is not visible in the target entity : transfer it!
      if (!countElementsInTableForEntity(self::getTable(), $entity, "`id`='".$this->getID()."'")) {
         $input                = $this->fields;
         $input['entities_id'] = $entity;
         $oldref               = $input['id'];
         unset($input['id']);
         $input['transfert']   = 1;
         $newid = $this->add($input);

         $reference_supplier   = new PluginOrderReference_Supplier();
         $reference_supplier->getFromDBByReference($oldref);
         $input = $reference_supplier->fields;
         $input['entities_id']                = $entity;
         $input['plugin_order_references_id'] = $newid;
         unset($input['id']);
         $reference_supplier->add($input);

         $PluginOrderOrder_Item = new PluginOrderOrder_Item();

         $query = "SELECT `id`
                   FROM `glpi_plugin_order_orders_items`
                   WHERE `plugin_order_references_id` = '$oldref'";

         $result = $DB->query($query);
         $num    = $DB->numrows($result);
         if ($num) {
            while ($dataref = $DB->fetch_array($result)) {
               $values["id"]                         = $dataref['id'];
               $values["plugin_order_references_id"] = $newid;
               $PluginOrderOrder_Item->update($values);
            }
         }
      }
   }


   public function copy($ID) {
      $source = new self();
      $source->getFromDB($ID);

      $target = clone $source;
      unset($target->fields['id']);
      $target->fields['name'] = __("Copy of", "order").' '.$target->fields['name'];
      $target->fields = Toolbox::addslashes_deep($target->fields);
      $newID = $this->add($target->fields);

      foreach (getAllDatasFromTable('glpi_plugin_order_references_suppliers',
                                     "`plugin_order_references_id`='$ID'") as $refsup) {
         $reference_supplier = new  PluginOrderReference_Supplier();
         $refsup['plugin_order_references_id'] = $newID;
         unset($refsup['id']);
         $reference_supplier->add($refsup);
      }
      return true;
   }


   /**
    * Display entities of the loaded profile
    *
   * @param $myname select name
    * @param $target target for entity change action
    */
   public static function showSelector($target) {
      global $CFG_GLPI;

      $rand = mt_rand();
      Plugin::loadLang('order');
      echo "<div class='center' ><span class='b'>".__("Select the wanted item type", "order")
        ."</span><br>";
      echo "<a style='font-size:14px;' href='".$target."?reset=reset' title=\""
        .__("Show all")."\">".str_replace(" ", "&nbsp;", __("Show all"))."</a></div>";

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


   public function title() {
      global $CFG_GLPI;

      echo "<div align='center'>";
      echo self::getPerTypeJavascriptCode();
      echo "<a onclick='order_window.show();' href='#modal_reference_content' title='"
           .__("View by item type", "order")."'>"
           .__("View by item type", "order")."</a>";
      echo "</div>";

   }


   public static function getPerTypeJavascriptCode() {
      global $CFG_GLPI;

      $out  = "<script type='text/javascript'>";
      $out .= "cleanhide('modal_reference_content');";
      $out .= "var order_window=new Ext.Window({
         layout:'fit',
         width:800,
         height:400,
         closeAction:'hide',
         modal: true,
         autoScroll: true,
         title: \"".__("View by item type", "order")."\",
         autoLoad: '".$CFG_GLPI['root_doc']."/plugins/order/ajax/referencetree.php'
      });";
      $out .= "</script>";
      return $out;
   }


   /**
    * Get the standard massive actions which are forbidden
    *
    * @since version 0.84
    *
    * @return an array of massive actions
    **/
   public function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      global $UNINSTALL_TYPES;

      switch ($ma->getAction()) {
         case 'transfert':
            Entity::dropdown();
            echo "&nbsp;".
                  Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
         case 'copy_reference':
            //useless ?
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
                     _sx('button', 'Post')."\" >";
            return true;
      }
      return "";
   }


   function getSpecificMassiveActions($checkitem=null) {

      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin) {
         if (Session::haveRight('transfer', READ)
             && Session::isMultiEntitiesMode()) {
            $actions['PluginOrderReference:transfert'] = __('Transfer');
         }
         $actions['PluginOrderReference:copy_reference'] = __("Copy reference", "order");
      }

      return $actions;
   }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      global $CFG_GLPI;

      switch ($ma->getAction()) {
         case "transfert":
            $input = $ma->getInput();
            $entities_id = $input['entities_id'];

            foreach ($ids as $id) {
               if ($item->getFromDB($id)) {
                  $item->update([
                     "id" => $id,
                     "entities_id" => $entities_id,
                     "update" => __('Update'),
                  ]);
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               }
            }
            return;
               break;
         case "copy_reference":
            foreach ($ids as $id) {
               if ($item->getFromDB($id)) {
                  $item->copy($id);
               }
               $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
            }
            return;
               break;
      }
      return;
   }


   /*   function getValueToSelect($field_id_or_search_options, $name = '', $values = '', $options = array()) {
      if (isset($field_id_or_search_options['displaytype'])
         && $field_id_or_search_options['displaytype'] == 'reference_type') {
            Toolbox::logDebug($field_id_or_search_options['displaytype'],
                              $field_id_or_search_options, $name, $values, $options);

      }
      return false;
   }*/

   public static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();
      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         //Install
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references` (
               `id` int(11) NOT NULL auto_increment,
               `entities_id` int(11) NOT NULL default '0',
               `is_recursive` tinyint(1) NOT NULL default '0',
               `name` varchar(255) collate utf8_unicode_ci default NULL,
               `manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
               `manufacturers_reference` varchar(255) collate utf8_unicode_ci NOT NULL DEFAULT '',
               `types_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtypes tables (id)',
               `models_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemmodels tables (id)',
               `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
               `templates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
               `comment` text collate utf8_unicode_ci,
               `is_deleted` tinyint(1) NOT NULL default '0',
               `is_active` tinyint(1) NOT NULL default '1',
               `notepad` longtext collate utf8_unicode_ci,
               `date_mod` datetime default NULL,
               PRIMARY KEY  (`id`),
               KEY `name` (`name`),
               KEY `entities_id` (`entities_id`),
               KEY `manufacturers_id` (`manufacturers_id`),
               KEY `types_id` (`types_id`),
               KEY `models_id` (`models_id`),
               KEY `templates_id` (`templates_id`),
               KEY `is_active` (`is_active`),
               KEY `is_deleted` (`is_deleted`),
               KEY date_mod (date_mod)
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
         $migration->addField($table, "is_active", "TINYINT(1) NOT NULL DEFAULT '1'");
         $migration->addField($table, "date_mod", "datetime");

         $migration->addKey($table, "name");
         $migration->addKey($table, "entities_id");
         $migration->addKey($table, "manufacturers_id");
         $migration->addKey($table, "types_id");
         $migration->addKey($table, "models_id");
         $migration->addKey($table, "templates_id");
         $migration->addKey($table, "is_deleted");
         $migration->addKey($table, "is_active");
         $migration->addKey($table, "date_mod");
         $migration->migrationOneTable($table);

         Plugin::migrateItemType([3151 => 'PluginOrderReference'],
                                 ["glpi_savedsearches", "glpi_savedsearches_users",
                                  "glpi_displaypreferences", "glpi_documents_items",
                                  "glpi_infocoms", "glpi_logs"]);

         if ($DB->fieldExists('glpi_tickets', 'itemtype')) {
            Plugin::migrateItemType([3151 => 'PluginOrderReference'], ["glpi_tickets"]);
         }

         Plugin::migrateItemType([], [], [$table]);

         //1.3.0
         $DB->query("UPDATE `glpi_plugin_order_references` SET
                        `itemtype`='ConsumableItem'
                     WHERE `itemtype` ='Consumable'") or die ($DB->error());
         $DB->query("UPDATE `glpi_plugin_order_references` SET
                        `itemtype`='CartridgeItem'
                     WHERE `itemtype` ='Cartridge'") or die ($DB->error());

         //1.7.0
         $migration->addField($table, "date_mod", "DATETIME NULL");
         $migration->addKey($table, "date_mod");

         //Displayprefs
         $prefs = [1 => 1, 2 => 4, 4 => 5, 5 => 9, 6 => 6, 7 => 7];
         foreach ($prefs as $num => $rank) {
            if (!countElementsInTable("glpi_displaypreferences",
                                       "`itemtype`='PluginOrderReference' AND `num`='$num'
                                          AND `users_id`='0'")) {
               $DB->query("INSERT INTO glpi_displaypreferences
                           VALUES (NULL,'PluginOrderReference','$num','$rank','0');");
            }
         }

         //Fix error naming field
         if ($DB->fieldExists($table, 'manufacturer_reference')) {
            $migration->changeField($table, "manufacturer_reference", "manufacturers_reference",
                                    "varchar(255) collate utf8_unicode_ci NOT NULL DEFAULT ''");
            $migration->migrationOneTable($table);
         }

         //2.0.1
         if (!$DB->fieldExists($table, 'manufacturers_reference')) {
            $migration->addField($table, "manufacturers_reference",
                                    "varchar(255) collate utf8_unicode_ci NOT NULL DEFAULT ''");
            $migration->migrationOneTable($table);
         }
      }
   }


   public static function uninstall() {
      global $DB;

      $table  = self::getTable();
      foreach (["glpi_displaypreferences", "glpi_documents_items", "glpi_savedsearches",
                "glpi_logs"] as $t) {
         $query = "DELETE FROM `$t` WHERE `itemtype`='".__CLASS__."'";
         $DB->query($query);
      }

      $DB->query("DROP TABLE IF EXISTS `$table`") or die ($DB->error());
   }


}
