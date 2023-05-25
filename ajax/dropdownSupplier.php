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

if (strpos($_SERVER['PHP_SELF'], "dropdownSupplier.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkCentralAccess();

// Make a select box
if (isset($_POST["suppliers_id"])) {

   // Make a select box
   $query = "SELECT c.`id`, c.`name`, c.`firstname`
             FROM `glpi_contacts` c
             LEFT JOIN `glpi_contacts_suppliers` s ON (s.`contacts_id` = c.`id`)
             WHERE s.`suppliers_id` = '{$_POST['suppliers_id']}'
             ORDER BY c.`name`";
   $result = $DB->query($query);
   $number = $DB->numrows($result);

   $values = [0 => Dropdown::EMPTY_VALUE];
   if ($number) {
      while ($data = $DB->fetchAssoc($result)) {
         $values[$data['id']] = formatUserName('', '', $data['name'], $data['firstname']);
      }
   }
   Dropdown::showFromArray($_POST['fieldname'], $values);
}
