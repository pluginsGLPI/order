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

include_once ("../../../inc/includes.php");

Session::checkCentralAccess();

// Make a select box for references
if (isset($_POST["itemtype"])) {
   $entity_restrict = getEntitiesRestrictRequest("AND", 'r', '', $_POST['entities_id'], 1);
   $query = "SELECT s.`plugin_order_references_id` as id, s.`price_taxfree`, s.`reference_code`, r.`name`
             FROM `glpi_plugin_order_references_suppliers` s
             LEFT JOIN `glpi_plugin_order_references` r
             ON (s.`plugin_order_references_id` = r.`id`
               AND  r.`is_active` = 1
               AND  r.`is_deleted` = 0)
             WHERE s.`suppliers_id` = '{$_POST['suppliers_id']}'
             AND r.`itemtype` = '{$_POST['itemtype']}'
             $entity_restrict
             ORDER BY s.`reference_code`";
   $result = $DB->query($query);
   $number = $DB->numrows($result);
   $values = [0 => Dropdown::EMPTY_VALUE];
   if ($number) {
      while ($data = $DB->fetchAssoc($result)) {
         $values[$data['id']] = $data['name']." - ".$data['reference_code'];
      }
   }
   Dropdown::showFromArray($_POST['fieldname'], $values,
                           ['rand'  => $_POST['rand'], 'width' => '100%']);
   Ajax::updateItemOnSelectEvent('dropdown_plugin_order_references_id' . $_POST['rand'],
                                 'show_priceht',
                                 '../ajax/dropdownReference.php',
                                 [
                                    'reference_id' => '__VALUE__',
                                    'suppliers_id' => $_POST['suppliers_id'],
                                 ]);

} else if (isset($_POST['reference_id'])) {
   // Get price
   $query = "SELECT `price_taxfree`
             FROM `glpi_plugin_order_references_suppliers`
             WHERE `plugin_order_references_id` = '{$_POST['reference_id']}' AND suppliers_id = '{$_POST['suppliers_id']}'";
   $result = $DB->query($query);
   $price = $DB->result($result, 0, 'price_taxfree');
   $price = Html::formatNumber($price, true);
   echo "<input value='$price' type='number' class='form-control' step='".PLUGIN_ORDER_NUMBER_STEP."' name='price' class='decimal' min='0' />";
}
