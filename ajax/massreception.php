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

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$PluginOrderReception = new PluginOrderReception();

echo "<table width='950px' class='tab_cadre_fixe'>";
echo "<tr class='tab_bg_2'><td>".__("Delivery date")."</td><td>";
Html::showDateFormItem("delivery_date",date("Y-m-d"),true,1);
echo "</td><td>";
echo __("Delivery form")."</td><td>";
echo "<input type='text' name='delivery_number' size='20'>";
echo "</td><td>";
echo "<input type='hidden' name='plugin_order_references_id' value='".
   $_POST['plugin_order_references_id']."'>";
echo "<input type='hidden' name='plugin_order_orders_id' value='".
   $_POST['plugin_order_orders_id']."'>";
echo __("Number to deliver", "order")."</td><td>";
$nb = $PluginOrderReception->checkItemStatus($_POST['plugin_order_orders_id'],
                                             $_POST['plugin_order_references_id'], 
                                             PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED);
Dropdown::showInteger('number_reception','',1,$nb);
echo "</td><td>";
echo __("Delivery status", "order")."&nbsp;";
PluginOrderDeliveryState::Dropdown(array('name' => "plugin_order_deliverystates_id"));
echo "</td>";
echo "<td><input type='submit' name='bulk_reception' class='submit' value='".
   _sx('button', 'Post')."'></td></tr></table>";

Html::ajaxFooter();

?>