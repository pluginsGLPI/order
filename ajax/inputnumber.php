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

use function Safe\preg_match;

/** @file
* @brief
*/



header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

if (isset($_POST['name'])) {
    $step = 1;
    if (!isset($_REQUEST['force_integer'])) {
        if (isset($_REQUEST['step'])) {
            $step = (float)$_REQUEST['step'];
        } else {
            $step = PLUGIN_ORDER_NUMBER_STEP;
        }
    }

    $class = "";
    if (isset($_REQUEST['class'])) {
        $class = "class='" . $_REQUEST['class'] . "'";
    }

    $min = 0;
    if (isset($_REQUEST['min'])) {
        $min = !empty($_REQUEST['force_integer']) ? (int) $_REQUEST['min'] : (float) $_REQUEST['min'];
    }

    $data = htmlescape(rawurldecode(stripslashes($_POST["data"])));

    // Validation et fallback
    $name  = preg_match('/^[a-zA-Z0-9_\-]+$/', $_POST['name']) !== 0 ? $_POST['name'] : 'default_name';

    // Ces variables existent déjà et ne sont pas null, donc pas besoin de ??=
    $value = $data;
    $step  = $step;
    $min   = $min;
    $class = $class;

    // Échappement pour HTML (caster les nombres en string)
    $name  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    $step  = htmlspecialchars((string) $step, ENT_QUOTES, 'UTF-8');
    $min   = htmlspecialchars((string) $min, ENT_QUOTES, 'UTF-8');
    $class = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');

    // Affichage
    echo "<input type='number' class='form-control' step='{$step}' min='{$min}' name='{$name}' value='{$value}' {$class}>";

}
