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

include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");

Html::header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

if (isset($_POST["action"])) {
   switch ($_POST["action"]) {
      case "reception":
         echo "</td><td>";
         Html::showDateFormItem("delivery_date", date("Y-m-d"), true, 1);
         echo "<table><tr><td>".__("Delivery form") . "</td>";
         echo "<td><input type='text' name='delivery_number' size='20'></td></tr>";
         echo "<tr><td>".__("Delivery status", "order") . "</td><td>";
         PluginOrderDeliveryState::Dropdown(array('name' => "plugin_order_deliverystates_id"));
         echo "</td></tr>";
         $config = PluginOrderConfig::getConfig();
         if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK) {
            echo "<tr><td>". __("Enable automatic generation", "order") . "</td>";
            echo "<td>";
            $tab = array(PluginOrderConfig::CONFIG_NEVER => __('No'),
                         PluginOrderConfig::CONFIG_YES   => __('Yes'));
            Dropdown::showFromArray('manual_generate', $tab);
            echo "</td></tr>";
            echo "<tr><td>" . __("Default name", "order") . "</td><td>";
            Html::autocompletionTextField($config, "generated_name");
            echo "</td></tr>";
            echo "<tr><td>" . __("Default serial number", "order") . "</td><td>";
            Html::autocompletionTextField($config, "generated_serial");
            echo "</td></tr>";
            echo "<tr><td>" . __("Default inventory number", "order") . "</td><td>";
            Html::autocompletionTextField($config, "generated_otherserial");
            echo "</td></tr>";
         }
         echo "</table><br /><input type='submit' name='reception' class='submit' value='"
            .  _sx('button', 'Post') . "'></td>";
         break;
   }
}
