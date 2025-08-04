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


Session::checkLoginUser();

$config = PluginOrderConfig::getConfig();
$PluginOrderOrder = new PluginOrderOrder();

if ($config->canGenerateOrderPDF() && ($PluginOrderOrder->canGenerateWithoutValidation() || $PluginOrderOrder->canGenerate())) {
    $criteria = ['id' => $_GET['id']] + getEntitiesRestrictCriteria(
        getTableForItemType(PluginOrderOrder::class),
        '',
        '',
        true
    );

    if ($PluginOrderOrder->getFromDBByCrit($criteria)) {
        $PluginOrderOrder->generateOrder($_GET);
    } else {
        Html::displayRightError("You don't have permission to perform this action.");
    }
} else {
    Html::displayRightError("PDF export for Order plugin is not enable");
}
