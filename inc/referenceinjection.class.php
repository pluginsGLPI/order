<?php

/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2023 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderReferenceInjection extends PluginOrderReference
                                    implements PluginDatainjectionInjectionInterface {


   public function __construct() {
      $this->table = getTableForItemType(get_parent_class($this));
   }


   /**
    * Returns the name of the table used to store this object parent
    *
    * @return string (table name)
   **/
   static function getTable($classname = null) {

      $parenttype = get_parent_class();
      return $parenttype::getTable();
   }


   public function isPrimaryType() {
      return true;
   }


   public function connectedTo() {
      return [];
   }


   public function addOrUpdateObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }


   public function getOptions($primary_type = '') {
      return Search::getOptions(get_parent_class($this));
   }


   public function getSpecificFieldValue($itemtype, $searchOption, $field, &$values) {
      $value = $values[$itemtype][$field];
      switch ($searchOption['displaytype']) {
         case "reference_itemtype":
            unset($values[$itemtype]['itemtype']);
            $classes = PluginOrderOrder_Item::getClasses();
            if (in_array($value, $classes)) {
               $value[$itemtype]['itemtype'] = $value;
            } else {
               foreach ($classes as $class) {
                  if (call_user_func([$class, 'getTypeName']) == $value) {
                     $values[$itemtype]['itemtype'] = $class;
                     break;
                  }
               }
            }
            break;
         case "reference_model" :
         case "reference_type" :
            if ($searchOption['displaytype'] == 'reference_model') {
               $type_prefix = 'Model';
            } else {
               $type_prefix = 'Type';
            }

            if (isset($values[$itemtype]['itemtype']) && class_exists($values[$itemtype]['itemtype'])) {
               $itemtype_formodel = $values[$itemtype]['itemtype'].$type_prefix;
               if (class_exists($itemtype_formodel)) {
                  $values[$itemtype][$field] = Dropdown::getDropdownName($itemtype_formodel::getTable(), $value);
               }
            }
            break;
      }
      return $value;
   }


}
