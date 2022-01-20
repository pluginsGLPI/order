<?php

/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * Copyright (C) 2009-2022 by the Order Development Team.
 *
 * https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Html::header(
   __("Orders", "order"),
   $_SERVER['PHP_SELF'],
   "management",
   "PluginOrderMenu",
   "order"
);

$order = new PluginOrderOrder();

if (PluginOrderOrder::canView() || Session::haveRight("config", UPDATE)) {
   Search::show("PluginOrderOrder");
} else {
   echo "<div class='center'><br><br><img src=\""
      . $CFG_GLPI["root_doc"] . "/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>" . __("Access denied") . "</b></div>";
}

Html::footer();
