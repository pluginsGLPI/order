<?php
/*
 * @version $Id$
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

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

if (isset($_POST['action'])) {
   $order_item = new PluginOrderOrder_Item();
   switch ($_POST['chooseAction']) {
      case 'bill':
      case 'state':
         if (isset ($_POST["item"])) {
            foreach ($_POST["item"] as $key => $val) {
               if ($val == 1) {
                  $tmp       = $_POST;
                  $tmp['id'] = $key;
                  $order_item->update($tmp);
               }
            }
         }
         break;
   }
   PluginOrderOrder::updateBillState($order_item->fields['plugin_order_orders_id']);
   Html::redirect($_SERVER["HTTP_REFERER"]);
}
$dropdown = new PluginOrderBill();
include (GLPI_ROOT . "/front/dropdown.common.form.php");

?>