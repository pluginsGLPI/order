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

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkCentralAccess();

$PluginOrderReference = new PluginOrderReference();

if ($_POST["itemtype"]) {
   switch ($_POST["field"]) {
      case "types_id":
         if (class_exists($_POST["itemtype"].'Type')) {
            Dropdown::show($_POST["itemtype"]."Type", ['name' => "types_id"]);
         }
         break;
      case "models_id":
         if (class_exists($_POST["itemtype"].'Model')) {
            Dropdown::show($_POST["itemtype"] . "Model", ['name' => "models_id"]);
         } else {
            return "";
         }
         break;
      case "templates_id":
         $item = new $_POST['itemtype']();
         if ($item->maybeTemplate()) {
            $table = getTableForItemType($_POST["itemtype"]);
            $PluginOrderReference->dropdownTemplate("templates_id", $_POST["entity_restrict"], $table);
         } else {
            return "";
         }
         break;
   }
} else {
   return '';
}
