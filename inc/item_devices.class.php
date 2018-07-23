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
  along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderItem_Devices extends Item_Devices {
   public static $rightname = 'plugin_order_order';


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      self::showForItem($item, $withtemplate);
      return true;
   }

   static function showForItem(CommonGLPI $item, $withtemplate=0) {
      global $CFG_GLPI;

      $is_device = ($item instanceof CommonDevice);

      $ID = $item->getField('id');

      if (!$item->can($ID, READ)) {
         return false;
      }

      $canedit = (($withtemplate != 2)
                  && $item->canEdit($ID)
                  && Session::haveRightsOr('device', array(UPDATE, PURGE)));
      echo "<div class='spaced'>";
      $rand = mt_rand();
      if ($canedit) {
         echo "\n<form id='form_device_add$rand' name='form_device_add$rand'
                  action='".$CFG_GLPI['root_doc']."/front/item_devices.form.php' method='post'>\n";
         echo "\t<input type='hidden' name='items_id' value='$ID'>\n";
         echo "\t<input type='hidden' name='itemtype' value='".$item->getType()."'>\n";
      }

      $table = new HTMLTableMain();

      $table->setTitle(_n('Component', 'Components', Session::getPluralNumber()));
      if ($canedit) {
         $delete_all_column = $table->addHeader('delete all',
                                                Html::getCheckAllAsCheckbox("form_device_action$rand",
                                                '__RAND__'));
         $delete_all_column->setHTMLClass('center');
      } else {
         $delete_all_column = NULL;
      }

      $column_label    = ($is_device ? _n('Item', 'Items', Session::getPluralNumber()) : __('Type of component'));
      $common_column   = $table->addHeader('common', $column_label);
      $specific_column = $table->addHeader('specificities', __('Specificities'));
      $specific_column->setHTMLClass('center');

      $dynamic_column = '';
      if ($item->isDynamic()) {
         $dynamic_column = $table->addHeader('is_dynamic', __('Automatic inventory'));
         $dynamic_column->setHTMLClass('center');
      }

      if ($canedit) {
         $massiveactionparams = array('container'     => "form_device_action$rand",
                                      'fixed'         => false,
                                      'display_arrow' => false);
         $content = array(array('function'   => 'Html::showMassiveActions',
                                'parameters' => array($massiveactionparams)));
         $delete_column = $table->addHeader('delete one', $content);
         $delete_column->setHTMLClass('center');
      } else {
         $delete_column = NULL;
      }

      $table_options = array('canedit' => $canedit,
                             'rand'    => $rand);

      if ($is_device) {
         Session::initNavigateListItems(static::getType(),
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));
         foreach (array_merge(array(''), self::getConcernedItems()) as $itemtype) {
            $table_options['itemtype'] = $itemtype;
            $link                      = getItemForItemtype(static::getType());

            $link->getTableGroup($item, $table, $table_options, $delete_all_column,
                                 $common_column, $specific_column, $delete_column,
                                 $dynamic_column);
         }
      } else {
         $devtypes = array();
         foreach (self::getItemAffinities($item->fields['itemtype']) as $link_type) {
            $devtypes [] = $link_type::getDeviceType();
            $link        = getItemForItemtype($link_type);

            Session::initNavigateListItems($link_type,
                                           sprintf(__('%1$s = %2$s'),
                                                   $item->getTypeName(1), $item->getName()));
            $link->getTableGroup($item, $table, $table_options, $delete_all_column,
                                 $common_column, $specific_column, $delete_column,
                                 $dynamic_column);
         }
      }

      if ($canedit) {
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_1'><td>";
         echo __('Add a new component')."</td><td class=left width='70%'>";
         if ($is_device) {
            Dropdown::showNumber('number_devices_to_add', array('value' => 0,
                                                                'min'   => 0,
                                                                'max'   => 10));
         } else {
            Dropdown::showSelectItemFromItemtypes(array('itemtype_name'       => 'devicetype',
                                                        'items_id_name'       => 'devices_id',
                                                        'itemtypes'           => $devtypes,
                                                        'entity_restrict'     => $item->getEntityID(),
                                                        'showItemSpecificity' => $CFG_GLPI['root_doc']
                                                                 .'/ajax/selectUnaffectedOrNewItem_Device.php'));
         }
         echo "</td><td>";
         echo "<input type='submit' class='submit' name='add' value='"._sx('button', 'Add')."'>";
         echo "</td></tr></table>";
         Html::closeForm();
      }

      if ($canedit) {
         echo "\n<form id='form_device_action$rand' name='form_device_action$rand'
                  action='".$CFG_GLPI['root_doc']."/front/item_devices.form.php' method='post'>\n";
         echo "\t<input type='hidden' name='items_id' value='$ID'>\n";
         echo "\t<input type='hidden' name='itemtype' value='".$item->getType()."'>\n";
      }

      $table->display(array('display_super_for_each_group' => false,
                            'display_title_for_each_group' => false));

      if ($canedit) {
         Html::closeForm();
      }

      echo "</div>";
      // Force disable selected items
      $_SESSION['glpimassiveactionselected'] = array();
   }


}
