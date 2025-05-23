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

include("../../../inc/includes.php");
Session::checkLoginUser();

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}
if (!isset($_GET["plugin_order_references_id"])) {
    $_GET["plugin_order_references_id"] = "";
}

$PluginOrderReference_Supplier = new PluginOrderReference_Supplier();

if (isset($_POST["add"])) {
    if (PluginOrderReference_Supplier::canCreate()) {
        if (isset($_POST["suppliers_id"]) && $_POST["suppliers_id"] > 0) {
            $PluginOrderReference_Supplier->add($_POST);
        }
    }
    Html::redirect($_SERVER['HTTP_REFERER']);
} else if (isset($_POST["update"])) {
    if (PluginOrderReference_Supplier::canCreate()) {
        $PluginOrderReference_Supplier->update($_POST);
    }
    Html::redirect($_SERVER['HTTP_REFERER']);
} else if (isset($_POST["delete"])) {
    if (PluginOrderReference_Supplier::canCreate()) {
        foreach ($_POST["check"] as $ID => $value) {
            $PluginOrderReference_Supplier->delete(["id" => $ID]);
        }
    }
    Html::redirect($_SERVER['HTTP_REFERER']);
} else {
    $PluginOrderReference_Supplier->checkGlobal(READ);
    Html::header(
        __("Supplier for the reference", "order"),
        $_SERVER['PHP_SELF'],
        "management",
        "PluginOrderMenu",
        "references"
    );

   /* load order form */
    $PluginOrderReference_Supplier->display($_GET);

    Html::footer();
}
