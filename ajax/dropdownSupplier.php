<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
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

if (strpos($_SERVER['PHP_SELF'],"dropdownSupplier.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkCentralAccess();

// Make a select box

if (isset($_POST["suppliers_id"])) {

   $rand=$_POST['rand'];

   $use_ajax=false;
   if ($CFG_GLPI["use_ajax"] &&
      countElementsInTable('glpi_suppliers',
                           "`glpi_suppliers`.`id` = '".$_POST["suppliers_id"]."' ".
                              getEntitiesRestrictRequest("AND", "glpi_suppliers","",
                                                         $_POST["entity_restrict"],true)) >
                                                            $CFG_GLPI["ajax_limit_count"]){
      $use_ajax = true;
   }

   $paramssuppliers_id=array('searchText'    => '__VALUE__',
                             'suppliers_id'   => $_POST["suppliers_id"],
                             'entity_restrict'=> $_POST["entity_restrict"],
                             'rand'           => $_POST['rand'],
                             'myname'         => $_POST['myname']);
   
   $default="<select name='".$_POST["myname"]."'><option value='0'>".Dropdown::EMPTY_VALUE.
               "</option></select>";
   Ajax::Dropdown($use_ajax,"/plugins/order/ajax/dropdownContact.php", $paramssuppliers_id, $default,
                  $rand);

}

?>