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
include_once ("../../../inc/includes.php");
Session::checkCentralAccess();
//     If a budget is selected display location name from budget's locations_id...
// ... Else, if no budget was selected and a payment_locations_id is set,
//     display locations list with location's name pre-selected from payment_locations_id ...
// ... Else, display locations list with EMPTY_VALUE pre-selected
if (isset($_POST['budgets_id']) &&
          $_POST['budgets_id'] !== '' &&
          $_POST['budgets_id'] !== '0') {
  // Get selected budget
  $budget = new Budget();
  $budget->getFromDB($_POST['budgets_id']);
  // Get selected budget's location
  $locations_id = $budget->fields["locations_id"];
  //echo "<input type='hidden' name='payment_locations_id' value='$locations_id' />";
  //echo Dropdown::getDropdownName("glpi_locations",  $budget->fields["locations_id"]);

  Location::Dropdown(array(
           'name'   => "payment_locations_id",
           'value'  => $budget->fields["locations_id"],
           'entity' => $_POST['entities_id'],
           'rand'   => $_POST['rand'],
  ));


} elseif (isset($_POST['payment_locations_id']) &&
                $_POST['payment_locations_id'] !== '' &&
                $_POST['payment_locations_id'] > 0) {
        Location::Dropdown(array(
           'name'   => "payment_locations_id",
           'value'  => $_POST['payment_locations_id'],
           'entity' => $_POST['entities_id'],
           'rand'   => $_POST['rand'],
        ));
} else {
  Location::Dropdown(array(
     'name'   => "payment_locations_id",
     'value'  => Dropdown::EMPTY_VALUE,
     'entity' => 0,
     'rand'   => $_POST['rand'],
  ));
}