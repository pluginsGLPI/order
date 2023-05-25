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

include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");

Html::header_nocache();

Session::checkLoginUser();

$PluginOrderReception = new PluginOrderReception();

echo "<table width='950px' class='tab_cadre_fixe'>";
echo "<tr class='tab_bg_2'><td>" . __("Delivery date") . "</td><td>";
Html::showDateField("delivery_date", [
   'value'      => date("Y-m-d"),
   'maybeempty' => true,
   'canedit'    => true
]);
echo "</td><td>";
echo __("Delivery form") . "</td><td>";
echo "<input type='text' name='delivery_number' size='20'>";
echo "</td><td>";
echo Html::hidden('plugin_order_references_id', ['value' => $_POST["plugin_order_references_id"]]);
echo Html::hidden('plugin_order_orders_id', ['value' => $_POST["plugin_order_orders_id"]]);
echo __("Number to deliver", "order") . "</td><td width='10%'>";
$nb = $PluginOrderReception->checkItemStatus($_POST['plugin_order_orders_id'],
                                             $_POST['plugin_order_references_id'],
                                             PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED);
Dropdown::showNumber('number_reception', [
   'value' => '',
   'min'   => 1,
   'max'   => $nb
]);
echo "</td><td>";
echo __("Delivery status", "order") . "&nbsp;";
PluginOrderDeliveryState::Dropdown(['name' => "plugin_order_deliverystates_id"]);
echo "</td></tr>";

echo "<tr class='tab_bg_2'>";
$config = PluginOrderConfig::getConfig();
if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK) {
   echo "<td>". __("Enable automatic generation", "order") . "</td>";
   echo "<td>";
   Dropdown::showYesNo("manual_generate", $config->canGenerateAsset());
   echo "</td><td>" . __("Default name", "order") . "</td>";
   echo "<td>&nbsp;";
   Html::autocompletionTextField($config, "generated_name");
   echo "</td>&nbsp;&nbsp;";

   echo "<td>" . __("Default serial number", "order") . "</td>";
   echo "<td>&nbsp;";
   Html::autocompletionTextField($config, "generated_serial");
   echo "</td>&nbsp;&nbsp;";

   echo "<td>" . __("Default inventory number", "order") . "</td>";
   echo "<td>&nbsp;";
   Html::autocompletionTextField($config, "generated_otherserial");
   echo "</td>";
}
echo "<td><input type='submit' name='bulk_reception' class='submit' value='"
      . _sx('button', 'Post') . "'></td></tr></table>";

Html::ajaxFooter();
