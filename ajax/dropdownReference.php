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

/** @var DBmysql $DB */
global $DB;

include_once(__DIR__ . "/../../../inc/includes.php");

Session::checkRight("plugin_order_reference", READ);

// Make a select box for references
if (isset($_POST["itemtype"])) {
    $criteria = [
        'SELECT' => [
            's.plugin_order_references_id AS id',
            's.price_taxfree',
            's.reference_code',
            'r.name',
        ],
        'FROM' => 'glpi_plugin_order_references_suppliers AS s',
        'LEFT JOIN' => [
            'glpi_plugin_order_references AS r' => [
                'ON' => [
                    's' => 'plugin_order_references_id',
                    'r' => 'id',
                ],
            ],
        ],
        'WHERE' => [
            's.suppliers_id' => $_POST['suppliers_id'],
            'r.itemtype' => $_POST['itemtype'],
            'r.is_active' => 1,
            'r.is_deleted' => 0,
        ] + getEntitiesRestrictCriteria('r', '', $_POST['entities_id'], true),
        'ORDER' => ['s.reference_code'],
    ];
    $result = $DB->request($criteria);
    $number = count($result);
    $values = [0 => Dropdown::EMPTY_VALUE];
    if ($number !== 0) {
        foreach ($result as $data) {
            $values[$data['id']] = $data['name'] . " - " . $data['reference_code'];
        }
    }

    Dropdown::showFromArray(
        $_POST['fieldname'],
        $values,
        ['rand'  => $_POST['rand'], 'width' => '100%'],
    );
    Ajax::updateItemOnSelectEvent(
        'dropdown_plugin_order_references_id' . $_POST['rand'],
        'show_priceht',
        '../ajax/dropdownReference.php',
        [
            'reference_id' => '__VALUE__',
            'suppliers_id' => $_POST['suppliers_id'],
        ],
    );
} elseif (isset($_POST['reference_id'])) {
    // Get price
    $criteria = [
        'SELECT' => ['price_taxfree'],
        'FROM' => 'glpi_plugin_order_references_suppliers',
        'WHERE' => [
            'plugin_order_references_id' => $_POST['reference_id'],
            'suppliers_id' => $_POST['suppliers_id'],
        ],
    ];
    $result = $DB->request($criteria);
    $row = $result->current();
    $price = $row['price_taxfree'];
    $price = Html::formatNumber($price, true);
    echo sprintf("<input value='%s' type='number' class='form-control' step='", $price) . PLUGIN_ORDER_NUMBER_STEP . "' name='price' class='decimal' min='0' />";
}
